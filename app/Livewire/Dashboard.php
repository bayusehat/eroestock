<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\Transaction;
use App\Models\WorkOrder;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderDetail;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        // $revenueMtd = Transaction::where('type', 'income')->whereBetween('date', [$startOfMonth, $now])->sum('amount');
        //gross profit
        $revenueMtd = DB::select("select sum(total_gross) total_gross from (
            select reference_no, (gp + so) as total_gross from (
            select reference_no, IFNULL(gross_profit,0) gp, IFNULL(subtotal_od,0) so
            from transactions a
            left join (
            select order_sn, buyer_username, create_time, escrow_amount, total_amount, gross_profit, buy_price
                        from(
                        select *, case when total_amount <> 0 then total_amount - buy_price else 0 end as gross_profit from (
                        select order_sn, buyer_username, create_time, total_amount, escrow_amount, b.model_discounted_price, b.model_sku from shopee_orders a
                            left join shopee_order_details b on a.id = b.shopee_order_id
                            ) a
                        left join (
                        select sku, buy_price from inventories a
                            left join items b on a.id_item = b.id
                        ) b on a.model_sku = b.sku
                    ) c
            ) b on a.reference_no = b.order_sn
            left join (
            select wo_number, (unit_price - buy_price) subtotal_od
            from (
            select wo_number, inventory_id, unit_price, buy_price from work_orders a
                join work_order_items b on a.id = b.work_order_id
                join inventories c on b.inventory_id = c.id
                join items d on c.id_item = d.id
            ) a
            ) c on a.reference_no = c.wo_number
            where type ='income' and date between ? and ?
            ) a
            ) b ", [$startOfMonth, $now]);

        $expensesMtd = Transaction::where('type', 'expense')->whereBetween('date', [$startOfMonth, $now])->sum('amount');
        $netProfitMtd = $revenueMtd[0]->total_gross - $expensesMtd;

        $cashAccounts = Account::where('type', 'asset')
            ->where(fn ($q) => $q->where('code', 'like', '1-100%')->orWhere('name', 'like', '%cash%')->orWhere('name', 'like', '%bank%'))
            ->pluck('id');

        $cashBalance = Transaction::whereIn('account_id', $cashAccounts)
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;
        $cashBalance += Account::whereIn('id', $cashAccounts)->sum('opening_balance');

        $outstandingReceivables = Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])->sum('balance_due');

        $recentTransactions = Transaction::with(['account'])
            ->latest('date')
            ->limit(10)
            ->get();

        $workOrderPipeline = ShopeeOrder::select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->get()
            ->pluck('count', 'order_status')
            ->toArray();

        $potentialPl = ShopeeOrder::sum('escrow_amount');

        $totalAssets = DB::select("SELECT SUM(total_assets) total FROM (
  SELECT id_item, (grand_stock * margin) profit, (grand_stock * buy_price) total_assets from (
    SELECT a.id_item, sum(store_stock) grand_stock,SUM(b.sell_price - b.buy_price) margin, buy_price FROM inventories a left join items b on a.id_item = b.id
    GROUP BY a.id_item, buy_price
    ORDER BY a.id_item
  ) a
) b");
        return view('livewire.dashboard', [
            'revenueMtd' => $revenueMtd[0]->total_gross,
            'expensesMtd' => $expensesMtd,
            'netProfitMtd' => $netProfitMtd,
            'cashBalance' => $cashBalance,
            'outstandingReceivables' => $outstandingReceivables,
            'outstandingPayables' => 0,
            'potentialPl' => $potentialPl,
            'totalAssets' => $totalAssets[0]->total,
            'recentTransactions' => $recentTransactions,
            'workOrderPipeline' => $workOrderPipeline,
        ]);
    }
}
