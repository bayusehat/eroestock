@props(['title', 'value', 'icon' => null, 'trend' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border bg-card p-6 shadow-sm']) }}>
    <div class="flex items-center justify-between pb-2">
        <span class="text-sm font-medium text-muted-foreground">{{ $title }}</span>
        @if ($icon)
            <x-icon :name="$icon" class="size-4 text-muted-foreground" />
        @endif
    </div>
    <div class="text-2xl font-bold">{{ $value }}</div>
    @if ($trend)
        <p class="mt-1 text-xs text-muted-foreground">{{ $trend }}</p>
    @endif
</div>
