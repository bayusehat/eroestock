<div class="space-y-6">
    <x-page-header :title="$client->name" :description="$client->code ?? ''">
        <a wire:navigate href="{{ route('clients.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent">
            <x-icon name="arrow-left" class="size-4" /> Kembali
        </a>
    </x-page-header>
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Detail Client</h3></div>
        <div class="grid gap-4 p-4 sm:grid-cols-2">
            @foreach ([['Email', $client->email], ['Telepon', $client->phone], ['NPWP', $client->tax_id], ['Contact Person', $client->contact_person], ['Syarat Pembayaran', $client->payment_terms], ['Alamat', $client->address]] as [$label, $value])
                <div>
                    <p class="text-sm text-muted-foreground">{{ $label }}</p>
                    <p class="font-medium">{{ $value ?? '-' }}</p>
                </div>
            @endforeach
            <div>
                <p class="text-sm text-muted-foreground">Status</p>
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $client->is_active ? 'bg-green-500/15 text-green-400' : 'bg-muted text-muted-foreground' }}">
                    {{ $client->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </div>
</div>
