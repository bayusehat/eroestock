<div class="space-y-6">
        <x-page-header title="Tax rates" description="Sales, income, and withholding rates" />

        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold">{{ $editingId ? 'Edit tax rate' : 'New tax rate' }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="name" class="mb-1.5 block text-sm font-medium">Name</label>
                    <input id="name" type="text" wire:model="name"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                    @error('name') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="rate" class="mb-1.5 block text-sm font-medium">Rate %</label>
                    <input id="rate" type="text" inputmode="decimal" wire:model="rate"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                    @error('rate') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="type" class="mb-1.5 block text-sm font-medium">Type</label>
                    <x-select wire:model="type"
                              :options="['sales' => 'Sales', 'income' => 'Income', 'withholding' => 'Withholding']" />
                    @error('type') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col gap-3 sm:col-span-2 lg:col-span-3 sm:flex-row sm:items-center">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.boolean="is_default" class="size-4 rounded border-input" />
                        Default for this type
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.boolean="is_active" class="size-4 rounded border-input" />
                        Active
                    </label>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @if ($editingId)
                    <button type="button" wire:click="update"
                            class="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90">
                        <x-icon name="check" class="size-4" />
                        Update
                    </button>
                    <button type="button" wire:click="cancelEdit"
                            class="inline-flex h-9 items-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent">
                        Cancel
                    </button>
                @else
                    <button type="button" wire:click="create"
                            class="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90">
                        <x-icon name="plus" class="size-4" />
                        Add rate
                    </button>
                @endif
            </div>
        </div>

        <div class="rounded-lg border bg-card shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b text-left text-sm text-muted-foreground">
                            <th class="px-6 py-3 font-medium">Name</th>
                            <th class="px-6 py-3 font-medium">Rate %</th>
                            <th class="px-6 py-3 font-medium">Type</th>
                            <th class="px-6 py-3 font-medium">Default</th>
                            <th class="px-6 py-3 font-medium">Active</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse ($taxRates as $row)
                            <tr class="border-t" wire:key="tax-{{ $row->id }}">
                                <td class="px-6 py-3 font-medium">{{ $row->name }}</td>
                                <td class="px-6 py-3 text-muted-foreground">{{ $row->rate }}</td>
                                <td class="px-6 py-3 capitalize text-muted-foreground">{{ str_replace('_', ' ', $row->type) }}</td>
                                <td class="px-6 py-3">
                                    @if ($row->is_default)
                                        <x-badge variant="default">Default</x-badge>
                                    @else
                                        <span class="text-muted-foreground">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    @if ($row->is_active)
                                        <x-badge variant="secondary">Active</x-badge>
                                    @else
                                        <x-badge variant="outline">Inactive</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" wire:click="edit({{ $row->id }})"
                                                class="inline-flex h-8 items-center gap-1 rounded-md border border-input bg-background px-3 text-xs font-medium hover:bg-accent">
                                            <x-icon name="pencil" class="size-3.5" />
                                            Edit
                                        </button>
                                        <button type="button"
                                                wire:click="delete({{ $row->id }})"
                                                wire:confirm="Delete this tax rate?"
                                                class="inline-flex h-8 items-center gap-1 rounded-md border border-destructive/30 bg-background px-3 text-xs font-medium text-destructive hover:bg-destructive/10">
                                            <x-icon name="trash-2" class="size-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t">
                                <td colspan="6" class="px-6 py-10 text-center text-muted-foreground">No tax rates yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>
