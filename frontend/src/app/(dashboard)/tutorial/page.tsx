import Link from "next/link";
import {
  BookOpen,
  Network,
  GraduationCap,
  Scale,
  ArrowRight,
  Plus,
  Pencil,
  Search,
  Eye,
  FileText,
  ChevronDown,
  Lightbulb,
  Calculator,
  RefreshCw,
  ClipboardCheck,
  BookMarked,
  TrendingUp,
  BarChart3,
  AlertTriangle,
} from "lucide-react";
import { PageHeader } from "@/components/page-header";
import { Badge } from "@/components/ui/badge";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

const TOC_ITEMS = [
  { id: "fundamentals", label: "Accounting Fundamentals", icon: GraduationCap },
  { id: "accounting-cycle", label: "The Accounting Cycle", icon: RefreshCw },
  { id: "financial-statements", label: "Financial Statements", icon: BarChart3 },
  { id: "advanced-concepts", label: "Advanced Concepts", icon: BookMarked },
  { id: "chart-of-accounts", label: "Chart of Accounts", icon: Network },
  { id: "journal-entries", label: "Journal Entries", icon: BookOpen },
  { id: "glossary", label: "Glossary", icon: ClipboardCheck },
];

const ACCOUNT_TYPES = [
  {
    type: "Asset",
    description: "Sumber daya yang dimiliki perusahaan",
    examples: "Cash, Bank, Accounts Receivable, Equipment, Inventory",
    normalBalance: "Debit",
    codeRange: "1xxx",
    color: "text-blue-600 dark:text-blue-400",
    bg: "bg-blue-50 dark:bg-blue-950/40",
  },
  {
    type: "Liability",
    description: "Kewajiban yang harus dibayar",
    examples: "Accounts Payable, Loans, Accrued Expenses, Unearned Revenue",
    normalBalance: "Credit",
    codeRange: "2xxx",
    color: "text-orange-600 dark:text-orange-400",
    bg: "bg-orange-50 dark:bg-orange-950/40",
  },
  {
    type: "Equity",
    description: "Modal atau hak pemilik atas aset",
    examples: "Owner's Capital, Retained Earnings, Drawings",
    normalBalance: "Credit",
    codeRange: "3xxx",
    color: "text-purple-600 dark:text-purple-400",
    bg: "bg-purple-50 dark:bg-purple-950/40",
  },
  {
    type: "Revenue",
    description: "Pendapatan dari aktivitas bisnis",
    examples: "Sales Revenue, Service Revenue, Interest Income",
    normalBalance: "Credit",
    codeRange: "4xxx",
    color: "text-green-600 dark:text-green-400",
    bg: "bg-green-50 dark:bg-green-950/40",
  },
  {
    type: "Expense",
    description: "Biaya yang dikeluarkan untuk operasional",
    examples: "Rent, Salaries, Utilities, Supplies, Depreciation",
    normalBalance: "Debit",
    codeRange: "5xxx",
    color: "text-red-600 dark:text-red-400",
    bg: "bg-red-50 dark:bg-red-950/40",
  },
];

const DEBIT_CREDIT_RULES = [
  { type: "Asset", debitEffect: "Increase (+)", creditEffect: "Decrease (-)" },
  { type: "Liability", debitEffect: "Decrease (-)", creditEffect: "Increase (+)" },
  { type: "Equity", debitEffect: "Decrease (-)", creditEffect: "Increase (+)" },
  { type: "Revenue", debitEffect: "Decrease (-)", creditEffect: "Increase (+)" },
  { type: "Expense", debitEffect: "Increase (+)", creditEffect: "Decrease (-)" },
];

const JOURNAL_EXAMPLES = [
  {
    title: "Menerima pembayaran dari klien",
    description: "Klien membayar Rp 5.000.000 untuk jasa konsultasi",
    lines: [
      { account: "1100 - Cash", debit: "Rp 5.000.000", credit: "-" },
      { account: "4100 - Service Revenue", debit: "-", credit: "Rp 5.000.000" },
    ],
  },
  {
    title: "Membayar sewa kantor",
    description: "Pembayaran sewa bulanan Rp 3.000.000",
    lines: [
      { account: "5200 - Rent Expense", debit: "Rp 3.000.000", credit: "-" },
      { account: "1100 - Cash", debit: "-", credit: "Rp 3.000.000" },
    ],
  },
  {
    title: "Membeli peralatan secara kredit",
    description: "Pembelian laptop Rp 15.000.000 dengan kredit",
    lines: [
      { account: "1300 - Equipment", debit: "Rp 15.000.000", credit: "-" },
      { account: "2100 - Accounts Payable", debit: "-", credit: "Rp 15.000.000" },
    ],
  },
  {
    title: "Pemilik menambah modal",
    description: "Pemilik menginvestasikan Rp 50.000.000 ke bisnis",
    lines: [
      { account: "1100 - Cash", debit: "Rp 50.000.000", credit: "-" },
      { account: "3100 - Owner's Capital", debit: "-", credit: "Rp 50.000.000" },
    ],
  },
  {
    title: "Membayar gaji karyawan",
    description: "Pembayaran gaji 3 karyawan total Rp 18.000.000",
    lines: [
      { account: "5100 - Salaries Expense", debit: "Rp 18.000.000", credit: "-" },
      { account: "1200 - Bank", debit: "-", credit: "Rp 18.000.000" },
    ],
  },
  {
    title: "Menerima pinjaman bank",
    description: "Perusahaan menerima pinjaman Rp 100.000.000 dari bank",
    lines: [
      { account: "1200 - Bank", debit: "Rp 100.000.000", credit: "-" },
      { account: "2200 - Bank Loan", debit: "-", credit: "Rp 100.000.000" },
    ],
  },
  {
    title: "Mencatat penyusutan bulanan",
    description: "Penyusutan peralatan bulan ini Rp 1.250.000",
    lines: [
      { account: "5400 - Depreciation Expense", debit: "Rp 1.250.000", credit: "-" },
      { account: "1301 - Accumulated Depreciation", debit: "-", credit: "Rp 1.250.000" },
    ],
  },
  {
    title: "Membayar sebagian utang dagang",
    description: "Membayar Rp 10.000.000 dari utang ke supplier",
    lines: [
      { account: "2100 - Accounts Payable", debit: "Rp 10.000.000", credit: "-" },
      { account: "1200 - Bank", debit: "-", credit: "Rp 10.000.000" },
    ],
  },
];

