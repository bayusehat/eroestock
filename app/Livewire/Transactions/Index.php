<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use DB;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $queryString = ['search', 'typeFilter', 'dateFrom', 'dateTo'];

    public function updatingSearch(): void { $this->resetPage(); }

    public function mount(){
        if($this->dateFrom == '' && $this->dateTo == ''){
            $this->dateFrom = Carbon::now()->subDay()->toDateString();
            $this->dateTo = Carbon::now()->toDateString();
        }

    }

    public function render()
    {
        $query = Transaction::query()->with('account:id,name');
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('transaction_no', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }
        if ($this->typeFilter){
            $query->where('type', $this->typeFilter);
        }

        $type = $this->typeFilter ? " a.type = '$this->typeFilter'" : "1=1";

        if ($this->dateFrom){
            $query->whereDate('date', '>=', $this->dateFrom);
        }
        if ($this->dateTo){
            $query->whereDate('date', '<=', $this->dateTo);
        }

        $total_gross_profit = DB::select("select sum(total_gross) total_gross from (
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
            where $type and date between ? and ? and deleted_at is null
            ) a
            ) b ", [$this->dateFrom, $this->dateTo]);

            $total_transaction = Transaction::whereBetween('date',[$this->dateFrom, $this->dateTo])
                                ->where('type','income')
                                ->sum('amount');
            $transaction = DB::select("select transaction_no, a.type, date, d.name, a.description, reference_no, amount, IFNULL(gross_profit,0) cgp, IFNULL(subtotal_od,0) cso,
  case when reference_no like 'OD-%' then IFNULL(subtotal_od,0) else IFNULL(gross_profit,0) end gp
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
            left join accounts d on a.account_id = d.id
            where $type and date between ? and ? and a.deleted_at is null
            order by a.date desc", [$this->dateFrom, $this->dateTo]);


        return view('livewire.transactions.index', [
            'transactions' => $transaction,
            'total_transaction' => $total_transaction,
            'total_gross_profit' => $total_gross_profit[0]->total_gross ?? 0
        ]);
    }
}
