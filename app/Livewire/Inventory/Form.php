<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Brand;
use App\Helpers\StockMovement;
use Livewire\Component;
use Muhanz\Shoapi\Facades\Shoapi;
use App\Models\ShopeeToken;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderDetail;
use App\Models\ShopeeItem;
use Carbon\Carbon;
use App\Helpers\Format;
use App\Models\Account;
use App\Models\Transaction;
use App\Traits\GeneratesNumber;
use Illuminate\Support\Facades\Log;
use DB;


class Form extends Component
{
    public ?Item $item = null;
    public string $name = '';
    public string $id_brand = '';
    public float $buy_price = 0;
    public float $sell_price = 0;
    public float $margin = 0;
    public array $items = [];
    public ?ShopeeToken $token = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'id_brand' => ['required'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['required', 'string'],
            'items.*.color' => ['required'],
            'items.*.size' => ['required', 'numeric', 'min:1'],
            'items.*.store_stock' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_brand' => 'Brand name is required',
            'items.*.sku' => 'SKU item is required',
            'items.*.color' => 'Color item is required',
            'items.*.size' => 'Size item is required',
            'items.*.store_stock' => 'Store Stock item is required',
        ];
    }

    public function mount(?Item $item = null): void
    {
        $this->token = ShopeeToken::where(['user_id' => auth()->id()])->first();   // 3. Assign initial values
        if($this->token?->isExpired()){
            $this->refreshToken(uth()->id(), $this->token->shop_id);
        }

        $this->item = $item;

        if ($item && $item->exists) {
            $this->id_brand = $item->id_brand;
            $this->name = $item->name;
            $this->buy_price = $item->buy_price ?? 0;
            $this->sell_price = $item->sell_price ?? 0;
            $this->margin = $item->margin ?? 0;
            $this->items = $item->inventory->map(fn ($i) => [
                'id' => $i->id,
                'sku' => $i->sku,
                'size' => $i->size,
                'color' => $i->color,
                'store_stock' => $i->store_stock,
                'total_stock' => $i->total_stock,
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['id' => '', 'sku' => '', 'size' => 38, 'color' => 'black', 'store_stock' => 0, 'total_stock' => 0];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
            $this->items = array_values($this->items);
        }
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $isUpdate = false;
            $data = [
                'id_brand' => $this->id_brand,
                'name' => $this->name,
                'buy_price' => $this->buy_price,
                'sell_price' => $this->sell_price ?: 0,
                'margin' => $this->sell_price - $this->buy_price ?: 0
            ];

            if ($this->item && $this->item->exists) {
                $this->item->update($data);
                $isUpdate = true;
            } else {
                $this->item = Item::create($data);
                $isUpdate = false;
            }

            foreach ($this->items as $item) {
                $total_stock = $item['store_stock'];
                $this->item->inventory()->updateOrCreate(
                    ['id' => $item['id'] ?: null],
                    [
                        'sku' => $item['sku'],
                        'color' => $item['color'],
                        'size' => $item['size'] ?? 0,
                        'store_stock' => $item['store_stock'],
                        'warehouse_stock' => 0,
                        'total_stock' => $total_stock ?? 0,
                    ]);
                    if($isUpdate){
                        $inv = ShopeeItem::where('model_sku', $item['sku'])->orderBy('model_sku')->get();
                        $storeStock = $item['store_stock'];
                        foreach ($inv as $si) {
                            $this->updateStockShopee($si?->item_id, $si?->model_id, $storeStock);
                        }
                    }
                }
        });

        session()->flash('success', 'Item berhasil disimpan.');
        $this->redirect(route('items.index'), navigate: true);
    }

    public function getModelDetail($item_id, $model_id){
        $params = [
            'item_id' =>  $item_id
        ];

        $response = Shoapi::call('product')
                ->access('get_model_list', $this->token->access_token)
                ->shop($this->token->shop_id)
                ->request($params)
                ->response();

        $response = Format::parseData($response);

        if($response['api_status'] == 'success'){
            $this->compareStockShopee($response,$item_id,$model_id);
            Log::info("Data model : ". json_encode($response));
            return true;
        }else{
            return false;
        }
    }

    public function compareStockShopee($response, $itemId, $modelId){
        $models = $response['model'];
        $checkModel = Inventory::where(['shopee_item_id' => $itemId, 'model_id' => $modelId])->first();
        foreach ($models as $model) {
            if($model['model_id'] == $modelId && $checkModel?->exists){
                //check last stock in inventory
                $lastStock = $checkModel->store_stock;
                $lastStockShopee = $model['stock_info_v2']['seller_stock'][0]['stock'];
                if($lastStockShopee < 5){
                    $stock_to_update = $lastStock > 5 ? ($lastStockShopee + (5 - $lastStockShopee)) : $lastStock;
                    $this->updateStockShopee($itemId, $modelId, $stock_to_update);
                    $ret = [
                        'item_id' => $itemId,
                        'model_id' => $modelId,
                        'stock_shopee' => $lastStockShopee,
                        'stock_store' => $lastStock,
                        'stock_to_update' => $stock_to_update
                    ];
                    Log::info('Data Stock Updated : '.json_encode($ret));
                }

                Log::info('Data Stock Shopee : '.json_encode(['stock_shopee' =>  $lastStockShopee, 'stock_store' => $lastStock]));
            }
        }
    }

    public function updateStockShopee($itemId, $modelId, $stock){
        $params = [
            "item_id" => (int) $itemId,
            "stock_list" => [
                [
                    "model_id" => (int) $modelId,
                    "seller_stock" => [
                        [
                            "location_id" => "IDZ",
                            "stock" => (int) $stock
                        ]
                    ]
                ]
            ]
        ];

        $response = Shoapi::call('product')
                ->access('update_stock', $this->token->access_token)
                ->shop($this->token->shop_id)
                ->request($params)
                ->response();

        $response = Format::parseData($response);

        Log::info("Response Update Stock : ". json_encode($response));

        if($response['api_status'] == 'success'){
            return true;
        }else{
            Log::error("Error update stock shopee :". json_encode($response));
            return false;
        }
    }

    public function refreshToken($userId, $shopId)
    {
        $token = ShopeeToken::where('user_id', $userId)
            ->where('shop_id', $shopId)
            ->first();

        if (!$token || !$token->isExpired()) { return $token;}

        try {
            $params = [
                'refresh_token' => $token->refresh_token,
                'shop_id' => (int) $shopId,
            ];

            $response = Shoapi::call('auth')
                ->access('refresh_access_token')
                ->shop((int) $shopId)
                ->request($params)
                ->response();

            $response = Format::parseData($response);

            if ($response['api_status'] === 'success') {
                $token->update([
                    'access_token' => $response['access_token'],
                    'refresh_token' => $response['refresh_token'],
                    'expires_in' => $response['expire_in'],
                    'expires_at' => now()->addSeconds($response['expire_in']),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Gagal refresh token: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.inventory.form', [
            'brands' => Brand::orderBy('name')->get(['id','name']),
            'isEditing' => $this->item && $this->item->exists
        ]);
    }
}
