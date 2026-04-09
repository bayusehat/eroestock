<div class="space-y-6">
    <x-page-header title="Brands" description="Kelola daftar klien">
        <a wire:navigate href="{{ route('brands.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="plus" class="size-4" /> Tambah Brand
        </a>
    </x-page-header>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari brand..."
           class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Kode</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Telepon</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($brands as $brand)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3">
                            <a wire:navigate href="{{ route('brands.show', $brand) }}" class="font-medium text-primary hover:underline">{{ $brand->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ $brand->code ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $brand->email ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $brand->phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $brand->is_active ? 'bg-green-500/15 text-green-400' : 'bg-muted text-muted-foreground' }}">
                                {{ $brand->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a wire:navigate href="{{ route('brands.show', $brand) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent">
                                <x-icon name="eye" class="size-3" /> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada brand</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $brands->links() }}</div>
</div>
