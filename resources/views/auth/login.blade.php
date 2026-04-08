<x-layouts.guest title="Masuk">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl border bg-card p-0 shadow-lg">
            <div class="space-y-3 p-6 pb-2 text-center">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight">Kucatat</h1>
                    <p class="text-xs text-muted-foreground">Catat, Kelola, Tumbuh.</p>
                                        <p class="text-sm text-muted-foreground">Kelola keuangan bisnis Anda dengan mudah</p>
                </div>
            </div>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="space-y-4 p-6">
                    @if ($errors->any())
                        <div class="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               placeholder="name@example.com" autocomplete="email" required autofocus
                               class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium">Kata Sandi</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <button type="submit"
                            class="inline-flex h-9 w-full items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow hover:bg-primary/90 transition-colors">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
