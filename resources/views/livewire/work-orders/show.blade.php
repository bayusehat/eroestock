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

    <x-page-header :title="$workOrder->order_sn" :description="$workOrder->flag">
        <a wire:navigate href="{{ route('work-orders.index') }}"
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
        <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium {{ $statusColors[$workOrder->order_status] ?? 'bg-muted' }}">
            {{ str_replace('_', ' ', $workOrder->order_status) }}
        </span>
        {{-- @if ($workOrder->priority)
            <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium">
                {{ $workOrder->priority }}
            </span>
        @endif --}}
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Work Order Info</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-muted-foreground">Buyer</p>
                    <p class="font-medium">{{ $workOrder->buyer_username ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Logistik & No. Resi</p>
                    <p class="font-medium">{{ $workOrder->shipping_carrier ?? '-' }} - {{ $workOrder->package_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Order Date</p>
                    <p class="font-medium">{{ \Carbon\Carbon::createFromTimestamp($workOrder->create_time)->toDateTimeString() }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Totals</h3></div>
            <div class="space-y-2 p-4">
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Total Amount</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->total_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Shipping Fee</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->actual_shipping_fee) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> Transaction Fee</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->buyer_transaction_fee) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> Withholding Tax</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->withholding_tax) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground"> Nett Income</span>
                    <span>{{ App\Helpers\Format::currency($workOrder->escrow_amount) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Line Items</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-muted-foreground">
                        <th class="px-4 py-3 font-medium">Item Name</th>
                        <th class="px-4 py-3 font-medium">Item SKU</th>
                        <th class="px-4 py-3 font-medium">Qty</th>
                        <th class="px-4 py-3 text-right font-medium">Unit Price</th>
                        <th class="px-4 py-3 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($workOrder->details as $item)
                        <tr class="border-b">
                            <td class="px-4 py-3">{{ $item->item_name }}</td>
                            <td class="px-4 py-3">{{ $item->model_sku }}</td>
                            <td class="px-4 py-3">{{ $item->model_quantity_purchased }}</td>
                            <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($item->model_original_price) }}</td>
                            <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency(($item->model_original_price * $item->model_quantity_purchased)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
