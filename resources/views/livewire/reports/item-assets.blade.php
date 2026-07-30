<div class="space-y-6">
    @php
        $statusColors = [
            'READY_TO_SHIP' => 'bg-muted text-muted-foreground',
            'SHIPPED' => 'bg-blue-500/15 text-blue-400',
            'TO_CONFIRM_RECEIVE' => 'bg-yellow-500/15 text-yellow-400',
            'CONFIRMED' => 'bg-green-500/15 text-green-400',
            'invoiced' => 'bg-purple-500/15 text-purple-400',
            'CANCELLED' => 'bg-red-500/15 text-red-400',
        ];
    @endphp

    <x-page-header title="List Asset" description="List total worth items">
        <a wire:navigate href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent">
            <x-icon name="arrow-left" class="size-4" /> Kembali
        </a>
        {{-- @if (in_array($workOrder->status, ['draft', 'confirmed']))
            <a wire:navigate href="{{ route('work-orders.edit', $workOrder) }}"
               class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <x-icon name="pencil" class="size-4" /> Edit
            </a>
        @endif --}}
    </x-page-header>

    <div class="flex items-center gap-3">
        {{-- <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium {{ $statusColors[$workOrder->order_status] ?? 'bg-muted' }}">
            {{ str_replace('_', ' ', $workOrder->order_status) }}
        </span> --}}
        {{-- @if ($workOrder->priority)
            <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium">
                {{ $workOrder->priority }}
            </span>
        @endif --}}
    </div>

    <div class="grid gap-6 lg:grid-cols-1">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Info</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div class="flex justify-between text-sm">
                    <h3> TOTAL ASSETS</h3>
                    <h3>{{ App\Helpers\Format::currency($total_asset) }}</h3>
                </div>
            </div>
        </div>

        {{-- <div class="rounded-lg border bg-card shadow-sm"> --}}
            {{-- <div class="border-b p-4"><h3 class="font-semibold">Totals</h3></div>
            <div class="space-y-2 p-4">
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> Original Price</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->original_price) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> Discount Amount</span>
                    <span class="text-red-300">{{ App\Helpers\Format::currency($workOrder->order_seller_discount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> After Discounted Amount</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->order_discounted_price) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> Buyer Service Fee</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->buyer_service_fee) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> Shopee Voucher</span>
                    <span class="text-red-300">{{ App\Helpers\Format::currency($workOrder->shopee_voucher) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Buyer Total Pay</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->total_amount) }}</span>
                </div>
                <hr>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> Nett Income</span>
                    <span class="text-green-300"><strong>{{ App\Helpers\Format::currency($workOrder->escrow_amount) }}</strong></span>
                </div>
            </div>
        </div> --}}
    {{-- </div> --}}

    {{-- Line Items --}}
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Line Items</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-muted-foreground">
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Item Name</th>
                        <th class="px-4 py-3 font-medium">Total Stock</th>
                        <th class="px-4 py-3 font-medium">Production Worth</th>
                        <th class="px-4 py-3 font-medium">Selling Worth</th>
                        <th class="px-4 py-3 text-right font-medium">Nett Worth</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $item)
                        <tr class="border-b">
                            <td class="px-4 py-3">{{ ++$i }}</td>
                            <td class="px-4 py-3">{{ $item->name }}</td>
                            <td class="px-4 py-3">{{ $item->total_stock }}</td>
                            <td class="px-4 py-3">{{ App\Helpers\Format::currency($item->total_hpp) }}</td>
                            <td class="px-4 py-3">{{ App\Helpers\Format::currency($item->total_asset) }}</td>
                            <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($item->nett_worth) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
