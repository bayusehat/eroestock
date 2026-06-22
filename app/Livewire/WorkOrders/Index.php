<?php

namespace App\Livewire\WorkOrders;

use App\Models\Client;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Muhanz\Shoapi\Facades\Shoapi;
use App\Models\ShopeeToken;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderDetail;
use Carbon\Carbon;
use App\Helpers\Format;
use DB;

class Index extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $clientFilter = '';
    public ?WorkOrder $changingStatusWo = null;
    public string $newStatus = '';
    public array $sn = [];

    protected $queryString = ['search', 'statusFilter', 'clientFilter'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingClientFilter(): void { $this->resetPage(); }

    public function openStatusModal(int $id): void
    {
        $this->changingStatusWo = WorkOrder::find($id);
        $this->newStatus = '';
    }

    public function closeStatusModal(): void
    {
        $this->changingStatusWo = null;
        $this->newStatus = '';
    }

    public function updateStatus(): void
    {
        if (! $this->changingStatusWo || ! $this->newStatus) return;

        $data = ['status' => $this->newStatus];
        if ($this->newStatus === 'completed') {
            $data['completed_date'] = now();
        }

        $this->changingStatusWo->update($data);
        session()->flash('success', "Status diperbarui ke {$this->newStatus}");
        $this->closeStatusModal();
    }

    public function duplicate(int $id): void
    {
        $wo = WorkOrder::with('items')->findOrFail($id);
        $new = $wo->replicate(['wo_number', 'status', 'completed_date']);
        $new->wo_number = \App\Traits\GeneratesNumber::generateNumber('WO', 'work_orders', 'wo_number', 'Y');
        $new->status = 'draft';
        $new->save();
        foreach ($wo->items as $item) {
            $new->items()->create($item->only(['description','quantity','unit','unit_price','discount','tax_rate','subtotal']));
        }
        session()->flash('success', 'Work order duplikat berhasil dibuat.');
    }

    public function getOrderShopee(){
        $token = ShopeeToken::where(['user_id' => auth()->id()])->first();
        $params =  [
            'time_range_field' => 'create_time',
            'time_from' => Carbon::now()->subDays(15)->timestamp,
            'time_to' => Carbon::now()->timestamp,
            'page_size' => 20
        ];
        $response = Shoapi::call('order')
                ->access('get_order_list',  $token->access_token)
                ->shop($token->shop_id)
                ->request($params)
                ->response();

        $response = Format::parseData($response);

        if($response['api_status'] == 'success'){
            foreach ($response['order_list'] as $sn) {
                $this->sn[] = $sn['order_sn'];
            }

            $getOrder = $this->getDetailOrderShopee($this->sn);
            if($getOrder){
                return redirect()->back()->with('success','Berhasil mengambil data order dari Shopee.');
            }else{
                return redirect()->back()->with('error','Gagal mengambil data order dari Shopee.');
            }
        }
        if($response['api_status'] == 'error'){
            return redirect()->back()->with('error','Token Shopee gagal diperbarui');
        }
    }

    public function getDetailOrderShopee(array $order_sn){
        $token = ShopeeToken::where(['user_id' => auth()->id()])->first();
        $params = [
            'order_sn_list' =>  implode(',', $order_sn),
            'response_optional_fields' => 'item_list,total_amount'
        ];

        $response = Shoapi::call('order')
                ->access('get_order_detail', $token->access_token)
                ->shop($token->shop_id)
                ->request($params)
                ->response();

        $response = Format::parseData($response);

        if($response['api_status'] == 'success'){
            //updateOrCreate Order from Shopee
            $this->dumpOrder($response);
            return true;
        }else{
            return false;
        }
    }

    public function dumpOrder($response){
        DB::transaction(function () use ($response){
           foreach ($response['order_list'] as $order_list) {
                $data = [
                    'order_sn' => $order_list['order_sn'],
                    'booking_sn' => $order_list['booking_sn'],
                    'create_time' => $order_list['create_time'],
                    'day_to_ship' => 1,
                    'order_status' => $order_list['order_status'],
                    'ship_by_date' => $order_list['ship_by_date'],
                    'cod' =>  $order_list['cod'],
                    'message_to_seller' => $order_list['message_to_seller'],
                    'total_amount' => $order_list['total_amount'],
                    'flag' => 'shopee'
                ];

                $order = ShopeeOrder::updateOrCreate([
                    'order_sn' => $order_list['order_sn']
                ],
                    $data
                );

                if($order->id){
                    if(!empty($order_list['item_list'])){
                        foreach($order_list['item_list'] as $detail){
                            ShopeeOrderDetail::updateOrCreate([
                                'shopee_order_id' => $order->id,
                                'item_id' => $detail['item_id'],
                            ],[
                                'shopee_order_id' => $order->id,
                                'item_id' => $detail['item_id'],
                                'item_name' => $detail['item_name'],
                                'item_sku' => $detail['item_sku'],
                                'order_item_id' => $detail['order_item_id'],
                                'weight' => $detail['weight'],
                                'active_qty' => $detail['active_qty'],
                                'image_info' => '-',
                                'model_original_price' => $detail['model_original_price'],
                                'model_id' => $detail['model_id'],
                                'model_discounted_price' =>  $detail['model_discounted_price'],
                                'model_quantity_purchased' => $detail['model_quantity_purchased'],
                                'model_sku' => $detail['model_sku']
                            ]);
                        }
                    }
                }
            }
        });

        return true;
    }


    public function render()
    {
        $query = ShopeeOrder::query();

        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('order_sn', 'like', "%{$s}%")->orWhere('total_amount', 'like', "%{$s}%"));
        }
        if ($this->statusFilter) $query->where('order_status', $this->statusFilter);
        // if ($this->clientFilter) $query->where('client_id', $this->clientFilter);

        return view('livewire.work-orders.index', [
            'workOrders' => $query->latest('create_time')->paginate(25),
            'transitions' => [
                'SHIPPED' => ['TO_CONFIRM_RECEIVE', 'CANCELLED'],
                'TO_CONFIRM_RECEIVE' => ['SHIPPED', 'CANCELLED']
            ],
        ]);
    }
    // public function render()
    // {
    //     $query = WorkOrder::query()->with('client:id,name');

    //     if ($this->search) {
    //         $s = $this->search;
    //         $query->where(fn ($q) => $q->where('wo_number', 'like', "%{$s}%")->orWhere('title', 'like', "%{$s}%"));
    //     }
    //     if ($this->statusFilter) $query->where('status', $this->statusFilter);
    //     if ($this->clientFilter) $query->where('client_id', $this->clientFilter);

    //     return view('livewire.work-orders.index', [
    //         'workOrders' => $query->latest('order_date')->paginate(25),
    //         'clients' => Client::orderBy('name')->get(['id','name']),
    //         'transitions' => [
    //             'draft' => ['confirmed', 'cancelled'],
    //             'confirmed' => ['in_progress', 'cancelled'],
    //             'in_progress' => ['completed', 'cancelled'],
    //             'completed' => ['invoiced'],
    //         ],
    //     ]);
    // }
}
