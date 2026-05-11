<div>
    {{-- Hero pending payout card --}}
    <div style="margin:16px 16px 0;">
        <div style="background:var(--sidebar-bg);border-radius:12px;padding:24px 20px;margin-bottom:16px;position:relative;overflow:hidden;">
            {{-- Grid texture --}}
            <div style="position:absolute;inset:0;opacity:0.04;background-image:linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);background-size:24px 24px;pointer-events:none;"></div>

            <div style="position:relative;">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,255,255,0.4);margin-bottom:10px;">
                    Commission en attente
                </div>

                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:18px;">
                    <span style="font-family:'DM Mono',monospace;font-size:32px;font-weight:800;color:#fff;letter-spacing:-0.03em;line-height:1;">
                        {{ number_format($summary['pendingTotal'] ?? 0, 0, ',', ' ') }}
                    </span>
                    <span style="font-size:14px;font-weight:500;color:rgba(255,255,255,0.45);">KMF</span>
                </div>

                {{-- Today / Week / Month strip --}}
                <div style="display:flex;gap:0;border-top:1px solid rgba(255,255,255,0.08);padding-top:14px;">
                    @php
                        $heroStats = [
                            ['label' => 'Aujourd\'hui', 'value' => $summary['todayEarned'] ?? 0],
                            ['label' => 'Cette semaine', 'value' => $summary['weekEarned'] ?? 0],
                            ['label' => 'Ce mois', 'value' => $summary['monthEarned'] ?? 0],
                        ];
                    @endphp
                    @foreach($heroStats as $i => $stat)
                        <div style="flex:1;{{ $i > 0 ? 'border-left:1px solid rgba(255,255,255,0.08);padding-left:12px;' : '' }}padding-right:8px;">
                            <div style="font-size:10px;color:rgba(255,255,255,0.38);font-weight:500;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.06em;">{{ $stat['label'] }}</div>
                            <div style="font-size:12px;font-weight:700;color:#fff;font-family:'DM Mono',monospace;">
                                {{ number_format($stat['value'], 0, ',', ' ') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Stats grid (2×2) --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;">
            @php
                $statsCards = [
                    ['label' => 'Aujourd\'hui',   'value' => $summary['todayEarned'] ?? 0,  'color' => 'var(--green)',  'bg' => 'var(--green-bg)'],
                    ['label' => 'Cette semaine',  'value' => $summary['weekEarned'] ?? 0,   'color' => 'var(--blue)',   'bg' => 'var(--blue-bg)'],
                    ['label' => 'Ce mois',        'value' => $summary['monthEarned'] ?? 0,  'color' => 'var(--purple)', 'bg' => 'var(--purple-bg)'],
                    ['label' => 'Statut',         'value' => null, 'badge' => 'PENDING'],
                ];
            @endphp
            @foreach($statsCards as $card)
                <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:10px;padding:14px;">
                    <div style="font-size:11px;color:var(--text-secondary);font-weight:500;margin-bottom:6px;">{{ $card['label'] }}</div>
                    @if(isset($card['badge']))
                        <x-agent-badge :status="$card['badge']" />
                    @else
                        <div style="font-family:'DM Mono',monospace;font-size:15px;font-weight:700;color:{{ $card['color'] }};">
                            {{ number_format($card['value'], 0, ',', ' ') }}
                            <span style="font-size:11px;font-weight:500;color:var(--text-secondary);">KMF</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Filter chips --}}
    <div style="padding:0 16px 10px;">
        <div style="display:flex;gap:6px;overflow-x:auto;scrollbar-width:none;">
            @php
                $statusChips = [
                    ['value' => '',          'label' => 'Tous'],
                    ['value' => 'PENDING',   'label' => 'En attente'],
                    ['value' => 'PAID',      'label' => 'Payé'],
                    ['value' => 'FAILED',    'label' => 'Échoué'],
                ];
            @endphp
            @foreach($statusChips as $chip)
                <button
                    wire:click="$set('filterStatus', '{{ $chip['value'] }}')"
                    type="button"
                    style="flex-shrink:0;padding:5px 14px;font-size:12px;font-weight:600;border-radius:20px;cursor:pointer;border:1px solid {{ $filterStatus === $chip['value'] ? 'var(--accent)' : 'var(--border-color)' }};background:{{ $filterStatus === $chip['value'] ? 'var(--accent)' : 'var(--surface)' }};color:{{ $filterStatus === $chip['value'] ? '#fff' : 'var(--text-secondary)' }};white-space:nowrap;transition:all 0.15s;">
                    {{ $chip['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Commission history list --}}
    <div style="padding:4px 16px 80px;">
        @php
            use Carbon\Carbon;

            // Group commissions by month
            $groupedByMonth = [];
            foreach ($commissions as $com) {
                $monthKey = Carbon::parse($com['createdAt'])->isoFormat('MMMM YYYY');
                $groupedByMonth[$monthKey][] = $com;
            }
        @endphp

        @forelse($groupedByMonth as $monthLabel => $group)
            {{-- Month group label --}}
            <div style="font-size:11px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:var(--text-secondary);margin:12px 0 6px;{{ !$loop->first ? 'margin-top:20px;' : '' }}">
                {{ $monthLabel }}
            </div>

            {{-- Group card --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;">
                @foreach($group as $com)
                    @php
                        $comDate = Carbon::parse($com['createdAt'])->isoFormat('D MMM');
                    @endphp
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;{{ !$loop->last ? 'border-bottom:1px solid var(--border-color);' : '' }}">
                        {{-- Date --}}
                        <div style="font-family:'DM Mono',monospace;font-size:12px;color:var(--text-secondary);flex-shrink:0;min-width:44px;">
                            {{ $comDate }}
                        </div>

                        {{-- Type badge --}}
                        <div style="flex-shrink:0;">
                            <x-agent-badge :status="$com['type']" />
                        </div>

                        {{-- Spacer --}}
                        <div style="flex:1;"></div>

                        {{-- Amount --}}
                        <div style="font-family:'DM Mono',monospace;font-size:13px;font-weight:700;color:var(--green);flex-shrink:0;">
                            +{{ number_format($com['amount'], 0, ',', ' ') }}
                        </div>

                        {{-- Status badge --}}
                        <div style="flex-shrink:0;">
                            <x-agent-badge :status="$com['status']" />
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <x-empty-state message="Aucune commission trouvée" />
        @endforelse
    </div>
</div>
