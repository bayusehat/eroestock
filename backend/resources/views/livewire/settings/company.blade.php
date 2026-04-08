<div class="space-y-6">
        <x-page-header title="Company settings" description="Legal name, contact details, logo, and defaults" />

        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <form wire:submit="save" class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="company_name" class="mb-1.5 block text-sm font-medium">Company name</label>
                        <input id="company_name" type="text" wire:model="company_name"
                               class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                        @error('company_name') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="company_address" class="mb-1.5 block text-sm font-medium">Address</label>
                        <textarea id="company_address" wire:model="company_address" rows="3"
                                  class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring"></textarea>
                        @error('company_address') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="company_phone" class="mb-1.5 block text-sm font-medium">Phone</label>
                        <input id="company_phone" type="text" wire:model="company_phone"
                               class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                        @error('company_phone') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="company_email" class="mb-1.5 block text-sm font-medium">Email</label>
                        <input id="company_email" type="email" wire:model="company_email"
                               class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                        @error('company_email') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="company_tax_id" class="mb-1.5 block text-sm font-medium">Tax ID</label>
                        <input id="company_tax_id" type="text" wire:model="company_tax_id"
                               class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                        @error('company_tax_id') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="default_currency" class="mb-1.5 block text-sm font-medium">Default currency</label>
                        <input id="default_currency" type="text" wire:model="default_currency"
                               class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                        @error('default_currency') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="default_payment_terms" class="mb-1.5 block text-sm font-medium">Default payment terms (days)</label>
                        <input id="default_payment_terms" type="text" wire:model="default_payment_terms"
                               class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                        @error('default_payment_terms') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="company_logo_upload" class="mb-1.5 block text-sm font-medium">Logo</label>
                        @if ($company_logo)
                            <div class="mb-3 flex items-center gap-3">
                                <img src="{{ asset('storage/' . $company_logo) }}" alt="Logo" class="h-14 w-auto rounded border object-contain" />
                                <span class="text-xs text-muted-foreground">{{ $company_logo }}</span>
                            </div>
                        @endif
                        <input id="company_logo_upload" type="file" wire:model="company_logo_upload" accept="image/*"
                               class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium" />
                        <div wire:loading wire:target="company_logo_upload" class="mt-1 text-xs text-muted-foreground">Uploading…</div>
                        @error('company_logo_upload') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-end border-t pt-4">
                    <button type="submit"
                            class="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 transition-colors">
                        <x-icon name="check" class="size-4" />
                        Save
                    </button>
                </div>
            </form>
        </div>
</div>
