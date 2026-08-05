<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PushDataShopee;
use Illuminate\Http\Request;
use Muhanz\Shoapi\Facades\Shoapi;
use App\Models\ShopeeToken;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderDetail;
use Carbon\Carbon;
use App\Helpers\Format;
use App\Models\Account;
use App\Models\Transaction;
use App\Traits\GeneratesNumber;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;
use DB;

class ShopeeWebhookController extends Controller
{
    public $token = null;

    public function __construct(ShopeeToken $token) {
        $this->token = ShopeeToken::where(['user_id' => 1])->first();   // 3. Assign initial values
        if($this->token?->isExpired()){
            $this->refreshToken(1, $this->token->shop_id);
        }
    }

    public function handle(Request $request) {
        $data = $request->all();
        if(!empty($data)){
            $response = json_encode($data, true);
            PushDataShopee::create(['push_data' =>  $response]);
            if(isset($data['code']) && $data['code'] == 3){
                $res = Format::parseData($data);
                $this->getDetailOrderShopee($res['data']['ordersn'], $res['data']['status']);
            }
        }
        return response()->json(['status' => 'success', 'val' => $data]);
    }

    public function getDetailOrderShopee($order_sn, $status){
        $params = [
            'order_sn_list' => $order_sn,
            'response_optional_fields' => 'item_list,total_amount,package_list,buyer_username'
        ];

        $response = Shoapi::call('order')
                ->access('get_order_detail', $this->token->access_token)
                ->shop($this->token->shop_id)
                ->request($params)
                ->response();

        $response = Format::parseData($response);

        if($response['api_status'] == 'success'){
            //updateOrCreate Order from Shopee
            // Log::info('Order Detail : '. json_encode($response));
            if($this->dumpOrder($response, $status)){
                return true;
            }else{
               return false;
            }
        }else{
            return false;
        }
    }

    public function getEscrowDetail($order){
        $params = [
            'order_sn' =>  $order->order_sn
        ];

        $response = Shoapi::call('payment')
                ->access('get_escrow_detail', $this->token->access_token)
                ->shop($this->token->shop_id)
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
           return true;
        }else{
            return false;
        }
    }

    public function getTrackingNumber($order){
        $params = [
            'order_sn' =>  $order->order_sn
        ];

        $response = Shoapi::call('logistics')
                ->access('get_tracking_number', $this->token->access_token)
                ->shop($this->token->shop_id)
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

    public function dumpOrder($response, $status){
        DB::transaction(function () use ($response, $status){
                $order_list = $response['order_list'][0];
                $data = [
                    'order_sn' => $order_list['order_sn'],
                    'booking_sn' => $order_list['booking_sn'],
                    'create_time' => $order_list['create_time'],
                    'day_to_ship' => 1,
                    'order_status' => $status,
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

                            //logistics
                            $this->getTrackingNumber($order);

                            if($child->wasRecentlyCreated) {
                                //get payment details
                                $this->getEscrowDetail($order);
                                $parent = ShopeeOrder::where('id',$child->shopee_order_id)->first();
                                if($parent->order_status == 'READY_TO_SHIP'){
                                    Inventory::where([
                                        'sku' => $child->model_sku
                                    ])->decrement('store_stock', (int) $child->model_quantity_purchased);
                                    $this->getModelDetail($detail['item_id'],$detail['model_id']);
                                }
                            }

                            if (!$child->wasRecentlyCreated) {
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
            });
        return true;
    }

    public function addToTransactions($order_id){
        Transaction::create([
            'transaction_no' => GeneratesNumber::generateNumber('TXN', 'transactions', 'transaction_no', 'Y'),
            'type' => 'income',
            'date' => Carbon::now()->toDateString(),
            'amount' => $order_id->escrow_amount,
            'account_id' => 4,
            'contra_account_id' => 22,
            'description' => 'Shopee Order '.$order_id->order_sn ?: null,
            'reference_no' => $order_id->order_sn ?: null,
            'payment_method' => 'bank_transfer' ?: null,
            'category' => '-' ?: null,
            'created_by' => auth()->id()
        ]);
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
                $stock_to_update = $lastStock < 5 ? ($lastStockShopee + ($lastStock - $lastStockShopee)) : $lastStock;
                $this->updateStockShopee($itemId, $modelId, $stock_to_update);
            }
        }
    }

    public function updateStockShopee($itemId, $modelId, $stock){
        $params = [
            "item_id" => $itemId,
            "stock_list" => [
                [
                    "model_id" => $modelId,
                    "seller_stock" => [
                        [
                            "location_id" => "IDZ",
                            "stock" => $stock
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

        if($response['api_status'] == 'success'){
            PushDataShopee::create(['push_data' =>  json_encode($response)]);
            return true;
        }else{
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
}
