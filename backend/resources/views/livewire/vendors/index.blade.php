<div class="space-y-6">
    <x-page-header title="Vendors" description="Kelola daftar vendor">
        <a wire:navigate href="{{ route('vendors.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="plus" class="size-4" /> Tambah Vendor
        </a>
    </x-page-header>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari vendor..."
           class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Telepon</th>
                    <th class="px-4 py-3 font-medium">Bank</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vendors as $vendor)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium">{{ $vendor->name }}</td>
                        <td class="px-4 py-3">{{ $vendor->email ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $vendor->phone ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $vendor->bank_name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $vendor->is_active ? 'bg-green-500/15 text-green-400' : 'bg-muted text-muted-foreground' }}">
                                {{ $vendor->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right flex gap-2 justify-end">
                            <a wire:navigate href="{{ route('vendors.show', $vendor) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="eye" class="size-3" /> View</a>
                            <a wire:navigate href="{{ route('vendors.edit', $vendor) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="pencil" class="size-3" /> Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada vendor</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $vendors->links() }}</div>
</div>
