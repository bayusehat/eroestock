<div class="space-y-6">
    <x-page-header title="Buat Journal Entry" />
    <form wire:submit="save">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Header</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Tanggal <span class="text-destructive">*</span></label>
                    <input wire:model="date" type="date" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-sm font-medium">Keterangan</label>
                    <input wire:model="description" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
            </div>
        </div>
        <div class="mt-4 rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4 flex items-center justify-between">
                <h3 class="font-semibold">Lines</h3>
                @php $balanced = abs($this->totalDebit - $this->totalCredit) < 0.01; @endphp
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-muted-foreground">Debit: <strong>{{ App\Helpers\Format::currency($this->totalDebit) }}</strong></span>
                    <span class="text-muted-foreground">Kredit: <strong>{{ App\Helpers\Format::currency($this->totalCredit) }}</strong></span>
                    @if ($balanced)
                        <span class="text-green-400 font-medium">Balanced ✓</span>
                    @else
                        <span class="text-red-400 font-medium">Not Balanced</span>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b text-left text-muted-foreground"><th class="px-4 py-2 font-medium">Akun</th><th class="px-4 py-2 font-medium">Keterangan</th><th class="px-4 py-2 font-medium text-right">Debit</th><th class="px-4 py-2 font-medium text-right">Kredit</th><th class="px-4 py-2"></th></tr></thead>
                    <tbody>
                        @foreach ($lines as $i => $line)
                            <tr class="border-b">
                                <td class="px-4 py-2">
                                    <x-select wire:model="lines.{{ $i }}.account_id" placeholder="Pilih akun..." :searchable="true" size="sm"
                                              :options="$accounts->mapWithKeys(fn($a) => [$a->id => $a->code . ' - ' . $a->name])->toArray()" />
                                </td>
                                <td class="px-4 py-2"><input wire:model="lines.{{ $i }}.description" type="text" placeholder="Keterangan" class="h-8 w-full rounded-md border border-input bg-transparent px-2 text-sm focus:outline-none" /></td>
                                <td class="px-4 py-2"><input wire:model.lazy="lines.{{ $i }}.debit" type="number" step="100" class="h-8 w-24 rounded-md border border-input bg-transparent px-2 text-sm focus:outline-none text-right" /></td>
                                <td class="px-4 py-2"><input wire:model.lazy="lines.{{ $i }}.credit" type="number" step="100" class="h-8 w-24 rounded-md border border-input bg-transparent px-2 text-sm focus:outline-none text-right" /></td>
                                <td class="px-4 py-2"><button type="button" wire:click="removeLine({{ $i }})" @if(count($lines)<=2) disabled @endif class="text-muted-foreground hover:text-destructive disabled:opacity-30"><x-icon name="trash-2" class="size-4" /></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <button type="button" wire:click="addLine" class="flex items-center gap-2 rounded-md border border-dashed px-4 py-2 text-sm text-muted-foreground hover:bg-accent"><x-icon name="plus" class="size-4" /> Tambah Line</button>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a wire:navigate href="{{ route('journal-entries.index') }}" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>Simpan</span><span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
