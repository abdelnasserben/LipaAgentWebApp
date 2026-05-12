<div>
    <x-api-error-alert :message="$apiError" class="mx-4 mt-4" />

    {{-- Balance hero (dark header) --}}
    <div class="relative mx-4 mt-4 overflow-hidden rounded-xl bg-app-sidebar px-5 pb-7 pt-6">
        {{-- Grid texture --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.04] bg-[linear-gradient(rgba(255,255,255,.5)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.5)_1px,transparent_1px)] bg-[length:24px_24px]"></div>

        <div class="relative">
            {{-- Greeting row --}}
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <div class="mb-0.5 text-xs font-medium text-white/45">
                        {{ now()->isoFormat('dddd D MMMM') }}
                    </div>
                    <div class="text-base font-bold text-white">
                        Bonjour, {{ Str::before($profile['fullName'], ' ') }} 👋
                    </div>
                </div>
                <x-agent-badge :status="$profile['status']" />
            </div>

            {{-- Balance --}}
            <div class="mb-5">
                <div class="mb-2 flex items-center justify-between text-[11px] font-semibold uppercase tracking-[0.1em] text-white/40">
                    <span>Float Balance</span>
                    <button wire:click="toggleBalance" type="button"
                        class="flex cursor-pointer border-0 bg-transparent p-0 text-white/40">
                        @if($balanceVisible)
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.3"/>
                                <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.3"/>
                            </svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.3"/>
                                <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M2 2l12 12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            </svg>
                        @endif
                    </button>
                </div>

                @if($balanceVisible)
                    <div class="flex items-baseline gap-2">
                        <span class="font-mono text-4xl font-extrabold leading-none tracking-[-0.03em] text-white">
                            {{ number_format($balance['availableBalance'], 0, ',', ' ') }}
                        </span>
                        <span class="text-sm font-medium text-white/50">KMF</span>
                    </div>
                @else
                    <div class="text-[28px] font-extrabold tracking-[0.15em] text-white/30">••••••</div>
                @endif

                @if($balance['frozenBalance'] > 0)
                    <div class="mt-1 text-[11px] text-white/40">
                        + {{ number_format($balance['frozenBalance'], 0, ',', ' ') }} KMF gelé
                    </div>
                @endif
            </div>

            {{-- Float low warning --}}
            @if($summary['belowFloatAlert'])
                <div class="mb-4 flex items-center gap-2 rounded-lg border border-yellow-500/30 bg-yellow-500/15 px-3 py-2">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M7 1L1.5 12h11L7 1z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                        <path d="M7 5.5v3M7 9.5v.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                    <span class="text-xs text-yellow-500">
                        Float en dessous du seuil d'alerte ({{ number_format($profile['floatAlertThreshold'], 0, ',', ' ') }} KMF)
                    </span>
                </div>
            @endif

            {{-- Today stats strip --}}
            <div class="flex gap-0 border-t border-white/[0.08] pt-4">
                @php
                    $stats = [
                        ['label' => 'Cash In', 'value' => number_format($summary['totalCompletedAmountToday'] * 0.6, 0, ',', ' ') . ' KMF'],
                        ['label' => 'Cash Out', 'value' => number_format($summary['totalCompletedAmountToday'] * 0.4, 0, ',', ' ') . ' KMF'],
                        ['label' => 'Opérations', 'value' => $summary['totalCompletedCountToday']],
                        ['label' => 'Commission', 'value' => number_format($summary['commissionEarnedToday'], 0, ',', ' ') . ' KMF'],
                    ];
                @endphp

                @foreach($stats as $i => $stat)
                    <div @class([
                        'flex-1 pr-3',
                        'border-l border-white/[0.08] pl-3' => $i > 0,
                    ])>
                        <div class="mb-1 text-[10px] font-medium uppercase tracking-[0.06em] text-white/[0.38]">
                            {{ $stat['label'] }}
                        </div>
                        <div class="font-mono text-[13px] font-bold tracking-[-0.01em] text-white">
                            {{ $stat['value'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-2 gap-2 p-4">
        @php
            $actions = [
                [
                    'label' => 'Cash-in Client',
                    'icon' => 'cash-in',
                    'iconClass' => 'text-app-green',
                    'iconBgClass' => 'bg-app-green-bg',
                    'href' => route('operations', ['tab' => 'cash-in']),
                ],
                [
                    'label' => 'Cash-out Marchand',
                    'icon' => 'cash-out',
                    'iconClass' => 'text-app-amber',
                    'iconBgClass' => 'bg-app-amber-bg',
                    'href' => route('operations', ['tab' => 'cash-out']),
                ],
                [
                    'label' => 'Enrôler Client',
                    'icon' => 'enroll',
                    'iconClass' => 'text-app-blue',
                    'iconBgClass' => 'bg-app-blue-bg',
                    'href' => route('enroll'),
                ],
                [
                    'label' => 'Transactions',
                    'icon' => 'transactions',
                    'iconClass' => 'text-app-purple',
                    'iconBgClass' => 'bg-app-purple-bg',
                    'href' => route('transactions'),
                ],
            ];
        @endphp

        @foreach($actions as $action)
            <a href="{{ $action['href'] }}" wire:navigate
                class="flex flex-col items-start gap-2 rounded-[10px] border border-app-border bg-app-surface p-3.5 text-app-text no-underline">
                <span class="flex h-[34px] w-[34px] items-center justify-center rounded-[9px] {{ $action['iconBgClass'] }}">
                    <x-agent-icon :name="$action['icon']" :size="18" class="{{ $action['iconClass'] }}" />
                </span>
                <span class="text-[13px] font-semibold">{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Recent transactions --}}
    <div class="px-4 pb-4">
        <div class="mb-3 flex items-center justify-between">
            <div class="text-xs font-bold uppercase tracking-[0.08em] text-app-muted">
                Transactions récentes
            </div>
            <a href="{{ route('transactions') }}" wire:navigate
                class="text-xs font-semibold text-app-accent no-underline">
                Voir tout
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
            @forelse($recentTransactions as $txn)
                @php
                    $isCashIn = $txn['type'] === 'CASH_IN';
                    $typeIconClass = $isCashIn ? 'text-app-green' : 'text-app-amber';
                    $typeBgClass = $isCashIn ? 'bg-app-green-bg' : 'bg-app-amber-bg';
                    $sign = $isCashIn ? '+' : '−';
                    $amountClass = $isCashIn ? 'text-app-green' : 'text-app-amber';
                @endphp

                <button wire:click="selectTransaction('{{ $txn['id'] }}')" type="button"
                    class="flex w-full cursor-pointer items-center gap-3 border-0 border-b border-app-border bg-transparent px-4 py-3 text-left hover:bg-app-row-hover">
                    <span class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-[9px] {{ $typeBgClass }}">
                        <x-agent-icon :name="$isCashIn ? 'cash-in' : 'cash-out'" :size="16" class="{{ $typeIconClass }}" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[13px] font-semibold text-app-text">
                            {{ $txn['description'] ?? ($isCashIn ? 'Cash-in client' : 'Cash-out marchand') }}
                        </div>
                        <div class="mt-px font-mono text-[11px] text-app-muted">
                            {{ \Carbon\Carbon::parse($txn['createdAt'])->format('d M, H:i') }}
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <div class="font-mono text-[13px] font-semibold {{ $amountClass }}">
                            {{ $sign }}{{ number_format($txn['requestedAmount'], 0, ',', ' ') }}
                        </div>
                        <x-agent-badge :status="$txn['status']" />
                    </div>
                </button>
            @empty
                <x-empty-state message="Aucune transaction récente" />
            @endforelse
        </div>
    </div>

    {{-- Agent info card --}}
    <div class="px-4 pb-6">
        <div class="rounded-xl border border-app-border bg-app-surface px-4 py-3.5">
            <div class="mb-2.5 text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                Informations agent
            </div>

            <div class="grid grid-cols-2 gap-2">
                @php
                    $infos = [
                        ['label' => 'Réf.', 'value' => $profile['externalRef'], 'mono' => true],
                        ['label' => 'Zone', 'value' => $profile['zone']],
                        ['label' => 'KYC', 'value' => $profile['kycLevel']],
                        ['label' => 'Contrat', 'value' => $profile['contractRef'] ?? '—', 'mono' => true],
                    ];
                @endphp

                @foreach($infos as $info)
                    <div>
                        <div class="mb-0.5 text-[10px] text-app-muted">
                            {{ $info['label'] }}
                        </div>
                        <div @class([
                            'text-xs font-semibold text-app-text',
                            'font-mono' => $info['mono'] ?? false,
                        ])>
                            {{ $info['value'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Transaction detail slide-over --}}
    @if($selectedTransaction)
        <div wire:click="closeTransaction" class="fixed inset-0 z-[200] bg-black/30"></div>

        <div class="fixed inset-0 z-[201] mx-auto flex max-w-[600px] flex-col bg-app-bg">
            <div class="flex shrink-0 items-center gap-3 border-b border-app-border bg-app-surface px-5 py-4">
                <button wire:click="closeTransaction" type="button"
                    class="flex cursor-pointer border-0 bg-transparent p-1 text-app-muted">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <h3 class="m-0 text-[15px] font-bold text-app-text">
                    Détail transaction
                </h3>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                @php $txn = $selectedTransaction; @endphp

                <div class="px-0 pb-7 pt-5 text-center">
                    <x-agent-badge :status="$txn['type']" />

                    <div class="my-3 mb-1 font-mono text-[32px] font-extrabold tracking-[-0.02em] text-app-text">
                        {{ number_format($txn['netAmountToDestination'], 0, ',', ' ') }}
                        <span class="text-base font-medium text-app-muted">KMF</span>
                    </div>

                    <x-agent-badge :status="$txn['status']" />
                </div>

                <x-detail-row label="ID Transaction" :mono="true">{{ $txn['id'] }}</x-detail-row>
                <x-detail-row label="Montant demandé" :mono="true">{{ number_format($txn['requestedAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Frais" :mono="true">{{ number_format($txn['feeAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Commission" :mono="true">{{ number_format($txn['commissionAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Net destination" :mono="true">{{ number_format($txn['netAmountToDestination'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Date">{{ \Carbon\Carbon::parse($txn['createdAt'])->format('d M Y, H:i') }}</x-detail-row>

                @if($txn['description'] ?? null)
                    <x-detail-row label="Description" :border="false">{{ $txn['description'] }}</x-detail-row>
                @endif
            </div>
        </div>
    @endif
</div>
