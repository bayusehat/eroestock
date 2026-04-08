<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use App\Models\PayrollRecord;
use Livewire\Component;

class TaxSummary extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $invoiceTax = Invoice::whereIn('status', ['paid', 'partially_paid', 'sent'])
            ->whereBetween('issue_date', [$this->dateFrom, $this->dateTo])
            ->sum('tax_amount');

        $payrollTax = PayrollRecord::where('status', 'paid')
            ->whereBetween('paid_date', [$this->dateFrom, $this->dateTo])
            ->sum('tax_amount');

        return [
            'rows' => collect([
                ['tax_type' => 'Sales Tax', 'tax_name' => 'Pajak Penjualan (PPN)', 'amount' => (float)$invoiceTax],
                ['tax_type' => 'Income Tax', 'tax_name' => 'Pajak Penghasilan (PPh 21)', 'amount' => (float)$payrollTax],
            ]),
            'total_collected' => (float)$invoiceTax,
            'total_withheld' => (float)$payrollTax,
            'net_liability' => (float)($invoiceTax + $payrollTax),
        ];
    }

    public function render()
    {
        return view('livewire.reports.tax-summary', ['data' => $this->getReportData()]);
    }
}
