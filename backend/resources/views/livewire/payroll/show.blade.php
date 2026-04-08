<div class="space-y-6">
    <x-page-header :title="$payrollRecord->payroll_no" :description="$payrollRecord->employee?->name ?? ''">
        <a wire:navigate href="{{ route('payroll.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent"><x-icon name="arrow-left" class="size-4" /> Kembali</a>
        @if ($payrollRecord->status === 'draft')
            <button wire:click="approve" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Setujui</button>
        @elseif ($payrollRecord->status === 'approved')
            <button wire:click="markAsPaid" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Tandai Dibayar</button>
        @endif
    </x-page-header>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Detail Payroll</h3></div>
            <div class="grid gap-3 p-4">
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Periode</span><span class="font-medium">{{ sprintf('%02d', $payrollRecord->period_month) }}/{{ $payrollRecord->period_year }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Gaji Pokok</span><span class="font-medium">{{ App\Helpers\Format::currency($payrollRecord->base_salary) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Lembur</span><span class="font-medium">{{ App\Helpers\Format::currency($payrollRecord->overtime_amount) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Total Tunjangan</span><span class="font-medium">{{ App\Helpers\Format::currency($payrollRecord->total_allowances) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Total Potongan</span><span class="font-medium text-red-400">{{ App\Helpers\Format::currency($payrollRecord->total_deductions) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Pajak PPh 21</span><span class="font-medium text-red-400">{{ App\Helpers\Format::currency($payrollRecord->tax_amount) }}</span></div>
                <div class="flex justify-between border-t pt-2 font-bold"><span>Gaji Bersih</span><span>{{ App\Helpers\Format::currency($payrollRecord->net_pay) }}</span></div>
            </div>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Status</h3></div>
            <div class="p-4 space-y-3">
                @php $statusColors = ['draft'=>'bg-muted text-muted-foreground','approved'=>'bg-blue-500/15 text-blue-400','paid'=>'bg-green-500/15 text-green-400']; @endphp
                <div><p class="text-sm text-muted-foreground">Status</p>
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $statusColors[$payrollRecord->status] ?? 'bg-muted' }}">{{ $payrollRecord->status }}</span></div>
                @if ($payrollRecord->paid_date)
                    <div><p class="text-sm text-muted-foreground">Tanggal Bayar</p><p class="font-medium">{{ $payrollRecord->paid_date?->format('d/m/Y') }}</p></div>
                @endif
                <div><p class="text-sm text-muted-foreground">Metode</p><p class="font-medium capitalize">{{ str_replace('_', ' ', $payrollRecord->payment_method ?? '-') }}</p></div>
            </div>
        </div>
    </div>
</div>
