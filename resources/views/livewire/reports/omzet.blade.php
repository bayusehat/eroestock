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

    <x-page-header title="Omzet Sum Detail" description="List total omzet order">
        <a wire:navigate href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent">
            <x-icon name="arrow-left" class="size-4" /> Kembali
        </a>
    </x-page-header>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring [color-scheme:dark]" placeholder="Date From"/>
        <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring [color-scheme:dark]" placeholder="Date To"/>

    </div>

    <div class="grid gap-6 lg:grid-cols-1">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Info</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div class="flex justify-between text-sm">
                    <h3> TOTAL OMZET</h3>
                    <h3>{{ App\Helpers\Format::currency($total_omzet) }}</h3>
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
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Order No.</th>
                        <th class="px-4 py-3 font-medium">Buyer Username</th>
                        <th class="px-4 py-3 font-medium">Order Date</th>
                        <th class="px-4 py-3 font-medium">Potential Income</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($omzet as $i => $item)
                        <tr class="border-b">
                            <td class="px-4 py-3">{{ ++$i }}</td>
                            <td class="px-4 py-3">{{ $item->order_sn }}</td>
                            <td class="px-4 py-3">{{ $item->buyer_username }}</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::createFromTimestamp($item->create_time)->toDateTimeString() }}</td>
                            <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($item->escrow_amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
