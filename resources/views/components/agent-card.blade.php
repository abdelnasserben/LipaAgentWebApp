@props(['pad' => '16px'])
<div {{ $attributes->merge(['style' => "background:var(--surface);border-radius:12px;border:1px solid var(--border-color);box-shadow:0 1px 4px rgba(0,0,0,0.04);padding:{$pad};"]) }}>
    {{ $slot }}
</div>
