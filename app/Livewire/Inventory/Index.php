<?php

namespace App\Livewire\Inventory;

use App\Models\Item;
use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;
use App\Helpers\StockMovement;
use Muhanz\Shoapi\Facades\Shoapi;
use App\Models\ShopeeToken;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderDetail;
use App\Models\ShopeeItem;
use Carbon\Carbon;
use App\Helpers\Format;
use DB;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public bool $showModal = false;
    public ?Inventory $stockStatus = null;
    public int $stockNow = 0;
    public string $stockSide = '';
    public int $update_stock = 0;
    public bool $isSuccess = false;
    public $token = null;
    public array $models = [];

    public function updatingSearch(): void { $this->resetPage(); }

    public function mount(){
        $this->token = ShopeeToken::where(['user_id' => auth()->id()])->first();
    }

    public function changeStock(int $id, $side){
        $this->showModal = true;
        $this->stockStatus = Inventory::find($id);
        if($side == 'store_stock'){
            $this->stockNow = $this->stockStatus->store_stock;
            $this->stockSide = 'store_stock';
            $this->update_stock = 0;
        }else{
            $this->stockNow = $this->stockStatus->warehouse_stock;
            $this->stockSide = 'warehouse_stock';
            $this->update_stock = 0;
        }
    }

    public function updateChangeStock(int $id, $side){
        $data = DB::transaction(function () use ($id,$side) {
            $inventory = Inventory::find($id);
            if($side == 'store_stock'){
                $stock_before = $inventory->store_stock;
                $inventory->store_stock = $this->update_stock;
                StockMovement::stockLog([
                    'id_inventory' => $inventory->id,
                    'user_id' => auth()->id(),
                    'movement_type' => 'DIRECT SO',
                    'quantity' => $this->update_stock,
                    'quantity_before' => $stock_before,
                    'quantity_after' => $this->update_stock,
                    'reason' => 'DIRECT',
                    'notes' => 'DIRECT'
                ]);
            }else{
                $inventory->warehouse_stock = $this->update_stock;
            }
            if($inventory->save()){
                $this->isSuccess = true;
                $this->showModal = false;
            }
            $this->calTotalStock($id);
        });
    }

    public function calTotalStock($id){
        DB::transaction(function () use ($id) {
            $inventory = Inventory::find($id);
            $stockTotal= $inventory->store_stock + $inventory->warehouse_stock;
            $inventory->total_stock = $stockTotal;
            $inventory->save();
        });
    }

    public function delete(int $id): void
    {
        Item::findOrFail($id)->delete();
        Inventory::findOrFail($id)->delete();
        session()->flash('success', 'Item berhasil dihapus.');
    }

    public function getItemFromShopee(){
        $params =  [
            'offset' => 0,
            'page_size' => 91,
            'item_status' => ['NORMAL']
        ];
        $response = Shoapi::call('product')
                ->access('get_item_list',  $this->token->access_token)
                ->shop($this->token->shop_id)
                ->request($params)
                ->response();

        $response = Format::parseData($response);
        if($response['api_status'] == 'success'){
            $items = collect($response['item'])->pluck('item_id')->toArray();
            $this->getModelList($items);
        }

        if($response['api_status'] == 'error'){
            $this->dispatch('notify', message: 'Error! connect to shopee first', color: 'bg-red-500');
        }
    }

    public function getModelList(array $items){
        foreach ($items as $i => $item) {
            $params =  [
                'item_id' => $item,
            ];
            $response = Shoapi::call('product')
                    ->access('get_model_list',  $this->token->access_token)
                    ->shop($this->token->shop_id)
                    ->request($params)
                    ->response();

            $response = Format::parseData($response);

            if($response['api_status'] == 'success'){
                DB::transaction(function () use ($response, $item) {
                    foreach($response['model'] as $mod){
                        ShopeeItem::updateOrCreate(
                        ['item_id' => $item, 'model_id' => $mod['model_id']],
                        [
                            'item_id' => $item,
                            'model_id' => $mod['model_id'],
                            'model_sku' => $mod['model_sku'],
                            'model_status' => $mod['model_status']
                        ]);
                    }
                });
            }

            if($response['api_status'] == 'error'){
                $this->dispatch('notify', message: 'Error! connect to shopee first', color: 'bg-red-500');
            }
        }

       $this->dispatch('notify', message: 'Get item shopee success', color: 'bg-green-500');
    }

    public function render()
    {
        $items = Item::query()->with('inventory');
        if ($this->search) {
            $s = $this->search;
            $items->where(function($query) use ($s){
                $query->where('name','like',"%{$s}%")
                    ->orWhereHas('inventory', function($query) use ($s){
                        $query->where('sku','like', "%{$s}%");
                    });
            });
        }

        return view('livewire.inventory.index', ['items' => $items->paginate(25)]);
    }
}
