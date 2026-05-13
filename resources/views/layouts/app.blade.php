<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>{{ $title ?? 'Lipa' }} — Agent Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased">

<div class="app-shell" style="height:100dvh; max-width:1280px; margin:0 auto;" x-data="{ drawer: false }" @keydown.escape.window="drawer = false">

    {{-- Sidebar (tablet/desktop) --}}
    <aside class="app-sidebar" id="app-sidebar">
        {{-- Logo --}}
        <div style="display:flex;align-items:center;gap:10px;padding:0 14px 20px;border-bottom:1px solid rgba(255,255,255,0.07);margin-bottom:8px;flex-shrink:0;overflow:hidden;">
            <div style="width:32px;height:32px;border-radius:9px;background:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M2 4h12v8a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" fill="white" fill-opacity=".2" stroke="white" stroke-width="1.2"/>
                    <path d="M5 4V3a3 3 0 016 0v1" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                    <path d="M5.5 8.5l1.5 1.5 3.5-3" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="sidebar-label-block" style="overflow:hidden;white-space:nowrap;">
                <div style="color:#fff;font-weight:800;font-size:15px;letter-spacing:-0.03em;line-height:1;">Lipa</div>
                <div style="color:rgba(255,255,255,0.32);font-size:9px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;margin-top:2px;">Agent Portal</div>
            </div>
        </div>

        {{-- Nav items --}}
        <nav style="flex:1;display:flex;flex-direction:column;gap:2px;padding:0 8px;overflow-y:auto;">
            @php
                $currentRoute = request()->path();
                $navItems = [
                    ['route' => 'dashboard',     'label' => 'Accueil',       'path' => 'dashboard',     'icon' => 'home'],
                    ['route' => 'operations',    'label' => 'Opérations',    'path' => 'operations',    'icon' => 'operations'],
                    ['route' => 'enroll',        'label' => 'Enrôlement',    'path' => 'enroll',        'icon' => 'enroll'],
                    ['route' => 'customers.kyc', 'label' => 'KYC client',    'path' => 'customers/kyc', 'icon' => 'enroll'],
                    ['route' => 'cards',         'label' => 'Cartes',        'path' => 'cards',         'icon' => 'card'],
                    ['route' => 'transactions',  'label' => 'Transactions',  'path' => 'transactions',  'icon' => 'transactions'],
                    ['route' => 'statement',     'label' => 'Relevé',        'path' => 'statement',     'icon' => 'statement'],
                    ['route' => 'commission',    'label' => 'Commission',    'path' => 'commission',    'icon' => 'commission'],
                    ['route' => 'profile',       'label' => 'Profil',        'path' => 'profile',       'icon' => 'profile'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php $isActive = $currentRoute === $item['path'] || str_starts_with($currentRoute, $item['path'].'/') @endphp
                <a href="{{ route($item['route']) }}"
                   wire:navigate
                   title="{{ $item['label'] }}"
                   class="sidebar-nav-item {{ $isActive ? 'active' : '' }}"
                   style="display:flex;align-items:center;gap:10px;padding:9px 8px;border-radius:9px;text-decoration:none;font-weight:{{ $isActive ? '700' : '500' }};color:{{ $isActive ? '#fff' : 'rgba(255,255,255,0.45)' }};transition:all 0.15s;white-space:nowrap;overflow:hidden;background:{{ $isActive ? 'rgba(255,255,255,0.10)' : 'transparent' }};">
                    <span style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;flex-shrink:0;background:{{ $isActive ? 'var(--accent)' : 'transparent' }};color:{{ $isActive ? '#fff' : 'inherit' }};transition:background 0.15s;">
                        <x-agent-icon :name="$item['icon']" />
                    </span>
                    <span class="sidebar-label" style="font-size:13px;">{{ $item['label'] }}</span>
                    @if($isActive)
                        <span class="sidebar-label" style="margin-left:auto;width:5px;height:5px;border-radius:50%;background:var(--accent);flex-shrink:0;"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Logout --}}
        <div style="padding:12px 8px 0;border-top:1px solid rgba(255,255,255,0.07);flex-shrink:0;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Se déconnecter"
                    style="display:flex;align-items:center;gap:10px;padding:9px 8px;border-radius:9px;border:none;cursor:pointer;background:transparent;color:rgba(255,255,255,0.35);font-family:inherit;font-weight:500;width:100%;transition:all 0.15s;white-space:nowrap;overflow:hidden;"
                    onmouseover="this.style.background='rgba(255,80,80,0.12)';this.style.color='#ff7b72'"
                    onmouseout="this.style.background='transparent';this.style.color='rgba(255,255,255,0.35)'">
                    <span style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M7 3H4a1 1 0 00-1 1v10a1 1 0 001 1h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 12l3-3-3-3M15 9H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="sidebar-label" style="font-size:13px;">Se déconnecter</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <main class="app-main">
        {{-- Top bar --}}
        <div style="display:flex;align-items:center;height:56px;padding:0 16px;background:var(--surface);border-bottom:1px solid var(--border-color);flex-shrink:0;gap:10px;" class="app-top-bar">
            {{-- Hamburger (mobile only) --}}
            <button type="button" @click="drawer = true" aria-label="Ouvrir le menu"
                class="app-hamburger"
                style="display:none;background:transparent;border:none;cursor:pointer;color:var(--text-primary);padding:6px;border-radius:8px;align-items:center;justify-content:center;">
                <x-agent-icon name="menu" size="22" />
            </button>
            <h1 style="font-size:16px;font-weight:700;color:var(--text-primary);letter-spacing:-0.02em;margin:0;">
                {{ $title ?? 'Agent Portal' }}
            </h1>
            <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
                {{ $headerActions ?? '' }}
            </div>
        </div>

        {{-- Page content --}}
        <div class="app-content" style="flex:1;overflow-y:auto;background:var(--bg);">
            @if (session('api_error'))
                <div class="px-4 pt-4">
                    <x-api-error-alert :message="session('api_error')" />
                </div>
            @endif

            {{ $slot }}
        </div>

        {{-- Bottom nav (mobile only) — 4 actions principales + "Plus" --}}
        <nav class="app-bottom-nav" style="background:var(--surface);border-top:1px solid var(--border-color);height:64px;padding-bottom:env(safe-area-inset-bottom);">
            @php
                $bottomTabs = [
                    ['route' => 'dashboard',   'label' => 'Accueil',     'icon' => 'home'],
                    ['route' => 'operations',  'label' => 'Opérations',  'icon' => 'operations'],
                    ['route' => 'enroll',      'label' => 'Enrôlement',  'icon' => 'enroll'],
                    ['route' => 'transactions','label' => 'Transactions','icon' => 'transactions'],
                ];
            @endphp
            @foreach($bottomTabs as $tab)
                @php $tabActive = request()->routeIs($tab['route']) || str_starts_with(request()->path(), $tab['route']) @endphp
                <a href="{{ route($tab['route']) }}" wire:navigate
                   style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;text-decoration:none;color:{{ $tabActive ? 'var(--accent)' : 'var(--text-secondary)' }};padding:6px 4px;position:relative;transition:color 0.15s;">
                    @if($tabActive)
                        <div style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:24px;height:2.5px;border-radius:0 0 3px 3px;background:var(--accent);"></div>
                    @endif
                    <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:{{ $tabActive ? 'var(--accent-bg)' : 'transparent' }};transition:background 0.15s;">
                        <x-agent-icon :name="$tab['icon']" size="20" />
                    </span>
                    <span style="font-size:10px;font-weight:{{ $tabActive ? '700' : '500' }};line-height:1;letter-spacing:0.01em;">{{ $tab['label'] }}</span>
                </a>
            @endforeach
            {{-- "Plus" tab — opens drawer. Active when current page is not in bottom nav. --}}
            @php
                $bottomRoutes = ['dashboard', 'operations', 'enroll', 'transactions'];
                $plusActive = !collect($bottomRoutes)->contains(fn($r) => request()->routeIs($r) || str_starts_with(request()->path(), $r));
            @endphp
            <button type="button" @click="drawer = true"
                style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;background:transparent;border:none;cursor:pointer;color:{{ $plusActive ? 'var(--accent)' : 'var(--text-secondary)' }};font-family:inherit;padding:6px 4px;position:relative;transition:color 0.15s;">
                @if($plusActive)
                    <div style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:24px;height:2.5px;border-radius:0 0 3px 3px;background:var(--accent);"></div>
                @endif
                <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:{{ $plusActive ? 'var(--accent-bg)' : 'transparent' }};transition:background 0.15s;">
                    <x-agent-icon name="more" size="20" />
                </span>
                <span style="font-size:10px;font-weight:{{ $plusActive ? '700' : '500' }};line-height:1;letter-spacing:0.01em;">Plus</span>
            </button>
        </nav>

        {{-- Mobile drawer (overlay + side panel) --}}
        <div class="app-drawer-root"
             x-show="drawer"
             x-cloak
             style="position:fixed;inset:0;z-index:300;">
            {{-- Overlay --}}
            <div @click="drawer = false"
                 x-show="drawer"
                 x-transition.opacity
                 style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>

            {{-- Panel --}}
            <aside
                x-show="drawer"
                x-transition:enter="drawer-enter"
                x-transition:enter-start="drawer-enter-start"
                x-transition:enter-end="drawer-enter-end"
                x-transition:leave="drawer-leave"
                x-transition:leave-start="drawer-leave-start"
                x-transition:leave-end="drawer-leave-end"
                style="position:absolute;top:0;bottom:0;left:0;width:min(82%,300px);background:var(--sidebar-bg);display:flex;flex-direction:column;padding:20px 0 16px;padding-top:calc(20px + env(safe-area-inset-top));box-shadow:2px 0 20px rgba(0,0,0,0.25);">

                {{-- Header --}}
                <div style="display:flex;align-items:center;gap:10px;padding:0 16px 18px;border-bottom:1px solid rgba(255,255,255,0.07);margin-bottom:10px;flex-shrink:0;">
                    <div style="width:32px;height:32px;border-radius:9px;background:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M2 4h12v8a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" fill="white" fill-opacity=".2" stroke="white" stroke-width="1.2"/>
                            <path d="M5 4V3a3 3 0 016 0v1" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                            <path d="M5.5 8.5l1.5 1.5 3.5-3" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="color:#fff;font-weight:800;font-size:15px;letter-spacing:-0.03em;line-height:1;">Lipa</div>
                        <div style="color:rgba(255,255,255,0.32);font-size:9px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;margin-top:2px;">Agent Portal</div>
                    </div>
                    <button type="button" @click="drawer = false" aria-label="Fermer le menu"
                        style="background:transparent;border:none;cursor:pointer;color:rgba(255,255,255,0.55);padding:6px;display:flex;align-items:center;justify-content:center;">
                        <x-agent-icon name="close" size="16" />
                    </button>
                </div>

                {{-- Nav items — exclude items already in bottom nav (dashboard, operations, enroll, transactions) --}}
                @php
                    $bottomNavRoutes = ['dashboard', 'operations', 'enroll', 'transactions'];
                    $drawerItems = array_values(array_filter($navItems, fn($i) => !in_array($i['route'], $bottomNavRoutes, true)));
                @endphp
                <nav style="flex:1;display:flex;flex-direction:column;gap:2px;padding:0 10px;overflow-y:auto;">
                    @foreach($drawerItems as $item)
                        @php $isActive = $currentRoute === $item['path'] || str_starts_with($currentRoute, $item['path'].'/') @endphp
                        <a href="{{ route($item['route']) }}"
                           wire:navigate
                           @click="drawer = false"
                           style="display:flex;align-items:center;gap:12px;padding:11px 10px;border-radius:9px;text-decoration:none;font-weight:{{ $isActive ? '700' : '500' }};color:{{ $isActive ? '#fff' : 'rgba(255,255,255,0.55)' }};background:{{ $isActive ? 'rgba(255,255,255,0.10)' : 'transparent' }};font-size:14px;">
                            <span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;flex-shrink:0;background:{{ $isActive ? 'var(--accent)' : 'rgba(255,255,255,0.04)' }};color:{{ $isActive ? '#fff' : 'inherit' }};">
                                <x-agent-icon :name="$item['icon']" size="18" />
                            </span>
                            <span>{{ $item['label'] }}</span>
                            @if($isActive)
                                <span style="margin-left:auto;width:6px;height:6px;border-radius:50%;background:var(--accent);flex-shrink:0;"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                {{-- Logout — prominent, easy one-tap access --}}
                <div style="padding:12px 10px 0;border-top:1px solid rgba(255,255,255,0.07);flex-shrink:0;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            style="display:flex;align-items:center;justify-content:center;gap:10px;padding:13px 14px;border-radius:11px;border:1px solid rgba(255,90,90,0.35);cursor:pointer;background:rgba(255,80,80,0.12);color:#ff7b72;font-family:inherit;font-weight:700;width:100%;font-size:14px;letter-spacing:-0.01em;">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M7 3H4a1 1 0 00-1 1v10a1 1 0 001 1h3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M12 12l3-3-3-3M15 9H7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Se déconnecter</span>
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </main>
</div>

@livewireScripts
</body>
</html>