const ACCOUNTING_CYCLE_STEPS = [
  { step: 1, title: "Identifikasi Transaksi", description: "Kenali dan kumpulkan bukti transaksi bisnis (faktur, kwitansi, nota, dll.)" },
  { step: 2, title: "Catat di Jurnal", description: "Catat setiap transaksi ke dalam journal entry dengan debit dan kredit yang sesuai." },
  { step: 3, title: "Posting ke Buku Besar", description: "Pindahkan catatan dari jurnal ke masing-masing akun di buku besar (general ledger)." },
  { step: 4, title: "Buat Trial Balance", description: "Susun neraca saldo untuk memastikan total debit sama dengan total kredit." },
  { step: 5, title: "Jurnal Penyesuaian", description: "Buat adjusting entries untuk mencatat pendapatan/beban yang belum tercatat (accrual, prepaid, depreciation)." },
  { step: 6, title: "Adjusted Trial Balance", description: "Susun ulang neraca saldo setelah jurnal penyesuaian." },
  { step: 7, title: "Laporan Keuangan", description: "Buat laporan keuangan: Income Statement, Balance Sheet, Cash Flow Statement." },
  { step: 8, title: "Jurnal Penutup", description: "Tutup akun Revenue, Expense, dan Drawings ke Retained Earnings di akhir periode." },
  { step: 9, title: "Post-Closing Trial Balance", description: "Verifikasi saldo akhir hanya berisi akun permanen (Asset, Liability, Equity)." },
];

const GLOSSARY_TERMS = [
  { term: "Accrual Basis", definition: "Metode pencatatan yang mengakui pendapatan saat diperoleh dan beban saat terjadi, terlepas dari kapan kas diterima atau dibayarkan." },
  { term: "Cash Basis", definition: "Metode pencatatan yang mengakui pendapatan dan beban hanya saat kas benar-benar diterima atau dibayarkan." },
  { term: "Accounts Receivable (Piutang)", definition: "Uang yang belum diterima dari pelanggan atas barang/jasa yang sudah diberikan." },
  { term: "Accounts Payable (Utang Dagang)", definition: "Uang yang harus dibayarkan kepada supplier atas barang/jasa yang sudah diterima." },
  { term: "Adjusting Entry", definition: "Jurnal penyesuaian yang dibuat di akhir periode untuk memastikan pencatatan akurat sesuai prinsip accrual." },
  { term: "Closing Entry", definition: "Jurnal penutup yang memindahkan saldo akun temporary (Revenue, Expense, Drawings) ke Retained Earnings." },
  { term: "Depreciation (Penyusutan)", definition: "Alokasi biaya aset tetap secara bertahap selama masa manfaatnya." },
  { term: "General Ledger (Buku Besar)", definition: "Kumpulan semua akun yang mencatat seluruh transaksi, dikelompokkan per akun." },
  { term: "Trial Balance (Neraca Saldo)", definition: "Daftar semua akun beserta saldo debit dan kreditnya, digunakan untuk memverifikasi keseimbangan." },
  { term: "Opening Balance (Saldo Awal)", definition: "Saldo akun pada awal periode akuntansi, yang merupakan saldo akhir periode sebelumnya." },
  { term: "Prepaid Expense (Beban Dibayar Dimuka)", definition: "Pembayaran yang dilakukan di muka untuk layanan/barang yang belum diterima (contoh: sewa dibayar dimuka)." },
  { term: "Unearned Revenue (Pendapatan Diterima Dimuka)", definition: "Uang yang sudah diterima dari pelanggan untuk layanan/barang yang belum diberikan." },
  { term: "Retained Earnings (Laba Ditahan)", definition: "Akumulasi laba bersih yang tidak dibagikan sebagai dividen, menjadi bagian dari ekuitas." },
  { term: "Contra Account", definition: "Akun yang memiliki saldo berlawanan dari akun induknya (contoh: Accumulated Depreciation adalah contra dari Equipment)." },
  { term: "Fiscal Year (Tahun Fiskal)", definition: "Periode 12 bulan yang digunakan untuk pelaporan keuangan, tidak harus dimulai dari Januari." },
  { term: "Materiality (Materialitas)", definition: "Prinsip bahwa pencatatan harus fokus pada item yang cukup signifikan untuk mempengaruhi keputusan pengguna laporan." },
  { term: "Revenue Recognition", definition: "Prinsip yang menentukan kapan pendapatan boleh diakui/dicatat — yaitu saat barang/jasa telah diberikan." },
  { term: "Matching Principle", definition: "Prinsip yang mengharuskan beban dicatat pada periode yang sama dengan pendapatan yang dihasilkannya." },
];

function TipCallout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
      <Lightbulb className="mt-0.5 size-5 shrink-0 text-amber-600 dark:text-amber-400" />
      <div className="text-sm text-amber-900 dark:text-amber-200">{children}</div>
    </div>
  );
}

function StepItem({
  step,
  title,
  children,
}: {
  step: number;
  title: string;
  children: React.ReactNode;
}) {
  return (
    <div className="flex gap-4">
      <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">
        {step}
      </div>
      <div className="space-y-1 pt-0.5">
        <h4 className="font-semibold">{title}</h4>
        <div className="text-sm text-muted-foreground">{children}</div>
      </div>
    </div>
  );
}

