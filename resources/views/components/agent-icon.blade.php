@props(['name', 'size' => 22])
@php
    // Each icon: [viewBox-size, paths]. The viewBox is independent of the display size,
    // so the same icon scales correctly whether rendered at 14px or 28px.
    $icons = [
        'home'         => [22, '<path d="M3 9.5L11 3l8 6.5V19a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M8 20v-7h6v7" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'],
        'operations'   => [22, '<rect x="3" y="6" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M3 10h16" stroke="currentColor" stroke-width="1.5"/><path d="M7 14h2M12 14h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M11 3v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
        'enroll'       => [22, '<circle cx="9" cy="8" r="4" stroke="currentColor" stroke-width="1.5"/><path d="M3 19c0-3.3 2.7-6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M16 13v6M13 16h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
        'transactions' => [22, '<path d="M4 7h14M4 11h10M4 15h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="17" cy="15" r="3" stroke="currentColor" stroke-width="1.5"/>'],
        'statement'    => [22, '<rect x="4" y="2" width="14" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M8 7h6M8 11h6M8 15h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
        'commission'   => [22, '<circle cx="8" cy="8" r="3" stroke="currentColor" stroke-width="1.5"/><circle cx="14" cy="14" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M5 17l12-12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
        'profile'      => [22, '<circle cx="11" cy="8" r="4" stroke="currentColor" stroke-width="1.5"/><path d="M3 19c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
        'cash-in'      => [24, '<path d="M12 5v14M5 12l7-7 7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
        'cash-out'     => [24, '<path d="M12 19V5M5 12l7 7 7-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
        'eye'          => [16, '<path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.3"/><circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.3"/>'],
        'eye-off'      => [16, '<path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.3"/><circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.3"/><path d="M2 2l12 12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>'],
        'back'         => [16, '<path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>'],
        'logout'       => [16, '<path d="M7 3H4a1 1 0 00-1 1v10a1 1 0 001 1h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12 12l3-3-3-3M15 9H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
        'close'        => [12, '<path d="M9 3L3 9M3 3l6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
        'check'        => [14, '<path d="M2 6l4 4 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
        'warning'      => [14, '<path d="M7 1L1.5 12h11L7 1z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><path d="M7 5.5v3M7 9.5v.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>'],
        'search'       => [18, '<circle cx="9" cy="9" r="5.5" stroke="currentColor" stroke-width="1.4"/><path d="M13 13l3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>'],
        'spinner'      => [18, '<circle cx="9" cy="9" r="7" stroke="rgba(0,0,0,0.1)" stroke-width="2"/><path d="M9 2a7 7 0 017 7" stroke="var(--accent)" stroke-width="2" stroke-linecap="round"/>'],
        'filter'       => [24, '<path d="M3 6h18M7 12h10M11 18h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'],
        'upload'       => [24, '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>'],
        'card'         => [24, '<rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.5"/>'],
        'menu'         => [22, '<path d="M3 6h16M3 11h16M3 16h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'],
        'more'         => [22, '<circle cx="5" cy="11" r="1.6" fill="currentColor"/><circle cx="11" cy="11" r="1.6" fill="currentColor"/><circle cx="17" cy="11" r="1.6" fill="currentColor"/>'],
    ];
    [$vb, $paths] = $icons[$name] ?? [22, ''];
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $vb }} {{ $vb }}" fill="none" {{ $attributes }}>
    {!! $paths !!}
</svg>
