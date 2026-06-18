<div class="space-y-6">
    @php
         $statusColors = [
            'draft' => 'bg-muted text-muted-foreground',
            'on_check' => 'bg-yellow-500/15 text-yellow-400',
            'approved' => 'bg-green-500/15 text-green-400',
        ];
    @endphp

    <x-page-header :title="$stockOpname->so_number"
                   :description="$stockOpname->description . ' • ' . ($stockOpname->user?->name ?? '-')">
        <a wire:navigate href="{{ route('stock-opname.index') }}"
           class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent">
            <x-icon name="arrow-left" class="size-4" /> Kembali
        </a>
        @if (in_array($stockOpname->status, ['draft', 'approved']))
            <a wire:navigate href="{{ route('stock-opname.edit', $stockOpname) }}"
               class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <x-icon name="pencil" class="size-4" /> Edit
            </a>
        @endif
    </x-page-header>

    <div class="flex items-center gap-3">
        <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium {{ $statusColors[$stockOpname->status] ?? 'bg-muted' }}">
            {{ str_replace('_', ' ', $stockOpname->status) }}
        </span>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Stock Opname Info</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-muted-foreground">PIC</p>
                    <p class="font-medium">{{ $stockOpname->user?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Stock Opname Date</p>
                    <p class="font-medium">{{ $stockOpname->so_date }}</p>
                </div>
                @if ($stockOpname->description)
                    <div class="sm:col-span-2">
                        <p class="text-sm text-muted-foreground">Deskripsi</p>
                        <p class="font-medium whitespace-pre-wrap">{{ $stockOpname->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Totals</h3></div>
            <div class="space-y-2 p-4">
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Subtotal</span>
                    <span>{{ App\Helpers\Format::currency($stockOpname->total_before_tax) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Total Discount</span>
                    <span>{{ App\Helpers\Format::currency($stockOpname->total_discount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Total Tax</span>
                    <span>{{ App\Helpers\Format::currency($stockOpname->total_tax) }}</span>
                </div>
                <div class="flex justify-between border-t pt-2 font-semibold">
                    <span>Grand Total</span>
                    <span>{{ App\Helpers\Format::currency($stockOpname->grand_total) }}</span>
                </div>
            </div>
        </div> --}}
    </div>

    {{-- Line Items --}}
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Line Items</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-muted-foreground">
                        <th class="px-4 py-3 font-medium">Item / SKU</th>
                        <th class="px-4 py-3 font-medium">Stock System</th>
                        <th class="px-4 py-3 font-medium">Stock Inhouse / Store</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stockOpname->items as $item)
                        <tr class="border-b">
                            <td class="px-4 py-3">
                               @php
                                   $parent = \App\Models\inventory::with('item')->find($item->inventory_id);
                               @endphp
                               {{ $parent->sku }}
                            </td>
                            <td class="px-4 py-3">{{ $item->stock_system }}</td>
                            <td class="px-4 py-3">{{ $item->stock_inhouse }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions --}}
    @if ($stockOpname->status !== 'approved')
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Actions</h3></div>
            <div class="flex flex-wrap gap-2 p-4">
                @if ($stockOpname->status === 'draft')
                    <button wire:click="updateStatus('approved')"
                            class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                        <x-icon name="check" class="size-4" /> Approve
                    </button>
                @endif
                @if ($stockOpname->status === 'on_check')
                    <button wire:click="updateStatus('approved')"
                            class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        <x-icon name="check" class="size-4" /> Approve
                    </button>
                @endif
                <button wire:click="updateStatus('draft')"
                        class="inline-flex items-center gap-2 rounded-md bg-destructive px-4 py-2 text-sm font-medium text-white hover:bg-destructive/90">
                    <x-icon name="x" class="size-4" /> Change to Draft
                </button>
            </div>
        </div>
    @endif
</div>
