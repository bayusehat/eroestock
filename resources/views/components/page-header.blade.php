@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ $title }}</h1>
        @if ($description)
            <p class="text-muted-foreground">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex items-center gap-2">{{ $slot }}</div>
    @endif
</div>
