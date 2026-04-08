<div class="space-y-6">
    @php
        $statusColors = ['draft'=>'bg-muted text-muted-foreground','sent'=>'bg-blue-500/15 text-blue-400','partially_paid'=>'bg-yellow-500/15 text-yellow-400','paid'=>'bg-green-500/15 text-green-400','overdue'=>'bg-red-500/15 text-red-400','cancelled'=>'bg-muted text-muted-foreground'];
    @endphp
    <x-page-header :title="$invoice->invoice_no" :description="$invoice->client?->name ?? ''">
        <a wire:navigate href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent"><x-icon name="arrow-left" class="size-4" /> Kembali</a>
        @if ($invoice->status === 'draft')
            <button wire:click="markAsSent" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Kirim Invoice</button>
            <a wire:navigate href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="pencil" class="size-4" /> Edit</a>
        @endif
        @if (in_array($invoice->status, ['sent', 'partially_paid', 'overdue']))
            <button wire:click="$set('showPaymentModal', true)" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Catat Pembayaran</button>
        @endif
    </x-page-header>

    <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium {{ $statusColors[$invoice->status] ?? 'bg-muted' }}">
        {{ str_replace('_', ' ', $invoice->status) }}
    </span>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Detail Invoice</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div><p class="text-sm text-muted-foreground">Client</p><p class="font-medium">{{ $invoice->client?->name ?? '-' }}</p></div>
                <div><p class="text-sm text-muted-foreground">Tanggal Invoice</p><p class="font-medium">{{ $invoice->issue_date?->format('d/m/Y') }}</p></div>
                <div><p class="text-sm text-muted-foreground">Jatuh Tempo</p><p class="font-medium">{{ $invoice->due_date?->format('d/m/Y') }}</p></div>
                <div><p class="text-sm text-muted-foreground">Total Dibayar</p><p class="font-medium text-green-400">{{ App\Helpers\Format::currency($invoice->amount_paid) }}</p></div>
            </div>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Summary</h3></div>
            <div class="space-y-2 p-4">
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Subtotal</span><span>{{ App\Helpers\Format::currency($invoice->subtotal) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Discount</span><span>{{ App\Helpers\Format::currency($invoice->discount_amount) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-muted-foreground">Tax</span><span>{{ App\Helpers\Format::currency($invoice->tax_amount) }}</span></div>
                <div class="flex justify-between border-t pt-2 font-bold"><span>Grand Total</span><span>{{ App\Helpers\Format::currency($invoice->grand_total) }}</span></div>
                <div class="flex justify-between font-semibold text-red-400"><span>Sisa Tagihan</span><span>{{ App\Helpers\Format::currency($invoice->balance_due) }}</span></div>
            </div>
        </div>
    </div>

    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Line Items</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b text-left text-muted-foreground"><th class="px-4 py-3 font-medium">Deskripsi</th><th class="px-4 py-3 font-medium">Qty</th><th class="px-4 py-3 text-right font-medium">Harga</th><th class="px-4 py-3 text-right font-medium">Tax %</th><th class="px-4 py-3 text-right font-medium">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr class="border-b">
                            <td class="px-4 py-3">{{ $item->description }}</td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($item->unit_price) }}</td>
                            <td class="px-4 py-3 text-right">{{ $item->tax_rate }}%</td>
                            <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-sm rounded-lg border bg-background p-6 shadow-xl">
                <h2 class="text-lg font-semibold">Catat Pembayaran</h2>
                <div class="mt-4 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Jumlah</label>
                        <input wire:model="paymentAmount" type="number" step="100" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Tanggal</label>
                        <input wire:model="paymentDate" type="date" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Metode</label>
                        <x-select wire:model="paymentMethod"
                                  :options="['bank_transfer' => 'Transfer Bank', 'cash' => 'Tunai', 'check' => 'Cek']" />
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('showPaymentModal', false)" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</button>
                    <button wire:click="recordPayment" class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>
