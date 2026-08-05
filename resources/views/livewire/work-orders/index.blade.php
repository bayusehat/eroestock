<div class="space-y-6">
    <x-page-header title="Orders" description="Kelola order">
        {{-- <button wire:click="getOrderShopee()"
           class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <span wire:loading.remove class="flex items-center gap-2">
                <x-icon name="arrow-down" class="size-4" /> Get Order From Shopee
            </span>
            <span wire:loading class="flex items-center gap-2">
                <x-icon name="loader-2" class="size-4 animate-spin" /> Processing...
            </span>
        </button> --}}
        <a wire:navigate href="{{ route('work-orders.create') }}"
           class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="plus" class="size-4" /> Create Order
        </a>
    </x-page-header>

    <div wire:loading wire:target="getOrderShopee()" class="fixed inset-0 z-50 h-screen w-full flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="px-6 py-4 bg-transparent rounded-lg shadow-xl flex items-center justify-center space-x-3">
            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-white-700 font-medium">Getting data from shopee, please wait ...</span>
        </div>
    </div>

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
    <ul class="hidden text-sm font-medium text-center text-body sm:flex -space-x-px">
        <li class="w-full focus-within:z-10">
            <a href="#" wire:click="setTab('shopee')" class="inline-flex items-center justify-center  w-full text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium hover:text-heading focus:ring-1 focus:ring-neutral-secondary-strong font-medium leading-5 text-sm px-4 py-2.5 focus:outline-none
            {{ $activeTab === 'shopee'
                        ? 'border-orange-600 text-orange-600 dark:text-orange-400 dark:border-orange-400'
                        : 'border-transparent text-gray-4000 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}
            ">
            <?xml version="1.0" encoding="utf-8"?><svg class="w-4 h-4 me-1.5" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 109.59 122.88" style="enable-background:new 0 0 109.59 122.88" xml:space="preserve"><style type="text/css"><![CDATA[
        .st0{fill:#EE4D2D;}
            ]]></style><g><path class="st0" d="M74.98,91.98C76.15,82.36,69.96,76.22,53.6,71c-7.92-2.7-11.66-6.24-11.57-11.12 c0.33-5.4,5.36-9.34,12.04-9.47c4.63,0.09,9.77,1.22,14.76,4.56c0.59,0.37,1.01,0.32,1.35-0.2c0.46-0.74,1.61-2.53,2-3.17 c0.26-0.42,0.31-0.96-0.35-1.44c-0.95-0.7-3.6-2.13-5.03-2.72c-3.88-1.62-8.23-2.64-12.86-2.63c-9.77,0.04-17.47,6.22-18.12,14.47 c-0.42,5.95,2.53,10.79,8.86,14.47c1.34,0.78,8.6,3.67,11.49,4.57c9.08,2.83,13.8,7.9,12.69,13.81c-1.01,5.36-6.65,8.83-14.43,8.93 c-6.17-0.24-11.71-2.75-16.02-6.1c-0.11-0.08-0.65-0.5-0.72-0.56c-0.53-0.42-1.11-0.39-1.47,0.15c-0.26,0.4-1.92,2.8-2.34,3.43 c-0.39,0.55-0.18,0.86,0.23,1.2c1.8,1.5,4.18,3.14,5.81,3.97c4.47,2.28,9.32,3.53,14.48,3.72c3.32,0.22,7.5-0.49,10.63-1.81 C70.63,102.67,74.25,97.92,74.98,91.98L74.98,91.98z M54.79,7.18c-10.59,0-19.22,9.98-19.62,22.47h39.25 C74.01,17.16,65.38,7.18,54.79,7.18L54.79,7.18z M94.99,122.88l-0.41,0l-80.82-0.01h0c-5.5-0.21-9.54-4.66-10.09-10.19l-0.05-1 l-3.61-79.5v0C0,32.12,0,32.06,0,32c0-1.28,1.03-2.33,2.3-2.35l0,0h25.48C28.41,13.15,40.26,0,54.79,0s26.39,13.15,27.01,29.65 h25.4h0.04c1.3,0,2.35,1.05,2.35,2.35c0,0.04,0,0.08,0,0.12v0l-3.96,79.81l-0.04,0.68C105.12,118.21,100.59,122.73,94.99,122.88 L94.99,122.88z"/></g></svg>
            Shopee
            </a>
        </li>
        <li class="w-full focus-within:z-10">
            <a href="#" wire:click="setTab('tiktok')" class="inline-flex items-center justify-center  w-full text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium hover:text-heading focus:ring-1 focus:ring-neutral-secondary-strong font-medium leading-5 text-sm px-4 py-2.5 focus:outline-none
            {{ $activeTab === 'tiktok'
                        ? 'border-pink-600 text-pink-600 dark:text-pink-400 dark:border-pink-400'
                        : 'border-transparent text-gray-4000 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="w-4 h-4 me-1.5" xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd" viewBox="0 0 461 512.235"><g fill-rule="nonzero"><path fill="#2DCCD3" d="M370.934 98.964c19.378 19.981 43.543 32.158 67.898 37.7v-15.005c-22.884-1.621-46.823-8.822-67.898-22.695zM230.952 0v335.533c0 43.959-31.593 72.234-70.009 72.234-12.743 0-24.844-2.978-35.363-8.483 13.346 17.041 34.421 26.843 57.531 26.843 38.417 0 70.01-28.275 70.01-72.272V18.322h60.886C312.348 12.479 310.99 6.371 309.934 0h-78.982zM181 195.062v-16.627c-7.691-1.281-15.382-1.696-21.753-1.696C72.573 176.739 0 246.296 0 332.555c0 56.626 27.559 105.033 69.444 133.685-29.18-28.953-47.276-69.481-47.276-115.362 0-86.109 72.347-155.628 158.832-155.816z"/><path fill="#F1204A" d="M318.87 329.991c0 107.144-81.96 163.921-159.209 163.921-33.44 0-64.505-10.103-90.217-27.672 28.879 28.652 68.616 45.995 112.385 45.995 77.248 0 159.208-56.777 159.208-163.921V173.723c-7.69-5.203-15.08-11.272-22.167-18.36v174.628zm-193.289 69.294c-9.426-11.914-15.043-27.334-15.043-45.43 0-50.782 39.698-77.624 92.629-72.045v-85.052c-7.69-1.282-15.381-1.697-21.79-1.697H181v68.389c-52.931-5.542-92.63 21.263-92.63 72.083 0 29.707 15.193 52.252 37.211 63.752zm313.251-262.621v63.525c-35.174 0-68.464-6.711-97.795-26.466 34.157 34.157 75.59 44.826 119.963 44.826v-78.567a137.713 137.713 0 01-22.168-3.318zm-67.898-37.701c-18.737-19.265-33.026-45.806-38.832-80.641h-18.095c10.329 37.663 31.592 63.94 56.927 80.641z"/><path fill="#fff" d="M159.661 493.912c77.248 0 159.209-56.777 159.209-163.921V155.364c7.088 7.087 14.477 13.157 22.168 18.359 29.33 19.755 62.62 26.466 97.794 26.466v-63.525c-24.354-5.542-48.52-17.72-67.898-37.7-25.335-16.702-46.597-42.979-56.928-80.641H253.12v335.533c0 43.996-31.593 72.271-70.009 72.271-23.111 0-44.185-9.801-57.531-26.842-22.017-11.499-37.21-34.044-37.21-63.751 0-50.821 39.698-77.626 92.63-72.084v-68.388c-86.485.189-158.832 69.708-158.832 155.815 0 45.882 18.096 86.409 47.277 115.363 25.711 17.569 56.776 27.672 90.216 27.672z"/></g></svg>
                TikTok
            </a>
        </li>
        <li class="w-full focus-within:z-10">
            <a href="#" wire:click="setTab('offline')" class="inline-flex items-center justify-center  w-full text-body bg-neutral-primary-soft border border-default rounded-e-base hover:bg-neutral-secondary-medium hover:text-heading focus:ring-1 focus:ring-neutral-secondary-strong font-medium leading-5 text-sm px-4 py-2.5 focus:outline-none
            {{ $activeTab === 'offline'
                        ? 'border-white-600 text-white-600 dark:text-white-400 dark:border-white-400'
                        : 'border-transparent text-gray-4000 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="w-4 h-4 me-1.5" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 110.95 122.88"><defs><style>.cls-1{fill:#ffe256;}</style></defs><title>shopping-bag</title><path d="M16.78,28.81h6.68a11.63,11.63,0,1,0,22.36,0H63.6a11.64,11.64,0,1,0,22.37,0h8.2a10.46,10.46,0,0,1,7.31,3,10.78,10.78,0,0,1,3.07,6.69h0l6.34,73.21a2.23,2.23,0,0,1,0,.37c0,.2,0,.35,0,.43a10.23,10.23,0,0,1-2.85,7,10.52,10.52,0,0,1-6.85,3.25h-.18l-.55,0H10.43l-.57,0H9.69a10.56,10.56,0,0,1-6.85-3.25,10.25,10.25,0,0,1-2.84-7c0-.09,0-.28,0-.54l0-.26H0L6.39,38.53l0-.31A10.82,10.82,0,0,1,7.33,35a10.67,10.67,0,0,1,2.13-3.11c.13-.13.27-.25.4-.36a10.37,10.37,0,0,1,3-1.87h0a10.38,10.38,0,0,1,3.94-.79Z"/><path class="cls-1" d="M16.78,33.74h6.36a11.63,11.63,0,0,0,23,0H63.28a11.63,11.63,0,0,0,23,0h7.88A5.35,5.35,0,0,1,98,35.34,5.65,5.65,0,0,1,99.64,39L106,112.17c0,.17,0,.3,0,.38a5.5,5.5,0,0,1-5.11,5.38l-.38,0H10.43l-.39,0a5.49,5.49,0,0,1-5.11-5.39c0-.08,0-.2,0-.38L11.3,39A5.7,5.7,0,0,1,13,35.33a5.44,5.44,0,0,1,3.83-1.59Z"/><path d="M36.16,26.48V23.26h0A18.59,18.59,0,0,1,54.67,4.73h0A18.59,18.59,0,0,1,73.22,23.24h0v3.35A5.65,5.65,0,1,0,80.62,32,5.59,5.59,0,0,0,78,27.17V23.24h0A23.3,23.3,0,0,0,54.71,0h0A23.3,23.3,0,0,0,31.44,23.26h0v4.2a5.65,5.65,0,1,0,4.73-1Z"/></svg>
                Offline
            </a>
        </li>
    </ul>

    @if ($activeTab == 'shopee')
        {{-- Table --}}
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">#</th>
                    <th class="px-4 py-3 font-medium">Order</th>
                    <th class="px-4 py-3 font-medium">Items</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Order Date</th>
                    <th class="px-4 py-3 text-right font-medium">Buyer Total Paid</th>
                    <th class="px-4 py-3 font-medium">Action</th>
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
                                {!! $wo->order_sn .'<br><span class="text-xs text-gray-400">'. $wo->buyer_username.'<br>'.$wo->tracking_number.' - '.$wo->shipping_carrier.'</span>' !!}
                            </a>
                        </td>
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
    {{-- <script>
        document.addEventListener('livewire:initialized', () => {
            setInterval(() => {
                @this.call('getOrderShopee');
                console.log('Get order Shopee ...')
            }, 120000); // 120000 milliseconds = 2 minutes
        });
    </script> --}}
</div>
