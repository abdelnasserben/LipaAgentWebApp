@props(['label', 'mono' => false, 'border' => true])
<div style="display:flex;justify-content:space-between;align-items:flex-start;padding:12px 0;{{ $border ? 'border-bottom:1px solid var(--border-color);' : '' }}">
    <span style="font-size:12px;color:var(--text-secondary);flex-shrink:0;margin-right:16px;">{{ $label }}</span>
    <span style="font-size:13px;color:var(--text-primary);font-weight:500;text-align:right;{{ $mono ? "font-family:'DM Mono',monospace;" : '' }}">
        {{ $slot->isNotEmpty() ? $slot : '—' }}
    </span>
</div>
