<div>
    <x-api-error-alert :message="$apiError" class="mx-4 mt-4" />

    {{-- Tab bar --}}
    <div class="border-b-2 border-app-border bg-app-surface">
        <div class="mx-auto flex max-w-5xl px-4 md:px-6">
            @php
                $tabs = [
                    ['key' => 'sell',          'label' => 'Vendre une carte', 'color' => 'text-app-purple'],
                    ['key' => 'report-lost',   'label' => 'Carte perdue',     'color' => 'text-app-amber'],
                    ['key' => 'report-stolen', 'label' => 'Carte volée',      'color' => 'text-app-red'],
                    ['key' => 'replace',       'label' => 'Remplacement',     'color' => 'text-app-accent'],
                ];
            @endphp

            @foreach ($tabs as $tab)
                <button wire:click="switchTab('{{ $tab['key'] }}')" type="button" @class([
                    'relative flex flex-1 cursor-pointer items-center justify-center gap-2 border-0 bg-transparent px-4 py-3.5 text-[13px] font-bold md:flex-none md:min-w-44',
                    $tab['color'] => $activeTab === $tab['key'],
                    'text-app-muted' => $activeTab !== $tab['key'],
                ])>
                    {{ $tab['label'] }}
                    @if ($activeTab === $tab['key'])
                        <span @class([
                            'absolute -bottom-0.5 left-0 right-0 h-0.5 rounded-t-sm',
                            'bg-app-purple' => $tab['key'] === 'sell',
                            'bg-app-amber' => $tab['key'] === 'report-lost',
                            'bg-app-red' => $tab['key'] === 'report-stolen',
                            'bg-app-accent' => $tab['key'] === 'replace',
                        ])></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-5 md:px-6 md:py-6">
        @if (! $canSellCards)
            <div class="rounded-xl border border-app-border bg-app-surface p-5 text-[13px] text-app-muted">
                Les actions sur les cartes ne sont pas activées pour ce compte Agent.
            </div>
        @else
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
                <div class="min-w-0 space-y-4">
                    {{-- SELL CARD OUTSIDE ENROLLMENT --}}
                    @if ($activeTab === 'sell')
                        {{-- step: lookup --}}
                        @if ($sellStep === 'lookup')
                            <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                                <div class="mb-5">
                                    <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                        Rechercher un client
                                    </div>
                                    <div class="text-xs text-app-muted">
                                        Entrez le numéro de téléphone du client à qui vendre une carte.
                                    </div>
                                </div>

                                @if ($sellError)
                                    <div class="mb-4 flex items-center gap-2 rounded-lg border border-app-red bg-app-red-bg px-3.5 py-2.5 text-[13px] text-app-red">
                                        <x-agent-icon name="warning" :size="14" />
                                        {{ $sellError }}
                                    </div>
                                @endif

                                <div class="mb-5">
                                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Numéro de téléphone
                                    </label>
                                    <div class="flex w-full items-stretch gap-2">
                                        <div class="flex shrink-0 items-center justify-center rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 font-mono text-sm font-bold text-app-text">
                                            +{{ $sellPhoneCountryCode }}
                                        </div>
                                        <input type="tel" wire:model="sellPhoneNumber" placeholder="3XX XXXX"
                                            inputmode="numeric" wire:keydown.enter="lookupSellCustomer"
                                            class="box-border min-w-0 flex-1 rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base tracking-[0.05em] text-app-text outline-none focus:border-app-purple" />
                                    </div>
                                    @error('sellPhoneNumber') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button wire:click="lookupSellCustomer" wire:loading.attr="disabled" type="button"
                                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-purple p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-48">
                                        <span wire:loading.remove wire:target="lookupSellCustomer" class="flex items-center gap-2">
                                            <x-agent-icon name="search" :size="16" />
                                            Rechercher
                                        </span>
                                        <span wire:loading.flex wire:target="lookupSellCustomer" class="hidden items-center gap-2">
                                            <x-spinner :size="16" />
                                            Recherche…
                                        </span>
                                    </button>
                                </div>
                            </div>

                        {{-- step: select card --}}
                        @elseif ($sellStep === 'select')
                            <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                                <button wire:click="backToSellLookup" type="button"
                                    class="mb-5 flex cursor-pointer items-center gap-1.5 border-0 bg-transparent p-0 text-[13px] font-semibold text-app-muted">
                                    <x-agent-icon name="back" :size="16" />
                                    Retour
                                </button>

                                <div class="mb-5 flex items-center gap-3 rounded-xl border-[1.5px] border-app-purple bg-app-purple-bg p-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-app-purple text-xl font-extrabold text-white">
                                        {{ mb_strtoupper(mb_substr($sellCustomer['fullName'] ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-base font-extrabold tracking-[-0.02em] text-app-text">
                                            {{ $sellCustomer['fullName'] ?? '—' }}
                                        </div>
                                        <div class="mt-0.5 font-mono text-[12px] text-app-muted">
                                            +{{ $sellCustomer['phoneCountryCode'] ?? '' }} {{ $sellCustomer['phoneNumber'] ?? '' }}
                                        </div>
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            <x-agent-badge :status="$sellCustomer['status'] ?? 'ACTIVE'" />
                                            <x-agent-badge :status="$sellCustomer['kycLevel'] ?? 'KYC_BASIC'" />
                                        </div>
                                    </div>
                                </div>

                                @if ($sellError)
                                    <div class="mb-4 flex items-center gap-2 rounded-lg border border-app-red bg-app-red-bg px-3.5 py-2.5 text-[13px] text-app-red">
                                        <x-agent-icon name="warning" :size="14" />
                                        {{ $sellError }}
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Carte de stock à vendre
                                    </label>

                                    @if (empty($cardStock))
                                        <div class="rounded-lg border border-app-border bg-app-bg px-3.5 py-3 text-xs text-app-muted">
                                            Aucune carte assignée à votre stock.
                                        </div>
                                    @else
                                        <div class="flex flex-col gap-2">
                                            @foreach ($cardStock as $card)
                                                @php $selected = $sellNfcUid === ($card['nfcUid'] ?? null); @endphp
                                                <button wire:click="selectSellCard('{{ $card['nfcUid'] ?? '' }}')" type="button" @class([
                                                    'flex w-full cursor-pointer items-center gap-3 rounded-[10px] px-3.5 py-3 text-left',
                                                    'border-2 border-app-purple bg-app-purple-bg' => $selected,
                                                    'border-[1.5px] border-app-border bg-app-bg' => ! $selected,
                                                ])>
                                                    <div @class([
                                                        'h-2 w-2 shrink-0 rounded-full border-2',
                                                        'border-app-purple bg-app-purple' => $selected,
                                                        'border-app-muted bg-transparent' => ! $selected,
                                                    ])></div>

                                                    <div class="min-w-0 flex-1">
                                                        <div class="truncate font-mono text-xs font-bold text-app-text">
                                                            {{ $card['internalCardNumber'] ?? '—' }}
                                                        </div>
                                                        <div class="mt-0.5 truncate font-mono text-[11px] tracking-[0.05em] text-app-muted">
                                                            NFC: {{ $card['nfcUid'] ?? '—' }}
                                                        </div>
                                                    </div>

                                                    @if ($selected)
                                                        <x-agent-icon name="check" :size="16" class="shrink-0 text-app-purple" />
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                    @error('sellNfcUid') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                                </div>

                                <div class="mb-5">
                                    <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Prix de la carte (KMF)
                                    </label>
                                    <div class="relative">
                                        <input type="number" wire:model="sellCardPrice" min="1" placeholder="0"
                                            class="box-border w-full rounded-[10px] border-2 border-app-border bg-app-surface py-3 pl-4 pr-14 font-mono text-xl font-bold tracking-[-0.02em] text-app-text outline-none focus:border-app-purple" />
                                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-app-muted">KMF</span>
                                    </div>
                                    @error('sellCardPrice') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button wire:click="submitSell" wire:loading.attr="disabled" type="button"
                                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-purple p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-52">
                                        <span wire:loading.remove wire:target="submitSell">Vendre la carte</span>
                                        <span wire:loading.flex wire:target="submitSell" class="hidden items-center gap-2">
                                            <x-spinner :size="16" /> Vente…
                                        </span>
                                    </button>
                                </div>
                            </div>

                        {{-- step: success --}}
                        @else
                            <div class="rounded-xl border border-app-green bg-app-green-bg p-4 md:p-5">
                                <div class="mb-3 flex items-center gap-2 text-[13px] font-bold text-app-green">
                                    <x-agent-icon name="check" :size="16" />
                                    Carte vendue avec succès
                                </div>

                                <div class="rounded-xl border border-app-border bg-app-surface px-4 py-1">
                                    <x-detail-row label="Client" :mono="false">
                                        {{ $sellCustomer['fullName'] ?? '—' }}
                                    </x-detail-row>
                                    <x-detail-row label="ID Transaction" :mono="true">
                                        {{ $sellResult['transactionId'] ?? '—' }}
                                    </x-detail-row>
                                    <x-detail-row label="ID Carte" :mono="true">
                                        {{ $sellResult['cardId'] ?? '—' }}
                                    </x-detail-row>
                                    <x-detail-row label="Prix" :mono="true">
                                        {{ number_format((int) ($sellResult['cardPrice'] ?? 0), 0, ',', ' ') }} KMF
                                    </x-detail-row>
                                    <x-detail-row label="Commission" :mono="true" :border="false">
                                        <span class="text-app-green">+ {{ number_format((int) ($sellResult['commissionAmount'] ?? 0), 0, ',', ' ') }} KMF</span>
                                    </x-detail-row>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button wire:click="resetSell" type="button"
                                        class="cursor-pointer rounded-[10px] border-0 bg-app-purple px-4 py-2.5 text-[13px] font-bold text-white">
                                        Nouvelle vente
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                    {{-- Common identifiers --}}
                    <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                        <div class="mb-4">
                            <div class="mb-1 text-[15px] font-bold text-app-text">Identifiants de la carte</div>
                            <div class="text-xs text-app-muted">
                                Saisissez l'identifiant du client et de la carte concernée.
                            </div>
                        </div>

                        <div class="grid gap-4">
                            <div>
                                <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Identifiant client (UUID)
                                </label>
                                <input type="text" wire:model="customerId"
                                    placeholder="00000000-0000-0000-0000-000000000000"
                                    class="box-border w-full rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-[11px] tracking-[0.03em] text-app-text outline-none focus:border-app-accent" />
                                @error('customerId') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Identifiant carte (UUID)
                                </label>
                                <input type="text" wire:model="cardId"
                                    placeholder="00000000-0000-0000-0000-000000000000"
                                    class="box-border w-full rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-[11px] tracking-[0.03em] text-app-text outline-none focus:border-app-accent" />
                                @error('cardId') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- REPORT LOST --}}
                    @if ($activeTab === 'report-lost')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-4 flex items-start gap-2 rounded-lg border border-app-amber bg-app-amber-bg px-3.5 py-2.5 text-app-amber">
                                <x-agent-icon name="warning" :size="14" class="mt-px shrink-0" />
                                <span class="text-xs font-medium">
                                    Cette action est idempotente si la carte est déjà marquée comme perdue.
                                </span>
                            </div>

                            <div class="flex justify-end">
                                <button wire:click="reportLost" wire:loading.attr="disabled" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-amber p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-52">
                                    <span wire:loading.remove wire:target="reportLost">Déclarer perdue</span>
                                    <span wire:loading.flex wire:target="reportLost" class="hidden items-center gap-2">
                                        <x-spinner :size="16" /> Traitement…
                                    </span>
                                </button>
                            </div>
                        </div>

                    {{-- REPORT STOLEN --}}
                    @elseif ($activeTab === 'report-stolen')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-4 flex items-start gap-2 rounded-lg border border-app-red bg-app-red-bg px-3.5 py-2.5 text-app-red">
                                <x-agent-icon name="warning" :size="14" class="mt-px shrink-0" />
                                <span class="text-xs font-medium">
                                    Cette action est idempotente si la carte est déjà marquée comme volée.
                                </span>
                            </div>

                            <div class="flex justify-end">
                                <button wire:click="reportStolen" wire:loading.attr="disabled" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-red p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-52">
                                    <span wire:loading.remove wire:target="reportStolen">Déclarer volée</span>
                                    <span wire:loading.flex wire:target="reportStolen" class="hidden items-center gap-2">
                                        <x-spinner :size="16" /> Traitement…
                                    </span>
                                </button>
                            </div>
                        </div>

                    {{-- REPLACE --}}
                    @else
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-4">
                                <div class="mb-1 text-[15px] font-bold text-app-text">Carte de remplacement</div>
                                <div class="text-xs text-app-muted">
                                    Sélectionnez une carte assignée à votre stock et saisissez les frais.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Carte de stock à utiliser
                                </label>

                                @if (empty($cardStock))
                                    <div class="rounded-lg border border-app-border bg-app-bg px-3.5 py-2.5 text-xs text-app-muted">
                                        Aucune carte assignée à votre stock.
                                    </div>
                                @else
                                    <div class="grid gap-2">
                                        @foreach ($cardStock as $card)
                                            <button wire:click="$set('stockId', '{{ $card['id'] }}')" type="button" @class([
                                                'flex w-full cursor-pointer items-center justify-between rounded-lg border-[1.5px] bg-app-surface px-3.5 py-2.5 text-left',
                                                'border-app-accent' => $stockId === $card['id'],
                                                'border-app-border' => $stockId !== $card['id'],
                                            ])>
                                                <div>
                                                    <div class="font-mono text-[12px] font-bold text-app-text">
                                                        {{ $card['internalCardNumber'] ?? '—' }}
                                                    </div>
                                                    <div class="mt-0.5 font-mono text-[10px] text-app-muted">
                                                        NFC {{ $card['nfcUid'] ?? '—' }}
                                                    </div>
                                                </div>
                                                <x-agent-badge :status="$card['status'] ?? 'ASSIGNED_TO_AGENT'" />
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                                @error('stockId') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>

                            <div class="mb-5">
                                <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Frais de remplacement (KMF)
                                </label>
                                <div class="relative">
                                    <input type="number" wire:model="replacementFee" min="1" placeholder="0"
                                        class="box-border w-full rounded-[10px] border-2 border-app-border bg-app-surface py-3 pl-4 pr-14 font-mono text-xl font-bold tracking-[-0.02em] text-app-text outline-none focus:border-app-accent" />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-app-muted">KMF</span>
                                </div>
                                @error('replacementFee') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end">
                                <button wire:click="replaceCard" wire:loading.attr="disabled" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-52">
                                    <span wire:loading.remove wire:target="replaceCard">Remplacer la carte</span>
                                    <span wire:loading.flex wire:target="replaceCard" class="hidden items-center gap-2">
                                        <x-spinner :size="16" /> Traitement…
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Result --}}
                    @if ($lastResult !== null)
                        <div class="rounded-xl border border-app-green bg-app-green-bg p-4 md:p-5">
                            <div class="mb-3 flex items-center gap-2 text-[13px] font-bold text-app-green">
                                <x-agent-icon name="check" :size="16" />
                                Opération effectuée
                            </div>

                            <div class="rounded-xl border border-app-border bg-app-surface px-4 py-1">
                                @if ($activeTab === 'replace')
                                    <x-detail-row label="Ancienne carte" :mono="true">{{ $lastResult['oldCardId'] ?? '—' }}</x-detail-row>
                                    <x-detail-row label="Nouvelle carte" :mono="true">{{ $lastResult['newCardId'] ?? '—' }}</x-detail-row>
                                    @if (! empty($lastResult['transactionId']))
                                        <x-detail-row label="Transaction" :mono="true">{{ $lastResult['transactionId'] }}</x-detail-row>
                                    @endif
                                    @if (isset($lastResult['replacementFee']))
                                        <x-detail-row label="Frais" :mono="true">
                                            {{ number_format((int) $lastResult['replacementFee'], 0, ',', ' ') }} KMF
                                        </x-detail-row>
                                    @endif
                                    <x-detail-row label="Statut" :border="false">
                                        <x-agent-badge :status="$lastResult['status'] ?? 'COMPLETED'" />
                                    </x-detail-row>
                                @else
                                    <x-detail-row label="ID Carte" :mono="true">{{ $lastResult['id'] ?? $cardId }}</x-detail-row>
                                    <x-detail-row label="N° interne" :mono="true">{{ $lastResult['internalCardNumber'] ?? '—' }}</x-detail-row>
                                    <x-detail-row label="Statut carte" :border="false">
                                        <x-agent-badge :status="$lastResult['status'] ?? 'LOST'" />
                                    </x-detail-row>
                                @endif
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button wire:click="resetForm" type="button"
                                    class="cursor-pointer rounded-[10px] border-0 bg-app-accent px-4 py-2.5 text-[13px] font-bold text-white">
                                    Nouvelle opération
                                </button>
                            </div>
                        </div>
                    @endif
                    @endif {{-- /sell-vs-other tabs --}}
                </div>

                <aside class="hidden rounded-xl border border-app-border bg-app-surface p-4 lg:block">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-app-accent-bg text-app-accent">
                        <x-agent-icon name="enroll" :size="20" />
                    </div>
                    <div class="mb-1 text-sm font-bold text-app-text">Cycle de vie carte</div>
                    <p class="mb-4 mt-0 text-xs leading-relaxed text-app-muted">
                        Déclarez une carte perdue ou volée, ou remplacez-la avec une carte assignée à votre stock.
                    </p>
                    <div class="rounded-lg bg-app-bg p-3 text-xs leading-relaxed text-app-muted">
                        Vérifiez toujours l'identité du client avant toute action sur ses cartes.
                    </div>
                </aside>
            </div>
        @endif
    </div>
</div>
