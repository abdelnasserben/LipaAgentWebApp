<div>
    {{-- Balance hero (dark header) --}}
    <div style="margin:16px 16px 0;background:var(--sidebar-bg);border-radius:12px;padding:24px 20px 28px;position:relative;overflow:hidden;">
        {{-- Grid texture --}}
        <div style="position:absolute;inset:0;opacity:0.04;background-image:linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);background-size:24px 24px;pointer-events:none;"></div>

        <div style="position:relative;">
            {{-- Greeting row --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                <div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.45);font-weight:500;margin-bottom:2px;">
                        {{ now()->isoFormat('dddd D MMMM') }}
                    </div>
                    <div style="font-size:16px;font-weight:700;color:#fff;">
                        Bonjour, {{ Str::before($profile['fullName'], ' ') }} 👋
                    </div>
                </div>
                <x-agent-badge :status="$profile['status']" />
            </div>

            {{-- Balance --}}
            <div style="margin-bottom:20px;">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,255,255,0.4);margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
                    <span>Float Balance</span>
                    <button wire:click="toggleBalance" type="button"
                        style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,0.4);display:flex;padding:0;">
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
                    <div style="display:flex;align-items:baseline;gap:8px;">
                        <span style="font-size:36px;font-weight:800;color:#fff;font-family:'DM Mono',monospace;letter-spacing:-0.03em;line-height:1;">
                            {{ number_format($balance['availableBalance'], 0, ',', ' ') }}
                        </span>
                        <span style="font-size:14px;font-weight:500;color:rgba(255,255,255,0.5);">KMF</span>
                    </div>
                @else
                    <div style="font-size:28px;font-weight:800;color:rgba(255,255,255,0.3);letter-spacing:0.15em;">••••••</div>
                @endif

                @if($balance['frozenBalance'] > 0)
                    <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-top:4px;">
                        + {{ number_format($balance['frozenBalance'], 0, ',', ' ') }} KMF gelé
                    </div>
                @endif
            </div>

            {{-- Float low warning --}}
            @if($summary['belowFloatAlert'])
                <div style="background:rgba(234,179,8,0.15);border:1px solid rgba(234,179,8,0.3);border-radius:8px;padding:8px 12px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M7 1L1.5 12h11L7 1z" stroke="rgba(234,179,8,0.9)" stroke-width="1.3" stroke-linejoin="round"/>
                        <path d="M7 5.5v3M7 9.5v.5" stroke="rgba(234,179,8,0.9)" stroke-width="1.3" stroke-linecap="round"/>
                    </svg>
                    <span style="font-size:12px;color:rgba(234,179,8,0.9);">
                        Float en dessous du seuil d'alerte ({{ number_format($profile['floatAlertThreshold'], 0, ',', ' ') }} KMF)
                    </span>
                </div>
            @endif

            {{-- Today stats strip --}}
            <div style="display:flex;gap:0;border-top:1px solid rgba(255,255,255,0.08);padding-top:16px;">
                @php
                    $stats = [
                        ['label' => 'Cash In', 'value' => number_format($summary['totalCompletedAmountToday'] * 0.6, 0, ',', ' ') . ' KMF'],
                        ['label' => 'Cash Out', 'value' => number_format($summary['totalCompletedAmountToday'] * 0.4, 0, ',', ' ') . ' KMF'],
                        ['label' => 'Opérations', 'value' => $summary['totalCompletedCountToday']],
                        ['label' => 'Commission', 'value' => number_format($summary['commissionEarnedToday'], 0, ',', ' ') . ' KMF'],
                    ];
                @endphp
                @foreach($stats as $i => $stat)
                    <div style="flex:1;{{ $i > 0 ? 'border-left:1px solid rgba(255,255,255,0.08);padding-left:12px;' : '' }}padding-right:12px;">
                        <div style="font-size:10px;color:rgba(255,255,255,0.38);font-weight:500;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.06em;">{{ $stat['label'] }}</div>
                        <div style="font-size:13px;font-weight:700;color:#fff;font-family:'DM Mono',monospace;letter-spacing:-0.01em;">{{ $stat['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        @php
            $actions = [
                ['label' => 'Cash-in Client', 'icon' => 'cash-in', 'color' => 'var(--green)', 'bg' => 'var(--green-bg)', 'href' => route('operations', ['tab' => 'cash-in'])],
                ['label' => 'Cash-out Marchand', 'icon' => 'cash-out', 'color' => 'var(--amber)', 'bg' => 'var(--amber-bg)', 'href' => route('operations', ['tab' => 'cash-out'])],
                ['label' => 'Enrôler Client', 'icon' => 'enroll', 'color' => 'var(--blue)', 'bg' => 'var(--blue-bg)', 'href' => route('enroll')],
                ['label' => 'Transactions', 'icon' => 'transactions', 'color' => 'var(--purple)', 'bg' => 'var(--purple-bg)', 'href' => route('transactions')],
            ];
        @endphp
        @foreach($actions as $action)
            <a href="{{ $action['href'] }}" wire:navigate
               style="display:flex;flex-direction:column;align-items:flex-start;gap:8px;padding:14px;background:var(--surface);border:1px solid var(--border-color);border-radius:10px;text-decoration:none;color:var(--text-primary);">
                <span style="width:34px;height:34px;border-radius:9px;background:{{ $action['bg'] }};display:flex;align-items:center;justify-content:center;">
                    <x-agent-icon :name="$action['icon']" :size="18" style="color:{{ $action['color'] }}" />
                </span>
                <span style="font-size:13px;font-weight:600;">{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Recent transactions --}}
    <div style="padding:0 16px 16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div style="font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);">Transactions récentes</div>
            <a href="{{ route('transactions') }}" wire:navigate style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600;">Voir tout</a>
        </div>

        <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;">
            @forelse($recentTransactions as $txn)
                @php
                    $isCashIn = $txn['type'] === 'CASH_IN';
                    $typeColor = $isCashIn ? 'var(--green)' : 'var(--amber)';
                    $typeBg = $isCashIn ? 'var(--green-bg)' : 'var(--amber-bg)';
                    $typeLabel = $isCashIn ? 'Cash In' : 'Cash Out';
                    $sign = $isCashIn ? '+' : '−';
                    $amountColor = $isCashIn ? 'var(--green)' : 'var(--amber)';
                @endphp
                <button wire:click="selectTransaction('{{ $txn['id'] }}')" type="button"
                    style="width:100%;display:flex;align-items:center;gap:12px;padding:12px 16px;background:none;border:none;cursor:pointer;text-align:left;border-bottom:1px solid var(--border-color);"
                    onmouseover="this.style.background='var(--row-hover)'" onmouseout="this.style.background='none'">
                    <span style="width:34px;height:34px;border-radius:9px;background:{{ $typeBg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <x-agent-icon :name="$isCashIn ? 'cash-in' : 'cash-out'" :size="16" style="color:{{ $typeColor }}" />
                    </span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $txn['description'] ?? ($isCashIn ? 'Cash-in client' : 'Cash-out marchand') }}
                        </div>
                        <div style="font-size:11px;color:var(--text-secondary);margin-top:1px;font-family:'DM Mono',monospace;">
                            {{ \Carbon\Carbon::parse($txn['createdAt'])->format('d M, H:i') }}
                        </div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <div style="font-family:'DM Mono',monospace;font-size:13px;font-weight:600;color:{{ $amountColor }};">
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
    <div style="padding:0 16px 24px;">
        <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);padding:14px 16px;">
            <div style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px;">Informations agent</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
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
                        <div style="font-size:10px;color:var(--text-secondary);margin-bottom:2px;">{{ $info['label'] }}</div>
                        <div style="font-size:12px;font-weight:600;color:var(--text-primary);{{ ($info['mono'] ?? false) ? "font-family:'DM Mono',monospace;" : '' }}">{{ $info['value'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Transaction detail slide-over --}}
    @if($selectedTransaction)
        <div wire:click="closeTransaction" style="position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:200;"></div>
        <div style="position:fixed;inset:0;background:var(--bg);z-index:201;display:flex;flex-direction:column;max-width:600px;margin:0 auto;">
            <div style="padding:16px 20px;background:var(--surface);border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:12px;flex-shrink:0;">
                <button wire:click="closeTransaction" type="button" style="background:none;border:none;cursor:pointer;color:var(--text-secondary);display:flex;padding:4px;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--text-primary);">Détail transaction</h3>
            </div>
            <div style="flex:1;overflow-y:auto;padding:20px;">
                @php $txn = $selectedTransaction; @endphp
                <div style="text-align:center;padding:20px 0 28px;">
                    <x-agent-badge :status="$txn['type']" />
                    <div style="font-size:32px;font-weight:800;color:var(--text-primary);font-family:'DM Mono',monospace;letter-spacing:-0.02em;margin:12px 0 4px;">
                        {{ number_format($txn['netAmountToDestination'], 0, ',', ' ') }}
                        <span style="font-size:16px;font-weight:500;color:var(--text-secondary);">KMF</span>
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
