<div class="space-y-6">
    <x-page-header :title="$item->item->name" :description="$item->item->brand->name ?? ''">
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
            {{-- @foreach ([['Email', $brand->email], ['Telepon', $brand->phone], ['Alamat', $brand->address]] as [$label, $value])
                <div>
                    <p class="text-sm text-muted-foreground">{{ $label }}</p>
                    <p class="font-medium">{{ $value ?? '-' }}</p>
                </div>
            @endforeach --}}
            <div>
                <p class="text-sm text-muted-foreground">Status</p>
                {{-- <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $brand->is_active ? 'bg-green-500/15 text-green-400' : 'bg-muted text-muted-foreground' }}">
                    {{ $brand->is_active ? 'Aktif' : 'Nonaktif' }}
                </span> --}}
            </div>
        </div>
    </div>
</div>
