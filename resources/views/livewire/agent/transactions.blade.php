<div>
    {{-- Filter bar --}}
    <div class="sticky top-0 z-10 border-b border-app-border bg-app-bg px-4 pt-3">
        {{-- Search input --}}
        <div class="relative mb-2.5">
            <span class="pointer-events-none absolute left-2.5 top-1/2 flex -translate-y-1/2 text-app-muted">
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                    <circle cx="6.5" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.4" />
                    <path d="M10.5 10.5L13.5 13.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                </svg>
            </span>

            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher…"
                class="box-border w-full rounded-lg border border-app-border bg-app-surface py-2 pl-8 pr-3 text-[13px] text-app-text outline-none focus:border-app-accent" />
        </div>

        {{-- Type filter chips --}}
        <div class="flex gap-1.5 overflow-x-auto pb-2.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @php
                $typeChips = [
                    ['value' => '', 'label' => 'Tous'],
                    ['value' => 'CASH_IN', 'label' => 'Cash In'],
                    ['value' => 'CASH_OUT', 'label' => 'Cash Out'],
                ];

                $statusChips = [
                    ['value' => 'COMPLETED', 'label' => 'Complété'],
                    ['value' => 'DECLINED', 'label' => 'Refusé'],
                    ['value' => 'PENDING', 'label' => 'En attente'],
                ];
            @endphp

            @foreach ($typeChips as $chip)
                <button wire:click="$set('filterType', '{{ $chip['value'] }}')" type="button"
                    @class([
                        'shrink-0 whitespace-nowrap rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors',
                        'border-app-accent bg-app-accent text-white' =>
                            $filterType === $chip['value'],
                        'border-app-border bg-app-surface text-app-muted hover:bg-app-row-hover' =>
                            $filterType !== $chip['value'],
                    ])>
                    {{ $chip['label'] }}
                </button>
            @endforeach

            <span class="mx-0.5 h-6 w-px shrink-0 self-center bg-app-border"></span>

            @foreach ($statusChips as $chip)
                <button wire:click="$set('filterStatus', '{{ $chip['value'] }}')" type="button"
                    @class([
                        'shrink-0 whitespace-nowrap rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors',
                        'border-app-accent bg-app-accent text-white' =>
                            $filterStatus === $chip['value'],
                        'border-app-border bg-app-surface text-app-muted hover:bg-app-row-hover' =>
                            $filterStatus !== $chip['value'],
                    ])>
                    {{ $chip['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Transaction list --}}
    <div class="px-4 pb-20 pt-3">
        @php
            use Carbon\Carbon;

            $today = Carbon::today();
            $yesterday = Carbon::yesterday();

            $grouped = [];
            foreach ($transactions as $txn) {
                $date = Carbon::parse($txn['createdAt'])->startOfDay();

                if ($date->isSameDay($today)) {
                    $key = 'Aujourd\'hui';
                } elseif ($date->isSameDay($yesterday)) {
                    $key = 'Hier';
                } else {
                    $key = Carbon::parse($txn['createdAt'])->isoFormat('D MMMM YYYY');
                }

                $grouped[$key][] = $txn;
            }
        @endphp

        @forelse($grouped as $dateLabel => $group)
            {{-- Date group label --}}
            <div @class([
                'mb-1.5 text-[11px] font-bold uppercase tracking-[0.07em] text-app-muted',
                'mt-3' => $loop->first,
                'mt-5' => !$loop->first,
            ])>
                {{ $dateLabel }}
            </div>

            {{-- Group card --}}
            <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                @foreach ($group as $txn)
                    @php
                        $isCashIn = $txn['type'] === 'CASH_IN';
                        $typeLabel = $isCashIn ? 'Cash In' : 'Cash Out';
                        $sign = $isCashIn ? '+' : '−';
                        $typeClass = $isCashIn ? 'text-app-green' : 'text-app-amber';
                        $typeBgClass = $isCashIn ? 'bg-app-green-bg' : 'bg-app-amber-bg';
                    @endphp

                    <button wire:click="selectTransaction('{{ $txn['id'] }}')" type="button"
                        @class([
                            'flex w-full cursor-pointer items-center gap-3 border-0 bg-transparent px-4 py-3 text-left hover:bg-app-row-hover',
                            'border-b border-app-border' => !$loop->last,
                        ])>
                        {{-- Icon circle --}}
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $typeBgClass }}">
                            @if ($isCashIn)
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="{{ $typeClass }}">
                                    <path d="M8 12V4M8 4L5 7M8 4l3 3" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @else
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    class="{{ $typeClass }}">
                                    <path d="M8 4v8M8 12l-3-3M8 12l3-3" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                        </span>

                        {{-- Middle: type + description --}}
                        <div class="min-w-0 flex-1">
                            <div class="text-[13px] font-semibold text-app-text">
                                {{ $typeLabel }}
                            </div>
                            <div class="mt-0.5 truncate font-mono text-[11px] text-app-muted">
                                {{ $txn['description'] ?? '—' }}
                            </div>
                        </div>

                        {{-- Right: amount + status --}}
                        <div class="shrink-0 text-right">
                            <div class="font-mono text-[13px] font-bold {{ $typeClass }}">
                                {{ $sign }}{{ number_format($txn['requestedAmount'], 0, ',', ' ') }}
                            </div>
                            <div class="mt-1">
                                <x-agent-badge :status="$txn['status']" />
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @empty
            <x-empty-state message="Aucune transaction trouvée" />
        @endforelse
    </div>

    {{-- Transaction detail slide-over --}}
    @if ($selectedTransaction)
        <div wire:click="closeTransaction" class="fixed inset-0 z-[200] bg-black/30"></div>

        <div class="fixed inset-0 z-[201] mx-auto flex max-w-[600px] flex-col bg-app-bg">
            {{-- Header --}}
            <div class="flex shrink-0 items-center gap-3 border-b border-app-border bg-app-surface px-5 py-4">
                <button wire:click="closeTransaction" type="button"
                    class="flex cursor-pointer border-0 bg-transparent p-1 text-app-muted">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>

                <h3 class="m-0 text-[15px] font-bold text-app-text">
                    Détail transaction
                </h3>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-5">
                @php
                    $txn = $selectedTransaction;
                    $isCashIn = $txn['type'] === 'CASH_IN';
                    $heroClass = $isCashIn ? 'text-app-green' : 'text-app-amber';
                @endphp

                {{-- Hero amount --}}
                <div class="mb-2 border-b border-app-border px-0 pb-7 pt-5 text-center">
                    <x-agent-badge :status="$txn['type']" />

                    <div
                        class="mb-1 mt-3.5 font-mono text-[34px] font-extrabold tracking-[-0.03em] {{ $heroClass }}">
                        {{ number_format($txn['netAmountToDestination'], 0, ',', ' ') }}
                        <span class="text-base font-medium text-app-muted">KMF</span>
                    </div>

                    <x-agent-badge :status="$txn['status']" />
                </div>

                {{-- Detail rows --}}
                <x-detail-row label="ID Transaction" :mono="true">{{ $txn['id'] }}</x-detail-row>

                <x-detail-row label="Type">
                    <x-agent-badge :status="$txn['type']" />
                </x-detail-row>

                <x-detail-row label="Statut">
                    <x-agent-badge :status="$txn['status']" />
                </x-detail-row>

                <x-detail-row label="Montant demandé" :mono="true">
                    {{ number_format($txn['requestedAmount'], 0, ',', ' ') }} KMF
                </x-detail-row>

                <x-detail-row label="Frais" :mono="true">
                    {{ number_format($txn['feeAmount'], 0, ',', ' ') }} KMF
                </x-detail-row>

                <x-detail-row label="Commission" :mono="true">
                    {{ number_format($txn['commissionAmount'], 0, ',', ' ') }} KMF
                </x-detail-row>

                <x-detail-row label="Net destination" :mono="true">
                    {{ number_format($txn['netAmountToDestination'], 0, ',', ' ') }} KMF
                </x-detail-row>

                <x-detail-row label="Date création">
                    {{ \Carbon\Carbon::parse($txn['createdAt'])->isoFormat('D MMM YYYY, HH:mm') }}
                </x-detail-row>

                @if ($txn['completedAt'])
                    <x-detail-row label="Date completion">
                        {{ \Carbon\Carbon::parse($txn['completedAt'])->isoFormat('D MMM YYYY, HH:mm') }}
                    </x-detail-row>
                @endif

                @if ($txn['status'] === 'DECLINED' && !empty($txn['declineReason']))
                    <x-detail-row label="Motif de refus" :border="false">
                        <span class="text-app-red">{{ $txn['declineReason'] }}</span>
                    </x-detail-row>
                @endif
            </div>
        </div>
    @endif
</div>
