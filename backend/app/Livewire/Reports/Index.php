<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $reports = [
            'KEUANGAN' => [
                ['route' => 'reports.profit-loss', 'title' => 'Profit & Loss', 'description' => 'Pendapatan, pengeluaran, dan laba bersih dalam periode tertentu'],
                ['route' => 'reports.balance-sheet', 'title' => 'Balance Sheet', 'description' => 'Aset, kewajiban, dan ekuitas pada suatu waktu'],
                ['route' => 'reports.cash-flow', 'title' => 'Cash Flow Statement', 'description' => 'Arus kas operasi, investasi, dan pendanaan'],
                ['route' => 'reports.trial-balance', 'title' => 'Trial Balance', 'description' => 'Saldo debit dan kredit per akun'],
                ['route' => 'reports.general-ledger', 'title' => 'General Ledger', 'description' => 'Riwayat transaksi per akun'],
            ],
            'PIUTANG & UTANG' => [
                ['route' => 'reports.receivable-aging', 'title' => 'Accounts Receivable Aging', 'description' => 'Invoice belum dibayar per klien dan periode jatuh tempo'],
                ['route' => 'reports.payable-aging', 'title' => 'Accounts Payable Aging', 'description' => 'Utang belum dibayar per vendor dan periode jatuh tempo'],
            ],
            'BISNIS' => [
                ['route' => 'reports.income-by-client', 'title' => 'Income by Client', 'description' => 'Rincian pendapatan per klien'],
                ['route' => 'reports.expense-by-category', 'title' => 'Expense by Category', 'description' => 'Rincian pengeluaran per akun/kategori'],
                ['route' => 'reports.work-order-summary', 'title' => 'Work Order Summary', 'description' => 'Work orders per status dan nilai'],
            ],
            'OPERASIONAL' => [
                ['route' => 'reports.payroll-summary', 'title' => 'Payroll Summary', 'description' => 'Total payroll per periode dan per karyawan'],
                ['route' => 'reports.tax-summary', 'title' => 'Tax Summary', 'description' => 'Pajak dipungut, dipotong, dan kewajiban pajak'],
            ],
        ];

        return view('livewire.reports.index', ['reportGroups' => $reports]);
    }
}
