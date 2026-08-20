<?php

namespace App\Livewire\WorkOrders;

use App\Models\Client;
use App\Models\WorkOrder;
use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Muhanz\Shoapi\Facades\Shoapi;
use App\Models\ShopeeToken;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderDetail;
use Carbon\Carbon;
use App\Helpers\Format;
use App\Models\Account;
use App\Models\Transaction;
use App\Traits\GeneratesNumber;
use DB;

class Index extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $clientFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $activeTab = 'shopee';
    public ?WorkOrder $changingStatusWo = null;
    public string $newStatus = '';
    public array $sn = [];

    protected $queryString = ['search', 'statusFilter', 'clientFilter'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingClientFilter(): void { $this->resetPage(); }

    public function setTab(string $tabName): void
    {
        $this->activeTab = $tabName;
    }

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
            'page_size' => 50
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

            $this->getDetailOrderShopee($this->sn);
        }

        if($response['api_status'] == 'error'){
            $this->dispatch('notify', message: 'Error! connect to shopee first', color: 'bg-red-500');
        }
    }

    public function getDetailOrderShopee(array $order_sn){
        $token = ShopeeToken::where(['user_id' => auth()->id()])->first();
        $params = [
            'order_sn_list' =>  implode(',', $order_sn),
            'response_optional_fields' => 'item_list,total_amount,package_list,buyer_username'
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
            session()->flash('success', 'Success get order from Shopee');
            // return true;
        }else{
            session()->flash('error', 'Check your connection to Shopee!');
        }
    }

    public function getEscrowDetail($order){
        $token = ShopeeToken::where(['user_id' => auth()->id()])->first();
        $params = [
            'order_sn' =>  $order->order_sn
        ];

        $response = Shoapi::call('payment')
                ->access('get_escrow_detail', $token->access_token)
                ->shop($token->shop_id)
                ->request($params)
                ->response();

        $response = Format::parseData($response);

        if($response['api_status'] == 'success'){
            //update escrow detail in shopee order
            ShopeeOrder::where('id',$order->id)->update([
                'escrow_amount' => $response['order_income']['escrow_amount'] ?? 0,
                'actual_shipping_fee' => $response['order_income']['actual_shipping_fee'] ?? 0,
                'buyer_transaction_fee' => $response['order_income']['buyer_transaction_fee'] ?? 0,
                'withholding_tax' => $response['order_income']['withholding_tax'] ?? 0,
                'original_price' =>  $response['order_income']['original_price'],
                'service_fee' => $response['order_income']['service_fee'],
                'order_discounted_price' => $response['order_income']['order_discounted_price'],
                'voucher_from_shopee' => $response['order_income']['voucher_from_shopee'],
                'voucher_from_seller' => $response['order_income']['voucher_from_seller'],
                'buyer_payment_method' => $response['order_income']['buyer_payment_method'],
                'order_seller_discount' => $response['order_income']['order_seller_discount'],
                'shopee_voucher' => $response['buyer_payment_info']['shopee_voucher'],
                'buyer_service_fee' => $response['buyer_payment_info']['buyer_service_fee']
            ]);
            $this->dispatch('notify', message: 'Data Shopee grabbed successfully!', color: 'bg-green-500');
        }else{
            return false;
        }
    }

    public function getTrackingNumber($order){
        $token = ShopeeToken::where(['user_id' => auth()->id()])->first();
        $params = [
            'order_sn' =>  $order->order_sn
        ];

        $response = Shoapi::call('logistics')
                ->access('get_tracking_number', $token->access_token)
                ->shop($token->shop_id)
                ->request($params)
                ->response();

        $response = Format::parseData($response);

        if($response['api_status'] == 'success'){
            //update tracking number in shopee order
            ShopeeOrder::where('id',$order->id)->update([
              'tracking_number' => $response['tracking_number']
            ]);
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
                    'escrow_amount' => 0,
                    'actual_shipping_fee' => 0,
                    'buyer_transaction_fee' => 0,
                    'withholding_tax' => 0,
                    'total_amount' => $order_list['total_amount'],
                    'flag' => 'shopee',
                    'buyer_username' => $order_list['buyer_username'],
                    'package_number' => $order_list['package_list'][0]['package_number'],
                    'shipping_carrier' => $order_list['package_list'][0]['shipping_carrier']
                ];

                $order = ShopeeOrder::updateOrCreate([
                    'order_sn' => $order_list['order_sn']
                ],
                    $data
                );

                if($order->id){
                    if(!empty($order_list['item_list'])){
                        foreach($order_list['item_list'] as $detail){
                            $child = ShopeeOrderDetail::updateOrCreate([
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
                            //get payment details
                            $this->getEscrowDetail($order);
                            //logistics
                            $this->getTrackingNumber($order);

                            if($child->wasRecentlyCreated) {
                                $parent = ShopeeOrder::where('id',$child->shopee_order_id)->first();
                                if($parent->order_status <> 'CANCELLED' && $parent->order_status == 'UNPAID'){
                                    $inv = Inventory::where([
                                        'sku' => $child->model_sku
                                    ])->decrement('store_stock', (int) $child->model_quantity_purchased);
                                }
                            }
                            if (!$child->wasRecentlyCreated && $order->wasChanged('order_status')) {
                                $parent = ShopeeOrder::where('id',$child->shopee_order_id)->first();
                                if($parent->order_status == 'CANCELLED'){
                                    Inventory::where([
                                        'sku' => $child->model_sku
                                    ])->increment('store_stock', (int) $child->model_quantity_purchased);
                                }

                                if($parent->order_status == 'COMPLETED'){
                                    $this->addToTransactions($parent);
                                }
                            }
                        }
                    }
                }
            }
        });
        return true;
    }

    public function addToTransactions($order_id){;
        Transaction::create([
            'transaction_no' => GeneratesNumber::generateNumber('TXN', 'transactions', 'transaction_no', 'Y'),
            'type' => 'income', 'date' => Carbon::now()->toDateString(), 'amount' => $order_id->escrow_amount,
            'account_id' => 4, 'contra_account_id' => 22,
            'description' => 'Shopee Order '.$order_id->order_sn ?: null, 'reference_no' => $order_id->order_sn ?: null,
            'payment_method' => 'bank_transfer' ?: null, 'category' => '-' ?: null, 'created_by' => auth()->id()
        ]);
    }

    public function getGeneralSku($sku){
        return substr($sku, 0, strrpos($sku, '-'));
    }

    public function shopeeItemIdtoInventory(){
        $shopeeDetail = ShopeeOrderDetail::select('item_id','model_sku')->distinct()->get();
            DB::transaction(function () use ($shopeeDetail) {
                 foreach ($shopeeDetail as $i => $item) {
                    Inventory::whereRaw("LEFT(sku, LENGTH(sku) - LOCATE('-', REVERSE(sku))) = ?",[ $this->getGeneralSku($item->model_sku)])->update([
                        'shopee_item_id' => $item->item_id
                    ]);
                 }
            });

        $this->shoppeModelIdtoInventory();
        return true;
    }

    public function shoppeModelIdtoInventory(){
        $shopeeDetail = ShopeeOrderDetail::select('item_id','model_id','model_sku')->where('model_sku','<>','')->distinct()->get();
            DB::transaction(function () use ($shopeeDetail) {
                 foreach ($shopeeDetail as $i => $item) {
                    Inventory::where(['shopee_item_id' => $item->item_id, 'sku' => $item->model_sku])
                    ->update([
                        'model_id' => $item->model_id
                    ]);
                 }
            });
        return true;
    }

    public function destroy($id){
        $workOrder = WorkOrder::find($id);
        if($workOrder){
            Transaction::where(['reference_no' => $workOrder->wo_number])->delete();
            $workOrder->delete();
            foreach($workOrder->items as $detail){
                Inventory::where([
                    'id' => $detail->inventory_id
                ])->increment('store_stock', (int) $detail->quantity);
            }
            $workOrder->items()->delete();
            session()->flash('success', 'Order berhasil dihapus.');
        }
        session()->flash('error', 'Order gagal dihapus.');
    }

    public function render()
    {
        //Shopee
        $query = ShopeeOrder::query();

        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('order_sn', 'like', "%{$s}%")->orWhere('total_amount', 'like', "%{$s}%"));
        }
        if ($this->statusFilter) $query->where('order_status', $this->statusFilter);

        if($this->dateFrom && $this->dateTo){
            $query->whereBetween(
                 DB::raw('DATE(FROM_UNIXTIME(create_time))'),
                    [$this->dateFrom, $this->dateTo]
                );
        }

        //Offline
        $offline = WorkOrder::query();

        if ($this->search) {
            $s = $this->search;
            $offline->where(fn ($q) => $q->where('wo_number', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }

        // if ($this->statusFilter) $offline->where('status', $this->statusFilter);

        if($this->dateFrom && $this->dateTo){
            $offline->whereBetween(
                DB::raw('DATE(created_at)'),
                    [$this->dateFrom, $this->dateTo]
            );
        }

        return view('livewire.work-orders.index', [
            'workOrders' => $query->latest('create_time')->paginate(50),
            'offline' => $offline->latest('created_at')->paginate(50),
            'transitions' => [
                'SHIPPED' => ['TO_CONFIRM_RECEIVE', 'CANCELLED'],
                'TO_CONFIRM_RECEIVE' => ['SHIPPED', 'CANCELLED']
            ],
        ]);
    }
}
