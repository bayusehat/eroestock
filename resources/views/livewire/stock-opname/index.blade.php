<div class="space-y-6">
    <x-page-header title="Stock Opname" description="Kelola daftar Stock Opname">
        <a wire:navigate href="{{ route('stock-opname.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="plus" class="size-4" /> Tambah Stock Opname
        </a>
    </x-page-header>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari stock opname..."
           class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">No. Stock Opname</th>
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Notes</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">PIC</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stockOpnames as $stockOpname)
                    @php
                        $statusColors = [
                            'draft' => 'bg-muted text-muted-foreground',
                            'on_check' => 'bg-yellow-500/15 text-yellow-400',
                            'approved' => 'bg-green-500/15 text-green-400',
                        ];
                    @endphp
                     <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3">
                            <a wire:navigate href="{{ route('stock-opname.show', $stockOpname) }}" class="font-medium text-primary hover:underline">{{ $stockOpname->so_number }}</a>
                        </td>
                        <td class="px-4 py-3">{{ date('d F Y H:i', strtotime($stockOpname->so_date)) }}</td>
                        <td class="px-4 py-3">{{ $stockOpname->description }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $statusColors[$stockOpname->status] ?? 'bg-muted' }}">
                                {{ str_replace('_', ' ', $stockOpname->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $stockOpname->user->name }}</td>
                        <td class="px-4 py-3 text-right">
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
                                        <button wire:click="openStatusModal({{ $stockOpname->id }})" @click="close()"
                                                class="flex w-full cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="refresh-cw" class="size-4" /> Ubah Status
                                        </button>
                                        <a wire:navigate href="{{ route('stock-opname.show', $stockOpname) }}"
                                        class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="eye" class="size-4" /> View
                                        </a>
                                        <a wire:navigate href="{{ route('stock-opname.edit', $stockOpname) }}"
                                            class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="pencil" class="size-4" /> Edit
                                        </a>
                                        @if (isset($transitions[$stockOpname->status]))
                                            <button wire:click="openStatusModal({{ $stockOpname->id }})" @click="close()"
                                                    class="flex w-full cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                                <x-icon name="refresh-cw" class="size-4" /> Ubah Status
                                            </button>
                                        @endif
                                        <button wire:click="delete({{ $stockOpname->id }})"
                                            class="flex w-full cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="trash-2" class="size-4" /> Hapus
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </td>
                     </tr>
                @empty
                     <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada Stock Opname</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $stockOpnames->links() }}</div>
    @if ($changingStatusSo)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-sm rounded-lg border bg-background p-6 shadow-xl">
                <h2 class="text-lg font-semibold">Ubah Status</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Pilih status baru untuk <strong>{{ $changingStatusSo->so_number }}</strong>.
                </p>
                <div class="mt-4">
                    <x-select wire:model="newStatus" placeholder="Pilih status..."
                              :options="collect($transitions[$changingStatusSo->status] ?? [])->mapWithKeys(fn($s) => [$s => str_replace('_', ' ', ucfirst($s))])->toArray()" />
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="closeStatusModal"
                            class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</button>
                    <button wire:click="updateStatus" :disabled="!$wire.newStatus"
                            class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                        Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

