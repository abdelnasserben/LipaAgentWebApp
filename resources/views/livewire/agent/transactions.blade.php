<div>
    {{-- Filter bar --}}
    <div style="position:sticky;top:0;z-index:10;background:var(--bg);border-bottom:1px solid var(--border-color);padding:12px 16px 0;">
        {{-- Search input --}}
        <div style="position:relative;margin-bottom:10px;">
            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-secondary);display:flex;pointer-events:none;">
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                    <circle cx="6.5" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.4"/>
                    <path d="M10.5 10.5L13.5 13.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                </svg>
            </span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher…"
                style="width:100%;box-sizing:border-box;padding:8px 12px 8px 32px;font-size:13px;border:1px solid var(--border-color);border-radius:8px;background:var(--surface);color:var(--text-primary);outline:none;"
            />
        </div>

        {{-- Type filter chips --}}
        <div style="display:flex;gap:6px;overflow-x:auto;padding-bottom:10px;scrollbar-width:none;">
            @php
                $typeChips = [
                    ['value' => '',         'label' => 'Tous'],
                    ['value' => 'CASH_IN',  'label' => 'Cash In'],
                    ['value' => 'CASH_OUT', 'label' => 'Cash Out'],
                ];
                $statusChips = [
                    ['value' => 'COMPLETED', 'label' => 'Complété'],
                    ['value' => 'DECLINED',  'label' => 'Refusé'],
                    ['value' => 'PENDING',   'label' => 'En attente'],
                ];
            @endphp

            @foreach($typeChips as $chip)
                <button
                    wire:click="$set('filterType', '{{ $chip['value'] }}')"
                    type="button"
                    style="flex-shrink:0;padding:5px 12px;font-size:12px;font-weight:600;border-radius:20px;cursor:pointer;border:1px solid {{ $filterType === $chip['value'] ? 'var(--accent)' : 'var(--border-color)' }};background:{{ $filterType === $chip['value'] ? 'var(--accent)' : 'var(--surface)' }};color:{{ $filterType === $chip['value'] ? '#fff' : 'var(--text-secondary)' }};white-space:nowrap;transition:all 0.15s;">
                    {{ $chip['label'] }}
                </button>
            @endforeach

            <span style="width:1px;height:24px;background:var(--border-color);flex-shrink:0;align-self:center;margin:0 2px;"></span>

            @foreach($statusChips as $chip)
                <button
                    wire:click="$set('filterStatus', '{{ $chip['value'] }}')"
                    type="button"
                    style="flex-shrink:0;padding:5px 12px;font-size:12px;font-weight:600;border-radius:20px;cursor:pointer;border:1px solid {{ $filterStatus === $chip['value'] ? 'var(--accent)' : 'var(--border-color)' }};background:{{ $filterStatus === $chip['value'] ? 'var(--accent)' : 'var(--surface)' }};color:{{ $filterStatus === $chip['value'] ? '#fff' : 'var(--text-secondary)' }};white-space:nowrap;transition:all 0.15s;">
                    {{ $chip['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Transaction list --}}
    <div style="padding:12px 16px 80px;">
        @php
            use Carbon\Carbon;
            $today     = Carbon::today();
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
            <div style="font-size:11px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:var(--text-secondary);margin:12px 0 6px;{{ !$loop->first ? 'margin-top:20px;' : '' }}">
                {{ $dateLabel }}
            </div>

            {{-- Group card --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;">
                @foreach($group as $txn)
                    @php
                        $isCashIn    = $txn['type'] === 'CASH_IN';
                        $typeColor   = $isCashIn ? 'var(--green)' : 'var(--amber)';
                        $typeBg      = $isCashIn ? 'var(--green-bg)' : 'var(--amber-bg)';
                        $typeLabel   = $isCashIn ? 'Cash In' : 'Cash Out';
                        $amountColor = $isCashIn ? 'var(--green)' : 'var(--amber)';
                        $sign        = $isCashIn ? '+' : '−';
                    @endphp
                    <button
                        wire:click="selectTransaction('{{ $txn['id'] }}')"
                        type="button"
                        style="width:100%;display:flex;align-items:center;gap:12px;padding:12px 16px;background:none;border:none;cursor:pointer;text-align:left;{{ !$loop->last ? 'border-bottom:1px solid var(--border-color);' : '' }}"
                        onmouseover="this.style.background='var(--row-hover)'"
                        onmouseout="this.style.background='none'"
                    >
                        {{-- Icon circle --}}
                        <span style="width:36px;height:36px;border-radius:50%;background:{{ $typeBg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            @if($isCashIn)
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="color:{{ $typeColor }}">
                                    <path d="M8 12V4M8 4L5 7M8 4l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="color:{{ $typeColor }}">
                                    <path d="M8 4v8M8 12l-3-3M8 12l3-3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif
                        </span>

                        {{-- Middle: type + description --}}
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $typeLabel }}</div>
                            <div style="font-size:11px;color:var(--text-secondary);font-family:'DM Mono',monospace;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $txn['description'] ?? '—' }}
                            </div>
                        </div>

                        {{-- Right: amount + status --}}
                        <div style="text-align:right;flex-shrink:0;">
                            <div style="font-family:'DM Mono',monospace;font-size:13px;font-weight:700;color:{{ $amountColor }};">
                                {{ $sign }}{{ number_format($txn['requestedAmount'], 0, ',', ' ') }}
                            </div>
                            <div style="margin-top:3px;">
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
    @if($selectedTransaction)
        <div wire:click="closeTransaction" style="position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:200;"></div>
        <div style="position:fixed;inset:0;background:var(--bg);z-index:201;display:flex;flex-direction:column;max-width:600px;margin:0 auto;">
            {{-- Header --}}
            <div style="padding:16px 20px;background:var(--surface);border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:12px;flex-shrink:0;">
                <button wire:click="closeTransaction" type="button"
                    style="background:none;border:none;cursor:pointer;color:var(--text-secondary);display:flex;padding:4px;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--text-primary);">Détail transaction</h3>
            </div>

            {{-- Body --}}
            <div style="flex:1;overflow-y:auto;padding:20px;">
                @php $txn = $selectedTransaction; $isCashIn = $txn['type'] === 'CASH_IN'; @endphp

                {{-- Hero amount --}}
                <div style="text-align:center;padding:20px 0 28px;border-bottom:1px solid var(--border-color);margin-bottom:8px;">
                    <x-agent-badge :status="$txn['type']" />
                    <div style="font-size:34px;font-weight:800;font-family:'DM Mono',monospace;letter-spacing:-0.03em;margin:14px 0 6px;color:{{ $isCashIn ? 'var(--green)' : 'var(--amber)' }};">
                        {{ number_format($txn['netAmountToDestination'], 0, ',', ' ') }}
                        <span style="font-size:16px;font-weight:500;color:var(--text-secondary);">KMF</span>
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
                <x-detail-row label="Montant demandé" :mono="true">{{ number_format($txn['requestedAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Frais" :mono="true">{{ number_format($txn['feeAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Commission" :mono="true">{{ number_format($txn['commissionAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Net destination" :mono="true">{{ number_format($txn['netAmountToDestination'], 0, ',', ' ') }} KMF</x-detail-row>
                <x-detail-row label="Date création">{{ \Carbon\Carbon::parse($txn['createdAt'])->isoFormat('D MMM YYYY, HH:mm') }}</x-detail-row>
                @if($txn['completedAt'])
                    <x-detail-row label="Date completion">{{ \Carbon\Carbon::parse($txn['completedAt'])->isoFormat('D MMM YYYY, HH:mm') }}</x-detail-row>
                @endif
                @if($txn['status'] === 'DECLINED' && !empty($txn['declineReason']))
                    <x-detail-row label="Motif de refus" :border="false">
                        <span style="color:var(--red);">{{ $txn['declineReason'] }}</span>
                    </x-detail-row>
                @endif
            </div>
        </div>
    @endif
</div>
