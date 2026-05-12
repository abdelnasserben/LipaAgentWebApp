@props(['message' => null])

@if($message)
    <div {{ $attributes->merge(['class' => 'rounded-lg border border-app-red bg-app-red-bg px-3.5 py-2.5 text-[13px] font-medium text-app-red']) }}>
        {{ $message }}
    </div>
@endif
