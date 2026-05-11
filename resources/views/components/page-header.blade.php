@props(['title', 'subtitle' => null])
<div style="display:flex;align-items:flex-start;justify-content:space-between;padding:16px 16px 0;margin-bottom:16px;">
    <div>
        <h2 style="margin:0;font-size:18px;font-weight:700;color:var(--text-primary);letter-spacing:-0.02em;">{{ $title }}</h2>
        @if($subtitle)
            <p style="margin:3px 0 0;font-size:13px;color:var(--text-secondary);">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($action))
        <div>{{ $action }}</div>
    @endif
</div>
