<div class="space-y-6">
    <x-page-header title="Orders" description="Kelola order">
        <button wire:click="getOrderShopee()"
           class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <span wire:loading.remove class="flex items-center gap-2">
                <x-icon name="arrow-down" class="size-4" /> Get Order From Shopee
            </span>
            <span wire:loading class="flex items-center gap-2">
                <x-icon name="loader-2" class="size-4 animate-spin" /> Processing...
            </span>
        </button>
        <a wire:navigate href="{{ route('work-orders.create') }}"
           class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="plus" class="size-4" /> Create Order
        </a>
    </x-page-header>

    {{-- Filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari Order number atau judul..."
               class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
        <x-select wire:model.live="statusFilter" placeholder="Semua Status"
                  :options="['' => 'Semua Status',
                  'SHIPPED' => 'Shipped',
                  'TO_CONFIRM_RECEIVE' => 'To Confirm Recieve',
                  'CANCELLED' => 'Cancelled',
                  'READY_TO_SHIP' => 'Ready to Ship',
                  'COMPLETED' => 'Completed',
                  'PROCESSED' => 'Processed',
                  'UNPAID' => 'Unpaid']" class="w-44" />
        <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring [color-scheme:dark]" />
        <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring [color-scheme:dark]" />

    </div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <nav class="flex space-x-4" aria-label="Tabs">

            <!-- Profile Tab Trigger -->
            <button
                wire:click="setTab('shopee')"
                class="py-4 px-1 inline-flex items-center gap-2 border-b-2 text-sm font-medium whitespace-nowrap transition-colors
                {{ $activeTab === 'shopee'
                    ? 'border-orange-600 text-orange-600 dark:text-orange-400 dark:border-orange-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                Shopee
            </button>

            <!-- Account Tab Trigger -->
            <button
                wire:click="setTab('tiktok')"
                class="py-4 px-1 inline-flex items-center gap-2 border-b-2 text-sm font-medium whitespace-nowrap transition-colors
                {{ $activeTab === 'tiktok'
                    ? 'border-pink-600 text-pink-600 dark:text-pink-400 dark:border-pink-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                TikTok
            </button>

            <!-- Security Tab Trigger -->
            <button
                wire:click="setTab('offline')"
                class="py-4 px-1 inline-flex items-center gap-2 border-b-2 text-sm font-medium whitespace-nowrap transition-colors
                {{ $activeTab === 'offline'
                    ? 'border-white-600 text-white-600 dark:text-white-400 dark:border-white-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                Offline
            </button>
        </nav>
    </div>

    @if ($activeTab == 'shopee')
        {{-- Table --}}
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th>#</th>
                    <th class="px-4 py-3 font-medium">Order</th>
                    <th class="px-4 py-3 font-medium">Source</th>
                    <th class="px-4 py-3 font-medium">Items</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Order Date</th>
                    <th class="px-4 py-3 text-right font-medium">Buyer Total Paid</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workOrders as $sp => $wo)
                    @php
                        $statusColors = [
                            'READY_TO_SHIP' => 'bg-muted text-muted-foreground',
                            'PROCESSED' => 'bg-blue-500/15 text-blue-400',
                            'TO_CONFIRM_RECEIVE' => 'bg-yellow-500/15 text-yellow-400',
                            'COMPLETED' => 'bg-green-500/15 text-green-400',
                            'SHIPPED' => 'bg-purple-500/15 text-purple-400',
                            'CANCELLED' => 'bg-red-500/15 text-red-400',
                            'UNPAID' => 'bg-orange-500/15 text-orange-400'
                        ];

                        $priorityColors = [
                            'low' => 'bg-muted text-muted-foreground',
                            'medium' => 'bg-blue-500/15 text-blue-400',
                            'high' => 'bg-yellow-500/15 text-yellow-400',
                            'urgent' => 'bg-red-500/15 text-red-400',
                        ];
                    @endphp
                    <tr class="border-b hover:bg-muted/30 transition-colors">
                        <td class="px-4 py-3">{{ ++$sp }}</td>
                        <td class="px-4 py-3">
                            <a wire:navigate href="{{ route('work-orders.show', $wo) }}" class="font-medium text-primary hover:underline">
                                {!! $wo->order_sn .'<br><span class="text-xs text-gray-50">'. $wo->buyer_username.'<br>'.$wo->tracking_number.' - '.$wo->shipping_carrier.'</span>' !!}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $wo->flag }}</td>
                        <td class="px-4 py-3 text-nowrap">
                            @foreach ($wo->details as $detail)
                                {!! '<span class="text-xs">'.$detail->item_name.' x'.$detail->model_quantity_purchased.'</span><br>' !!}
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-nowrap">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $statusColors[$wo->order_status] ?? 'bg-muted' }}">
                                {{ str_replace('_', ' ', $wo->order_status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-nowrap">{{ \Carbon\Carbon::createFromTimestamp($wo->create_time)->toDateTimeString() }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ App\Helpers\Format::currency($wo->total_amount) }}</td>
                        <td class="px-4 py-3">
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
                                        <a wire:navigate href="{{ route('work-orders.show', $wo) }}"
                                           class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="eye" class="size-4" /> View
                                        </a>
                                        <a wire:navigate href="{{ route('shopee.exchange', $wo) }}"
                                           class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="refresh-cw" class="size-4" /> Tukar Size
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-muted-foreground">Tidak ada work order</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $workOrders->links() }}</div>
    {{-- Change Status Modal --}}
    @if ($changingStatusWo)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-sm rounded-lg border bg-background p-6 shadow-xl">
                <h2 class="text-lg font-semibold">Ubah Status</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Pilih status baru untuk <strong>{{ $changingStatusWo->wo_number }}</strong>.
                </p>
                <div class="mt-4">
                    <x-select wire:model="newStatus" placeholder="Pilih status..."
                              :options="collect($transitions[$changingStatusWo->status] ?? [])->mapWithKeys(fn($s) => [$s => str_replace('_', ' ', ucfirst($s))])->toArray()" />
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
    @endif

     @if ($activeTab == 'tiktok')
        <h1>TikTok Order Tab</h1>
    @endif

    @if ($activeTab == 'offline')
        {{-- Table --}}
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Order Number</th>
                    <th class="px-4 py-3 font-medium">Customer</th>
                    <th class="px-4 py-3 font-medium">Deskripsi</th>
                    <th class="px-4 py-3 font-medium">Priority</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Order Date</th>
                    <th class="px-4 py-3 font-medium">Due Date</th>
                    <th class="px-4 py-3 text-right font-medium">Grand Total</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($offline as $wo)
                    @php
                        $statusColors = [
                            'draft' => 'bg-muted text-muted-foreground',
                            'confirmed' => 'bg-blue-500/15 text-blue-400',
                            'in_progress' => 'bg-yellow-500/15 text-yellow-400',
                            'completed' => 'bg-green-500/15 text-green-400',
                            'invoiced' => 'bg-purple-500/15 text-purple-400',
                            'cancelled' => 'bg-red-500/15 text-red-400',
                        ];
                        $priorityColors = [
                            'low' => 'bg-muted text-muted-foreground',
                            'medium' => 'bg-blue-500/15 text-blue-400',
                            'high' => 'bg-yellow-500/15 text-yellow-400',
                            'urgent' => 'bg-red-500/15 text-red-400',
                        ];
                    @endphp
                    <tr class="border-b hover:bg-muted/30 transition-colors">
                        <td class="px-4 py-3 text-nowrap">
                            <a wire:navigate href="{{ route('work-orders.show', $wo) }}" class="font-medium text-primary hover:underline">
                                {{ $wo->wo_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $wo->client_work_order_id ?? '-' }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $wo->description }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $priorityColors[$wo->priority ?? 'medium'] ?? 'bg-muted' }}">
                                {{ $wo->priority ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $statusColors[$wo->status] ?? 'bg-muted' }}">
                                {{ str_replace('_', ' ', $wo->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $wo->order_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $wo->due_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium">{{ App\Helpers\Format::currency($wo->grand_total) }}</td>
                        <td class="px-4 py-3">
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
                                        <a wire:navigate href="{{ route('offline.show', $wo) }}"
                                           class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="eye" class="size-4" /> View
                                        </a>
                                        @if (in_array($wo->status, ['draft', 'confirmed']))
                                            <a wire:navigate href="{{ route('work-orders.edit', $wo) }}"
                                               class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                                <x-icon name="pencil" class="size-4" /> Edit
                                            </a>
                                        @endif
                                        <button wire:click="duplicate({{ $wo->id }})" @click="close()"
                                                class="flex w-full cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="copy" class="size-4" /> Duplikat
                                        </button>
                                        @if (isset($transitions[$wo->status]))
                                            <button wire:click="openStatusModal({{ $wo->id }})" @click="close()"
                                                    class="flex w-full cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                                <x-icon name="refresh-cw" class="size-4" /> Ubah Status
                                            </button>
                                        @endif
                                        <button wire:click="destroy({{ $wo->id }})" @click="close()"
                                                class="flex w-full cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="trash-2" class="size-4" /> Hapus
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-muted-foreground">Tidak ada work order</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $offline->links() }}</div>

    {{-- Change Status Modal --}}
    @if ($changingStatusWo)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-sm rounded-lg border bg-background p-6 shadow-xl">
                <h2 class="text-lg font-semibold">Ubah Status</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Pilih status baru untuk <strong>{{ $changingStatusWo->wo_number }}</strong>.
                </p>
                <div class="mt-4">
                    <x-select wire:model="newStatus" placeholder="Pilih status..."
                              :options="collect($transitions[$changingStatusWo->status] ?? [])->mapWithKeys(fn($s) => [$s => str_replace('_', ' ', ucfirst($s))])->toArray()" />
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
    @endif
    <script>
        document.addEventListener('livewire:initialized', () => {
            setInterval(() => {
                @this.call('getOrderShopee');
                console.log('Get order Shopee ...')
            }, 120000); // 120000 milliseconds = 2 minutes
        });
    </script>
    <div
        x-data="{ open: false, message: '' }"
        @notify.window="open = true; message = $event.detail.message; setTimeout(() => open = false, 3000)"
        x-show="open"
        class="fixed bottom-5 right-5 bg-blue-600 text-white px-4 py-2 rounded shadow-lg"
        style="display: none;"
    >
        <span x-text="message"></span>
</div>
</div>
