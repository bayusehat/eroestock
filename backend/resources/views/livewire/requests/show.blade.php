<div class="space-y-6">
    <x-page-header :title="$budgetRequest->title" :description="$budgetRequest->request_no">
        <a wire:navigate href="{{ route('requests.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent"><x-icon name="arrow-left" class="size-4" /> Kembali</a>
        @if ($budgetRequest->status === 'pending' && auth()->user()->can('budget_requests-edit'))
            <button wire:click="review('approved')" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"><x-icon name="check" class="size-4" /> Setujui</button>
            <button wire:click="review('rejected')" class="inline-flex items-center gap-2 rounded-md bg-destructive px-4 py-2 text-sm font-medium text-white hover:bg-destructive/90"><x-icon name="x" class="size-4" /> Tolak</button>
        @endif
    </x-page-header>
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Detail Request</h3></div>
        <div class="grid gap-4 p-4 sm:grid-cols-2">
            <div><p class="text-sm text-muted-foreground">Tipe</p><p class="font-medium capitalize">{{ $budgetRequest->type }}</p></div>
            <div><p class="text-sm text-muted-foreground">Status</p><p class="font-medium capitalize">{{ $budgetRequest->status }}</p></div>
            <div><p class="text-sm text-muted-foreground">Jumlah</p><p class="font-medium">{{ $budgetRequest->amount ? App\Helpers\Format::currency($budgetRequest->amount) : '-' }}</p></div>
            <div><p class="text-sm text-muted-foreground">Dibuat oleh</p><p class="font-medium">{{ $budgetRequest->createdByUser?->name ?? '-' }}</p></div>
            @if ($budgetRequest->description)
                <div class="sm:col-span-2"><p class="text-sm text-muted-foreground">Deskripsi</p><p class="font-medium whitespace-pre-wrap">{{ $budgetRequest->description }}</p></div>
            @endif
            @if ($budgetRequest->reviewed_by)
                <div><p class="text-sm text-muted-foreground">Direview oleh</p><p class="font-medium">{{ $budgetRequest->reviewedBy?->name ?? '-' }}</p></div>
                <div><p class="text-sm text-muted-foreground">Tanggal Review</p><p class="font-medium">{{ $budgetRequest->reviewed_at?->format('d/m/Y H:i') }}</p></div>
            @endif
        </div>
    </div>
</div>
