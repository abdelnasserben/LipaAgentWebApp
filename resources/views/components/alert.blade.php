@props([
    'variant' => 'info',
    'icon' => true,
    'textClass' => 'text-xs font-medium',
])

@php
    $variants = [
        'info' => [
            'box' => 'border-app-blue bg-app-blue-bg',
            'text' => 'text-app-blue',
        ],
        'success' => [
            'box' => 'border-app-green bg-app-green-bg',
            'text' => 'text-app-green',
        ],
        'warning' => [
            'box' => 'border-app-amber bg-app-amber-bg',
            'text' => 'text-app-amber',
        ],
        'danger' => [
            'box' => 'border-app-red bg-app-red-bg',
            'text' => 'text-app-red',
        ],
        'neutral' => [
            'box' => 'border-app-border bg-app-bg',
            'text' => 'text-app-muted',
        ],
    ];

    $style = $variants[$variant] ?? $variants['info'];
@endphp

<div
    {{ $attributes->merge([
        'class' => "flex items-center gap-2 rounded-lg border px-3.5 py-2.5 {$style['box']}",
    ]) }}>
    @if ($icon)
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="shrink-0 {{ $style['text'] }}">
            <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.3" />
            <path d="M7 6v4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
            <circle cx="7" cy="4.5" r=".7" fill="currentColor" />
        </svg>
    @endif

    <span class="{{ $textClass }} {{ $style['text'] }}">
        {{ $slot }}
    </span>
</div>
