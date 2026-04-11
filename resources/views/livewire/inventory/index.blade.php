<div class="space-y-6">
    <x-page-header title="Items" description="Kelola daftar Item">
        <a wire:navigate href="{{ route('items.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="plus" class="size-4" /> Tambah Item
        </a>
    </x-page-header>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari item..."
           class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">SKU</th>
                    <th class="px-4 py-3 font-medium">Size</th>
                    <th class="px-4 py-3 font-medium">Stock Toko</th>
                    <th class="px-4 py-3 font-medium">Stock Gudang</th>
                    <th class="px-4 py-3 font-medium">Total Stock</th>
                    <th class="px-4 py-3 font-medium">Grand Total Stock</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php($rowspan = $item->inventory->count())
                    @foreach ($item->inventory as $i => $iv)
                        <tr class="border-b hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <a wire:navigate href="{{ route('items.show', $item) }}" class="font-medium text-primary hover:underline">{{ $item->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $iv->sku ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $iv->size ?? 0 }}</td>
                            <td class="px-4 py-3">{{ $iv->store_stock ?? 0 }}</td>
                            <td class="px-4 py-3">{{ $iv->warehouse_stock ?? 0 }}</td>
                            <td class="flex items-center gap-2 px-4 py-3"><span> {{ $iv->total_stock ?? 0 }} </span>
                                @if ($iv->total_stock < 5)
                                <div class="group relative flex justify-center">
                                    <span><x-icon name="alert-circle" class="size-4 text-red-700" data-tooltip-target="tooltip-light"/></span>
                                    <span class="absolute bottom-full mb-2 hidden group-hover:block w-auto p-2 bg-white text-black text-xs rounded shadow-lg">
                                        Segera lakukan penambahan stok
                                    </span>
                                </div>
                                @endif
                            </td>
                             @if($i === 0)
                                <td class="px-4 py-3" rowspan="{{ $rowspan }}">{{ $item->inventory->sum('total_stock') }}</td>
                            <td class="px-4 py-3 text-right" rowspan="{{ $rowspan }}">
                                <div x-data="{
                                    open: false,
                                    pos: { top: '0px', left: '0px' },
                                    toggle() {
                                        this.open = !this.open;
                                        if (this.open) {
                                            const rect = this.$refs.trigger.getBoundingClientRect();
                                            this.$nextTick(() => {
                                                const menuH = this.$refs.menu.offsetHeight;
                                                const menuW = this.$refs.menu.offsetWidth;
                                                const spaceBelow = window.innerHeight - rect.bottom;
                                                this.pos = {
                                                    top: spaceBelow < menuH + 8
                                                        ? (rect.top - menuH - 4) + 'px'
                                                        : (rect.bottom + 4) + 'px',
                                                    left: (rect.right - menuW) + 'px',
                                                };
                                            });
                                        }
                                    },
                                    close() { this.open = false }
                                }">
                                    <button x-ref="trigger" @click="toggle()"
                                            class="rounded-md p-1 hover:bg-accent">
                                        <x-icon name="more-horizontal" class="size-4" />
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            @click.outside="close()"
                                            x-ref="menu" :style="{ position: 'fixed', top: pos.top, left: pos.left, zIndex: 9999 }"
                                            class="w-40 origin-top-right rounded-lg bg-popover p-1 text-popover-foreground shadow-md ring-1 ring-foreground/10">
                                            <a wire:navigate href="{{ route('items.show', $item) }}"
                                            class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                                <x-icon name="eye" class="size-4" /> View
                                            </a>
                                            <a wire:navigate href="{{ route('items.edit', $item) }}"
                                                class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                                    <x-icon name="pencil" class="size-4" /> Edit
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada Item</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $items->links() }}</div>
</div>
