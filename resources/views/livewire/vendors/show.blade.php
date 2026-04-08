<div class="space-y-6">
    <x-page-header :title="$vendor->name">
        <a wire:navigate href="{{ route('vendors.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent"><x-icon name="arrow-left" class="size-4" /> Kembali</a>
        <a wire:navigate href="{{ route('vendors.edit', $vendor) }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="pencil" class="size-4" /> Edit</a>
    </x-page-header>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Informasi Vendor</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                @foreach ([['Email', $vendor->email], ['Telepon', $vendor->phone], ['NPWP', $vendor->tax_id], ['Contact Person', $vendor->contact_person], ['Alamat', $vendor->address]] as [$label, $value])
                    <div><p class="text-sm text-muted-foreground">{{ $label }}</p><p class="font-medium">{{ $value ?? '-' }}</p></div>
                @endforeach
            </div>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Informasi Bank</h3></div>
            <div class="grid gap-4 p-4">
                @foreach ([['Nama Bank', $vendor->bank_name], ['Nomor Rekening', $vendor->bank_account], ['Atas Nama', $vendor->bank_holder]] as [$label, $value])
                    <div><p class="text-sm text-muted-foreground">{{ $label }}</p><p class="font-medium">{{ $value ?? '-' }}</p></div>
                @endforeach
            </div>
        </div>
    </div>
</div>
