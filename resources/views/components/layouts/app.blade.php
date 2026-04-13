<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Eroestock' }} - Eroestock</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-background font-sans antialiased">
    {{-- SPA navigation loading bar --}}
    <div x-data="{ loading: false }"
         x-on:livewire:navigate-start.window="loading = true"
         x-on:livewire:navigate-end.window="loading = false">
        <div x-show="loading" x-transition.opacity
             class="fixed inset-x-0 top-0 z-50 h-0.5 overflow-hidden bg-primary/20">
            <div class="h-full w-1/3 animate-[loading_1s_ease-in-out_infinite] bg-primary rounded-full"></div>
        </div>
    </div>
    <div class="flex min-h-screen" x-data="{ sidebarOpen: true }" @keydown.escape.window="sidebarOpen = false">
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col border-r border-sidebar-border bg-sidebar transition-transform duration-200"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               @click.away="if (window.innerWidth < 1024) sidebarOpen = false">
            {{-- Logo --}}
            <div class="border-b border-sidebar-border p-4">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex flex-col gap-0.5">
                    <span class="text-lg font-bold tracking-tight text-sidebar-foreground">Kucatat</span>
                    <span class="text-[11px] text-muted-foreground">Catat, Kelola, Tumbuh.</span>
                </a>
            </div>
            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
                @php
                    $navGroups = [
                        ['section' => 'UTAMA', 'items' => [
                            ['href' => '/dashboard', 'label' => 'Beranda', 'icon' => 'layout-dashboard'],
                            ['href' => '/tutorial', 'label' => 'Tutorial', 'icon' => 'graduation-cap'],
                        ]],
                        ['section' => 'BISNIS', 'items' => [
                            ['href' => '/work-orders', 'label' => 'Work Orders', 'icon' => 'clipboard-list', 'permission' => 'work_orders-view'],
                            ['href' => '/requests', 'label' => 'Requests', 'icon' => 'send', 'permission' => 'budget_requests-view'],
                            ['href' => '/clients', 'label' => 'Clients', 'icon' => 'building-2', 'permission' => 'clients-view'],
                            ['href' => '/vendors', 'label' => 'Vendors', 'icon' => 'truck', 'permission' => 'vendors-view'],
                            ['href' => '/invoices', 'label' => 'Invoices', 'icon' => 'file-text', 'permission' => 'invoices-view'],
                        ]],
                        ['section' => 'KEUANGAN', 'items' => [
                            ['href' => '/transactions', 'label' => 'Transactions', 'icon' => 'arrow-left-right', 'permission' => 'transactions-view'],
                            ['href' => '/journal-entries', 'label' => 'Journal Entries', 'icon' => 'book-open', 'permission' => 'journal_entries-view'],
                            ['href' => '/accounts', 'label' => 'Chart of Accounts', 'icon' => 'network', 'permission' => 'accounts-view'],
                        ]],
                        ['section' => 'SDM', 'items' => [
                            ['href' => '/employees', 'label' => 'Employees', 'icon' => 'users', 'permission' => 'employees-view'],
                            ['href' => '/payroll', 'label' => 'Payroll', 'icon' => 'banknote', 'permission' => 'payroll-view'],
                        ]],
                        ['section' => 'LAPORAN', 'items' => [
                            ['href' => '/reports', 'label' => 'Laporan', 'icon' => 'bar-chart-3', 'permission' => 'reports-view'],
                        ]],
                        ['section' => 'PENGATURAN', 'items' => [
                            ['href' => '/settings/company', 'label' => 'Company Settings', 'icon' => 'settings', 'permission' => 'settings-view'],
                            ['href' => '/settings/users', 'label' => 'Users', 'icon' => 'users', 'permission' => 'users-view'],
                            ['href' => '/settings/roles', 'label' => 'Roles', 'icon' => 'shield', 'permission' => 'roles-view'],
                            ['href' => '/settings/tax-rates', 'label' => 'Tax Rates', 'icon' => 'percent', 'permission' => 'settings-view'],
                            ['href' => '/settings/audit-logs', 'label' => 'Audit Logs', 'icon' => 'shield', 'permission' => 'audit_logs-view'],
                        ]],
                    ];
                @endphp

                @foreach ($navGroups as $group)
                    @php
                        $visibleItems = collect($group['items'])->filter(function ($item) {
                            return !isset($item['permission']) || auth()->user()->can($item['permission']);
                        });
                    @endphp
                    @if ($visibleItems->count() > 0)
                        <div>
                            <h3 class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">{{ $group['section'] }}</h3>
                            <ul class="space-y-0.5">
                                @foreach ($visibleItems as $item)
                                    @php $isActive = request()->is(ltrim($item['href'], '/') . '*'); @endphp
                                    <li>
                                        <a href="{{ $item['href'] }}" wire:navigate
                                           class="flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors {{ $isActive ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' }}">
                                            <x-icon :name="$item['icon']" class="size-4 shrink-0" />
                                            {{ $item['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </nav>
            {{-- Footer --}}
            <div class="border-t border-sidebar-border p-2" x-data="{ userMenu: false }">
                <button @click="userMenu = !userMenu" @click.away="userMenu = false"
                        class="flex w-full items-center gap-2 rounded-md p-2 text-left hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors">
                    <div class="flex size-8 items-center justify-center rounded-full bg-primary text-primary-foreground text-sm font-semibold shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex flex-1 flex-col min-w-0 text-sm leading-tight">
                        <span class="truncate font-medium text-sidebar-foreground">{{ auth()->user()->name }}</span>
                        <span class="truncate text-xs text-muted-foreground">{{ auth()->user()->email }}</span>
                    </div>
                    <svg class="ml-auto size-4 shrink-0 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
                <div x-show="userMenu"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute bottom-full left-2 right-2 mb-1 min-w-32 origin-bottom rounded-lg bg-popover p-1 text-popover-foreground shadow-md ring-1 ring-foreground/10"
                     style="display: none;">
                    <div class="px-1.5 py-1 text-xs font-medium text-muted-foreground">Akun Saya</div>
                    <a wire:navigate href="{{ route('settings.company') }}"
                       class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                        <x-icon name="settings" class="size-4" /> Company Settings
                    </a>
                    <div class="-mx-1 my-1 h-px bg-border"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none text-destructive hover:bg-destructive/10 hover:text-destructive">
                            <x-icon name="log-out" class="size-4" /> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex flex-1 flex-col transition-all duration-200" :class="sidebarOpen ? 'lg:ml-64' : 'ml-0'">
            {{-- Header --}}
            <header class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-4 border-b bg-background px-4">
                <button @click="sidebarOpen = !sidebarOpen" class="rounded-md p-1.5 hover:bg-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
                </button>
                {{-- Breadcrumb --}}
                <nav class="flex items-center text-sm text-muted-foreground">
                    @php
                        $segments = array_filter(explode('/', request()->path()));
                        $breadcrumbs = [];
                        $href = '';
                        $labels = [
                            'dashboard' => 'Dashboard', 'work-orders' => 'Work Orders', 'requests' => 'Requests',
                            'clients' => 'Clients', 'vendors' => 'Vendors', 'invoices' => 'Invoices',
                            'transactions' => 'Transactions', 'journal-entries' => 'Journal Entries',
                            'accounts' => 'Chart of Accounts', 'employees' => 'Employees', 'payroll' => 'Payroll',
                            'reports' => 'Reports', 'settings' => 'Settings', 'company' => 'Company',
                            'users' => 'Users', 'roles' => 'Roles', 'audit-logs' => 'Audit Logs',
                            'tax-rates' => 'Tax Rates', 'create' => 'Create', 'edit' => 'Edit', 'tutorial' => 'Tutorial',
                        ];
                        foreach ($segments as $segment) {
                            $href .= '/' . $segment;
                            $breadcrumbs[] = ['href' => $href, 'label' => $labels[$segment] ?? ucfirst(str_replace('-', ' ', $segment))];
                        }
                    @endphp
                    @foreach ($breadcrumbs as $i => $crumb)
                        @if ($i > 0)
                            <svg class="mx-2 size-3.5 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        @endif
                        @if ($loop->last)
                            <span class="font-medium text-foreground">{{ $crumb['label'] }}</span>
                        @else
                            <a href="{{ $crumb['href'] }}" wire:navigate class="hover:text-foreground">{{ $crumb['label'] }}</a>
                        @endif
                    @endforeach
                </nav>
                <div class="ml-auto flex items-center gap-2">
                </div>
            </header>
            <main class="flex-1 overflow-auto p-4">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         class="mb-4 rounded-md bg-green-500/15 p-3 text-sm text-green-400">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         class="mb-4 rounded-md bg-destructive/10 p-3 text-sm text-destructive">
                        {{ session('error') }}
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
