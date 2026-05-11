<div>
    {{-- Hero pending payout card --}}
    <div class="mx-4 mt-4">
        <div class="relative mb-4 overflow-hidden rounded-xl bg-app-sidebar px-5 py-6">
            {{-- Grid texture --}}
            <div class="pointer-events-none absolute inset-0 opacity-[0.04] bg-[linear-gradient(rgba(255,255,255,.5)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.5)_1px,transparent_1px)] bg-[length:24px_24px]"></div>

            <div class="relative">
                <div class="mb-2.5 text-[11px] font-semibold uppercase tracking-[0.1em] text-white/40">
                    Commission en attente
                </div>

                <div class="mb-[18px] flex items-baseline gap-2">
                    <span class="font-mono text-[32px] font-extrabold leading-none tracking-[-0.03em] text-white">
                        {{ number_format($summary['pendingTotal'] ?? 0, 0, ',', ' ') }}
                    </span>
                    <span class="text-sm font-medium text-white/45">KMF</span>
                </div>

                {{-- Today / Week / Month strip --}}
                <div class="flex gap-0 border-t border-white/[0.08] pt-3.5">
                    @php
                        $heroStats = [
                            ['label' => 'Aujourd\'hui', 'value' => $summary['todayEarned'] ?? 0],
                            ['label' => 'Cette semaine', 'value' => $summary['weekEarned'] ?? 0],
                            ['label' => 'Ce mois', 'value' => $summary['monthEarned'] ?? 0],
                        ];
                    @endphp

                    @foreach($heroStats as $i => $stat)
                        <div @class([
                            'flex-1 pr-2',
                            'border-l border-white/[0.08] pl-3' => $i > 0,
                        ])>
                            <div class="mb-1 text-[10px] font-medium uppercase tracking-[0.06em] text-white/[0.38]">
                                {{ $stat['label'] }}
                            </div>
                            <div class="font-mono text-xs font-bold text-white">
                                {{ number_format($stat['value'], 0, ',', ' ') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Stats grid (2×2) --}}
        <div class="mb-4 grid grid-cols-2 gap-2">
            @php
                $statsCards = [
                    [
                        'label' => 'Aujourd\'hui',
                        'value' => $summary['todayEarned'] ?? 0,
                        'amountClass' => 'text-app-green',
                    ],
                    [
                        'label' => 'Cette semaine',
                        'value' => $summary['weekEarned'] ?? 0,
                        'amountClass' => 'text-app-blue',
                    ],
                    [
                        'label' => 'Ce mois',
                        'value' => $summary['monthEarned'] ?? 0,
                        'amountClass' => 'text-app-purple',
                    ],
                    [
                        'label' => 'Statut',
                        'value' => null,
                        'badge' => 'PENDING',
                    ],
                ];
            @endphp

            @foreach($statsCards as $card)
                <div class="rounded-[10px] border border-app-border bg-app-surface p-3.5">
                    <div class="mb-1.5 text-[11px] font-medium text-app-muted">
                        {{ $card['label'] }}
                    </div>

                    @if(isset($card['badge']))
                        <x-agent-badge :status="$card['badge']" />
                    @else
                        <div class="font-mono text-[15px] font-bold {{ $card['amountClass'] }}">
                            {{ number_format($card['value'], 0, ',', ' ') }}
                            <span class="text-[11px] font-medium text-app-muted">KMF</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Filter chips --}}
    <div class="px-4 pb-2.5">
        <div class="flex gap-1.5 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
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
                    @class([
                        'shrink-0 whitespace-nowrap rounded-full border px-3.5 py-1.5 text-xs font-semibold transition-colors',
                        'border-app-accent bg-app-accent text-white' => $filterStatus === $chip['value'],
                        'border-app-border bg-app-surface text-app-muted hover:bg-app-row-hover' => $filterStatus !== $chip['value'],
                    ])>
                    {{ $chip['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Commission history list --}}
    <div class="px-4 pb-20 pt-1">
        @php
            use Carbon\Carbon;

            $groupedByMonth = [];
            foreach ($commissions as $com) {
                $monthKey = Carbon::parse($com['createdAt'])->isoFormat('MMMM YYYY');
                $groupedByMonth[$monthKey][] = $com;
            }
        @endphp

        @forelse($groupedByMonth as $monthLabel => $group)
            {{-- Month group label --}}
            <div @class([
                'mb-1.5 text-[11px] font-bold uppercase tracking-[0.07em] text-app-muted',
                'mt-3' => $loop->first,
                'mt-5' => ! $loop->first,
            ])>
                {{ $monthLabel }}
            </div>

            {{-- Group card --}}
            <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                @foreach($group as $com)
                    @php
                        $comDate = Carbon::parse($com['createdAt'])->isoFormat('D MMM');
                    @endphp

                    <div @class([
                        'flex items-center gap-3 px-4 py-3',
                        'border-b border-app-border' => ! $loop->last,
                    ])>
                        {{-- Date --}}
                        <div class="min-w-11 shrink-0 font-mono text-xs text-app-muted">
                            {{ $comDate }}
                        </div>

                        {{-- Type badge --}}
                        <div class="shrink-0">
                            <x-agent-badge :status="$com['type']" />
                        </div>

                        <div class="flex-1"></div>

                        {{-- Amount --}}
                        <div class="shrink-0 font-mono text-[13px] font-bold text-app-green">
                            +{{ number_format($com['amount'], 0, ',', ' ') }}
                        </div>

                        {{-- Status badge --}}
                        <div class="shrink-0">
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