<div>
    <x-api-error-alert :message="$apiError" class="mx-4 mt-4" />

    {{-- Date range filter --}}
    <div class="px-4 pt-3">
        <div class="mb-1 rounded-[10px] border border-app-border bg-app-surface px-4 py-3.5">
            <div class="mb-2.5 text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                Période
            </div>

            <div class="flex items-center gap-2.5">
                <div class="flex-1">
                    <label class="mb-1 block text-[11px] text-app-muted">Du</label>
                    <input
                        type="date"
                        wire:model.live="filterFrom"
                        class="box-border w-full rounded-[7px] border border-app-border bg-app-bg px-2.5 py-[7px] text-[13px] text-app-text outline-none focus:border-app-accent"
                    />
                </div>

                <div class="flex-1">
                    <label class="mb-1 block text-[11px] text-app-muted">Au</label>
                    <input
                        type="date"
                        wire:model.live="filterTo"
                        class="box-border w-full rounded-[7px] border border-app-border bg-app-bg px-2.5 py-[7px] text-[13px] text-app-text outline-none focus:border-app-accent"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Entries list --}}
    <div class="px-4 pb-20 pt-3">
        @php
            use Carbon\Carbon;

            $today = Carbon::today();
            $yesterday = Carbon::yesterday();

            $grouped = [];
            foreach ($entries as $entry) {
                $date = Carbon::parse($entry['postedAt'])->startOfDay();

                if ($date->isSameDay($today)) {
                    $key = 'Aujourd\'hui';
                } elseif ($date->isSameDay($yesterday)) {
                    $key = 'Hier';
                } else {
                    $key = Carbon::parse($entry['postedAt'])->isoFormat('D MMMM YYYY');
                }

                $grouped[$key][] = $entry;
            }
        @endphp

        @forelse($grouped as $dateLabel => $group)
            {{-- Date group label --}}
            <div @class([
                'mb-1.5 text-[11px] font-bold uppercase tracking-[0.07em] text-app-muted',
                'mt-3' => $loop->first,
                'mt-5' => ! $loop->first,
            ])>
                {{ $dateLabel }}
            </div>

            {{-- Group card --}}
            <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                @foreach($group as $entry)
                    @php
                        $isCredit = $entry['entryType'] === 'CREDIT';
                        $sign = $isCredit ? '+' : '−';
                        $time = Carbon::parse($entry['postedAt'])->format('H:i');
                        $signClass = $isCredit ? 'text-app-green' : 'text-app-red';
                        $iconBgClass = $isCredit ? 'bg-app-green-bg' : 'bg-app-red-bg';
                    @endphp

                    <button
                        wire:click="selectEntry('{{ $entry['id'] }}')"
                        type="button"
                        @class([
                            'flex w-full cursor-pointer items-center gap-3 border-0 bg-transparent px-4 py-3 text-left hover:bg-app-row-hover',
                            'border-b border-app-border' => ! $loop->last,
                        ])
                    >
                        {{-- Left icon circle --}}
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $iconBgClass }}">
                            @if($isCredit)
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="{{ $signClass }}">
                                    <path d="M8 12V4M8 4L5 7M8 4l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="{{ $signClass }}">
                                    <path d="M8 4v8M8 12l-3-3M8 12l3-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif
                        </span>

                        {{-- Middle: description + time --}}
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-[13px] text-app-text">
                                {{ $entry['description'] }}
                            </div>
                            <div class="mt-0.5 font-mono text-[11px] text-app-muted">
                                {{ $time }}
                            </div>
                        </div>

                        {{-- Right: amount + running balance --}}
                        <div class="shrink-0 text-right">
                            <div class="font-mono text-[13px] font-bold {{ $signClass }}">
                                {{ $sign }}{{ number_format($entry['amount'], 0, ',', ' ') }}
                            </div>
                            <div class="mt-0.5 text-[11px] text-app-muted">
                                → {{ number_format($entry['runningBalance'], 0, ',', ' ') }} KMF
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @empty
            <x-empty-state message="Aucune écriture trouvée" />
        @endforelse
    </div>

    {{-- Entry detail slide-over --}}
    @if($selectedEntry)
        <div wire:click="closeEntry" class="fixed inset-0 z-[200] bg-black/30"></div>

        <div class="fixed inset-0 z-[201] mx-auto flex max-w-[600px] flex-col bg-app-bg">
            {{-- Header --}}
            <div class="flex shrink-0 items-center gap-3 border-b border-app-border bg-app-surface px-5 py-4">
                <button wire:click="closeEntry" type="button"
                    class="flex cursor-pointer border-0 bg-transparent p-1 text-app-muted">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <h3 class="m-0 text-[15px] font-bold text-app-text">
                    Détail écriture
                </h3>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-5">
                @php
                    $e = $selectedEntry;
                    $isCredit = $e['entryType'] === 'CREDIT';
                    $sign = $isCredit ? '+' : '−';
                    $heroClass = $isCredit ? 'text-app-green' : 'text-app-red';
                @endphp

                {{-- Hero amount --}}
                <div class="mb-2 border-b border-app-border px-0 pb-7 pt-5 text-center">
                    <x-agent-badge :status="$e['entryType']" />

                    <div class="mb-1 mt-3.5 font-mono text-[34px] font-extrabold tracking-[-0.03em] {{ $heroClass }}">
                        {{ $sign }}{{ number_format($e['amount'], 0, ',', ' ') }}
                        <span class="text-base font-medium text-app-muted">KMF</span>
                    </div>
                </div>

                {{-- Detail rows --}}
                <x-detail-row label="N° séquence" :mono="true">{{ $e['globalSequence'] }}</x-detail-row>
                <x-detail-row label="ID Transaction" :mono="true">{{ $e['transactionId'] }}</x-detail-row>

                <x-detail-row label="Type d'écriture">
                    <x-agent-badge :status="$e['entryType']" />
                </x-detail-row>

                <x-detail-row label="Montant" :mono="true">
                    {{ $sign }}{{ number_format($e['amount'], 0, ',', ' ') }} KMF
                </x-detail-row>

                <x-detail-row label="Solde après" :mono="true">
                    {{ number_format($e['runningBalance'], 0, ',', ' ') }} KMF
                </x-detail-row>

                <x-detail-row label="Description">{{ $e['description'] }}</x-detail-row>

                <x-detail-row label="Date comptable" :border="false">
                    {{ \Carbon\Carbon::parse($e['postedAt'])->isoFormat('D MMM YYYY, HH:mm') }}
                </x-detail-row>
            </div>
        </div>
    @endif
</div>
