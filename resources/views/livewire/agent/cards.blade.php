<div>
    @if ($apiError)
        <x-alert variant="danger" class="mx-4 mt-4" text-class="text-[13px] font-normal leading-relaxed">
            {{ $apiError }}
        </x-alert>
    @endif

    {{-- Tab bar --}}
    <div class="border-b-2 border-app-border bg-app-surface">
        <div class="mx-auto flex max-w-5xl px-4 md:px-6">
            @php
                $tabs = [
                    ['key' => 'sell', 'label' => 'Vendre', 'color' => 'text-app-accent'],
                    ['key' => 'report', 'label' => 'Déclarer', 'color' => 'text-app-accent'],
                    ['key' => 'replace', 'label' => 'Remplacer', 'color' => 'text-app-accent'],
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
                        <span class="absolute -bottom-0.5 left-0 right-0 h-0.5 rounded-t-sm bg-app-accent"></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-5 md:px-6 md:py-6">
        @if (!$canSellCards)
            <x-alert variant="neutral" class="p-5" text-class="text-[13px] font-normal leading-relaxed">
                Les actions sur les cartes ne sont pas activées pour ce compte Agent.
            </x-alert>
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
                                    <x-alert variant="danger" class="mb-4"
                                        text-class="text-[13px] font-normal leading-relaxed">
                                        {{ $sellError }}
                                    </x-alert>
                                @endif

                                <div class="mb-5">
                                    <label
                                        class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Numéro de téléphone
                                    </label>
                                    <div class="flex w-full items-stretch gap-2">
                                        <div
                                            class="flex shrink-0 items-center justify-center rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 font-mono text-sm font-bold text-app-text">
                                            +{{ $sellPhoneCountryCode }}
                                        </div>
                                        <input type="tel" wire:model.blur="sellPhoneNumber" placeholder="3XX XXXX"
                                            inputmode="numeric" wire:keydown.enter="lookupSellCustomer"
                                            class="box-border min-w-0 flex-1 rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base tracking-[0.05em] text-app-text outline-none focus:border-app-accent" />
                                    </div>
                                    @error('sellPhoneNumber')
                                        <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button wire:click="lookupSellCustomer" wire:loading.attr="disabled" type="button"
                                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-48">
                                        <span wire:loading.remove wire:target="lookupSellCustomer"
                                            class="flex items-center gap-2">
                                            <x-agent-icon name="search" :size="16" />
                                            Rechercher
                                        </span>
                                        <span wire:loading.flex wire:target="lookupSellCustomer"
                                            class="hidden items-center gap-2">
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

                                <div
                                    class="mb-5 flex items-center gap-3 rounded-xl border-[1.5px] border-app-accent bg-app-accent-bg p-4">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-app-accent text-xl font-extrabold text-white">
                                        {{ mb_strtoupper(mb_substr($sellCustomer['fullName'] ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-base font-extrabold tracking-[-0.02em] text-app-text">
                                            {{ $sellCustomer['fullName'] ?? '—' }}
                                        </div>
                                        <div class="mt-0.5 font-mono text-[12px] text-app-muted">
                                            +{{ $sellCustomer['phoneCountryCode'] ?? '' }}
                                            {{ $sellCustomer['phoneNumber'] ?? '' }}
                                        </div>
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            <x-agent-badge :status="$sellCustomer['status'] ?? 'ACTIVE'" />
                                            <x-agent-badge :status="$sellCustomer['kycLevel'] ?? 'KYC_BASIC'" />
                                        </div>
                                    </div>
                                </div>

                                @if ($sellError)
                                    <x-alert variant="danger" class="mb-4"
                                        text-class="text-[13px] font-normal leading-relaxed">
                                        {{ $sellError }}
                                    </x-alert>
                                @endif

                                <div class="mb-4">
                                    <label
                                        class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Carte de stock à vendre
                                    </label>

                                    @if (empty($cardStock))
                                        <x-alert variant="neutral" :icon="false" class="mb-0"
                                            text-class="text-xs font-normal leading-relaxed">
                                            Aucune carte assignée à votre stock.
                                        </x-alert>
                                    @else
                                        <div class="flex flex-col gap-2">
                                            @foreach ($cardStock as $card)
                                                @php $selected = $sellNfcUid === ($card['nfcUid'] ?? null); @endphp
                                                <button wire:click="selectSellCard('{{ $card['nfcUid'] ?? '' }}')"
                                                    type="button" @class([
                                                        'flex w-full cursor-pointer items-center gap-3 rounded-[10px] px-3.5 py-3 text-left',
                                                        'border-2 border-app-accent bg-app-accent-bg' => $selected,
                                                        'border-[1.5px] border-app-border bg-app-bg' => !$selected,
                                                    ])>
                                                    <div @class([
                                                        'h-2 w-2 shrink-0 rounded-full border-2',
                                                        'border-app-accent bg-app-accent' => $selected,
                                                        'border-app-muted bg-transparent' => !$selected,
                                                    ])></div>

                                                    <div class="min-w-0 flex-1">
                                                        <div class="truncate font-mono text-xs font-bold text-app-text">
                                                            {{ $card['internalCardNumber'] ?? '—' }}
                                                        </div>
                                                        <div
                                                            class="mt-0.5 truncate font-mono text-[11px] tracking-[0.05em] text-app-muted">
                                                            NFC: {{ $card['nfcUid'] ?? '—' }}
                                                        </div>
                                                    </div>

                                                    @if ($selected)
                                                        <x-agent-icon name="check" :size="16"
                                                            class="shrink-0 text-app-accent" />
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                    @error('sellNfcUid')
                                        <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-5">
                                    <label
                                        class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Prix de la carte (KMF)
                                    </label>
                                    <div class="relative">
                                        <input type="number" wire:model="sellCardPrice" min="1" placeholder="0"
                                            class="box-border w-full rounded-[10px] border-2 border-app-border bg-app-surface py-3 pl-4 pr-14 font-mono text-xl font-bold tracking-[-0.02em] text-app-text outline-none focus:border-app-accent" />
                                        <span
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-app-muted">KMF</span>
                                    </div>
                                    @error('sellCardPrice')
                                        <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button wire:click="submitSell" wire:loading.attr="disabled" type="button"
                                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-52">
                                        <span wire:loading.remove wire:target="submitSell">Vendre la carte</span>
                                        <span wire:loading.flex wire:target="submitSell"
                                            class="hidden items-center gap-2">
                                            <x-spinner :size="16" /> Vente…
                                        </span>
                                    </button>
                                </div>
                            </div>

                            {{-- step: success --}}
                        @else
                            <x-alert variant="success" class="p-4 md:p-5"
                                text-class="text-[13px] font-normal leading-relaxed">
                                <div class="mb-3 font-bold text-app-green">
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
                                        <span class="text-app-green">+
                                            {{ number_format((int) ($sellResult['commissionAmount'] ?? 0), 0, ',', ' ') }}
                                            KMF</span>
                                    </x-detail-row>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button wire:click="resetSell" type="button"
                                        class="cursor-pointer rounded-[10px] border-0 bg-app-accent px-4 py-2.5 text-[13px] font-bold text-white">
                                        Nouvelle vente
                                    </button>
                                </div>
                            </x-alert>
                        @endif
                    @else
                        {{-- Card lookup (shared by report + replace) --}}
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-4">
                                <div class="mb-1 text-[15px] font-bold text-app-text">Rechercher la carte</div>
                                <div class="text-xs text-app-muted">
                                    Saisissez le NFC UID de la carte.
                                </div>
                            </div>

                            @if ($cardLookupError)
                                <x-alert variant="danger" class="mb-4"
                                    text-class="text-[13px] font-normal leading-relaxed">
                                    {{ $cardLookupError }}
                                </x-alert>
                            @endif

                            @if (!$lookedUpCard)
                                <div class="mb-3">
                                    <label
                                        class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        NFC UID
                                    </label>
                                    <input type="text" wire:model.blur="cardNfcUid" placeholder="04AABB01CCDDE0FF"
                                        maxlength="14" wire:keydown.enter="lookupCard"
                                        class="box-border w-full rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-[13px] uppercase tracking-[0.05em] text-app-text outline-none focus:border-app-accent" />
                                    @error('cardNfcUid')
                                        <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button wire:click="lookupCard" wire:loading.attr="disabled" type="button"
                                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-48">
                                        <span wire:loading.remove wire:target="lookupCard"
                                            class="flex items-center gap-2">
                                            <x-agent-icon name="search" :size="16" />
                                            Rechercher
                                        </span>
                                        <span wire:loading.flex wire:target="lookupCard"
                                            class="hidden items-center gap-2">
                                            <x-spinner :size="16" />
                                            Recherche…
                                        </span>
                                    </button>
                                </div>
                            @else
                                @php
                                    $lookupCardStatus = $lookedUpCard['cardStatus'] ?? ($lookedUpCard['status'] ?? 'ACTIVE');
                                    $lookupCardNumber =
                                        $lookedUpCard['maskedInternalCardNumber'] ??
                                        (!empty($lookedUpCard['internalCardLast4'])
                                            ? '**** ' . $lookedUpCard['internalCardLast4']
                                            : '—');
                                @endphp
                                <div
                                    class="flex items-start gap-3 rounded-xl border-[1.5px] border-app-accent bg-app-accent-bg p-4">
                                    <div
                                        class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-[10px] bg-app-accent text-white">
                                        <x-agent-icon name="card" :size="20" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="truncate text-[14px] font-extrabold text-app-text">
                                            {{ $lookedUpCard['customerFullName'] ?? 'Client rattaché' }}
                                        </div>
                                        @if (!empty($lookedUpCard['customerPhoneMasked']))
                                            <div class="mt-0.5 font-mono text-[12px] text-app-muted">
                                                {{ $lookedUpCard['customerPhoneMasked'] }}
                                            </div>
                                        @endif

                                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                            <div>
                                                <div
                                                    class="text-[10px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                                    Carte
                                                </div>
                                                <div class="mt-0.5 font-mono text-[13px] font-bold text-app-text">
                                                    {{ $lookupCardNumber }}
                                                </div>
                                                @if (!empty($lookedUpCard['expiresAt']))
                                                    <div class="mt-0.5 text-[11px] text-app-muted">
                                                        Expire le {{ $lookedUpCard['expiresAt'] }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div
                                                    class="text-[10px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                                    Type et statut
                                                </div>
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    <x-agent-badge :status="$lookedUpCard['cardType'] ?? 'STANDARD'" />
                                                    <x-agent-badge :status="$lookupCardStatus" />
                                                </div>
                                            </div>
                                        </div>

                                        @if (empty($lookedUpCard['customerFullName']) || empty($lookedUpCard['customerPhoneMasked']))
                                            <div class="mt-2 text-[11px] font-semibold text-app-muted">
                                                Vérifiez l'identité du client avant de continuer.
                                            </div>
                                        @endif
                                    </div>

                                    <button wire:click="clearCardLookup" type="button"
                                        class="shrink-0 cursor-pointer border-0 bg-transparent p-1 text-[11px] font-semibold text-app-muted">
                                        Changer
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- REPORT (perdue OU volée) --}}
                        @if ($activeTab === 'report')
                            @php
                                $isStolen = $reportStatus === 'stolen';
                                $reportLabel = $isStolen ? 'volée' : 'perdue';
                                $reportButton = $isStolen ? 'Déclarer volée' : 'Déclarer perdue';
                            @endphp

                            <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                                {{-- Status selector --}}
                                <div class="mb-4">
                                    <label
                                        class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Statut à appliquer
                                    </label>

                                    <div class="flex flex-wrap gap-2">
                                        <button wire:click="setReportStatus('lost')" type="button"
                                            @class([
                                                'inline-flex cursor-pointer items-center rounded-full border px-3 py-1.5 text-[12px] font-bold transition-colors',
                                                'border-app-amber bg-app-amber-bg text-app-amber' => !$isStolen,
                                                'border-app-border bg-app-surface text-app-muted hover:bg-app-row-hover' => $isStolen,
                                            ])>
                                            Perdue
                                        </button>

                                        <button wire:click="setReportStatus('stolen')" type="button"
                                            @class([
                                                'inline-flex cursor-pointer items-center rounded-full border px-3 py-1.5 text-[12px] font-bold transition-colors',
                                                'border-app-red bg-app-red-bg text-app-red' => $isStolen,
                                                'border-app-border bg-app-surface text-app-muted hover:bg-app-row-hover' => !$isStolen,
                                            ])>
                                            Volée
                                        </button>
                                    </div>
                                </div>

                                {{-- Single idempotency info --}}
                                <x-alert variant="neutral" class="mb-4"
                                    text-class="text-xs font-normal leading-relaxed">
                                    Cette opération est idempotente si la carte est déjà déclarée perdue ou volée.
                                </x-alert>

                                <div class="flex justify-end">
                                    <button wire:click="reportCard" wire:loading.attr="disabled" type="button"
                                        @disabled(!$lookedUpCard) @class([
                                            'flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-52',
                                            'bg-app-red' => $isStolen,
                                            'bg-app-amber' => !$isStolen,
                                        ])>
                                        <span wire:loading.remove wire:target="reportCard">{{ $reportButton }}</span>
                                        <span wire:loading.flex wire:target="reportCard"
                                            class="hidden items-center gap-2">
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
                                    <label
                                        class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Carte de stock à utiliser
                                    </label>

                                    @if (empty($cardStock))
                                        <x-alert variant="neutral" :icon="false" class="mb-0"
                                            text-class="text-xs font-normal leading-relaxed">
                                            Aucune carte assignée à votre stock.
                                        </x-alert>
                                    @else
                                        <div class="grid gap-2">
                                            @foreach ($cardStock as $card)
                                                <button wire:click="$set('stockId', '{{ $card['id'] }}')"
                                                    type="button" @class([
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
                                    @error('stockId')
                                        <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-5">
                                    <label
                                        class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Frais de remplacement (KMF)
                                    </label>
                                    <div class="relative">
                                        <input type="number" wire:model="replacementFee" min="1"
                                            placeholder="0"
                                            class="box-border w-full rounded-[10px] border-2 border-app-border bg-app-surface py-3 pl-4 pr-14 font-mono text-xl font-bold tracking-[-0.02em] text-app-text outline-none focus:border-app-accent" />
                                        <span
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-app-muted">KMF</span>
                                    </div>
                                    @error('replacementFee')
                                        <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button wire:click="replaceCard" wire:loading.attr="disabled" type="button"
                                        @disabled(!$lookedUpCard)
                                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-52">
                                        <span wire:loading.remove wire:target="replaceCard">Remplacer la carte</span>
                                        <span wire:loading.flex wire:target="replaceCard"
                                            class="hidden items-center gap-2">
                                            <x-spinner :size="16" /> Traitement…
                                        </span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Result --}}
                        @if ($lastResult !== null)
                            @php
                                $confirmedCard = $activeTab === 'replace' ? ($lastConfirmedCard ?? []) : $lastResult;
                                $confirmedCardStatus =
                                    $confirmedCard['cardStatus'] ??
                                    ($confirmedCard['status'] ?? ($reportStatus === 'stolen' ? 'STOLEN' : 'LOST'));
                                $confirmedCardNumber =
                                    $confirmedCard['maskedInternalCardNumber'] ??
                                    (!empty($confirmedCard['internalCardLast4'])
                                        ? '**** ' . $confirmedCard['internalCardLast4']
                                        : ($confirmedCard['internalCardNumber'] ?? '—'));
                                $confirmedCustomerName = $confirmedCard['customerFullName'] ?? '—';
                                $confirmedCustomerPhone = $confirmedCard['customerPhoneMasked'] ?? '—';
                            @endphp
                            <x-alert variant="success" class="p-4 md:p-5"
                                text-class="text-[13px] font-normal leading-relaxed">
                                <div class="mb-3 font-bold text-app-green">
                                    Opération effectuée
                                </div>

                                <div class="rounded-xl border border-app-border bg-app-surface px-4 py-1">
                                    @if ($activeTab === 'replace')
                                        <x-detail-row label="Client">
                                            {{ $confirmedCustomerName }}
                                        </x-detail-row>
                                        <x-detail-row label="Téléphone" :mono="true">
                                            {{ $confirmedCustomerPhone }}
                                        </x-detail-row>
                                        <x-detail-row label="Carte remplacée" :mono="true">
                                            {{ $confirmedCardNumber }}
                                        </x-detail-row>
                                        <x-detail-row label="Type ancienne carte">
                                            <x-agent-badge :status="$confirmedCard['cardType'] ?? 'STANDARD'" />
                                        </x-detail-row>
                                        <x-detail-row label="Nouvelle carte"
                                            :mono="true">{{ $lastResult['newCardId'] ?? '—' }}</x-detail-row>
                                        @if (!empty($lastResult['transactionId']))
                                            <x-detail-row label="Transaction"
                                                :mono="true">{{ $lastResult['transactionId'] }}</x-detail-row>
                                        @endif
                                        @if (isset($lastResult['replacementFee']))
                                            <x-detail-row label="Frais" :mono="true">
                                                {{ number_format((int) $lastResult['replacementFee'], 0, ',', ' ') }}
                                                KMF
                                            </x-detail-row>
                                        @endif
                                        <x-detail-row label="Statut opération" :border="false">
                                            <x-agent-badge :status="$lastResult['status'] ?? 'COMPLETED'" />
                                        </x-detail-row>
                                    @else
                                        <x-detail-row label="Client">
                                            {{ $confirmedCustomerName }}
                                        </x-detail-row>
                                        <x-detail-row label="Téléphone" :mono="true">
                                            {{ $confirmedCustomerPhone }}
                                        </x-detail-row>
                                        <x-detail-row label="Carte" :mono="true">
                                            {{ $confirmedCardNumber }}
                                        </x-detail-row>
                                        <x-detail-row label="Type">
                                            <x-agent-badge :status="$confirmedCard['cardType'] ?? 'STANDARD'" />
                                        </x-detail-row>
                                        <x-detail-row label="Statut carte" :border="false">
                                            <x-agent-badge :status="$confirmedCardStatus" />
                                        </x-detail-row>
                                    @endif
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button wire:click="resetForm" type="button"
                                        class="cursor-pointer rounded-[10px] border-0 bg-app-accent px-4 py-2.5 text-[13px] font-bold text-white">
                                        Nouvelle opération
                                    </button>
                                </div>
                            </x-alert>
                        @endif
                    @endif {{-- /sell-vs-other tabs --}}
                </div>

                <aside class="hidden rounded-xl border border-app-border bg-app-surface p-4 lg:block">
                    <div
                        class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-app-accent-bg text-app-accent">
                        <x-agent-icon name="card" :size="20" />
                    </div>
                    <div class="mb-1 text-sm font-bold text-app-text">Cycle de vie carte</div>
                    <p class="mb-4 mt-0 text-xs leading-relaxed text-app-muted">
                        Déclarez une carte perdue ou volée, ou remplacez-la avec une carte assignée à votre stock.
                    </p>
                    <x-alert variant="neutral" :icon="false" class="border-0"
                        text-class="text-xs font-normal leading-relaxed">
                        Vérifiez toujours l'identité du client avant toute action sur ses cartes.
                    </x-alert>
                </aside>
            </div>
        @endif
    </div>
</div>
