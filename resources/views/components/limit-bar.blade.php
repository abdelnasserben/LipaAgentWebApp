@props(['used', 'limit', 'currency' => 'KMF'])
@php
    $pct = $limit > 0 ? min(($used / $limit) * 100, 100) : 0;
    if ($pct > 85) $color = 'var(--red)';
    elseif ($pct > 65) $color = 'var(--amber)';
    else $color = 'var(--accent)';
@endphp
<div>
    <div style="height:6px;background:var(--border-color);border-radius:3px;overflow:hidden;margin-bottom:6px;">
        <div style="width:{{ $pct }}%;height:100%;background:{{ $color }};border-radius:3px;transition:width 0.5s;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;">
        <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--text-secondary);">{{ number_format($used, 0, ',', ' ') }} utilisé</span>
        <span style="font-family:'DM Mono',monospace;font-size:11px;color:var(--text-secondary);">{{ number_format($limit, 0, ',', ' ') }} {{ $currency }}</span>
    </div>
</div>
