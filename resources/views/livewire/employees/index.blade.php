<div class="space-y-6">
    <x-page-header title="Karyawan" description="Kelola data karyawan">
        <a wire:navigate href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="plus" class="size-4" /> Tambah Karyawan</a>
    </x-page-header>
    <div class="flex gap-3">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari karyawan..." class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        <x-select wire:model.live="statusFilter" placeholder="Semua Status"
                  :options="['' => 'Semua Status', 'active' => 'Aktif', 'inactive' => 'Nonaktif', 'terminated' => 'Diberhentikan']" class="w-40" />
    </div>
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-muted/50 text-left text-muted-foreground"><th class="px-4 py-3 font-medium">ID Karyawan</th><th class="px-4 py-3 font-medium">Nama</th><th class="px-4 py-3 font-medium">Jabatan</th><th class="px-4 py-3 font-medium">Departemen</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Gaji Pokok</th><th class="px-4 py-3"></th></tr></thead>
            <tbody>
                @php $statusColors = ['active'=>'bg-green-500/15 text-green-400','inactive'=>'bg-muted text-muted-foreground','terminated'=>'bg-red-500/15 text-red-400']; @endphp
                @forelse ($employees as $emp)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium">{{ $emp->employee_id }}</td>
                        <td class="px-4 py-3"><a wire:navigate href="{{ route('employees.show', $emp) }}" class="text-primary hover:underline">{{ $emp->name }}</a></td>
                        <td class="px-4 py-3">{{ $emp->position ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $emp->department ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $statusColors[$emp->status] ?? 'bg-muted' }}">{{ $emp->status }}</span></td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($emp->base_salary) }}</td>
                        <td class="px-4 py-3 flex gap-1 justify-end">
                            <a wire:navigate href="{{ route('employees.edit', $emp) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="pencil" class="size-3" /> Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-muted-foreground">Tidak ada karyawan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $employees->links() }}</div>
</div>
