<div class="space-y-6">
        <x-page-header title="Roles" description="Define roles and attach permissions">
            @can('roles-create')
                <button type="button" wire:click="openCreate"
                        class="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 transition-colors">
                    <x-icon name="plus" class="size-4" />
                    Create role
                </button>
            @endcan
        </x-page-header>

        @if ($rolePanelOpen)
            <div class="fixed inset-0 z-40 flex items-start justify-center overflow-y-auto bg-background/80 p-4 backdrop-blur-sm" wire:click.self="closePanel">
                <div class="relative z-50 mt-8 w-full max-w-3xl rounded-lg border bg-card p-6 shadow-lg" @click.stop wire:key="role-panel">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <h2 class="text-lg font-semibold">{{ $editingRoleId ? 'Edit role' : 'Create role' }}</h2>
                        <button type="button" wire:click="closePanel" class="rounded-md p-1 hover:bg-accent" aria-label="Close">
                            <x-icon name="x" class="size-5" />
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="roleName" class="mb-1.5 block text-sm font-medium">Role name</label>
                            <input id="roleName" type="text" wire:model="roleName"
                                   class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                            @error('roleName') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                        </div>
                        <div class="max-h-[50vh] space-y-4 overflow-y-auto rounded-md border border-input p-4">
                            @foreach ($permissionsGrouped as $module => $perms)
                                <div wire:key="perm-mod-{{ $module }}">
                                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                        {{ str($module)->replace('_', ' ')->title() }}
                                    </h3>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        @foreach ($perms as $permission)
                                            <label class="flex cursor-pointer items-center gap-2 text-sm">
                                                <input type="checkbox" value="{{ $permission->id }}" wire:model="selectedPermissions"
                                                       class="size-4 rounded border-input" />
                                                <span>{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('selectedPermissions') <p class="text-sm text-destructive">{{ $message }}</p> @enderror
                        @error('selectedPermissions.*') <p class="text-sm text-destructive">{{ $message }}</p> @enderror
                        <div class="flex justify-end gap-2 border-t pt-4">
                            <button type="button" wire:click="closePanel"
                                    class="inline-flex h-9 items-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent">
                                Cancel
                            </button>
                            @if ($editingRoleId)
                                @can('roles-edit')
                                    <button type="button" wire:click="updateRole"
                                            class="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90">
                                        <x-icon name="check" class="size-4" />
                                        Save changes
                                    </button>
                                @endcan
                            @else
                                @can('roles-create')
                                    <button type="button" wire:click="createRole"
                                            class="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90">
                                        <x-icon name="check" class="size-4" />
                                        Create role
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="rounded-lg border bg-card shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b text-left text-sm text-muted-foreground">
                            <th class="px-6 py-3 font-medium">Role</th>
                            <th class="px-6 py-3 font-medium">Permissions</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse ($roles as $role)
                            <tr class="border-t" wire:key="role-{{ $role->id }}">
                                <td class="px-6 py-3 font-medium">{{ $role->name }}</td>
                                <td class="px-6 py-3">
                                    <x-badge variant="secondary">{{ $role->permissions_count }} permissions</x-badge>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        @can('roles-edit')
                                            <button type="button" wire:click="openEdit({{ $role->id }})"
                                                    class="inline-flex h-8 items-center gap-1 rounded-md border border-input bg-background px-3 text-xs font-medium hover:bg-accent">
                                                <x-icon name="pencil" class="size-3.5" />
                                                Edit
                                            </button>
                                        @endcan
                                        @can('roles-delete')
                                            <button type="button"
                                                    wire:click="deleteRole({{ $role->id }})"
                                                    wire:confirm="Delete this role?"
                                                    class="inline-flex h-8 items-center gap-1 rounded-md border border-destructive/30 bg-background px-3 text-xs font-medium text-destructive hover:bg-destructive/10">
                                                <x-icon name="trash-2" class="size-3.5" />
                                                Delete
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t">
                                <td colspan="3" class="px-6 py-10 text-center text-muted-foreground">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
</div>
