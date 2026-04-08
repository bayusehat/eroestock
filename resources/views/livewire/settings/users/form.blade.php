<div class="space-y-6">
        <x-page-header :title="$user ? 'Edit user' : 'Create user'" description="Account details and role assignment">
            <a href="{{ route('settings.users.index') }}"
               wire:navigate
               class="inline-flex h-9 items-center gap-2 rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent transition-colors">
                <x-icon name="arrow-left" class="size-4" />
                Back
            </a>
        </x-page-header>

        <div class="mx-auto max-w-2xl rounded-lg border bg-card p-6 shadow-sm">
            <form wire:submit="save" class="space-y-5">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium">Name</label>
                    <input id="name" type="text" wire:model="name"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                    @error('name') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium">Email</label>
                    <input id="email" type="email" wire:model="email"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                    @error('email') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium">Password</label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password"
                           placeholder="{{ $user ? 'Leave blank to keep current' : '' }}"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                    @error('password') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium">Phone</label>
                    <input id="phone" type="text" wire:model="phone"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring" />
                    @error('phone') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-2">
                    <input id="is_active" type="checkbox" wire:model.boolean="is_active"
                           class="size-4 rounded border-input" />
                    <label for="is_active" class="text-sm font-medium">Active</label>
                </div>
                @error('is_active') <p class="text-sm text-destructive">{{ $message }}</p> @enderror

                <div class="border-t pt-4">
                    <p class="mb-3 text-sm font-medium">Roles</p>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($availableRoles as $role)
                            <label class="flex cursor-pointer items-center gap-2 rounded-md border border-input px-3 py-2 text-sm hover:bg-accent/50">
                                <input type="checkbox" value="{{ $role->name }}" wire:model="roles"
                                       class="size-4 rounded border-input" />
                                <span>{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                    @error('roles.*') <p class="mt-1 text-sm text-destructive">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end border-t pt-4">
                    <button type="submit"
                            class="inline-flex h-9 items-center gap-2 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 transition-colors">
                        <x-icon name="check" class="size-4" />
                        {{ $user ? 'Update user' : 'Create user' }}
                    </button>
                </div>
            </form>
        </div>
</div>
