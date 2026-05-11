@props(['value', 'currency' => 'KMF', 'size' => 13, 'colored' => false])
@php
    $abs = abs($value);
    $formatted = number_format($abs, 0, ',', ' ');
    $sign = $value < 0 ? '−' : '';
    if ($colored) {
        $color = $value < 0 ? 'var(--red)' : 'var(--green)';
    } else {
        $color = 'var(--text-primary)';
    }
@endphp
<span style="font-family:'DM Mono',monospace;font-size:{{ $size }}px;font-weight:500;color:{{ $color }};">
    {{ $sign }}{{ $formatted }}&nbsp;<span style="font-weight:400;color:var(--text-secondary);font-size:{{ $size - 1 }}px;">{{ $currency }}</span>
</span>