export default function TutorialPage() {
  return (
    <div className="space-y-8">
      <PageHeader
        title="Tutorial"
        description="Pelajari dasar-dasar akuntansi dan cara menggunakan fitur Journal Entries & Chart of Accounts"
      />

      {/* Table of Contents */}
      <Card>
        <CardHeader>
          <CardTitle>Daftar Isi</CardTitle>
        </CardHeader>
        <CardContent>
          <nav className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {TOC_ITEMS.map((item) => {
              const Icon = item.icon;
              return (
                <a
                  key={item.id}
                  href={`#${item.id}`}
                  className="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-muted"
                >
                  <Icon className="size-5 text-primary" />
                  <span className="text-sm font-medium">{item.label}</span>
                  <ArrowRight className="ml-auto size-4 text-muted-foreground" />
                </a>
              );
            })}
          </nav>
        </CardContent>
      </Card>

      {/* Section 1: Accounting Fundamentals */}
      <div id="fundamentals" className="scroll-mt-20 space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
            <GraduationCap className="size-5 text-primary" />
          </div>
          <div>
            <h2 className="text-xl font-bold">Accounting Fundamentals</h2>
            <p className="text-sm text-muted-foreground">Dasar-dasar akuntansi yang perlu Anda ketahui</p>
          </div>
        </div>

        {/* Double-Entry Bookkeeping */}
        <Card>
          <CardHeader>
            <CardTitle>Double-Entry Bookkeeping</CardTitle>
            <CardDescription>Sistem pencatatan berpasangan</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm leading-relaxed">
              Double-entry bookkeeping adalah prinsip dasar akuntansi modern. Setiap transaksi
              dicatat di <strong>minimal dua akun</strong> &mdash; satu sisi debit dan satu sisi
              kredit. Total debit harus <strong>selalu sama</strong> dengan total kredit.
            </p>
            <TipCallout>
              Sistem ini memastikan bahwa buku besar Anda selalu seimbang (balanced) dan
              memudahkan pelacakan kesalahan pencatatan.
            </TipCallout>
          </CardContent>
        </Card>

        {/* The Accounting Equation */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Scale className="size-5" />
              The Accounting Equation
            </CardTitle>
            <CardDescription>Persamaan dasar akuntansi</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center justify-center rounded-lg bg-muted/50 p-6">
              <div className="flex flex-wrap items-center justify-center gap-2 text-center">
                <Badge variant="outline" className="px-4 py-2 text-base font-bold text-blue-600 dark:text-blue-400">
                  Assets
                </Badge>
                <span className="text-xl font-bold">=</span>
                <Badge variant="outline" className="px-4 py-2 text-base font-bold text-orange-600 dark:text-orange-400">
                  Liabilities
                </Badge>
                <span className="text-xl font-bold">+</span>
                <Badge variant="outline" className="px-4 py-2 text-base font-bold text-purple-600 dark:text-purple-400">
                  Equity
                </Badge>
              </div>
            </div>
            <p className="text-sm leading-relaxed">
              Setiap transaksi yang dicatat akan menjaga persamaan ini tetap seimbang.
              Aset (harta) selalu sama dengan jumlah Kewajiban (utang) ditambah Ekuitas (modal pemilik).
            </p>
          </CardContent>
        </Card>

        {/* The 5 Account Types */}
        <Card>
          <CardHeader>
            <CardTitle>The 5 Account Types</CardTitle>
            <CardDescription>Lima jenis akun dalam akuntansi</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
              {ACCOUNT_TYPES.map((acc) => (
                <div
                  key={acc.type}
                  className={`rounded-lg border p-4 ${acc.bg}`}
                >
                  <div className="mb-2 flex items-center justify-between">
                    <h4 className={`font-bold ${acc.color}`}>{acc.type}</h4>
                    <Badge variant="outline" className="font-mono text-xs">
                      {acc.codeRange}
                    </Badge>
                  </div>
                  <p className="mb-2 text-sm">{acc.description}</p>
                  <p className="mb-2 text-xs text-muted-foreground">
                    Contoh: {acc.examples}
                  </p>
                  <div className="text-xs font-medium">
                    Normal Balance:{" "}
                    <Badge variant="outline" className="ml-1">
                      {acc.normalBalance}
                    </Badge>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Debits and Credits */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calculator className="size-5" />
              Understanding Debits &amp; Credits
            </CardTitle>
            <CardDescription>Kapan menggunakan debit dan kredit</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="overflow-x-auto rounded-md border">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b bg-muted/50">
                    <th className="p-3 text-left font-semibold">Account Type</th>
                    <th className="p-3 text-center font-semibold">Debit</th>
                    <th className="p-3 text-center font-semibold">Credit</th>
                  </tr>
                </thead>
                <tbody>
                  {DEBIT_CREDIT_RULES.map((rule) => (
                    <tr key={rule.type} className="border-b">
                      <td className="p-3 font-medium">{rule.type}</td>
                      <td className="p-3 text-center">
                        <Badge
                          variant="outline"
                          className={
                            rule.debitEffect.includes("+")
                              ? "border-green-300 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-400"
                              : "border-red-300 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-400"
                          }
                        >
                          {rule.debitEffect}
                        </Badge>
                      </td>
                      <td className="p-3 text-center">
                        <Badge
                          variant="outline"
                          className={
                            rule.creditEffect.includes("+")
                              ? "border-green-300 bg-green-50 text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-400"
                              : "border-red-300 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-400"
                          }
                        >
                          {rule.creditEffect}
                        </Badge>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <TipCallout>
              <strong>Cara mudah mengingat:</strong> Debit menambah Asset dan Expense (sisi kiri persamaan akuntansi).
              Credit menambah Liability, Equity, dan Revenue (sisi kanan).
            </TipCallout>
          </CardContent>
        </Card>

        {/* Common Journal Entry Examples */}
        <Card>
          <CardHeader>
            <CardTitle>Common Journal Entry Examples</CardTitle>
            <CardDescription>Contoh pencatatan jurnal yang umum dalam bisnis</CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            {JOURNAL_EXAMPLES.map((example, i) => (
              <div key={i} className="space-y-2">
                <div>
                  <h4 className="font-semibold">
                    {i + 1}. {example.title}
                  </h4>
                  <p className="text-sm text-muted-foreground">{example.description}</p>
                </div>
                <div className="overflow-x-auto rounded-md border">
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="border-b bg-muted/50">
                        <th className="p-2 text-left font-medium">Account</th>
                        <th className="p-2 text-right font-medium">Debit</th>
                        <th className="p-2 text-right font-medium">Credit</th>
                      </tr>
                    </thead>
                    <tbody>
                      {example.lines.map((line, j) => (
                        <tr key={j} className="border-b last:border-0">
                          <td className="p-2 font-mono text-sm">{line.account}</td>
                          <td className="p-2 text-right font-mono">{line.debit}</td>
                          <td className="p-2 text-right font-mono">{line.credit}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            ))}
          </CardContent>
        </Card>

        {/* Account Numbering Convention */}
        <Card>
          <CardHeader>
            <CardTitle>Account Numbering Convention</CardTitle>
            <CardDescription>Konvensi penomoran akun standar</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm leading-relaxed">
              Akun biasanya diberi kode nomor untuk memudahkan pengorganisasian. Berikut konvensi
              umum yang digunakan:
            </p>
            <div className="overflow-x-auto rounded-md border">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b bg-muted/50">
                    <th className="p-3 text-left font-semibold">Code Range</th>
                    <th className="p-3 text-left font-semibold">Type</th>
                    <th className="p-3 text-left font-semibold">Contoh</th>
                  </tr>
                </thead>
                <tbody>
                  <tr className="border-b"><td className="p-3 font-mono">1000 &ndash; 1999</td><td className="p-3">Asset</td><td className="p-3 text-muted-foreground">1100 Cash, 1200 Bank, 1300 Equipment</td></tr>
                  <tr className="border-b"><td className="p-3 font-mono">2000 &ndash; 2999</td><td className="p-3">Liability</td><td className="p-3 text-muted-foreground">2100 Accounts Payable, 2200 Loans</td></tr>
                  <tr className="border-b"><td className="p-3 font-mono">3000 &ndash; 3999</td><td className="p-3">Equity</td><td className="p-3 text-muted-foreground">3100 Owner&apos;s Capital, 3200 Retained Earnings</td></tr>
                  <tr className="border-b"><td className="p-3 font-mono">4000 &ndash; 4999</td><td className="p-3">Revenue</td><td className="p-3 text-muted-foreground">4100 Sales, 4200 Service Revenue</td></tr>
                  <tr><td className="p-3 font-mono">5000 &ndash; 5999</td><td className="p-3">Expense</td><td className="p-3 text-muted-foreground">5100 Salaries, 5200 Rent, 5300 Utilities</td></tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Section 2: The Accounting Cycle */}
      <div id="accounting-cycle" className="scroll-mt-20 space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
            <RefreshCw className="size-5 text-primary" />
          </div>
          <div>
            <h2 className="text-xl font-bold">The Accounting Cycle</h2>
            <p className="text-sm text-muted-foreground">Siklus akuntansi dari awal hingga akhir periode</p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Apa itu Siklus Akuntansi?</CardTitle>
            <CardDescription>Proses berulang setiap periode akuntansi</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm leading-relaxed">
              Siklus akuntansi (accounting cycle) adalah serangkaian langkah yang dilakukan secara
              berurutan untuk mencatat, mengolah, dan melaporkan transaksi keuangan dalam satu periode.
              Siklus ini berulang setiap periode akuntansi (biasanya bulanan, kuartalan, atau tahunan).
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>9 Langkah Siklus Akuntansi</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {ACCOUNTING_CYCLE_STEPS.map((item) => (
                <div key={item.step} className="flex gap-4">
                  <div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">
                    {item.step}
                  </div>
                  <div className="pt-0.5">
                    <h4 className="font-semibold">{item.title}</h4>
                    <p className="text-sm text-muted-foreground">{item.description}</p>
                  </div>
                </div>
              ))}
            </div>
            <div className="mt-6">
              <TipCallout>
                Di Kucatat, langkah 1&ndash;3 dilakukan saat Anda membuat Journal Entry. Langkah 4
                bisa dilihat di{" "}
                <Link href="/reports" className="font-medium underline">
                  halaman Reports
                </Link>{" "}
                (Trial Balance). Laporan keuangan (langkah 7) tersedia di menu Reports (Profit &amp; Loss,
                Balance Sheet, Cash Flow).
              </TipCallout>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Section 3: Financial Statements */}
      <div id="financial-statements" className="scroll-mt-20 space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
            <BarChart3 className="size-5 text-primary" />
          </div>
          <div>
            <h2 className="text-xl font-bold">Financial Statements</h2>
            <p className="text-sm text-muted-foreground">Memahami laporan keuangan utama</p>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <TrendingUp className="size-5" />
                Income Statement (Laporan Laba Rugi)
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <p className="text-sm leading-relaxed">
                Menunjukkan <strong>pendapatan</strong> dan <strong>beban</strong> selama periode tertentu,
                menghasilkan <strong>laba bersih</strong> (net profit) atau <strong>rugi bersih</strong> (net loss).
              </p>
              <div className="rounded-lg bg-muted/50 p-3">
                <p className="text-center text-sm font-semibold">
                  Revenue &minus; Expenses = Net Profit/Loss
                </p>
              </div>
              <p className="text-xs text-muted-foreground">
                Di Kucatat: lihat di{" "}
                <Link href="/reports" className="text-primary hover:underline">Reports</Link>{" "}
                &rarr; Profit &amp; Loss
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Scale className="size-5" />
                Balance Sheet (Neraca)
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <p className="text-sm leading-relaxed">
                Menunjukkan posisi keuangan pada <strong>satu titik waktu</strong> tertentu:
                apa yang dimiliki (Assets), apa yang diutangkan (Liabilities), dan berapa
                modal pemilik (Equity).
              </p>
              <div className="rounded-lg bg-muted/50 p-3">
                <p className="text-center text-sm font-semibold">
                  Assets = Liabilities + Equity
                </p>
              </div>
              <p className="text-xs text-muted-foreground">
                Di Kucatat: lihat di{" "}
                <Link href="/reports" className="text-primary hover:underline">Reports</Link>{" "}
                &rarr; Balance Sheet
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <RefreshCw className="size-5" />
                Cash Flow Statement (Laporan Arus Kas)
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <p className="text-sm leading-relaxed">
                Melacak pergerakan kas masuk dan keluar, dibagi menjadi tiga aktivitas:
              </p>
              <ul className="ml-4 list-disc space-y-1 text-sm">
                <li><strong>Operating</strong> &mdash; Arus kas dari operasional bisnis sehari-hari</li>
                <li><strong>Investing</strong> &mdash; Pembelian/penjualan aset jangka panjang</li>
                <li><strong>Financing</strong> &mdash; Pinjaman, setoran modal, pembayaran utang</li>
              </ul>
              <p className="text-xs text-muted-foreground">
                Di Kucatat: lihat di{" "}
                <Link href="/reports" className="text-primary hover:underline">Reports</Link>{" "}
                &rarr; Cash Flow Statement
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <ClipboardCheck className="size-5" />
                Trial Balance (Neraca Saldo)
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <p className="text-sm leading-relaxed">
                Daftar semua akun beserta saldo debit dan kreditnya. Digunakan sebagai
                <strong> alat verifikasi</strong> bahwa total debit sama dengan total kredit
                sebelum menyusun laporan keuangan.
              </p>
              <div className="rounded-lg bg-muted/50 p-3">
                <p className="text-center text-sm font-semibold">
                  Total Debits = Total Credits
                </p>
              </div>
              <p className="text-xs text-muted-foreground">
                Di Kucatat: lihat di{" "}
                <Link href="/reports" className="text-primary hover:underline">Reports</Link>{" "}
                &rarr; Trial Balance
              </p>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Hubungan Antar Laporan Keuangan</CardTitle>
            <CardDescription>Bagaimana laporan keuangan saling terhubung</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="overflow-x-auto rounded-md border">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b bg-muted/50">
                    <th className="p-3 text-left font-semibold">Dari</th>
                    <th className="p-3 text-left font-semibold">Ke</th>
                    <th className="p-3 text-left font-semibold">Hubungan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr className="border-b">
                    <td className="p-3 font-medium">Income Statement</td>
                    <td className="p-3">Balance Sheet</td>
                    <td className="p-3 text-muted-foreground">Net Profit masuk ke Retained Earnings (Equity)</td>
                  </tr>
                  <tr className="border-b">
                    <td className="p-3 font-medium">Income Statement</td>
                    <td className="p-3">Cash Flow</td>
                    <td className="p-3 text-muted-foreground">Net Profit menjadi titik awal Operating Activities</td>
                  </tr>
                  <tr className="border-b">
                    <td className="p-3 font-medium">Cash Flow</td>
                    <td className="p-3">Balance Sheet</td>
                    <td className="p-3 text-muted-foreground">Closing Balance = saldo Cash di Balance Sheet</td>
                  </tr>
                  <tr>
                    <td className="p-3 font-medium">Trial Balance</td>
                    <td className="p-3">Semua Laporan</td>
                    <td className="p-3 text-muted-foreground">Sumber data untuk menyusun ketiga laporan keuangan</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Section 4: Advanced Concepts */}
      <div id="advanced-concepts" className="scroll-mt-20 space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
            <BookMarked className="size-5 text-primary" />
          </div>
          <div>
            <h2 className="text-xl font-bold">Advanced Concepts</h2>
            <p className="text-sm text-muted-foreground">Konsep akuntansi lanjutan untuk pemahaman yang lebih mendalam</p>
          </div>
        </div>

        {/* Accrual vs Cash Basis */}
        <Card>
          <CardHeader>
            <CardTitle>Accrual vs Cash Basis</CardTitle>
            <CardDescription>Dua metode pencatatan akuntansi</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                <h4 className="mb-2 font-bold text-blue-700 dark:text-blue-400">Accrual Basis</h4>
                <ul className="space-y-2 text-sm">
                  <li>Pendapatan diakui saat <strong>diperoleh</strong> (earned), bukan saat kas diterima</li>
                  <li>Beban diakui saat <strong>terjadi</strong> (incurred), bukan saat kas dibayar</li>
                  <li>Lebih akurat menggambarkan kondisi keuangan</li>
                  <li>Digunakan oleh sebagian besar bisnis menengah-besar</li>
                  <li>Sesuai dengan standar akuntansi (PSAK/IFRS)</li>
                </ul>
              </div>
              <div className="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950/30">
                <h4 className="mb-2 font-bold text-green-700 dark:text-green-400">Cash Basis</h4>
                <ul className="space-y-2 text-sm">
                  <li>Pendapatan diakui hanya saat kas <strong>diterima</strong></li>
                  <li>Beban diakui hanya saat kas <strong>dibayarkan</strong></li>
                  <li>Lebih sederhana dan mudah dipahami</li>
                  <li>Cocok untuk bisnis kecil dan usaha perorangan</li>
                  <li>Tidak menampilkan utang-piutang</li>
                </ul>
              </div>
            </div>
            <TipCallout>
              Kucatat mendukung <strong>accrual basis</strong> karena menggunakan Accounts Receivable
              (Piutang) dan Accounts Payable (Utang) dalam pencatatan. Ini memberikan gambaran
              keuangan yang lebih lengkap.
            </TipCallout>
          </CardContent>
        </Card>

        {/* Adjusting Entries */}
        <Card>
          <CardHeader>
            <CardTitle>Adjusting Entries (Jurnal Penyesuaian)</CardTitle>
            <CardDescription>Pencatatan di akhir periode untuk memastikan akurasi</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm leading-relaxed">
              Adjusting entries dibuat di akhir periode akuntansi untuk memastikan bahwa pendapatan dan
              beban dicatat pada periode yang tepat. Ada empat jenis utama:
            </p>
            <div className="grid gap-4 md:grid-cols-2">
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">1. Accrued Revenue</h4>
                <p className="mb-2 text-sm text-muted-foreground">Pendapatan yang sudah diperoleh tapi belum diterima kasnya</p>
                <div className="rounded bg-muted/50 p-2 text-xs font-mono">
                  <p>Dr. Accounts Receivable</p>
                  <p>&nbsp;&nbsp;Cr. Service Revenue</p>
                </div>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">2. Accrued Expense</h4>
                <p className="mb-2 text-sm text-muted-foreground">Beban yang sudah terjadi tapi belum dibayarkan</p>
                <div className="rounded bg-muted/50 p-2 text-xs font-mono">
                  <p>Dr. Salaries Expense</p>
                  <p>&nbsp;&nbsp;Cr. Salaries Payable</p>
                </div>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">3. Prepaid Expense (Deferred)</h4>
                <p className="mb-2 text-sm text-muted-foreground">Beban yang sudah dibayar di muka, diakui secara bertahap</p>
                <div className="rounded bg-muted/50 p-2 text-xs font-mono">
                  <p>Dr. Insurance Expense</p>
                  <p>&nbsp;&nbsp;Cr. Prepaid Insurance</p>
                </div>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">4. Unearned Revenue (Deferred)</h4>
                <p className="mb-2 text-sm text-muted-foreground">Pendapatan diterima di muka, diakui saat jasa diberikan</p>
                <div className="rounded bg-muted/50 p-2 text-xs font-mono">
                  <p>Dr. Unearned Revenue</p>
                  <p>&nbsp;&nbsp;Cr. Service Revenue</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Closing Entries */}
        <Card>
          <CardHeader>
            <CardTitle>Closing Entries (Jurnal Penutup)</CardTitle>
            <CardDescription>Menutup akun temporary di akhir periode</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-sm leading-relaxed">
              Di akhir periode akuntansi, akun <strong>temporary</strong> (Revenue, Expense, Drawings)
              harus ditutup ke <strong>Retained Earnings</strong>. Ini meng-nol-kan akun temporary
              agar siap untuk periode berikutnya.
            </p>
            <div className="grid gap-4 md:grid-cols-2">
              <div className="rounded-lg border p-4">
                <Badge variant="outline" className="mb-2">Permanent Accounts</Badge>
                <p className="text-sm">Asset, Liability, Equity</p>
                <p className="mt-1 text-xs text-muted-foreground">
                  Saldo terbawa ke periode berikutnya (tidak ditutup)
                </p>
              </div>
              <div className="rounded-lg border p-4">
                <Badge variant="outline" className="mb-2">Temporary Accounts</Badge>
                <p className="text-sm">Revenue, Expense, Drawings</p>
                <p className="mt-1 text-xs text-muted-foreground">
                  Saldo ditutup ke Retained Earnings di akhir periode
                </p>
              </div>
            </div>
            <div className="space-y-2">
              <h4 className="text-sm font-semibold">Langkah menutup akun:</h4>
              <div className="space-y-3">
                <div className="rounded-lg border p-3">
                  <p className="text-sm font-medium">1. Tutup Revenue ke Income Summary</p>
                  <div className="mt-1 rounded bg-muted/50 p-2 text-xs font-mono">
                    <p>Dr. Revenue &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp xxx</p>
                    <p>&nbsp;&nbsp;Cr. Income Summary &nbsp;&nbsp;&nbsp;&nbsp;Rp xxx</p>
                  </div>
                </div>
                <div className="rounded-lg border p-3">
                  <p className="text-sm font-medium">2. Tutup Expense ke Income Summary</p>
                  <div className="mt-1 rounded bg-muted/50 p-2 text-xs font-mono">
                    <p>Dr. Income Summary &nbsp;&nbsp;&nbsp;&nbsp;Rp xxx</p>
                    <p>&nbsp;&nbsp;Cr. Expenses &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Rp xxx</p>
                  </div>
                </div>
                <div className="rounded-lg border p-3">
                  <p className="text-sm font-medium">3. Tutup Income Summary ke Retained Earnings</p>
                  <div className="mt-1 rounded bg-muted/50 p-2 text-xs font-mono">
                    <p>Dr. Income Summary &nbsp;&nbsp;&nbsp;&nbsp;Rp xxx (jika laba)</p>
                    <p>&nbsp;&nbsp;Cr. Retained Earnings &nbsp;&nbsp;Rp xxx</p>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Common Mistakes */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <AlertTriangle className="size-5" />
              Kesalahan Umum dalam Akuntansi
            </CardTitle>
            <CardDescription>Hal-hal yang perlu dihindari</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <div className="flex gap-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/30">
                <span className="text-sm font-bold text-red-600 dark:text-red-400">1.</span>
                <div>
                  <p className="text-sm font-medium text-red-900 dark:text-red-200">Mencampur debit dan kredit</p>
                  <p className="text-xs text-red-800 dark:text-red-300">Pastikan memahami akun mana yang bertambah di sisi debit vs kredit. Gunakan tabel referensi di atas.</p>
                </div>
              </div>
              <div className="flex gap-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/30">
                <span className="text-sm font-bold text-red-600 dark:text-red-400">2.</span>
                <div>
                  <p className="text-sm font-medium text-red-900 dark:text-red-200">Tidak mencatat transaksi tepat waktu</p>
                  <p className="text-xs text-red-800 dark:text-red-300">Catat transaksi sesegera mungkin agar tidak ada yang terlewat dan laporan selalu up-to-date.</p>
                </div>
              </div>
              <div className="flex gap-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/30">
                <span className="text-sm font-bold text-red-600 dark:text-red-400">3.</span>
                <div>
                  <p className="text-sm font-medium text-red-900 dark:text-red-200">Menggunakan akun yang salah</p>
                  <p className="text-xs text-red-800 dark:text-red-300">Pastikan memilih akun yang tepat dari Chart of Accounts. Contoh: jangan mencatat pembelian aset sebagai expense.</p>
                </div>
              </div>
              <div className="flex gap-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/30">
                <span className="text-sm font-bold text-red-600 dark:text-red-400">4.</span>
                <div>
                  <p className="text-sm font-medium text-red-900 dark:text-red-200">Tidak melakukan rekonsiliasi</p>
                  <p className="text-xs text-red-800 dark:text-red-300">Secara berkala bandingkan catatan di Kucatat dengan rekening bank untuk menemukan perbedaan.</p>
                </div>
              </div>
              <div className="flex gap-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/30">
                <span className="text-sm font-bold text-red-600 dark:text-red-400">5.</span>
                <div>
                  <p className="text-sm font-medium text-red-900 dark:text-red-200">Lupa membuat jurnal penyesuaian</p>
                  <p className="text-xs text-red-800 dark:text-red-300">Di akhir periode, buat adjusting entries untuk penyusutan, prepaid, accrual, dan item lain yang memerlukan penyesuaian.</p>
                </div>
              </div>
              <div className="flex gap-3 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/30">
                <span className="text-sm font-bold text-red-600 dark:text-red-400">6.</span>
                <div>
                  <p className="text-sm font-medium text-red-900 dark:text-red-200">Deskripsi transaksi tidak jelas</p>
                  <p className="text-xs text-red-800 dark:text-red-300">Selalu isi deskripsi yang jelas pada journal entry agar mudah dilacak dan di-audit di kemudian hari.</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Accounting Principles */}
        <Card>
          <CardHeader>
            <CardTitle>Prinsip-Prinsip Dasar Akuntansi</CardTitle>
            <CardDescription>Pedoman yang mendasari pencatatan keuangan</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-2">
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">Going Concern</h4>
                <p className="text-sm text-muted-foreground">
                  Asumsi bahwa bisnis akan terus beroperasi di masa mendatang, bukan akan dilikuidasi.
                </p>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">Matching Principle</h4>
                <p className="text-sm text-muted-foreground">
                  Beban harus dicatat pada periode yang sama dengan pendapatan yang dihasilkannya.
                </p>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">Revenue Recognition</h4>
                <p className="text-sm text-muted-foreground">
                  Pendapatan diakui saat barang/jasa telah diberikan, bukan saat kas diterima.
                </p>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">Conservatism</h4>
                <p className="text-sm text-muted-foreground">
                  Jika ada keraguan, pilih metode yang menghasilkan aset/pendapatan lebih rendah atau
                  kewajiban/beban lebih tinggi.
                </p>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">Consistency</h4>
                <p className="text-sm text-muted-foreground">
                  Gunakan metode akuntansi yang sama dari periode ke periode agar laporan dapat dibandingkan.
                </p>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">Materiality</h4>
                <p className="text-sm text-muted-foreground">
                  Fokus pencatatan pada item yang cukup signifikan untuk mempengaruhi keputusan pengguna laporan.
                </p>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">Historical Cost</h4>
                <p className="text-sm text-muted-foreground">
                  Aset dicatat pada harga perolehan aslinya, bukan pada nilai pasar saat ini.
                </p>
              </div>
              <div className="rounded-lg border p-4">
                <h4 className="mb-1 font-semibold">Full Disclosure</h4>
                <p className="text-sm text-muted-foreground">
                  Semua informasi yang relevan dan material harus diungkapkan dalam laporan keuangan.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Section 5: Chart of Accounts */}
      <div id="chart-of-accounts" className="scroll-mt-20 space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
            <Network className="size-5 text-primary" />
          </div>
          <div>
            <h2 className="text-xl font-bold">Chart of Accounts</h2>
            <p className="text-sm text-muted-foreground">Cara mengelola daftar akun di Kucatat</p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Apa itu Chart of Accounts?</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <p className="text-sm leading-relaxed">
              Chart of Accounts (COA) adalah daftar lengkap semua akun yang digunakan dalam pencatatan
              keuangan bisnis Anda. COA mengorganisir akun-akun ke dalam 5 kategori utama (Asset,
              Liability, Equity, Revenue, Expense) dan dapat memiliki struktur hierarki (parent &amp; child).
            </p>
            <p className="text-sm leading-relaxed">
              Di Kucatat, Anda bisa melihat COA dalam bentuk <strong>tree view</strong> yang bisa
              di-expand/collapse untuk navigasi yang mudah.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Cara Menggunakan Chart of Accounts</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <StepItem step={1} title="Buka halaman Chart of Accounts">
              <span>
                Klik menu{" "}
                <Link href="/accounts" className="font-medium text-primary hover:underline">
                  Chart of Accounts
                </Link>{" "}
                di sidebar pada bagian <strong>Keuangan</strong>.
              </span>
            </StepItem>

            <StepItem step={2} title="Cari dan filter akun">
              <span className="flex items-start gap-2">
                <Search className="mt-0.5 size-4 shrink-0" />
                Gunakan kolom pencarian untuk mencari akun berdasarkan nama atau kode.
                Anda juga bisa memfilter berdasarkan tipe akun (Asset, Liability, dll.).
              </span>
            </StepItem>

            <StepItem step={3} title="Lihat struktur akun">
              <span className="flex items-start gap-2">
                <ChevronDown className="mt-0.5 size-4 shrink-0" />
                Klik tombol expand/collapse untuk melihat akun child di bawah akun parent.
                Akun ditampilkan dalam format tree (hierarki).
              </span>
            </StepItem>

            <StepItem step={4} title="Tambah akun baru">
              <span className="flex items-start gap-2">
                <Plus className="mt-0.5 size-4 shrink-0" />
                Klik tombol <strong>&quot;Add Account&quot;</strong> di kanan atas. Isi kode akun,
                nama, dan tipe akun, lalu klik <strong>Add</strong>.
              </span>
            </StepItem>

            <StepItem step={5} title="Edit akun">
              <span className="flex items-start gap-2">
                <Pencil className="mt-0.5 size-4 shrink-0" />
                Klik ikon menu (&middot;&middot;&middot;) di kolom aksi, lalu pilih <strong>Edit</strong>.
                Anda bisa mengubah kode, nama, tipe, sub-tipe, parent account, opening balance,
                status aktif, dan apakah akun tersebut header account.
              </span>
            </StepItem>

            <TipCallout>
              <strong>Header Account vs Transaction Account:</strong> Header account digunakan untuk
              pengelompokan saja dan tidak bisa digunakan dalam transaksi. Transaction account
              (non-header) adalah akun yang bisa menerima pencatatan jurnal.
            </TipCallout>
          </CardContent>
        </Card>
      </div>

      {/* Section 3: Journal Entries */}
      <div id="journal-entries" className="scroll-mt-20 space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
            <BookOpen className="size-5 text-primary" />
          </div>
          <div>
            <h2 className="text-xl font-bold">Journal Entries</h2>
            <p className="text-sm text-muted-foreground">Cara mencatat jurnal transaksi di Kucatat</p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Apa itu Journal Entry?</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <p className="text-sm leading-relaxed">
              Journal Entry adalah metode pencatatan transaksi ke dalam sistem akuntansi. Setiap
              journal entry memiliki minimal <strong>dua baris</strong> (lines) &mdash; satu untuk
              debit dan satu untuk kredit &mdash; sesuai dengan prinsip double-entry bookkeeping.
            </p>
            <p className="text-sm leading-relaxed">
              Kucatat secara otomatis memvalidasi bahwa total debit sama dengan total kredit sebelum
              journal entry bisa disimpan.
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Cara Menggunakan Journal Entries</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <StepItem step={1} title="Buka halaman Journal Entries">
              <span>
                Klik menu{" "}
                <Link href="/journal-entries" className="font-medium text-primary hover:underline">
                  Journal Entries
                </Link>{" "}
                di sidebar pada bagian <strong>Keuangan</strong>.
              </span>
            </StepItem>

            <StepItem step={2} title="Lihat daftar jurnal">
              <span className="flex items-start gap-2">
                <Search className="mt-0.5 size-4 shrink-0" />
                Anda akan melihat daftar semua journal entries. Gunakan kolom pencarian untuk
                mencari berdasarkan nomor jurnal atau deskripsi.
              </span>
            </StepItem>

            <StepItem step={3} title="Buat journal entry baru">
              <span>
                Klik tombol{" "}
                <Link href="/journal-entries/create" className="font-medium text-primary hover:underline">
                  <FileText className="mr-1 inline size-4" />
                  New Journal Entry
                </Link>{" "}
                di kanan atas halaman.
              </span>
            </StepItem>

            <StepItem step={4} title="Isi detail jurnal">
              <div className="space-y-2">
                <p>Pada halaman pembuatan, isi informasi berikut:</p>
                <ul className="ml-4 list-disc space-y-1">
                  <li><strong>Date</strong> &mdash; Tanggal transaksi</li>
                  <li><strong>Description</strong> &mdash; Keterangan transaksi (opsional)</li>
                </ul>
              </div>
            </StepItem>

            <StepItem step={5} title="Tambahkan baris jurnal (lines)">
              <div className="space-y-2">
                <p>Setiap baris jurnal memerlukan:</p>
                <ul className="ml-4 list-disc space-y-1">
                  <li><strong>Account</strong> &mdash; Pilih akun dari dropdown (hanya transaction account)</li>
                  <li><strong>Debit</strong> &mdash; Jumlah debit (kosongkan jika kredit)</li>
                  <li><strong>Credit</strong> &mdash; Jumlah kredit (kosongkan jika debit)</li>
                  <li><strong>Description</strong> &mdash; Keterangan per baris (opsional)</li>
                </ul>
                <p className="mt-2">
                  Klik <strong>&quot;Add Line&quot;</strong> untuk menambah baris baru.
                  Minimal 2 baris diperlukan.
                </p>
              </div>
            </StepItem>

            <StepItem step={6} title="Pastikan jurnal seimbang">
              <div className="space-y-2">
                <p>
                  Perhatikan bagian <strong>Totals</strong> dan <strong>Difference</strong> di
                  bawah tabel. Total Debit harus sama dengan Total Credit (Difference = 0).
                </p>
                <p>
                  Jika tidak seimbang, tombol <strong>&quot;Create Journal Entry&quot;</strong> akan
                  nonaktif (disabled) dan selisih ditampilkan dalam warna merah.
                </p>
              </div>
            </StepItem>

            <StepItem step={7} title="Simpan journal entry">
              <span>
                Klik <strong>&quot;Create Journal Entry&quot;</strong> untuk menyimpan. Anda akan
                diarahkan kembali ke daftar journal entries.
              </span>
            </StepItem>

            <StepItem step={8} title="Lihat detail jurnal">
              <span className="flex items-start gap-2">
                <Eye className="mt-0.5 size-4 shrink-0" />
                Klik nomor jurnal (Journal No) di daftar untuk melihat detail lengkap,
                termasuk semua baris debit dan kredit.
              </span>
            </StepItem>

            <TipCallout>
              <strong>Jurnal harus selalu seimbang:</strong> Kucatat secara otomatis menghitung
              total debit dan kredit. Anda tidak akan bisa menyimpan journal entry jika totalnya
              tidak sama. Ini memastikan integritas data keuangan Anda.
            </TipCallout>
          </CardContent>
        </Card>
      </div>

      {/* Section 7: Glossary */}
      <div id="glossary" className="scroll-mt-20 space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10">
            <ClipboardCheck className="size-5 text-primary" />
          </div>
          <div>
            <h2 className="text-xl font-bold">Glossary</h2>
            <p className="text-sm text-muted-foreground">Kamus istilah akuntansi yang sering digunakan</p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Istilah Akuntansi A&ndash;Z</CardTitle>
            <CardDescription>Referensi cepat untuk memahami istilah-istilah akuntansi</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="divide-y">
              {GLOSSARY_TERMS.map((item) => (
                <div key={item.term} className="py-3 first:pt-0 last:pb-0">
                  <dt className="text-sm font-semibold">{item.term}</dt>
                  <dd className="mt-0.5 text-sm text-muted-foreground">{item.definition}</dd>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
