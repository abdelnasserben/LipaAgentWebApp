@props(['message' => 'Aucune donnée'])
<div style="display:flex;flex-direction:column;align-items:center;padding:48px 24px;color:var(--text-secondary);">
    <div style="width:44px;height:44px;border-radius:50%;background:var(--border-color);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <rect x="3" y="5" width="14" height="11" rx="2" stroke="currentColor" stroke-width="1.4"/>
            <path d="M6 9h8M6 12h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
        </svg>
    </div>
    <p style="margin:0;font-size:13px;">{{ $message }}</p>
</div>
