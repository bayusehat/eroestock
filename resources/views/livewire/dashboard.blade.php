<div class="space-y-6">
    <x-page-header title="Selamat datang di Eroestock, {{ auth()->user()->name }}" description="Ringkasan keuangan bisnis Anda hari ini" />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card title="Pendapatan Bulan Ini" :value="App\Helpers\Format::currency($revenueMtd)" icon="trending-up" />
        <x-stat-card title="Pengeluaran Bulan Ini" :value="App\Helpers\Format::currency($expensesMtd)" icon="trending-down" />
        <x-stat-card title="Laba Bersih Bulan Ini" :value="App\Helpers\Format::currency($netProfitMtd)" icon="trending-up" />
        <x-stat-card title="Saldo Kas" :value="App\Helpers\Format::currency($cashBalance)" icon="wallet" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h3 class="flex items-center gap-2 text-base font-semibold">
                <x-icon name="receipt" class="size-4" /> Piutang
            </h3>
            <p class="mt-2 text-2xl font-bold">{{ App\Helpers\Format::currency($outstandingReceivables) }}</p>
        </div>
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h3 class="flex items-center gap-2 text-base font-semibold">
                <x-icon name="credit-card" class="size-4" /> Utang
            </h3>
            <p class="mt-2 text-2xl font-bold">{{ App\Helpers\Format::currency($outstandingPayables) }}</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        {{-- Revenue vs Expense Chart --}}
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold">Pendapatan vs Pengeluaran</h3>
            <div class="h-64">
                <canvas id="revenueChart"
                    x-data
                    x-init="
                        new Chart($el, {
                            type: 'bar',
                            data: {
                                labels: @js($recentTransactions->take(12)->map(fn($t, $i) => 'T'.($i+1))),
                                datasets: [
                                    { label: 'Pendapatan', data: @js($recentTransactions->take(12)->map(fn($t) => $t->amount > 0 ? $t->amount : 0)), backgroundColor: '#2563eb' },
                                    { label: 'Pengeluaran', data: @js($recentTransactions->take(12)->map(fn($t) => $t->amount < 0 ? abs($t->amount) : 0)), backgroundColor: '#f97316' },
                                ]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: '#9ca3af' } }, y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#9ca3af' } } } }
                        })
                    ">
                </canvas>
            </div>
        </div>

        {{-- Work Order Pipeline --}}
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold">Work Order Pipeline</h3>
            <div class="h-64">
                @if (count($workOrderPipeline) > 0)
                    <canvas id="pipelineChart"
                        x-data
                        x-init="
                            new Chart($el, {
                                type: 'pie',
                                data: {
                                    labels: @js(array_keys($workOrderPipeline)),
                                    datasets: [{ data: @js(array_values($workOrderPipeline)), backgroundColor: ['#2563eb','#f97316','#22c55e','#a855f7','#ef4444'] }]
                                },
                                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#9ca3af' } } } }
                            })
                        ">
                    </canvas>
                @else
                    <div class="flex h-full items-center justify-center text-muted-foreground">Tidak ada data work order</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="p-6 pb-4">
            <h3 class="flex items-center gap-2 text-base font-semibold">
                <x-icon name="arrow-left-right" class="size-4" /> Transaksi Terbaru
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-t text-left text-sm text-muted-foreground">
                        <th class="px-6 py-3 font-medium">Tanggal</th>
                        <th class="px-6 py-3 font-medium">Keterangan</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 text-right font-medium">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse ($recentTransactions as $txn)
                        <tr class="border-t">
                            <td class="px-6 py-3">{{ $txn->date?->format('d/m/Y') }}</td>
                            <td class="px-6 py-3">{{ $txn->description ?? $txn->transaction_no }}</td>
                            <td class="px-6 py-3">{{ $txn->type }}</td>
                            <td class="px-6 py-3 text-right {{ $txn->amount >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ App\Helpers\Format::currency($txn->amount) }}
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t">
                            <td colspan="4" class="px-6 py-8 text-center text-muted-foreground">Tidak ada transaksi terbaru</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
