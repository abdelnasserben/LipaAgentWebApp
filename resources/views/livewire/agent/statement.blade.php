<div>
    {{-- Date range filter --}}
    <div style="padding:12px 16px 0;">
        <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:10px;padding:14px 16px;margin-bottom:4px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:10px;">Période</div>
            <div style="display:flex;gap:10px;align-items:center;">
                <div style="flex:1;">
                    <label style="font-size:11px;color:var(--text-secondary);display:block;margin-bottom:4px;">Du</label>
                    <input
                        type="date"
                        wire:model.live="filterFrom"
                        style="width:100%;box-sizing:border-box;padding:7px 10px;font-size:13px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg);color:var(--text-primary);outline:none;"
                    />
                </div>
                <div style="flex:1;">
                    <label style="font-size:11px;color:var(--text-secondary);display:block;margin-bottom:4px;">Au</label>
                    <input
                        type="date"
                        wire:model.live="filterTo"
                        style="width:100%;box-sizing:border-box;padding:7px 10px;font-size:13px;border:1px solid var(--border-color);border-radius:7px;background:var(--bg);color:var(--text-primary);outline:none;"
                    />
                </div>
            </div>
        </div>
    </div>

    {{-- Entries list --}}
    <div style="padding:12px 16px 80px;">
        @php
            use Carbon\Carbon;
            $today     = Carbon::today();
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
            <div style="font-size:11px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:var(--text-secondary);margin:12px 0 6px;{{ !$loop->first ? 'margin-top:20px;' : '' }}">
                {{ $dateLabel }}
            </div>

            {{-- Group card --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;">
                @foreach($group as $entry)
                    @php
                        $isCredit    = $entry['entryType'] === 'CREDIT';
                        $signColor   = $isCredit ? 'var(--green)' : 'var(--red)';
                        $iconBg      = $isCredit ? 'var(--green-bg)' : 'var(--red-bg)';
                        $sign        = $isCredit ? '+' : '−';
                        $time        = Carbon::parse($entry['postedAt'])->format('H:i');
                    @endphp
                    <button
                        wire:click="selectEntry('{{ $entry['id'] }}')"
                        type="button"
                        style="width:100%;display:flex;align-items:center;gap:12px;padding:12px 16px;background:none;border:none;cursor:pointer;text-align:left;{{ !$loop->last ? 'border-bottom:1px solid var(--border-color);' : '' }}"
                        onmouseover="this.style.background='var(--row-hover)'"
                        onmouseout="this.style.background='none'"
                    >
                        {{-- Left icon circle --}}
                        <span style="width:36px;height:36px;border-radius:50%;background:{{ $iconBg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            @if($isCredit)
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="color:{{ $signColor }}">
                                    <path d="M8 12V4M8 4L5 7M8 4l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="color:{{ $signColor }}">
                                    <path d="M8 4v8M8 12l-3-3M8 12l3-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif
                        </span>

                        {{-- Middle: description + time --}}
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $entry['description'] }}
                            </div>
                            <div style="font-size:11px;color:var(--text-secondary);font-family:'DM Mono',monospace;margin-top:2px;">
                                {{ $time }}
                            </div>
                        </div>

                        {{-- Right: amount + running balance --}}
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-family:'DM Mono',monospace;font-size:13px;font-weight:700;color:{{ $signColor }};">
                                {{ $sign }}{{ number_format($entry['amount'], 0, ',', ' ') }}
                            </div>
                            <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">
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
        <div wire:click="closeEntry" style="position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:200;"></div>
        <div style="position:fixed;inset:0;background:var(--bg);z-index:201;display:flex;flex-direction:column;max-width:600px;margin:0 auto;">
            {{-- Header --}}
            <div style="padding:16px 20px;background:var(--surface);border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:12px;flex-shrink:0;">
                <button wire:click="closeEntry" type="button"
                    style="background:none;border:none;cursor:pointer;color:var(--text-secondary);display:flex;padding:4px;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--text-primary);">Détail écriture</h3>
            </div>

            {{-- Body --}}
            <div style="flex:1;overflow-y:auto;padding:20px;">
                @php
                    $e        = $selectedEntry;
                    $isCredit = $e['entryType'] === 'CREDIT';
                    $heroColor = $isCredit ? 'var(--green)' : 'var(--red)';
                    $sign      = $isCredit ? '+' : '−';
                @endphp

                {{-- Hero amount --}}
                <div style="text-align:center;padding:20px 0 28px;border-bottom:1px solid var(--border-color);margin-bottom:8px;">
                    <x-agent-badge :status="$e['entryType']" />
                    <div style="font-size:34px;font-weight:800;font-family:'DM Mono',monospace;letter-spacing:-0.03em;margin:14px 0 6px;color:{{ $heroColor }};">
                        {{ $sign }}{{ number_format($e['amount'], 0, ',', ' ') }}
                        <span style="font-size:16px;font-weight:500;color:var(--text-secondary);">KMF</span>
                    </div>
                </div>

                {{-- Detail rows --}}
                <x-detail-row label="N° séquence" :mono="true">{{ $e['globalSequence'] }}</x-detail-row>
                <x-detail-row label="ID Transaction" :mono="true">{{ $e['transactionId'] }}</x-detail-row>
                <x-detail-row label="Type d'écriture">
                    <x-agent-badge :status="$e['entryType']" />
                </x-detail-row>
                <x-detail-row label="Montant" :mono="true">{{ $sign }}{{ number_format($e['amount'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Solde après" :mono="true">{{ number_format($e['runningBalance'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Description">{{ $e['description'] }}</x-detail-row>
                <x-detail-row label="Date comptable" :border="false">{{ \Carbon\Carbon::parse($e['postedAt'])->isoFormat('D MMM YYYY, HH:mm') }}</x-detail-row>
            </div>
        </div>
    @endif
</div>
