@props(['variant' => 'default'])

@php
    $base = 'inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium transition-colors';
    $variants = [
        'default' => 'bg-primary text-primary-foreground',
        'outline' => 'border',
        'secondary' => 'bg-secondary text-secondary-foreground',
        'destructive' => 'bg-destructive text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => $base . ' ' . ($variants[$variant] ?? $variants['default'])]) }}>
    {{ $slot }}
</span>
