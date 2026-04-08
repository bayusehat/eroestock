<div class="space-y-6">
        <x-page-header title="Users" description="Manage who can access the application">
            @can('users-create')
                <a href="{{ route('settings.users.create') }}"
                   wire:navigate
                   class="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 transition-colors">
                    <x-icon name="plus" class="size-4" />
                    Create user
                </a>
            @endcan
        </x-page-header>

        <div class="rounded-lg border bg-card shadow-sm">
            <div class="flex flex-col gap-4 border-b p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative max-w-md flex-1">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input type="search"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search name, email, or phone…"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent py-1 pl-9 pr-3 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-t text-left text-sm text-muted-foreground">
                            <th class="px-6 py-3 font-medium">Name</th>
                            <th class="px-6 py-3 font-medium">Email</th>
                            <th class="px-6 py-3 font-medium">Roles</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Last login</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse ($users as $user)
                            <tr class="border-t" wire:key="user-{{ $user->id }}">
                                <td class="px-6 py-3 font-medium">{{ $user->name }}</td>
                                <td class="px-6 py-3 text-muted-foreground">{{ $user->email }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($user->roles as $role)
                                            <x-badge variant="secondary">{{ $role->name }}</x-badge>
                                        @empty
                                            <span class="text-muted-foreground">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    @if ($user->is_active)
                                        <x-badge variant="default">Active</x-badge>
                                    @else
                                        <x-badge variant="outline">Inactive</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-muted-foreground">
                                    {{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @can('users-edit')
                                        <a href="{{ route('settings.users.edit', $user) }}"
                                           wire:navigate
                                           class="inline-flex h-8 items-center gap-1 rounded-md border border-input bg-background px-3 text-xs font-medium hover:bg-accent">
                                            <x-icon name="pencil" class="size-3.5" />
                                            Edit
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr class="border-t">
                                <td colspan="6" class="px-6 py-10 text-center text-muted-foreground">
                                    No users match your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t px-6 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
</div>
