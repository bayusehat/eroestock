<x-layouts.app title="Tutorial">
    <div class="space-y-8">
        <x-page-header title="Tutorial" description="Pelajari dasar-dasar akuntansi dan cara menggunakan fitur Journal Entries &amp; Chart of Accounts" />

        {{-- Table of Contents --}}
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-6 pb-4">
                <h2 class="text-base font-semibold">Daftar Isi</h2>
            </div>
            <div class="px-6 pb-6">
                <nav class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @php
                        $toc = [
                            ['id' => 'fundamentals', 'label' => 'Accounting Fundamentals', 'icon' => 'graduation-cap'],
                            ['id' => 'accounting-cycle', 'label' => 'The Accounting Cycle', 'icon' => 'refresh-cw'],
                            ['id' => 'financial-statements', 'label' => 'Financial Statements', 'icon' => 'bar-chart-3'],
                            ['id' => 'advanced-concepts', 'label' => 'Advanced Concepts', 'icon' => 'book-marked'],
                            ['id' => 'chart-of-accounts', 'label' => 'Chart of Accounts', 'icon' => 'network'],
                            ['id' => 'journal-entries', 'label' => 'Journal Entries', 'icon' => 'book-open'],
                            ['id' => 'glossary', 'label' => 'Glossary', 'icon' => 'clipboard-check'],
                        ];
                    @endphp
                    @foreach ($toc as $item)
                        <a href="#{{ $item['id'] }}"
                           class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-muted">
                            <x-icon :name="$item['icon']" class="size-5 text-primary shrink-0" />
                            <span class="text-sm font-medium">{{ $item['label'] }}</span>
                            <x-icon name="arrow-right" class="ml-auto size-4 text-muted-foreground shrink-0" />
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Section 1: Accounting Fundamentals --}}
        <div id="fundamentals" class="scroll-mt-20 space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                    <x-icon name="graduation-cap" class="size-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">Accounting Fundamentals</h2>
                    <p class="text-sm text-muted-foreground">Dasar-dasar akuntansi yang perlu Anda ketahui</p>
                </div>
            </div>

            {{-- Double Entry --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2">
                    <h3 class="font-semibold">Double-Entry Bookkeeping</h3>
                    <p class="text-sm text-muted-foreground">Sistem pencatatan berpasangan</p>
                </div>
                <div class="space-y-4 p-6 pt-3">
                    <p class="text-sm leading-relaxed">
                        Double-entry bookkeeping adalah prinsip dasar akuntansi modern. Setiap transaksi
                        dicatat di <strong>minimal dua akun</strong> &mdash; satu sisi debit dan satu sisi
                        kredit. Total debit harus <strong>selalu sama</strong> dengan total kredit.
                    </p>
                    <div class="flex gap-3 rounded-lg border border-amber-900 bg-amber-950/30 p-4">
                        <x-icon name="lightbulb" class="mt-0.5 size-5 shrink-0 text-amber-400" />
                        <p class="text-sm text-amber-200">Sistem ini memastikan bahwa buku besar Anda selalu seimbang (balanced) dan memudahkan pelacakan kesalahan pencatatan.</p>
                    </div>
                </div>
            </div>

            {{-- Accounting Equation --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2">
                    <h3 class="flex items-center gap-2 font-semibold"><x-icon name="scale" class="size-5" /> The Accounting Equation</h3>
                    <p class="text-sm text-muted-foreground">Persamaan dasar akuntansi</p>
                </div>
                <div class="space-y-4 p-6 pt-3">
                    <div class="flex items-center justify-center rounded-lg bg-muted/50 p-6">
                        <div class="flex flex-wrap items-center justify-center gap-2 text-center">
                            <span class="inline-flex items-center rounded-md border px-4 py-2 text-base font-bold text-blue-400">Assets</span>
                            <span class="text-xl font-bold">=</span>
                            <span class="inline-flex items-center rounded-md border px-4 py-2 text-base font-bold text-orange-400">Liabilities</span>
                            <span class="text-xl font-bold">+</span>
                            <span class="inline-flex items-center rounded-md border px-4 py-2 text-base font-bold text-purple-400">Equity</span>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed">Setiap transaksi yang dicatat akan menjaga persamaan ini tetap seimbang. Aset (harta) selalu sama dengan jumlah Kewajiban (utang) ditambah Ekuitas (modal pemilik).</p>
                </div>
            </div>

            {{-- 5 Account Types --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2">
                    <h3 class="font-semibold">The 5 Account Types</h3>
                    <p class="text-sm text-muted-foreground">Lima jenis akun dalam akuntansi</p>
                </div>
                <div class="p-6 pt-3">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @php
                            $accountTypes = [
                                ['type' => 'Asset', 'desc' => 'Sumber daya yang dimiliki perusahaan', 'examples' => 'Cash, Bank, Accounts Receivable, Equipment, Inventory', 'balance' => 'Debit', 'range' => '1xxx', 'color' => 'text-blue-400', 'bg' => 'bg-blue-950/40 border-blue-900'],
                                ['type' => 'Liability', 'desc' => 'Kewajiban yang harus dibayar', 'examples' => 'Accounts Payable, Loans, Accrued Expenses, Unearned Revenue', 'balance' => 'Credit', 'range' => '2xxx', 'color' => 'text-orange-400', 'bg' => 'bg-orange-950/40 border-orange-900'],
                                ['type' => 'Equity', 'desc' => 'Modal atau hak pemilik atas aset', 'examples' => "Owner's Capital, Retained Earnings, Drawings", 'balance' => 'Credit', 'range' => '3xxx', 'color' => 'text-purple-400', 'bg' => 'bg-purple-950/40 border-purple-900'],
                                ['type' => 'Revenue', 'desc' => 'Pendapatan dari aktivitas bisnis', 'examples' => 'Sales Revenue, Service Revenue, Interest Income', 'balance' => 'Credit', 'range' => '4xxx', 'color' => 'text-green-400', 'bg' => 'bg-green-950/40 border-green-900'],
                                ['type' => 'Expense', 'desc' => 'Biaya yang dikeluarkan untuk operasional', 'examples' => 'Rent, Salaries, Utilities, Supplies, Depreciation', 'balance' => 'Debit', 'range' => '5xxx', 'color' => 'text-red-400', 'bg' => 'bg-red-950/40 border-red-900'],
                            ];
                        @endphp
                        @foreach ($accountTypes as $acc)
                            <div class="rounded-lg border p-4 {{ $acc['bg'] }}">
                                <div class="mb-2 flex items-center justify-between">
                                    <h4 class="font-bold {{ $acc['color'] }}">{{ $acc['type'] }}</h4>
                                    <span class="inline-flex items-center rounded-md border bg-background px-2 py-0.5 font-mono text-xs">{{ $acc['range'] }}</span>
                                </div>
                                <p class="mb-2 text-sm">{{ $acc['desc'] }}</p>
                                <p class="mb-2 text-xs text-muted-foreground">Contoh: {{ $acc['examples'] }}</p>
                                <p class="text-xs font-medium">Normal Balance: <span class="inline-flex items-center rounded-md border bg-background px-2 py-0.5 text-xs ml-1">{{ $acc['balance'] }}</span></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Debit & Credit Rules --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2">
                    <h3 class="flex items-center gap-2 font-semibold"><x-icon name="calculator" class="size-5" /> Understanding Debits &amp; Credits</h3>
                    <p class="text-sm text-muted-foreground">Kapan menggunakan debit dan kredit</p>
                </div>
                <div class="space-y-4 p-6 pt-3">
                    <div class="overflow-x-auto rounded-md border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="p-3 text-left font-semibold">Account Type</th>
                                    <th class="p-3 text-center font-semibold">Debit</th>
                                    <th class="p-3 text-center font-semibold">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dcRules = [
                                        ['type' => 'Asset', 'debit' => 'Increase (+)', 'credit' => 'Decrease (-)'],
                                        ['type' => 'Liability', 'debit' => 'Decrease (-)', 'credit' => 'Increase (+)'],
                                        ['type' => 'Equity', 'debit' => 'Decrease (-)', 'credit' => 'Increase (+)'],
                                        ['type' => 'Revenue', 'debit' => 'Decrease (-)', 'credit' => 'Increase (+)'],
                                        ['type' => 'Expense', 'debit' => 'Increase (+)', 'credit' => 'Decrease (-)'],
                                    ];
                                @endphp
                                @foreach ($dcRules as $rule)
                                    <tr class="border-b last:border-0">
                                        <td class="p-3 font-medium">{{ $rule['type'] }}</td>
                                        <td class="p-3 text-center">
                                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ str_contains($rule['debit'], '+') ? 'border-green-800 bg-green-950/40 text-green-400' : 'border-red-800 bg-red-950/40 text-red-400' }}">
                                                {{ $rule['debit'] }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ str_contains($rule['credit'], '+') ? 'border-green-800 bg-green-950/40 text-green-400' : 'border-red-800 bg-red-950/40 text-red-400' }}">
                                                {{ $rule['credit'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex gap-3 rounded-lg border border-amber-900 bg-amber-950/30 p-4">
                        <x-icon name="lightbulb" class="mt-0.5 size-5 shrink-0 text-amber-400" />
                        <p class="text-sm text-amber-200"><strong>Cara mudah mengingat:</strong> Debit menambah Asset dan Expense (sisi kiri persamaan akuntansi). Credit menambah Liability, Equity, dan Revenue (sisi kanan).</p>
                    </div>
                </div>
            </div>

            {{-- Journal Entry Examples --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2">
                    <h3 class="font-semibold">Common Journal Entry Examples</h3>
                    <p class="text-sm text-muted-foreground">Contoh pencatatan jurnal yang umum dalam bisnis</p>
                </div>
                <div class="space-y-6 p-6 pt-3">
                    @php
                        $journalExamples = [
                            ['title' => 'Menerima pembayaran dari klien', 'desc' => 'Klien membayar Rp 5.000.000 untuk jasa konsultasi', 'lines' => [['account' => '1100 - Cash', 'debit' => 'Rp 5.000.000', 'credit' => '-'], ['account' => '4100 - Service Revenue', 'debit' => '-', 'credit' => 'Rp 5.000.000']]],
                            ['title' => 'Membayar sewa kantor', 'desc' => 'Pembayaran sewa bulanan Rp 3.000.000', 'lines' => [['account' => '5200 - Rent Expense', 'debit' => 'Rp 3.000.000', 'credit' => '-'], ['account' => '1100 - Cash', 'debit' => '-', 'credit' => 'Rp 3.000.000']]],
                            ['title' => 'Membeli peralatan secara kredit', 'desc' => 'Pembelian laptop Rp 15.000.000 dengan kredit', 'lines' => [['account' => '1300 - Equipment', 'debit' => 'Rp 15.000.000', 'credit' => '-'], ['account' => '2100 - Accounts Payable', 'debit' => '-', 'credit' => 'Rp 15.000.000']]],
                            ['title' => 'Pemilik menambah modal', 'desc' => 'Pemilik menginvestasikan Rp 50.000.000 ke bisnis', 'lines' => [['account' => '1100 - Cash', 'debit' => 'Rp 50.000.000', 'credit' => '-'], ["account" => "3100 - Owner's Capital", 'debit' => '-', 'credit' => 'Rp 50.000.000']]],
                            ['title' => 'Membayar gaji karyawan', 'desc' => 'Pembayaran gaji 3 karyawan total Rp 18.000.000', 'lines' => [['account' => '5100 - Salaries Expense', 'debit' => 'Rp 18.000.000', 'credit' => '-'], ['account' => '1200 - Bank', 'debit' => '-', 'credit' => 'Rp 18.000.000']]],
                            ['title' => 'Menerima pinjaman bank', 'desc' => 'Perusahaan menerima pinjaman Rp 100.000.000 dari bank', 'lines' => [['account' => '1200 - Bank', 'debit' => 'Rp 100.000.000', 'credit' => '-'], ['account' => '2200 - Bank Loan', 'debit' => '-', 'credit' => 'Rp 100.000.000']]],
                            ['title' => 'Mencatat penyusutan bulanan', 'desc' => 'Penyusutan peralatan bulan ini Rp 1.250.000', 'lines' => [['account' => '5400 - Depreciation Expense', 'debit' => 'Rp 1.250.000', 'credit' => '-'], ['account' => '1301 - Accumulated Depreciation', 'debit' => '-', 'credit' => 'Rp 1.250.000']]],
                            ['title' => 'Membayar sebagian utang dagang', 'desc' => 'Membayar Rp 10.000.000 dari utang ke supplier', 'lines' => [['account' => '2100 - Accounts Payable', 'debit' => 'Rp 10.000.000', 'credit' => '-'], ['account' => '1200 - Bank', 'debit' => '-', 'credit' => 'Rp 10.000.000']]],
                        ];
                    @endphp
                    @foreach ($journalExamples as $i => $ex)
                        <div class="space-y-2">
                            <div>
                                <h4 class="font-semibold">{{ $i + 1 }}. {{ $ex['title'] }}</h4>
                                <p class="text-sm text-muted-foreground">{{ $ex['desc'] }}</p>
                            </div>
                            <div class="overflow-x-auto rounded-md border">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b bg-muted/50">
                                            <th class="p-2 text-left font-medium">Account</th>
                                            <th class="p-2 text-right font-medium">Debit</th>
                                            <th class="p-2 text-right font-medium">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ex['lines'] as $line)
                                            <tr class="border-b last:border-0">
                                                <td class="p-2 font-mono text-sm">{{ $line['account'] }}</td>
                                                <td class="p-2 text-right font-mono">{{ $line['debit'] }}</td>
                                                <td class="p-2 text-right font-mono">{{ $line['credit'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Account Numbering --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2">
                    <h3 class="font-semibold">Account Numbering Convention</h3>
                    <p class="text-sm text-muted-foreground">Konvensi penomoran akun standar</p>
                </div>
                <div class="space-y-4 p-6 pt-3">
                    <p class="text-sm leading-relaxed">Akun biasanya diberi kode nomor untuk memudahkan pengorganisasian. Berikut konvensi umum yang digunakan:</p>
                    <div class="overflow-x-auto rounded-md border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="p-3 text-left font-semibold">Code Range</th>
                                    <th class="p-3 text-left font-semibold">Type</th>
                                    <th class="p-3 text-left font-semibold">Contoh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b"><td class="p-3 font-mono">1000 &ndash; 1999</td><td class="p-3">Asset</td><td class="p-3 text-muted-foreground">1100 Cash, 1200 Bank, 1300 Equipment</td></tr>
                                <tr class="border-b"><td class="p-3 font-mono">2000 &ndash; 2999</td><td class="p-3">Liability</td><td class="p-3 text-muted-foreground">2100 Accounts Payable, 2200 Loans</td></tr>
                                <tr class="border-b"><td class="p-3 font-mono">3000 &ndash; 3999</td><td class="p-3">Equity</td><td class="p-3 text-muted-foreground">3100 Owner's Capital, 3200 Retained Earnings</td></tr>
                                <tr class="border-b"><td class="p-3 font-mono">4000 &ndash; 4999</td><td class="p-3">Revenue</td><td class="p-3 text-muted-foreground">4100 Sales, 4200 Service Revenue</td></tr>
                                <tr><td class="p-3 font-mono">5000 &ndash; 5999</td><td class="p-3">Expense</td><td class="p-3 text-muted-foreground">5100 Salaries, 5200 Rent, 5300 Utilities</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: The Accounting Cycle --}}
        <div id="accounting-cycle" class="scroll-mt-20 space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                    <x-icon name="refresh-cw" class="size-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">The Accounting Cycle</h2>
                    <p class="text-sm text-muted-foreground">Siklus akuntansi dari awal hingga akhir periode</p>
                </div>
            </div>

            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Apa itu Siklus Akuntansi?</h3><p class="text-sm text-muted-foreground">Proses berulang setiap periode akuntansi</p></div>
                <div class="p-6 pt-3"><p class="text-sm leading-relaxed">Siklus akuntansi (accounting cycle) adalah serangkaian langkah yang dilakukan secara berurutan untuk mencatat, mengolah, dan melaporkan transaksi keuangan dalam satu periode. Siklus ini berulang setiap periode akuntansi (biasanya bulanan, kuartalan, atau tahunan).</p></div>
            </div>

            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">9 Langkah Siklus Akuntansi</h3></div>
                <div class="p-6 pt-3 space-y-4">
                    @php
                        $cycleSteps = [
                            ['step' => 1, 'title' => 'Identifikasi Transaksi', 'desc' => 'Kenali dan kumpulkan bukti transaksi bisnis (faktur, kwitansi, nota, dll.)'],
                            ['step' => 2, 'title' => 'Catat di Jurnal', 'desc' => 'Catat setiap transaksi ke dalam journal entry dengan debit dan kredit yang sesuai.'],
                            ['step' => 3, 'title' => 'Posting ke Buku Besar', 'desc' => 'Pindahkan catatan dari jurnal ke masing-masing akun di buku besar (general ledger).'],
                            ['step' => 4, 'title' => 'Buat Trial Balance', 'desc' => 'Susun neraca saldo untuk memastikan total debit sama dengan total kredit.'],
                            ['step' => 5, 'title' => 'Jurnal Penyesuaian', 'desc' => 'Buat adjusting entries untuk mencatat pendapatan/beban yang belum tercatat (accrual, prepaid, depreciation).'],
                            ['step' => 6, 'title' => 'Adjusted Trial Balance', 'desc' => 'Susun ulang neraca saldo setelah jurnal penyesuaian.'],
                            ['step' => 7, 'title' => 'Laporan Keuangan', 'desc' => 'Buat laporan keuangan: Income Statement, Balance Sheet, Cash Flow Statement.'],
                            ['step' => 8, 'title' => 'Jurnal Penutup', 'desc' => 'Tutup akun Revenue, Expense, dan Drawings ke Retained Earnings di akhir periode.'],
                            ['step' => 9, 'title' => 'Post-Closing Trial Balance', 'desc' => 'Verifikasi saldo akhir hanya berisi akun permanen (Asset, Liability, Equity).'],
                        ];
                    @endphp
                    @foreach ($cycleSteps as $item)
                        <div class="flex gap-4">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">{{ $item['step'] }}</div>
                            <div class="pt-0.5">
                                <h4 class="font-semibold">{{ $item['title'] }}</h4>
                                <p class="text-sm text-muted-foreground">{{ $item['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-2 flex gap-3 rounded-lg border border-amber-900 bg-amber-950/30 p-4">
                        <x-icon name="lightbulb" class="mt-0.5 size-5 shrink-0 text-amber-400" />
                        <p class="text-sm text-amber-200">Di Kucatat, langkah 1&ndash;3 dilakukan saat Anda membuat Journal Entry. Langkah 4 bisa dilihat di <a wire:navigate href="{{ route('reports.trial-balance') }}" class="font-medium underline">halaman Reports</a> (Trial Balance). Laporan keuangan (langkah 7) tersedia di menu Reports (Profit &amp; Loss, Balance Sheet, Cash Flow).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Financial Statements --}}
        <div id="financial-statements" class="scroll-mt-20 space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                    <x-icon name="bar-chart-3" class="size-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">Financial Statements</h2>
                    <p class="text-sm text-muted-foreground">Memahami laporan keuangan utama</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg border bg-card shadow-sm p-6 space-y-3">
                    <h3 class="flex items-center gap-2 font-semibold"><x-icon name="trending-up" class="size-5" /> Income Statement (Laporan Laba Rugi)</h3>
                    <p class="text-sm leading-relaxed">Menunjukkan <strong>pendapatan</strong> dan <strong>beban</strong> selama periode tertentu, menghasilkan <strong>laba bersih</strong> (net profit) atau <strong>rugi bersih</strong> (net loss).</p>
                    <div class="rounded-lg bg-muted/50 p-3 text-center text-sm font-semibold">Revenue &minus; Expenses = Net Profit/Loss</div>
                    <p class="text-xs text-muted-foreground">Di Kucatat: lihat di <a wire:navigate href="{{ route('reports.profit-loss') }}" class="text-primary hover:underline">Reports</a> &rarr; Profit &amp; Loss</p>
                </div>
                <div class="rounded-lg border bg-card shadow-sm p-6 space-y-3">
                    <h3 class="flex items-center gap-2 font-semibold"><x-icon name="scale" class="size-5" /> Balance Sheet (Neraca)</h3>
                    <p class="text-sm leading-relaxed">Menunjukkan posisi keuangan pada <strong>satu titik waktu</strong> tertentu: apa yang dimiliki (Assets), apa yang diutangkan (Liabilities), dan berapa modal pemilik (Equity).</p>
                    <div class="rounded-lg bg-muted/50 p-3 text-center text-sm font-semibold">Assets = Liabilities + Equity</div>
                    <p class="text-xs text-muted-foreground">Di Kucatat: lihat di <a wire:navigate href="{{ route('reports.balance-sheet') }}" class="text-primary hover:underline">Reports</a> &rarr; Balance Sheet</p>
                </div>
                <div class="rounded-lg border bg-card shadow-sm p-6 space-y-3">
                    <h3 class="flex items-center gap-2 font-semibold"><x-icon name="refresh-cw" class="size-5" /> Cash Flow Statement (Laporan Arus Kas)</h3>
                    <p class="text-sm leading-relaxed">Melacak pergerakan kas masuk dan keluar, dibagi menjadi tiga aktivitas:</p>
                    <ul class="ml-4 list-disc space-y-1 text-sm">
                        <li><strong>Operating</strong> &mdash; Arus kas dari operasional bisnis sehari-hari</li>
                        <li><strong>Investing</strong> &mdash; Pembelian/penjualan aset jangka panjang</li>
                        <li><strong>Financing</strong> &mdash; Pinjaman, setoran modal, pembayaran utang</li>
                    </ul>
                    <p class="text-xs text-muted-foreground">Di Kucatat: lihat di <a wire:navigate href="{{ route('reports.cash-flow') }}" class="text-primary hover:underline">Reports</a> &rarr; Cash Flow Statement</p>
                </div>
                <div class="rounded-lg border bg-card shadow-sm p-6 space-y-3">
                    <h3 class="flex items-center gap-2 font-semibold"><x-icon name="clipboard-check" class="size-5" /> Trial Balance (Neraca Saldo)</h3>
                    <p class="text-sm leading-relaxed">Daftar semua akun beserta saldo debit dan kreditnya. Digunakan sebagai <strong>alat verifikasi</strong> bahwa total debit sama dengan total kredit sebelum menyusun laporan keuangan.</p>
                    <div class="rounded-lg bg-muted/50 p-3 text-center text-sm font-semibold">Total Debits = Total Credits</div>
                    <p class="text-xs text-muted-foreground">Di Kucatat: lihat di <a wire:navigate href="{{ route('reports.trial-balance') }}" class="text-primary hover:underline">Reports</a> &rarr; Trial Balance</p>
                </div>
            </div>

            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Hubungan Antar Laporan Keuangan</h3><p class="text-sm text-muted-foreground">Bagaimana laporan keuangan saling terhubung</p></div>
                <div class="p-6 pt-3">
                    <div class="overflow-x-auto rounded-md border">
                        <table class="w-full text-sm">
                            <thead><tr class="border-b bg-muted/50"><th class="p-3 text-left font-semibold">Dari</th><th class="p-3 text-left font-semibold">Ke</th><th class="p-3 text-left font-semibold">Hubungan</th></tr></thead>
                            <tbody>
                                <tr class="border-b"><td class="p-3 font-medium">Income Statement</td><td class="p-3">Balance Sheet</td><td class="p-3 text-muted-foreground">Net Profit masuk ke Retained Earnings (Equity)</td></tr>
                                <tr class="border-b"><td class="p-3 font-medium">Income Statement</td><td class="p-3">Cash Flow</td><td class="p-3 text-muted-foreground">Net Profit menjadi titik awal Operating Activities</td></tr>
                                <tr class="border-b"><td class="p-3 font-medium">Cash Flow</td><td class="p-3">Balance Sheet</td><td class="p-3 text-muted-foreground">Closing Balance = saldo Cash di Balance Sheet</td></tr>
                                <tr><td class="p-3 font-medium">Trial Balance</td><td class="p-3">Semua Laporan</td><td class="p-3 text-muted-foreground">Sumber data untuk menyusun ketiga laporan keuangan</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Advanced Concepts --}}
        <div id="advanced-concepts" class="scroll-mt-20 space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                    <x-icon name="book-marked" class="size-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">Advanced Concepts</h2>
                    <p class="text-sm text-muted-foreground">Konsep akuntansi lanjutan untuk pemahaman yang lebih mendalam</p>
                </div>
            </div>

            {{-- Accrual vs Cash Basis --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Accrual vs Cash Basis</h3><p class="text-sm text-muted-foreground">Dua metode pencatatan akuntansi</p></div>
                <div class="space-y-4 p-6 pt-3">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-blue-900 bg-blue-950/30 p-4">
                            <h4 class="mb-2 font-bold text-blue-400">Accrual Basis</h4>
                            <ul class="space-y-2 text-sm">
                                <li>Pendapatan diakui saat <strong>diperoleh</strong> (earned), bukan saat kas diterima</li>
                                <li>Beban diakui saat <strong>terjadi</strong> (incurred), bukan saat kas dibayar</li>
                                <li>Lebih akurat menggambarkan kondisi keuangan</li>
                                <li>Digunakan oleh sebagian besar bisnis menengah-besar</li>
                                <li>Sesuai dengan standar akuntansi (PSAK/IFRS)</li>
                            </ul>
                        </div>
                        <div class="rounded-lg border border-green-900 bg-green-950/30 p-4">
                            <h4 class="mb-2 font-bold text-green-400">Cash Basis</h4>
                            <ul class="space-y-2 text-sm">
                                <li>Pendapatan diakui hanya saat kas <strong>diterima</strong></li>
                                <li>Beban diakui hanya saat kas <strong>dibayarkan</strong></li>
                                <li>Lebih sederhana dan mudah dipahami</li>
                                <li>Cocok untuk bisnis kecil dan usaha perorangan</li>
                                <li>Tidak menampilkan utang-piutang</li>
                            </ul>
                        </div>
                    </div>
                    <div class="flex gap-3 rounded-lg border border-amber-900 bg-amber-950/30 p-4">
                        <x-icon name="lightbulb" class="mt-0.5 size-5 shrink-0 text-amber-400" />
                        <p class="text-sm text-amber-200">Kucatat mendukung <strong>accrual basis</strong> karena menggunakan Accounts Receivable (Piutang) dan Accounts Payable (Utang) dalam pencatatan. Ini memberikan gambaran keuangan yang lebih lengkap.</p>
                    </div>
                </div>
            </div>

            {{-- Adjusting Entries --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Adjusting Entries (Jurnal Penyesuaian)</h3><p class="text-sm text-muted-foreground">Pencatatan di akhir periode untuk memastikan akurasi</p></div>
                <div class="space-y-4 p-6 pt-3">
                    <p class="text-sm leading-relaxed">Adjusting entries dibuat di akhir periode akuntansi untuk memastikan bahwa pendapatan dan beban dicatat pada periode yang tepat. Ada empat jenis utama:</p>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border p-4">
                            <h4 class="mb-1 font-semibold">1. Accrued Revenue</h4>
                            <p class="mb-2 text-sm text-muted-foreground">Pendapatan yang sudah diperoleh tapi belum diterima kasnya</p>
                            <div class="rounded bg-muted/50 p-2 text-xs font-mono"><p>Dr. Accounts Receivable</p><p>&nbsp;&nbsp;Cr. Service Revenue</p></div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <h4 class="mb-1 font-semibold">2. Accrued Expense</h4>
                            <p class="mb-2 text-sm text-muted-foreground">Beban yang sudah terjadi tapi belum dibayarkan</p>
                            <div class="rounded bg-muted/50 p-2 text-xs font-mono"><p>Dr. Salaries Expense</p><p>&nbsp;&nbsp;Cr. Salaries Payable</p></div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <h4 class="mb-1 font-semibold">3. Prepaid Expense (Deferred)</h4>
                            <p class="mb-2 text-sm text-muted-foreground">Beban yang sudah dibayar di muka, diakui secara bertahap</p>
                            <div class="rounded bg-muted/50 p-2 text-xs font-mono"><p>Dr. Insurance Expense</p><p>&nbsp;&nbsp;Cr. Prepaid Insurance</p></div>
                        </div>
                        <div class="rounded-lg border p-4">
                            <h4 class="mb-1 font-semibold">4. Unearned Revenue (Deferred)</h4>
                            <p class="mb-2 text-sm text-muted-foreground">Pendapatan diterima di muka, diakui saat jasa diberikan</p>
                            <div class="rounded bg-muted/50 p-2 text-xs font-mono"><p>Dr. Unearned Revenue</p><p>&nbsp;&nbsp;Cr. Service Revenue</p></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Closing Entries --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Closing Entries (Jurnal Penutup)</h3><p class="text-sm text-muted-foreground">Menutup akun temporary di akhir periode</p></div>
                <div class="space-y-4 p-6 pt-3">
                    <p class="text-sm leading-relaxed">Di akhir periode akuntansi, akun <strong>temporary</strong> (Revenue, Expense, Drawings) harus ditutup ke <strong>Retained Earnings</strong>. Ini meng-nol-kan akun temporary agar siap untuk periode berikutnya.</p>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border p-4">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium mb-2">Permanent Accounts</span>
                            <p class="text-sm">Asset, Liability, Equity</p>
                            <p class="mt-1 text-xs text-muted-foreground">Saldo terbawa ke periode berikutnya (tidak ditutup)</p>
                        </div>
                        <div class="rounded-lg border p-4">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium mb-2">Temporary Accounts</span>
                            <p class="text-sm">Revenue, Expense, Drawings</p>
                            <p class="mt-1 text-xs text-muted-foreground">Saldo ditutup ke Retained Earnings di akhir periode</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <h4 class="text-sm font-semibold">Langkah menutup akun:</h4>
                        <div class="rounded-lg border p-3"><p class="text-sm font-medium">1. Tutup Revenue ke Income Summary</p><div class="mt-1 rounded bg-muted/50 p-2 text-xs font-mono"><p>Dr. Revenue &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp xxx</p><p>&nbsp;&nbsp;Cr. Income Summary &nbsp;&nbsp;&nbsp;&nbsp;Rp xxx</p></div></div>
                        <div class="rounded-lg border p-3"><p class="text-sm font-medium">2. Tutup Expense ke Income Summary</p><div class="mt-1 rounded bg-muted/50 p-2 text-xs font-mono"><p>Dr. Income Summary &nbsp;&nbsp;&nbsp;&nbsp;Rp xxx</p><p>&nbsp;&nbsp;Cr. Expenses &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp xxx</p></div></div>
                        <div class="rounded-lg border p-3"><p class="text-sm font-medium">3. Tutup Income Summary ke Retained Earnings</p><div class="mt-1 rounded bg-muted/50 p-2 text-xs font-mono"><p>Dr. Income Summary &nbsp;&nbsp;&nbsp;&nbsp;Rp xxx (jika laba)</p><p>&nbsp;&nbsp;Cr. Retained Earnings &nbsp;&nbsp;Rp xxx</p></div></div>
                    </div>
                </div>
            </div>

            {{-- Common Mistakes --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="flex items-center gap-2 font-semibold"><x-icon name="alert-triangle" class="size-5" /> Kesalahan Umum dalam Akuntansi</h3><p class="text-sm text-muted-foreground">Hal-hal yang perlu dihindari</p></div>
                <div class="space-y-3 p-6 pt-3">
                    @php
                        $mistakes = [
                            ['title' => 'Mencampur debit dan kredit', 'desc' => 'Pastikan memahami akun mana yang bertambah di sisi debit vs kredit. Gunakan tabel referensi di atas.'],
                            ['title' => 'Tidak mencatat transaksi tepat waktu', 'desc' => 'Catat transaksi sesegera mungkin agar tidak ada yang terlewat dan laporan selalu up-to-date.'],
                            ['title' => 'Menggunakan akun yang salah', 'desc' => 'Pastikan memilih akun yang tepat dari Chart of Accounts. Contoh: jangan mencatat pembelian aset sebagai expense.'],
                            ['title' => 'Tidak melakukan rekonsiliasi', 'desc' => 'Secara berkala bandingkan catatan di Kucatat dengan rekening bank untuk menemukan perbedaan.'],
                            ['title' => 'Lupa membuat jurnal penyesuaian', 'desc' => 'Di akhir periode, buat adjusting entries untuk penyusutan, prepaid, accrual, dan item lain yang memerlukan penyesuaian.'],
                            ['title' => 'Deskripsi transaksi tidak jelas', 'desc' => 'Selalu isi deskripsi yang jelas pada journal entry agar mudah dilacak dan di-audit di kemudian hari.'],
                        ];
                    @endphp
                    @foreach ($mistakes as $i => $m)
                        <div class="flex gap-3 rounded-lg border border-red-900 bg-red-950/30 p-3">
                            <span class="text-sm font-bold text-red-400">{{ $i + 1 }}.</span>
                            <div>
                                <p class="text-sm font-medium text-red-200">{{ $m['title'] }}</p>
                                <p class="text-xs text-red-300">{{ $m['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Accounting Principles --}}
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Prinsip-Prinsip Dasar Akuntansi</h3><p class="text-sm text-muted-foreground">Pedoman yang mendasari pencatatan keuangan</p></div>
                <div class="p-6 pt-3">
                    <div class="grid gap-4 md:grid-cols-2">
                        @php
                            $principles = [
                                ['name' => 'Going Concern', 'desc' => 'Asumsi bahwa bisnis akan terus beroperasi di masa mendatang, bukan akan dilikuidasi.'],
                                ['name' => 'Matching Principle', 'desc' => 'Beban harus dicatat pada periode yang sama dengan pendapatan yang dihasilkannya.'],
                                ['name' => 'Revenue Recognition', 'desc' => 'Pendapatan diakui saat barang/jasa telah diberikan, bukan saat kas diterima.'],
                                ['name' => 'Conservatism', 'desc' => 'Jika ada keraguan, pilih metode yang menghasilkan aset/pendapatan lebih rendah atau kewajiban/beban lebih tinggi.'],
                                ['name' => 'Consistency', 'desc' => 'Gunakan metode akuntansi yang sama dari periode ke periode agar laporan dapat dibandingkan.'],
                                ['name' => 'Materiality', 'desc' => 'Fokus pencatatan pada item yang cukup signifikan untuk mempengaruhi keputusan pengguna laporan.'],
                                ['name' => 'Historical Cost', 'desc' => 'Aset dicatat pada harga perolehan aslinya, bukan pada nilai pasar saat ini.'],
                                ['name' => 'Full Disclosure', 'desc' => 'Semua informasi yang relevan dan material harus diungkapkan dalam laporan keuangan.'],
                            ];
                        @endphp
                        @foreach ($principles as $p)
                            <div class="rounded-lg border p-4">
                                <h4 class="mb-1 font-semibold">{{ $p['name'] }}</h4>
                                <p class="text-sm text-muted-foreground">{{ $p['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 5: Chart of Accounts --}}
        <div id="chart-of-accounts" class="scroll-mt-20 space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                    <x-icon name="network" class="size-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">Chart of Accounts</h2>
                    <p class="text-sm text-muted-foreground">Cara mengelola daftar akun di Kucatat</p>
                </div>
            </div>

            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Apa itu Chart of Accounts?</h3></div>
                <div class="space-y-3 p-6 pt-3">
                    <p class="text-sm leading-relaxed">Chart of Accounts (COA) adalah daftar lengkap semua akun yang digunakan dalam pencatatan keuangan bisnis Anda. COA mengorganisir akun-akun ke dalam 5 kategori utama (Asset, Liability, Equity, Revenue, Expense) dan dapat memiliki struktur hierarki (parent &amp; child).</p>
                    <p class="text-sm leading-relaxed">Di Kucatat, Anda bisa melihat COA dalam bentuk <strong>tree view</strong> yang bisa di-expand/collapse untuk navigasi yang mudah.</p>
                </div>
            </div>

            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Cara Menggunakan Chart of Accounts</h3></div>
                <div class="space-y-6 p-6 pt-3">
                    @php
                        $coaSteps = [
                            ['step' => 1, 'title' => 'Buka halaman Chart of Accounts', 'content' => null, 'link' => ['href' => 'accounts.index', 'label' => 'Chart of Accounts'], 'suffix' => ' di sidebar pada bagian <strong>Keuangan</strong>.'],
                            ['step' => 2, 'title' => 'Cari dan filter akun', 'icon' => 'search', 'desc' => 'Gunakan kolom pencarian untuk mencari akun berdasarkan nama atau kode. Anda juga bisa memfilter berdasarkan tipe akun (Asset, Liability, dll.).'],
                            ['step' => 3, 'title' => 'Lihat struktur akun', 'icon' => 'chevron-down', 'desc' => 'Klik tombol expand/collapse untuk melihat akun child di bawah akun parent. Akun ditampilkan dalam format tree (hierarki).'],
                            ['step' => 4, 'title' => 'Tambah akun baru', 'icon' => 'plus', 'desc' => 'Klik tombol <strong>"Add Account"</strong> di kanan atas. Isi kode akun, nama, dan tipe akun, lalu klik <strong>Add</strong>.'],
                            ['step' => 5, 'title' => 'Edit akun', 'icon' => 'pencil', 'desc' => 'Klik ikon menu (&middot;&middot;&middot;) di kolom aksi, lalu pilih <strong>Edit</strong>. Anda bisa mengubah kode, nama, tipe, sub-tipe, parent account, opening balance, status aktif, dan apakah akun tersebut header account.'],
                        ];
                    @endphp
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">1</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Buka halaman Chart of Accounts</h4>
                            <p class="text-sm text-muted-foreground">Klik menu <a wire:navigate href="{{ route('accounts.index') }}" class="font-medium text-primary hover:underline">Chart of Accounts</a> di sidebar pada bagian <strong>Keuangan</strong>.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">2</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Cari dan filter akun</h4>
                            <p class="flex items-start gap-2 text-sm text-muted-foreground"><x-icon name="search" class="mt-0.5 size-4 shrink-0" /> Gunakan kolom pencarian untuk mencari akun berdasarkan nama atau kode. Anda juga bisa memfilter berdasarkan tipe akun (Asset, Liability, dll.).</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">3</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Lihat struktur akun</h4>
                            <p class="flex items-start gap-2 text-sm text-muted-foreground"><x-icon name="chevron-down" class="mt-0.5 size-4 shrink-0" /> Klik tombol expand/collapse untuk melihat akun child di bawah akun parent. Akun ditampilkan dalam format tree (hierarki).</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">4</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Tambah akun baru</h4>
                            <p class="flex items-start gap-2 text-sm text-muted-foreground"><x-icon name="plus" class="mt-0.5 size-4 shrink-0" /> Klik tombol <strong>"Tambah Akun"</strong> di kanan atas. Isi kode akun, nama, dan tipe akun, lalu klik <strong>Simpan</strong>.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">5</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Edit akun</h4>
                            <p class="flex items-start gap-2 text-sm text-muted-foreground"><x-icon name="pencil" class="mt-0.5 size-4 shrink-0" /> Klik tombol <strong>Edit</strong> di kolom aksi. Anda bisa mengubah kode, nama, tipe, sub-tipe, parent account, opening balance, status aktif, dan apakah akun tersebut header account.</p>
                        </div>
                    </div>
                    <div class="flex gap-3 rounded-lg border border-amber-900 bg-amber-950/30 p-4">
                        <x-icon name="lightbulb" class="mt-0.5 size-5 shrink-0 text-amber-400" />
                        <p class="text-sm text-amber-200"><strong>Header Account vs Transaction Account:</strong> Header account digunakan untuk pengelompokan saja dan tidak bisa digunakan dalam transaksi. Transaction account (non-header) adalah akun yang bisa menerima pencatatan jurnal.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 6: Journal Entries --}}
        <div id="journal-entries" class="scroll-mt-20 space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                    <x-icon name="book-open" class="size-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">Journal Entries</h2>
                    <p class="text-sm text-muted-foreground">Cara mencatat jurnal transaksi di Kucatat</p>
                </div>
            </div>

            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Apa itu Journal Entry?</h3></div>
                <div class="space-y-3 p-6 pt-3">
                    <p class="text-sm leading-relaxed">Journal Entry adalah metode pencatatan transaksi ke dalam sistem akuntansi. Setiap journal entry memiliki minimal <strong>dua baris</strong> (lines) &mdash; satu untuk debit dan satu untuk kredit &mdash; sesuai dengan prinsip double-entry bookkeeping.</p>
                    <p class="text-sm leading-relaxed">Kucatat secara otomatis memvalidasi bahwa total debit sama dengan total kredit sebelum journal entry bisa disimpan.</p>
                </div>
            </div>

            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Cara Menggunakan Journal Entries</h3></div>
                <div class="space-y-6 p-6 pt-3">
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">1</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Buka halaman Journal Entries</h4>
                            <p class="text-sm text-muted-foreground">Klik menu <a wire:navigate href="{{ route('journal-entries.index') }}" class="font-medium text-primary hover:underline">Journal Entries</a> di sidebar pada bagian <strong>Keuangan</strong>.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">2</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Lihat daftar jurnal</h4>
                            <p class="flex items-start gap-2 text-sm text-muted-foreground"><x-icon name="search" class="mt-0.5 size-4 shrink-0" /> Anda akan melihat daftar semua journal entries. Gunakan kolom pencarian untuk mencari berdasarkan nomor jurnal atau deskripsi.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">3</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Buat journal entry baru</h4>
                            <p class="text-sm text-muted-foreground">Klik tombol <a wire:navigate href="{{ route('journal-entries.create') }}" class="inline-flex items-center gap-1 font-medium text-primary hover:underline"><x-icon name="file-text" class="size-4" /> Buat Jurnal</a> di kanan atas halaman.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">4</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Isi detail jurnal</h4>
                            <div class="space-y-2 text-sm text-muted-foreground">
                                <p>Pada halaman pembuatan, isi informasi berikut:</p>
                                <ul class="ml-4 list-disc space-y-1">
                                    <li><strong>Date</strong> &mdash; Tanggal transaksi</li>
                                    <li><strong>Description</strong> &mdash; Keterangan transaksi (opsional)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">5</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Tambahkan baris jurnal (lines)</h4>
                            <div class="space-y-2 text-sm text-muted-foreground">
                                <p>Setiap baris jurnal memerlukan:</p>
                                <ul class="ml-4 list-disc space-y-1">
                                    <li><strong>Account</strong> &mdash; Pilih akun dari dropdown (hanya transaction account)</li>
                                    <li><strong>Debit</strong> &mdash; Jumlah debit (kosongkan jika kredit)</li>
                                    <li><strong>Credit</strong> &mdash; Jumlah kredit (kosongkan jika debit)</li>
                                    <li><strong>Description</strong> &mdash; Keterangan per baris (opsional)</li>
                                </ul>
                                <p class="mt-2">Klik <strong>"Tambah Baris"</strong> untuk menambah baris baru. Minimal 2 baris diperlukan.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">6</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Pastikan jurnal seimbang</h4>
                            <div class="space-y-2 text-sm text-muted-foreground">
                                <p>Perhatikan bagian <strong>Totals</strong> dan <strong>Difference</strong> di bawah tabel. Total Debit harus sama dengan Total Credit (Difference = 0).</p>
                                <p>Jika tidak seimbang, tombol <strong>"Simpan Jurnal"</strong> akan nonaktif (disabled) dan selisih ditampilkan dalam warna merah.</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">7</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Simpan journal entry</h4>
                            <p class="text-sm text-muted-foreground">Klik <strong>"Simpan Jurnal"</strong> untuk menyimpan. Anda akan diarahkan kembali ke daftar journal entries.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">8</div>
                        <div class="pt-0.5">
                            <h4 class="font-semibold">Lihat detail jurnal</h4>
                            <p class="flex items-start gap-2 text-sm text-muted-foreground"><x-icon name="eye" class="mt-0.5 size-4 shrink-0" /> Klik nomor jurnal (Journal No) di daftar untuk melihat detail lengkap, termasuk semua baris debit dan kredit.</p>
                        </div>
                    </div>
                    <div class="flex gap-3 rounded-lg border border-amber-900 bg-amber-950/30 p-4">
                        <x-icon name="lightbulb" class="mt-0.5 size-5 shrink-0 text-amber-400" />
                        <p class="text-sm text-amber-200"><strong>Jurnal harus selalu seimbang:</strong> Kucatat secara otomatis menghitung total debit dan kredit. Anda tidak akan bisa menyimpan journal entry jika totalnya tidak sama. Ini memastikan integritas data keuangan Anda.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 7: Glossary --}}
        <div id="glossary" class="scroll-mt-20 space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10">
                    <x-icon name="clipboard-check" class="size-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold">Glossary</h2>
                    <p class="text-sm text-muted-foreground">Kamus istilah akuntansi yang sering digunakan</p>
                </div>
            </div>

            <div class="rounded-lg border bg-card shadow-sm">
                <div class="p-6 pb-2"><h3 class="font-semibold">Istilah Akuntansi A&ndash;Z</h3><p class="text-sm text-muted-foreground">Referensi cepat untuk memahami istilah-istilah akuntansi</p></div>
                <div class="p-6 pt-3">
                    <dl class="divide-y">
                        @php
                            $glossary = [
                                ['term' => 'Accrual Basis', 'def' => 'Metode pencatatan yang mengakui pendapatan saat diperoleh dan beban saat terjadi, terlepas dari kapan kas diterima atau dibayarkan.'],
                                ['term' => 'Cash Basis', 'def' => 'Metode pencatatan yang mengakui pendapatan dan beban hanya saat kas benar-benar diterima atau dibayarkan.'],
                                ['term' => 'Accounts Receivable (Piutang)', 'def' => 'Uang yang belum diterima dari pelanggan atas barang/jasa yang sudah diberikan.'],
                                ['term' => 'Accounts Payable (Utang Dagang)', 'def' => 'Uang yang harus dibayarkan kepada supplier atas barang/jasa yang sudah diterima.'],
                                ['term' => 'Adjusting Entry', 'def' => 'Jurnal penyesuaian yang dibuat di akhir periode untuk memastikan pencatatan akurat sesuai prinsip accrual.'],
                                ['term' => 'Closing Entry', 'def' => 'Jurnal penutup yang memindahkan saldo akun temporary (Revenue, Expense, Drawings) ke Retained Earnings.'],
                                ['term' => 'Depreciation (Penyusutan)', 'def' => 'Alokasi biaya aset tetap secara bertahap selama masa manfaatnya.'],
                                ['term' => 'General Ledger (Buku Besar)', 'def' => 'Kumpulan semua akun yang mencatat seluruh transaksi, dikelompokkan per akun.'],
                                ['term' => 'Trial Balance (Neraca Saldo)', 'def' => 'Daftar semua akun beserta saldo debit dan kreditnya, digunakan untuk memverifikasi keseimbangan.'],
                                ['term' => 'Opening Balance (Saldo Awal)', 'def' => 'Saldo akun pada awal periode akuntansi, yang merupakan saldo akhir periode sebelumnya.'],
                                ['term' => 'Prepaid Expense (Beban Dibayar Dimuka)', 'def' => 'Pembayaran yang dilakukan di muka untuk layanan/barang yang belum diterima (contoh: sewa dibayar dimuka).'],
                                ['term' => 'Unearned Revenue (Pendapatan Diterima Dimuka)', 'def' => 'Uang yang sudah diterima dari pelanggan untuk layanan/barang yang belum diberikan.'],
                                ['term' => 'Retained Earnings (Laba Ditahan)', 'def' => 'Akumulasi laba bersih yang tidak dibagikan sebagai dividen, menjadi bagian dari ekuitas.'],
                                ['term' => 'Contra Account', 'def' => 'Akun yang memiliki saldo berlawanan dari akun induknya (contoh: Accumulated Depreciation adalah contra dari Equipment).'],
                                ['term' => 'Fiscal Year (Tahun Fiskal)', 'def' => 'Periode 12 bulan yang digunakan untuk pelaporan keuangan, tidak harus dimulai dari Januari.'],
                                ['term' => 'Materiality (Materialitas)', 'def' => 'Prinsip bahwa pencatatan harus fokus pada item yang cukup signifikan untuk mempengaruhi keputusan pengguna laporan.'],
                                ['term' => 'Revenue Recognition', 'def' => 'Prinsip yang menentukan kapan pendapatan boleh diakui/dicatat — yaitu saat barang/jasa telah diberikan.'],
                                ['term' => 'Matching Principle', 'def' => 'Prinsip yang mengharuskan beban dicatat pada periode yang sama dengan pendapatan yang dihasilkannya.'],
                            ];
                        @endphp
                        @foreach ($glossary as $item)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <dt class="text-sm font-semibold">{{ $item['term'] }}</dt>
                                <dd class="mt-0.5 text-sm text-muted-foreground">{{ $item['def'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
