<div class="space-y-6">
    <x-page-header :title="$employee->name" :description="$employee->employee_id">
        <a wire:navigate href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent"><x-icon name="arrow-left" class="size-4" /> Kembali</a>
        <a wire:navigate href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="pencil" class="size-4" /> Edit</a>
    </x-page-header>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Informasi Karyawan</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                @foreach ([['Email', $employee->email],['Telepon', $employee->phone],['Jabatan', $employee->position],['Departemen', $employee->department],['Tanggal Bergabung', $employee->join_date?->format('d/m/Y')],['Gaji Pokok', App\Helpers\Format::currency($employee->base_salary)]] as [$label, $value])
                    <div><p class="text-sm text-muted-foreground">{{ $label }}</p><p class="font-medium">{{ $value ?? '-' }}</p></div>
                @endforeach
                <div><p class="text-sm text-muted-foreground">Status</p><span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $employee->status === 'active' ? 'bg-green-500/15 text-green-400' : 'bg-muted text-muted-foreground' }}">{{ $employee->status }}</span></div>
            </div>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Informasi Bank</h3></div>
            <div class="grid gap-4 p-4">
                @foreach ([['Nama Bank', $employee->bank_name],['Nomor Rekening', $employee->bank_account],['Atas Nama', $employee->bank_holder]] as [$label, $value])
                    <div><p class="text-sm text-muted-foreground">{{ $label }}</p><p class="font-medium">{{ $value ?? '-' }}</p></div>
                @endforeach
            </div>
        </div>
    </div>
</div>
