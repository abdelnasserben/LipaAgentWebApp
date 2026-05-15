<div>
    {{-- Tab bar --}}
    <div class="border-b-2 border-app-border bg-app-surface">
        <div class="mx-auto flex max-w-5xl px-4 md:px-6">
            <button wire:click="switchTab('cash-in')" type="button" @class([
                'relative flex flex-1 cursor-pointer items-center justify-center gap-2 border-0 bg-transparent px-4 py-3.5 text-[13px] font-bold md:flex-none md:min-w-44',
                'text-app-green' => $activeTab === 'cash-in',
                'text-app-muted' => $activeTab !== 'cash-in',
            ])>
                <x-agent-icon name="cash-in" :size="16" />
                Cash-in

                @if ($activeTab === 'cash-in')
                    <span class="absolute -bottom-0.5 left-0 right-0 h-0.5 rounded-t-sm bg-app-green"></span>
                @endif
            </button>

            <button wire:click="switchTab('cash-out')" type="button" @class([
                'relative flex flex-1 cursor-pointer items-center justify-center gap-2 border-0 bg-transparent px-4 py-3.5 text-[13px] font-bold md:flex-none md:min-w-44',
                'text-app-amber' => $activeTab === 'cash-out',
                'text-app-muted' => $activeTab !== 'cash-out',
            ])>
                <x-agent-icon name="cash-out" :size="16" />
                Cash-out

                @if ($activeTab === 'cash-out')
                    <span class="absolute -bottom-0.5 left-0 right-0 h-0.5 rounded-t-sm bg-app-amber"></span>
                @endif
            </button>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-5 md:px-6 md:py-6">
        {{-- CASH-IN TAB --}}
        @if ($activeTab === 'cash-in')
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
                <div class="min-w-0">
                    {{-- lookup step --}}
                    @if ($ciStep === 'lookup')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-5">
                                <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                    Rechercher un client
                                </div>
                                <div class="text-xs text-app-muted">
                                    Entrez le numéro de téléphone du client
                                </div>
                            </div>

                            @if ($ciError)
                                <x-alert variant="danger" class="mb-4"
                                    text-class="text-[13px] font-normal leading-relaxed">
                                    {{ $ciError }}
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
                                        +{{ $ciPhoneCountryCode }}
                                    </div>

                                    <input type="tel" wire:model.blur="ciPhoneNumber" placeholder="3XX XXXX"
                                        inputmode="numeric" wire:keydown.enter="lookupCustomer"
                                        class="box-border min-w-0 flex-1 rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base tracking-[0.05em] text-app-text outline-none focus:border-app-green" />
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button wire:click="lookupCustomer" wire:loading.attr="disabled" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-green p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-48">
                                    <span wire:loading.remove wire:target="lookupCustomer"
                                        class="flex items-center gap-2">
                                        <x-agent-icon name="search" :size="16" />
                                        Rechercher
                                    </span>
                                    <span wire:loading.flex wire:target="lookupCustomer"
                                        class="hidden items-center gap-2">
                                        <x-spinner :size="16" />
                                        Recherche en cours…
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- confirm step --}}
                    @elseif($ciStep === 'confirm')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-5">
                                <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                    Confirmer le client
                                </div>
                                <div class="text-xs text-app-muted">
                                    Vérifiez les informations avant de continuer
                                </div>
                            </div>

                            <div
                                class="mb-5 flex items-start gap-3.5 rounded-xl border-[1.5px] border-app-border bg-app-bg p-5">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-app-accent-bg text-xl font-extrabold text-app-accent">
                                    {{ mb_strtoupper(mb_substr($ciCustomer['fullName'], 0, 1)) }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-lg font-extrabold tracking-[-0.02em] text-app-text">
                                        {{ $ciCustomer['fullName'] }}
                                    </div>
                                    <div class="mt-0.5 font-mono text-[13px] text-app-muted">
                                        +{{ $ciCustomer['phoneCountryCode'] }} {{ $ciCustomer['phoneNumber'] }}
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <x-agent-badge :status="$ciCustomer['status']" />
                                        <x-agent-badge :status="$ciCustomer['kycLevel']" />
                                    </div>

                                    <div class="mt-3.5 border-t border-app-border pt-3.5">
                                        <x-detail-row label="ID Client" :mono="true"
                                            :border="false">{{ $ciCustomer['customerId'] }}</x-detail-row>
                                    </div>
                                </div>

                                <button wire:click="backToLookup" type="button"
                                    class="shrink-0 cursor-pointer border-0 bg-transparent p-1 text-[11px] font-semibold text-app-muted hover:text-app-text">
                                    Changer
                                </button>
                            </div>

                            <div class="flex justify-end">
                                <button wire:click="confirmCustomer" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-green p-3.5 text-[15px] font-bold text-white md:w-auto md:min-w-52">
                                    <x-agent-icon name="check" :size="16" />
                                    Confirmer ce client
                                </button>
                            </div>
                        </div>

                        {{-- amount step --}}
                    @elseif($ciStep === 'amount')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div
                                class="mb-5 flex items-center gap-2.5 rounded-[10px] border border-app-green bg-app-green-bg px-3.5 py-3">
                                <div
                                    class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-full bg-app-green text-sm font-extrabold text-white">
                                    {{ mb_strtoupper(mb_substr($ciCustomer['fullName'], 0, 1)) }}
                                </div>

                                <div class="min-w-0">
                                    <div class="truncate text-[13px] font-bold text-app-text">
                                        {{ $ciCustomer['fullName'] }}
                                    </div>
                                    <div class="font-mono text-[11px] text-app-muted">
                                        +{{ $ciCustomer['phoneCountryCode'] }} {{ $ciCustomer['phoneNumber'] }}
                                    </div>
                                </div>

                                <div class="ml-auto flex items-center gap-2">
                                    <x-agent-badge :status="$ciCustomer['status']" />
                                    <button wire:click="backToLookup" type="button"
                                        class="shrink-0 cursor-pointer border-0 bg-transparent p-1 text-[11px] font-semibold text-app-muted hover:text-app-text">
                                        Changer
                                    </button>
                                </div>
                            </div>

                            @if ($ciError)
                                <x-alert variant="danger" class="mb-4"
                                    text-class="text-[13px] font-normal leading-relaxed">
                                    {{ $ciError }}
                                </x-alert>
                            @endif

                            <div>
                                <div class="mb-4">
                                    <label
                                        class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Montant en KMF
                                    </label>

                                    <div class="relative">
                                        <input type="number" wire:model.live="ciAmount" min="1" placeholder="0"
                                            class="box-border w-full rounded-[10px] border-2 border-app-border bg-app-surface py-4 pl-4 pr-14 font-mono text-[28px] font-bold tracking-[-0.02em] text-app-text outline-none focus:border-app-green" />
                                        <span
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-app-muted">
                                            KMF
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-5 grid grid-cols-4 gap-2">
                                    @foreach ([5000, 10000, 25000, 50000] as $quick)
                                        <button wire:click="setCashInAmount({{ $quick }})" type="button"
                                            @class([
                                                'cursor-pointer rounded-lg border-[1.5px] px-1 py-2 font-mono text-xs font-bold',
                                                'border-app-green bg-app-green text-white' => (int) $ciAmount === $quick,
                                                'border-app-border bg-app-surface text-app-muted' =>
                                                    (int) $ciAmount !== $quick,
                                            ])>
                                            {{ number_format($quick, 0, ',', ' ') }}
                                        </button>
                                    @endforeach
                                </div>

                                @if ((int) $ciAmount > 0)
                                    <x-alert variant="neutral" :icon="false" class="mb-0"
                                        text-class="text-[11px] font-normal leading-relaxed">
                                        Des frais et une commission peuvent s'appliquer. Les montants définitifs seront
                                        affichés après confirmation.
                                    </x-alert>
                                @endif
                            </div>

                            <div class="mt-5 flex justify-end">
                                <button wire:click="submitCashIn" wire:loading.attr="disabled" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-green p-4 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-60">
                                    <span wire:loading.remove wire:target="submitCashIn"
                                        class="flex items-center gap-2">
                                        <x-agent-icon name="cash-in" :size="16" />
                                        Effectuer le Cash-in
                                    </span>
                                    <span wire:loading.flex wire:target="submitCashIn"
                                        class="hidden items-center gap-2">
                                        <x-spinner :size="16" />
                                        Traitement…
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- success step --}}
                    @elseif($ciStep === 'success')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 text-center md:p-5">
                            <div
                                class="mx-auto mb-5 flex h-[72px] w-[72px] items-center justify-center rounded-full border-2 border-app-green bg-app-green-bg">
                                <svg width="34" height="34" viewBox="0 0 34 34" fill="none"
                                    class="text-app-green">
                                    <path d="M8 17l6 6 12-12" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div class="mb-1 text-[22px] font-extrabold tracking-[-0.02em] text-app-text">
                                Cash-in réussi !
                            </div>
                            <div class="mb-6 text-[13px] text-app-muted">
                                La transaction a été effectuée avec succès
                            </div>

                            <div
                                class="mb-5 inline-block w-full box-border rounded-xl border border-app-green bg-app-green-bg p-5">
                                <div class="mb-1.5 text-[11px] font-bold uppercase tracking-[0.1em] text-app-green">
                                    Montant crédité
                                </div>
                                <div class="font-mono text-4xl font-extrabold tracking-[-0.03em] text-app-text">
                                    {{ number_format($ciResult['netAmountToDestination'], 0, ',', ' ') }}
                                    <span class="text-base font-medium text-app-muted">KMF</span>
                                </div>
                            </div>

                            <div class="mb-6 rounded-xl border border-app-border bg-app-bg px-4 py-1 text-left">
                                <x-detail-row label="ID Transaction"
                                    :mono="true">{{ $ciResult['transactionId'] }}</x-detail-row>
                                <x-detail-row label="Montant demandé"
                                    :mono="true">{{ number_format($ciResult['requestedAmount'], 0, ',', ' ') }}
                                    KMF</x-detail-row>
                                <x-detail-row label="Frais"
                                    :mono="true">{{ number_format($ciResult['feeAmount'], 0, ',', ' ') }}
                                    KMF</x-detail-row>
                                <x-detail-row label="Commission" :mono="true">
                                    <span class="text-app-green">+
                                        {{ number_format($ciResult['commissionAmount'], 0, ',', ' ') }} KMF</span>
                                </x-detail-row>
                                <x-detail-row label="Statut" :border="false">
                                    <x-agent-badge status="COMPLETED" />
                                </x-detail-row>
                            </div>

                            <button wire:click="resetCashIn" type="button"
                                class="w-full cursor-pointer rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white md:w-auto md:min-w-52">
                                Nouvelle opération
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Desktop helper panel --}}
                <aside class="hidden rounded-xl border border-app-border bg-app-surface p-4 lg:block">
                    <div
                        class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-app-green-bg text-app-green">
                        <x-agent-icon name="cash-in" :size="20" />
                    </div>
                    <div class="mb-1 text-sm font-bold text-app-text">Cash-in client</div>
                    <p class="mb-4 mt-0 text-xs leading-relaxed text-app-muted">
                        Recherchez le client par numéro, confirmez son identité puis saisissez le montant à créditer.
                    </p>

                    <x-alert variant="neutral" :icon="false" class="border-0"
                        text-class="text-xs font-normal leading-relaxed">
                        Vérifiez toujours le nom, le statut et le niveau KYC avant de valider l’opération.
                    </x-alert>
                </aside>
            </div>

            {{-- CASH-OUT TAB --}}
        @elseif($activeTab === 'cash-out')
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
                <div class="min-w-0">
                    {{-- lookup step --}}
                    @if ($coStep === 'lookup')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-5">
                                <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                    Rechercher un marchand
                                </div>
                                <div class="text-xs text-app-muted">
                                    Entrez le numéro de téléphone du marchand
                                </div>
                            </div>

                            @if ($coError)
                                <x-alert variant="danger" class="mb-4"
                                    text-class="text-[13px] font-normal leading-relaxed">
                                    {{ $coError }}
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
                                        +{{ $coPhoneCountryCode }}
                                    </div>

                                    <input type="tel" wire:model.blur="coPhoneNumber" placeholder="3XX XXXX"
                                        inputmode="numeric" wire:keydown.enter="lookupMerchant"
                                        class="box-border min-w-0 flex-1 rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base tracking-[0.05em] text-app-text outline-none focus:border-app-amber" />
                                </div>
                                @error('coPhoneNumber')
                                    <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button wire:click="lookupMerchant" wire:loading.attr="disabled" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-amber p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-48">
                                    <span wire:loading.remove wire:target="lookupMerchant"
                                        class="flex items-center gap-2">
                                        <x-agent-icon name="search" :size="16" />
                                        Rechercher
                                    </span>
                                    <span wire:loading.flex wire:target="lookupMerchant"
                                        class="hidden items-center gap-2">
                                        <x-spinner :size="16" />
                                        Recherche en cours…
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- amount step --}}
                    @elseif($coStep === 'amount')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div
                                class="mb-5 flex items-start gap-3 rounded-xl border-[1.5px] border-app-amber bg-app-amber-bg p-4">
                                <div
                                    class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-[10px] bg-app-amber text-white">
                                    <x-agent-icon name="cash-out" :size="20" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-base font-extrabold tracking-[-0.02em] text-app-text">
                                        {{ $coMerchant['businessName'] ?? '—' }}
                                    </div>
                                    <div class="mt-0.5 font-mono text-[12px] text-app-muted">
                                        {{ $coMerchant['externalRef'] ?? '—' }} •
                                        +{{ $coMerchant['phoneCountryCode'] ?? '' }}
                                        {{ $coMerchant['phoneNumber'] ?? '' }}
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        <x-agent-badge :status="$coMerchant['status'] ?? 'ACTIVE'" />
                                        <x-agent-badge :status="$coMerchant['kycLevel'] ?? 'KYC_BASIC'" />
                                    </div>
                                </div>

                                <button wire:click="backToMerchantLookup" type="button"
                                    class="shrink-0 cursor-pointer border-0 bg-transparent p-1 text-[11px] font-semibold text-app-muted hover:text-app-text">
                                    Changer
                                </button>
                            </div>

                            @if ($coError)
                                <x-alert variant="danger" class="mb-4"
                                    text-class="text-[13px] font-normal leading-relaxed">
                                    {{ $coError }}
                                </x-alert>
                            @endif

                            <div>
                                <label
                                    class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Montant en KMF
                                </label>

                                <div class="relative">
                                    <input type="number" wire:model.live="coAmount" min="1" placeholder="0"
                                        class="box-border w-full rounded-[10px] border-2 border-app-border bg-app-surface py-3 pl-4 pr-14 font-mono text-xl font-bold tracking-[-0.02em] text-app-text outline-none focus:border-app-amber md:py-4 md:text-[28px]" />
                                    <span
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-[13px] font-semibold text-app-muted">
                                        KMF
                                    </span>
                                </div>

                                <div class="mt-3 grid grid-cols-4 gap-2">
                                    @foreach ([5000, 10000, 25000, 50000] as $quick)
                                        <button wire:click="setCashOutAmount({{ $quick }})" type="button"
                                            @class([
                                                'cursor-pointer rounded-lg border-[1.5px] px-1 py-2 font-mono text-xs font-bold',
                                                'border-app-amber bg-app-amber text-white' => (int) $coAmount === $quick,
                                                'border-app-border bg-app-surface text-app-muted' =>
                                                    (int) $coAmount !== $quick,
                                            ])>
                                            {{ number_format($quick, 0, ',', ' ') }}
                                        </button>
                                    @endforeach
                                </div>
                                @error('coAmount')
                                    <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-alert variant="warning" class="my-5"
                                text-class="text-xs font-medium leading-relaxed">
                                Une validation backoffice peut être requise
                            </x-alert>

                            <div class="flex justify-end">
                                <button wire:click="confirmCashOut" wire:loading.attr="disabled"
                                    wire:target="confirmCashOut" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-amber p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-48">
                                    <span wire:loading.remove wire:target="confirmCashOut"
                                        class="flex items-center gap-2">
                                        Continuer
                                    </span>
                                    <span wire:loading.flex wire:target="confirmCashOut"
                                        class="hidden items-center gap-2">
                                        <x-spinner :size="16" />
                                        Vérification…
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- confirm step --}}
                    @elseif($coStep === 'confirm')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <button wire:click="backToAmount" type="button"
                                class="mb-5 flex cursor-pointer items-center gap-1.5 border-0 bg-transparent p-0 text-[13px] font-semibold text-app-muted">
                                <x-agent-icon name="back" :size="16" />
                                Retour
                            </button>

                            <div class="mb-5">
                                <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                    Confirmer le Cash-out
                                </div>
                                <div class="text-xs text-app-muted">
                                    Vérifiez les détails avant de valider
                                </div>
                            </div>

                            @if ($coError)
                                <x-alert variant="danger" class="mb-4"
                                    text-class="text-[13px] font-normal leading-relaxed">
                                    {{ $coError }}
                                </x-alert>
                            @endif

                            <div
                                class="mb-5 flex items-start gap-3 rounded-xl border-[1.5px] border-app-amber bg-app-amber-bg p-4">
                                <div
                                    class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-[10px] bg-app-amber text-white">
                                    <x-agent-icon name="cash-out" :size="20" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="text-[10px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Marchand
                                    </div>
                                    <div class="mt-0.5 truncate text-[13px] font-bold text-app-text">
                                        {{ $coMerchant['businessName'] ?? '—' }}
                                    </div>
                                    <div class="font-mono text-[11px] text-app-muted">
                                        {{ $coMerchant['externalRef'] ?? '' }} •
                                        +{{ $coMerchant['phoneCountryCode'] ?? '' }}
                                        {{ $coMerchant['phoneNumber'] ?? '' }}
                                    </div>
                                </div>

                                <button wire:click="backToMerchantLookup" type="button"
                                    class="shrink-0 cursor-pointer border-0 bg-transparent p-1 text-[11px] font-semibold text-app-muted hover:text-app-text">
                                    Changer
                                </button>
                            </div>

                            <div class="mb-5 rounded-[10px] border border-app-border bg-app-bg p-3.5">
                                <div class="text-center">
                                    <div class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Montant
                                    </div>
                                    <div class="font-mono text-[32px] font-extrabold tracking-[-0.02em] text-app-text">
                                        {{ number_format((int) $coAmount, 0, ',', ' ') }}
                                        <span class="text-base font-medium text-app-muted">KMF</span>
                                    </div>
                                </div>

                                <div
                                    class="mt-3 border-t border-app-border pt-3 text-center text-[11px] text-app-muted">
                                    Des frais et une commission peuvent s'appliquer. Le détail définitif sera affiché
                                    après confirmation.
                                </div>
                            </div>

                            @if ((int) $coAmount > 100000)
                                <x-alert variant="warning" class="mb-4"
                                    text-class="text-xs font-medium leading-relaxed">
                                    Ce montant nécessitera une approbation backoffice
                                </x-alert>
                            @endif

                            <div class="grid gap-2.5 md:flex md:justify-end">
                                <button wire:click="resetCashOut" type="button"
                                    class="order-2 w-full cursor-pointer rounded-[10px] border-[1.5px] border-app-border bg-transparent p-3 text-sm font-semibold text-app-muted md:order-1 md:w-auto md:min-w-36">
                                    Annuler
                                </button>

                                <button wire:click="submitCashOut" wire:loading.attr="disabled" type="button"
                                    class="order-1 flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-amber p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:order-2 md:w-auto md:min-w-48">
                                    <span wire:loading.remove wire:target="submitCashOut"
                                        class="flex items-center gap-2">
                                        <x-agent-icon name="check" :size="16" />
                                        Confirmer
                                    </span>
                                    <span wire:loading.flex wire:target="submitCashOut"
                                        class="hidden items-center gap-2">
                                        <x-spinner :size="16" />
                                        Traitement…
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- pin step (PENDING_PIN) --}}
                    @elseif($coStep === 'pin')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-5">
                                <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                    PIN marchand requis
                                </div>
                                <div class="text-xs text-app-muted">
                                    Demandez au marchand de saisir son PIN sur cet appareil pour valider l'opération.
                                </div>
                            </div>

                            @if ($coPinError)
                                <x-alert variant="danger" class="mb-4"
                                    text-class="text-[13px] font-normal leading-relaxed">
                                    {{ $coPinError }}
                                </x-alert>
                            @endif

                            <div class="mb-5 rounded-[10px] border border-app-border bg-app-bg p-3.5 text-center">
                                <div class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Montant
                                </div>
                                <div class="font-mono text-2xl font-extrabold tracking-[-0.02em] text-app-text">
                                    {{ number_format((int) $coAmount, 0, ',', ' ') }}
                                    <span class="text-sm font-medium text-app-muted">KMF</span>
                                </div>
                                @if (! is_null($coMatchedThreshold))
                                    <div class="mt-2 text-[11px] text-app-muted">
                                        Seuil PIN dépassé :
                                        {{ number_format($coMatchedThreshold, 0, ',', ' ') }} KMF
                                    </div>
                                @endif
                            </div>

                            <div class="mb-2">
                                <label for="coMerchantPin"
                                    class="mb-1.5 block text-[12px] font-semibold text-app-muted">
                                    PIN marchand (4 à 8 chiffres)
                                </label>
                                <input id="coMerchantPin" wire:model.defer="coMerchantPin" type="password"
                                    inputmode="numeric" autocomplete="off" maxlength="8"
                                    autocorrect="off" autocapitalize="off" spellcheck="false"
                                    class="w-full rounded-[10px] border border-app-border bg-app-bg p-3 text-center font-mono text-xl tracking-[0.4em] text-app-text outline-none focus:border-app-accent" />
                                @error('coMerchantPin')
                                    <div class="mt-1 text-[12px] text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 text-[11px] text-app-muted">
                                Politique : 3 échecs verrouillent le PIN marchand pendant 15 minutes.
                                Le PIN n'est jamais enregistré côté Agent.
                            </div>

                            <div class="grid gap-2.5 md:flex md:justify-end">
                                <button wire:click="resetCashOut" type="button"
                                    class="order-2 w-full cursor-pointer rounded-[10px] border-[1.5px] border-app-border bg-transparent p-3 text-sm font-semibold text-app-muted md:order-1 md:w-auto md:min-w-36">
                                    Annuler
                                </button>

                                <button wire:click="submitMerchantPin" wire:loading.attr="disabled" type="button"
                                    class="order-1 flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-amber p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:order-2 md:w-auto md:min-w-48">
                                    <span wire:loading.remove wire:target="submitMerchantPin"
                                        class="flex items-center gap-2">
                                        <x-agent-icon name="check" :size="16" />
                                        Valider le PIN
                                    </span>
                                    <span wire:loading.flex wire:target="submitMerchantPin"
                                        class="hidden items-center gap-2">
                                        <x-spinner :size="16" />
                                        Vérification…
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- confirmation step (PENDING_CONFIRMATION) --}}
                    @elseif($coStep === 'confirmation')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                            <div class="mb-5">
                                <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                    Confirmation requise
                                </div>
                                <div class="text-xs text-app-muted">
                                    Le montant dépasse le seuil de confirmation. Vérifiez avant de poursuivre.
                                </div>
                            </div>

                            @if ($coError)
                                <x-alert variant="danger" class="mb-4"
                                    text-class="text-[13px] font-normal leading-relaxed">
                                    {{ $coError }}
                                </x-alert>
                            @endif

                            <div class="mb-5 rounded-[10px] border border-app-border bg-app-bg p-3.5 text-center">
                                <div class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Montant
                                </div>
                                <div class="font-mono text-2xl font-extrabold tracking-[-0.02em] text-app-text">
                                    {{ number_format((int) $coAmount, 0, ',', ' ') }}
                                    <span class="text-sm font-medium text-app-muted">KMF</span>
                                </div>
                                @if (! is_null($coMatchedThreshold))
                                    <div class="mt-2 text-[11px] text-app-muted">
                                        Seuil de confirmation :
                                        {{ number_format($coMatchedThreshold, 0, ',', ' ') }} KMF
                                    </div>
                                @endif
                            </div>

                            <div class="grid gap-2.5 md:flex md:justify-end">
                                <button wire:click="resetCashOut" type="button"
                                    class="order-2 w-full cursor-pointer rounded-[10px] border-[1.5px] border-app-border bg-transparent p-3 text-sm font-semibold text-app-muted md:order-1 md:w-auto md:min-w-36">
                                    Annuler
                                </button>

                                <button wire:click="acknowledgeConfirmation" wire:loading.attr="disabled"
                                    type="button"
                                    class="order-1 flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-amber p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:order-2 md:w-auto md:min-w-48">
                                    <span wire:loading.remove wire:target="acknowledgeConfirmation"
                                        class="flex items-center gap-2">
                                        <x-agent-icon name="check" :size="16" />
                                        Confirmer et poursuivre
                                    </span>
                                    <span wire:loading.flex wire:target="acknowledgeConfirmation"
                                        class="hidden items-center gap-2">
                                        <x-spinner :size="16" />
                                        Traitement…
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- success step --}}
                    @elseif($coStep === 'success')
                        <div class="rounded-xl border border-app-border bg-app-surface p-4 text-center md:p-5">
                            @if ($coStatus === 200)
                                <div
                                    class="mx-auto mb-5 flex h-[72px] w-[72px] items-center justify-center rounded-full border-2 border-app-green bg-app-green-bg">
                                    <svg width="34" height="34" viewBox="0 0 34 34" fill="none"
                                        class="text-app-green">
                                        <path d="M8 17l6 6 12-12" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="mb-1 text-[22px] font-extrabold tracking-[-0.02em] text-app-green">
                                    Cash-out effectué !
                                </div>
                                <div class="mb-6 text-[13px] text-app-muted">
                                    La transaction a été effectuée avec succès
                                </div>
                            @else
                                <div
                                    class="mx-auto mb-5 flex h-[72px] w-[72px] items-center justify-center rounded-full border-2 border-app-amber bg-app-amber-bg text-app-amber">
                                    <x-agent-icon name="warning" :size="30" />
                                </div>
                                <div class="mb-1 text-[22px] font-extrabold tracking-[-0.02em] text-app-amber">
                                    En attente d'approbation
                                </div>
                                <div class="mb-6 text-[13px] text-app-muted">
                                    Ce montant nécessite une validation backoffice
                                </div>
                            @endif

                            <div @class([
                                'mb-5 inline-block w-full box-border rounded-xl border p-5',
                                'border-app-green bg-app-green-bg' => $coStatus === 200,
                                'border-app-amber bg-app-amber-bg' => $coStatus !== 200,
                            ])>
                                <div @class([
                                    'mb-1.5 text-[11px] font-bold uppercase tracking-[0.1em]',
                                    'text-app-green' => $coStatus === 200,
                                    'text-app-amber' => $coStatus !== 200,
                                ])>
                                    Montant
                                </div>

                                <div class="font-mono text-4xl font-extrabold tracking-[-0.03em] text-app-text">
                                    {{ number_format($coResult['requestedAmount'], 0, ',', ' ') }}
                                    <span class="text-base font-medium text-app-muted">KMF</span>
                                </div>
                            </div>

                            <div class="mb-6 rounded-xl border border-app-border bg-app-bg px-4 py-1 text-left">
                                @if ($coStatus === 202)
                                    <x-detail-row label="Réf. approbation"
                                        :mono="true">{{ $coResult['approvalId'] ?? '—' }}</x-detail-row>
                                @else
                                    <x-detail-row label="ID Transaction"
                                        :mono="true">{{ $coResult['transactionId'] ?? '—' }}</x-detail-row>

                                    <x-detail-row label="Frais" :mono="true">
                                        {{ number_format($coResult['feeAmount'] ?? 0, 0, ',', ' ') }} KMF
                                    </x-detail-row>

                                    <x-detail-row label="Commission" :mono="true">
                                        <span class="text-app-green">+
                                            {{ number_format($coResult['commissionAmount'] ?? 0, 0, ',', ' ') }}
                                            KMF</span>
                                    </x-detail-row>
                                @endif

                                <x-detail-row label="Statut" :border="false">
                                    <x-agent-badge :status="$coResult['status']" />
                                </x-detail-row>
                            </div>

                            <button wire:click="resetCashOut" type="button"
                                class="w-full cursor-pointer rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white md:w-auto md:min-w-52">
                                Nouvelle opération
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Desktop helper panel --}}
                <aside class="hidden rounded-xl border border-app-border bg-app-surface p-4 lg:block">
                    <div
                        class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-app-amber-bg text-app-amber">
                        <x-agent-icon name="cash-out" :size="20" />
                    </div>
                    <div class="mb-1 text-sm font-bold text-app-text">Cash-out marchand</div>
                    <p class="mb-4 mt-0 text-xs leading-relaxed text-app-muted">
                        Saisissez la référence marchand, vérifiez le montant et confirmez l’opération.
                    </p>

                    <x-alert variant="neutral" class="border-0" :icon="false"
                        text-class="text-xs font-normal leading-relaxed">
                        Certains montants peuvent nécessiter une approbation backoffice.
                    </x-alert>
                </aside>
            </div>
        @endif
    </div>
</div>
