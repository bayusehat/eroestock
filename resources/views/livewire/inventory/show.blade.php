<div class="space-y-6">
    <x-page-header :title="$item->name" :description="$item->brand->name ?? ''">
        <a wire:navigate href="{{ route('items.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent">
            <x-icon name="arrow-left" class="size-4" /> Kembali
        </a>
         {{-- @if (in_array($->status, ['draft', 'confirmed'])) --}}
        <a wire:navigate href="{{ route('items.edit', $item) }}"
            class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="pencil" class="size-4" /> Edit
        </a>
        {{-- @endif --}}
    </x-page-header>
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Detail Brand</h3></div>
        <div class="grid gap-4 p-4 sm:grid-cols-2">
            @foreach ([['Nama', $item->name], ['Brand', $item->brand->name], ['Buy Price', number_format($item->buy_price,0, '', '.')], ['Sell Price', number_format($item->sell_price,0, '', '.')], ['Margin', $item->margin]] as [$label, $value])
                <div>
                    <p class="text-sm text-muted-foreground">{{ $label }}</p>
                    <p class="font-medium">{{ $value ?? '-' }}</p>
                </div>
            @endforeach
        </div>
        <div class="grid gap-4 p-4 sm:grid-cols-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                        <th class="px-4 py-3 font-medium">SKU</th>
                        <th class="px-4 py-3 font-medium">Size</th>
                        <th class="px-4 py-3 font-medium">Stock Toko</th>
                        <th class="px-4 py-3 font-medium">Stock Gudang</th>
                        <th class="px-4 py-3 font-medium">Total Stock</th>
                        <th class="px-4 py-3 font-medium">Grand Total Stock</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @forelse ($item as $itm) --}}
                        @php($rowspan = $item->inventory->count())
                        @foreach ($item->inventory as $i => $iv)
                            <tr class="border-b hover:bg-muted/30">
                                <td class="px-4 py-3 text-muted-foreground">{{ $iv->sku ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $iv->size ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $iv->store_stock ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $iv->warehouse_stock ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $iv->total_stock ?? 0 }}</td>
                                @if($i === 0)
                                    <td class="px-4 py-3" rowspan="{{ $rowspan }}">{{ $item->inventory->sum('total_stock') }}</td>
                                @endif
                            </tr>
                        @endforeach
                    {{-- @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada Item</td></tr>
                    @endforelse --}}
                </tbody>
            </table>
        </div>
    </div>
</div>
