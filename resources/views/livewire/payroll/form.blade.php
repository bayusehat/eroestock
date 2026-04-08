<div class="space-y-6">
    <x-page-header :title="$isEditing ? 'Edit Payroll' : 'Buat Payroll'" />
    <form wire:submit="save">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Detail Payroll</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-sm font-medium">Karyawan <span class="text-destructive">*</span></label>
                    <x-select wire:model.live="employee_id" placeholder="Pilih karyawan..." :searchable="true"
                              :options="$employees->pluck('name', 'id')->toArray()" />
                    @error('employee_id') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Bulan</label>
                    <x-select wire:model="period_month"
                              :options="collect(range(1, 12))->mapWithKeys(fn($m) => [$m => \Carbon\Carbon::create()->month($m)->format('F')])->toArray()" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Tahun</label>
                    <input wire:model="period_year" type="number" min="2020" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Gaji Pokok <span class="text-destructive">*</span></label>
                    <input wire:model="base_salary" type="number" step="50000" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Jam Lembur</label>
                    <input wire:model="overtime_hours" type="number" step="0.5" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Rate Lembur/jam</label>
                    <input wire:model="overtime_rate" type="number" step="1000" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Pajak PPh 21</label>
                    <input wire:model="tax_amount" type="number" step="1000" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Metode Pembayaran</label>
                    <x-select wire:model="payment_method"
                              :options="['bank_transfer' => 'Transfer Bank', 'cash' => 'Tunai']" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-sm font-medium">Catatan</label>
                    <textarea wire:model="notes" rows="2" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring"></textarea>
                </div>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a wire:navigate href="{{ route('payroll.index') }}" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>Simpan</span><span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
