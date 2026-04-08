<div class="space-y-6">
    <x-page-header title="Chart of Accounts" description="Kelola akun keuangan">
        <a wire:navigate href="{{ route('accounts.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="plus" class="size-4" /> Tambah Akun</a>
    </x-page-header>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari akun..." class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-muted/50 text-left text-muted-foreground"><th class="px-4 py-3 font-medium">Kode</th><th class="px-4 py-3 font-medium">Nama</th><th class="px-4 py-3 font-medium">Tipe</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3"></th></tr></thead>
            <tbody>
                @foreach ($accounts as $account)
                    <tr class="border-b hover:bg-muted/30 font-medium">
                        <td class="px-4 py-3 font-mono text-muted-foreground">{{ $account->code }}</td>
                        <td class="px-4 py-3"><a wire:navigate href="{{ route('accounts.show', $account) }}" class="text-primary hover:underline">{{ $account->name }}</a></td>
                        <td class="px-4 py-3 capitalize">{{ $account->type }}</td>
                        <td class="px-4 py-3"><span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs {{ $account->is_active ? 'bg-green-500/15 text-green-400' : 'bg-muted text-muted-foreground' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-4 py-3 flex items-center gap-1">
                            <a wire:navigate href="{{ route('accounts.show', $account) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="eye" class="size-3" /> Lihat</a>
                            <a wire:navigate href="{{ route('accounts.edit', $account) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="pencil" class="size-3" /> Edit</a>
                        </td>
                    </tr>
                    @foreach ($account->children as $child)
                        <tr class="border-b hover:bg-muted/30">
                            <td class="px-4 py-3 pl-8 font-mono text-muted-foreground">{{ $child->code }}</td>
                            <td class="px-4 py-3 pl-8"><a wire:navigate href="{{ route('accounts.show', $child) }}" class="text-primary hover:underline">{{ $child->name }}</a></td>
                            <td class="px-4 py-3 capitalize text-muted-foreground">{{ $child->type }}</td>
                            <td class="px-4 py-3"><span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs {{ $child->is_active ? 'bg-green-500/15 text-green-400' : 'bg-muted text-muted-foreground' }}">{{ $child->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <a wire:navigate href="{{ route('accounts.show', $child) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="eye" class="size-3" /> Lihat</a>
                                    <a wire:navigate href="{{ route('accounts.edit', $child) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="pencil" class="size-3" /> Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                @if ($accounts->isEmpty())
                    <tr><td colspan="5" class="px-4 py-12 text-center text-muted-foreground">Tidak ada akun</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
