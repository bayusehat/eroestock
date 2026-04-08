<div class="space-y-6">
    <x-page-header title="Requests" description="Kelola budget requests">
        <a wire:navigate href="{{ route('requests.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="plus" class="size-4" /> Buat Request</a>
    </x-page-header>
    <div class="flex gap-3">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari request..." class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        <x-select wire:model.live="statusFilter" placeholder="Semua Status"
                  :options="['' => 'Semua Status', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']" class="w-40" />
    </div>
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-muted/50 text-left text-muted-foreground"><th class="px-4 py-3 font-medium">No. Request</th><th class="px-4 py-3 font-medium">Judul</th><th class="px-4 py-3 font-medium">Tipe</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Jumlah</th><th class="px-4 py-3 font-medium">Dibuat oleh</th><th class="px-4 py-3"></th></tr></thead>
            <tbody>
                @php $statusColors = ['pending'=>'bg-yellow-500/15 text-yellow-400','approved'=>'bg-green-500/15 text-green-400','rejected'=>'bg-red-500/15 text-red-400']; @endphp
                @forelse ($requests as $req)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium">{{ $req->request_no }}</td>
                        <td class="px-4 py-3">{{ $req->title }}</td>
                        <td class="px-4 py-3 capitalize">{{ $req->type }}</td>
                        <td class="px-4 py-3"><span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $statusColors[$req->status] ?? 'bg-muted' }}">{{ $req->status }}</span></td>
                        <td class="px-4 py-3 text-right">{{ $req->amount ? App\Helpers\Format::currency($req->amount) : '-' }}</td>
                        <td class="px-4 py-3">{{ $req->createdByUser?->name ?? '-' }}</td>
                        <td class="px-4 py-3 flex gap-1 justify-end">
                            <a wire:navigate href="{{ route('requests.show', $req) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="eye" class="size-3" /> View</a>
                            @if ($req->status === 'pending' && auth()->user()->can('budget_requests-edit'))
                                <button wire:click="review({{ $req->id }}, 'approved')" class="inline-flex items-center gap-1 rounded-md bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700"><x-icon name="check" class="size-3" /></button>
                                <button wire:click="review({{ $req->id }}, 'rejected')" class="inline-flex items-center gap-1 rounded-md bg-destructive px-2 py-1 text-xs text-white hover:bg-destructive/90"><x-icon name="x" class="size-3" /></button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-muted-foreground">Tidak ada request</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $requests->links() }}</div>
</div>
