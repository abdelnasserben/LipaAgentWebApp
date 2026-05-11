<div>
    {{-- Progress stepper --}}
    <div class="border-b border-app-border bg-app-surface">
        <div class="mx-auto max-w-5xl px-4 pt-5 md:px-6">
            <div class="mx-auto mb-4 flex max-w-xs items-center justify-center gap-0 md:max-w-md">
                @php
                    $stepLabels = ['Identité', 'Adresse', 'Documents', 'Résumé'];
                @endphp

                @foreach($stepLabels as $i => $label)
                    @php
                        $n = $i + 1;
                        $isDone = $step > $n;
                        $isCurrent = $step === $n;
                    @endphp

                    <div class="relative z-[1] flex flex-col items-center gap-1">
                        <div @class([
                            'flex h-7 w-7 items-center justify-center rounded-full text-xs font-extrabold md:h-8 md:w-8',
                            'bg-app-green text-white' => $isDone,
                            'bg-app-accent text-white' => $isCurrent,
                            'bg-app-border text-app-muted' => ! $isDone && ! $isCurrent,
                        ])>
                            @if($isDone)
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M3 7l3 3 5-5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                {{ $n }}
                            @endif
                        </div>

                        <div @class([
                            'whitespace-nowrap text-[10px] font-semibold md:text-[11px]',
                            'text-app-green' => $isDone,
                            'text-app-accent' => $isCurrent,
                            'text-app-muted' => ! $isDone && ! $isCurrent,
                        ])>
                            {{ $label }}
                        </div>
                    </div>

                    @if($n < 4)
                        <div @class([
                            'mx-1 mb-[18px] h-0.5 flex-1 md:mx-2',
                            'bg-app-green' => $step > $n,
                            'bg-app-border' => $step <= $n,
                        ])></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-5xl px-4 py-5 md:px-6 md:py-6">
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
            {{-- Main panel --}}
            <div class="min-w-0">
                {{-- STEP 1 – Identité --}}
                @if($step === 1)
                    <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                        <div class="mb-5">
                            <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                Informations personnelles
                            </div>
                            <div class="text-xs text-app-muted">
                                Renseignez l'identité du client
                            </div>
                        </div>

                        <div class="grid gap-3.5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Nom complet <span class="text-app-red">*</span>
                                </label>
                                <input type="text" wire:model="fullName" placeholder="Prénom Nom"
                                    class="box-border w-full rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 text-sm text-app-text outline-none focus:border-app-accent" />
                                @error('fullName') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Date de naissance <span class="text-app-red">*</span>
                                </label>
                                <input type="date" wire:model="dateOfBirth"
                                    class="box-border w-full rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 text-sm text-app-text outline-none focus:border-app-accent" />
                                @error('dateOfBirth') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Numéro de téléphone <span class="text-app-red">*</span>
                                </label>
                                <div class="flex items-stretch gap-2">
                                    <div class="flex shrink-0 items-center justify-center rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 font-mono text-sm font-bold text-app-text">
                                        +{{ $phoneCountryCode }}
                                    </div>
                                    <input type="tel" wire:model="phoneNumber" placeholder="3XX XXXX" inputmode="numeric"
                                        class="box-border min-w-0 flex-1 rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base tracking-[0.05em] text-app-text outline-none focus:border-app-accent" />
                                </div>
                                @error('phoneNumber') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Type de pièce d'identité <span class="text-app-red">*</span>
                                </label>
                                <select wire:model="nationalIdType"
                                    class="w-full cursor-pointer appearance-none rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 text-sm text-app-text outline-none focus:border-app-accent">
                                    <option value="NATIONAL_ID">Carte Nationale d'Identité</option>
                                    <option value="PASSPORT">Passeport</option>
                                    <option value="OTHER">Autre</option>
                                </select>
                                @error('nationalIdType') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                    Numéro de pièce <span class="text-app-red">*</span>
                                </label>
                                <input type="text" wire:model="nationalIdNumber" placeholder="ex. KM-1234567"
                                    class="box-border w-full rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-sm tracking-[0.04em] text-app-text outline-none focus:border-app-accent" />
                                @error('nationalIdNumber') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button wire:click="nextStep" type="button"
                                class="w-full cursor-pointer rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white md:w-auto md:min-w-48">
                                Suivant →
                            </button>
                        </div>
                    </div>

                {{-- STEP 2 – Adresse --}}
                @elseif($step === 2)
                    <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                        <div class="mb-4">
                            <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">Adresse du client</div>
                            <div class="text-xs text-app-muted">Cette étape est optionnelle</div>
                        </div>

                        <div class="mb-5 flex items-center gap-2 rounded-lg border border-app-blue bg-app-blue-bg px-3.5 py-2.5">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="shrink-0 text-app-blue">
                                <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.3"/>
                                <path d="M7 6v4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                                <circle cx="7" cy="4.5" r=".7" fill="currentColor"/>
                            </svg>
                            <span class="text-xs font-medium text-app-blue">
                                Vous pouvez passer cette étape sans saisir d'adresse
                            </span>
                        </div>

                        <div class="grid gap-3.5 md:grid-cols-3">
                            @foreach([
                                ['model' => 'addressIsland', 'label' => 'Île', 'placeholder' => 'Grande Comore, Anjouan, Mohéli…'],
                                ['model' => 'addressCity', 'label' => 'Ville', 'placeholder' => 'Moroni, Mutsamudu…'],
                                ['model' => 'addressDistrict', 'label' => 'Quartier', 'placeholder' => 'Nom du quartier'],
                            ] as $field)
                                <div>
                                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        {{ $field['label'] }}
                                    </label>
                                    <input type="text" wire:model="{{ $field['model'] }}" placeholder="{{ $field['placeholder'] }}"
                                        class="box-border w-full rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 text-sm text-app-text outline-none focus:border-app-accent" />
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 grid grid-cols-[1fr_2fr] gap-2.5 md:flex md:justify-end">
                            <button wire:click="goToStep(1)" type="button"
                                class="flex cursor-pointer items-center justify-center gap-1.5 rounded-[10px] border-[1.5px] border-app-border bg-transparent p-3.5 text-sm font-semibold text-app-muted md:min-w-36">
                                <x-agent-icon name="back" :size="14" />
                                Retour
                            </button>
                            <button wire:click="nextStep" type="button"
                                class="cursor-pointer rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white md:min-w-48">
                                Suivant →
                            </button>
                        </div>
                    </div>

                {{-- STEP 3 – Documents KYC --}}
                @elseif($step === 3)
                    <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                        <div class="mb-5">
                            <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">Documents KYC</div>
                            <div class="text-xs text-app-muted">Ajoutez les pièces justificatives du client</div>
                        </div>

                        <div class="mb-4 flex gap-2">
                            <select wire:model="kycDocType"
                                class="min-w-0 flex-1 cursor-pointer appearance-none rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 text-[13px] text-app-text outline-none focus:border-app-accent">
                                <option value="NATIONAL_ID">Carte Nationale d'Identité</option>
                                <option value="PASSPORT">Passeport</option>
                                <option value="PROOF_OF_ADDRESS">Justificatif de domicile</option>
                                <option value="BUSINESS_LICENSE">Licence commerciale</option>
                                <option value="OTHER">Autre document</option>
                            </select>

                            <button wire:click="addKycDoc" type="button"
                                class="flex shrink-0 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg border-[1.5px] border-app-accent bg-app-accent-bg px-4 py-3 text-[13px] font-bold text-app-accent">
                                <x-agent-icon name="upload" :size="14" />
                                Ajouter
                            </button>
                        </div>

                        @if(count($kycDocuments) > 0)
                            <div class="mb-4 overflow-hidden rounded-[10px] border border-app-border bg-app-surface">
                                @foreach($kycDocuments as $i => $doc)
                                    @php
                                        $docLabels = [
                                            'NATIONAL_ID' => "Carte Nationale d'Identité",
                                            'PASSPORT' => 'Passeport',
                                            'PROOF_OF_ADDRESS' => 'Justificatif de domicile',
                                            'BUSINESS_LICENSE' => 'Licence commerciale',
                                            'OTHER' => 'Autre document',
                                        ];
                                    @endphp

                                    <div @class([
                                        'flex items-center gap-3 px-3.5 py-3',
                                        'border-t border-app-border' => $i > 0,
                                    ])>
                                        <div class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-lg bg-app-blue-bg">
                                            <x-agent-icon name="card" :size="16" class="text-app-blue" />
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-[13px] font-semibold text-app-text">
                                                {{ $docLabels[$doc['type']] ?? $doc['type'] }}
                                            </div>
                                            <div class="mt-0.5 truncate font-mono text-[11px] text-app-muted">
                                                {{ $doc['filename'] }}
                                            </div>
                                        </div>

                                        <x-agent-badge status="PENDING_REVIEW" />

                                        <button wire:click="removeKycDoc({{ $i }})" type="button"
                                            class="flex cursor-pointer rounded border-0 bg-transparent p-1 text-app-muted hover:text-app-red">
                                            <x-agent-icon name="close" :size="16" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mb-4 rounded-[10px] border border-dashed border-app-border bg-app-bg p-8 text-center">
                                <div class="text-xs text-app-muted">Aucun document ajouté</div>
                            </div>
                        @endif

                        <div class="mb-5 flex items-center gap-2 rounded-lg border border-app-amber bg-app-amber-bg px-3.5 py-2.5 text-app-amber">
                            <x-agent-icon name="warning" :size="14" />
                            <span class="text-xs font-medium">
                                Vous pouvez continuer sans ajouter de documents — ils peuvent être fournis ultérieurement
                            </span>
                        </div>

                        <div class="grid grid-cols-[1fr_2fr] gap-2.5 md:flex md:justify-end">
                            <button wire:click="goToStep(2)" type="button"
                                class="flex cursor-pointer items-center justify-center gap-1.5 rounded-[10px] border-[1.5px] border-app-border bg-transparent p-3.5 text-sm font-semibold text-app-muted md:min-w-36">
                                <x-agent-icon name="back" :size="14" />
                                Retour
                            </button>

                            <button wire:click="nextStep" wire:loading.attr="disabled" type="button"
                                class="flex cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:min-w-48">
                                <span wire:loading.remove wire:target="nextStep">Soumettre</span>
                                <span wire:loading.flex wire:target="nextStep" class="hidden items-center gap-2">
                                    <x-spinner :size="16" />
                                    Enrôlement…
                                </span>
                            </button>
                        </div>
                    </div>

                {{-- STEP 4 – Récapitulatif --}}
                @elseif($step === 4)
                    @if(! $enrollResult)
                        <div class="rounded-xl border border-app-border bg-app-surface">
                            <div class="flex flex-col items-center justify-center gap-4 px-5 py-[60px]">
                                <x-spinner :size="32" />
                                <div class="text-sm text-app-muted">Enrôlement en cours…</div>
                            </div>
                        </div>
                    @else
                        <div class="mx-auto grid max-w-5xl gap-5 lg:grid-cols-[minmax(320px,1fr)_360px]">
                            <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                                <div class="px-0 pb-7 pt-4 text-center">
                                    <div class="mx-auto mb-4 flex h-[72px] w-[72px] items-center justify-center rounded-full border-2 border-app-green bg-app-green-bg">
                                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none" class="text-app-green">
                                            <path d="M8 17l6 6 12-12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>

                                    <div class="mb-1 text-xl font-extrabold tracking-[-0.02em] text-app-text">
                                        Client enrôlé avec succès !
                                    </div>
                                    <div class="text-[13px] text-app-muted">{{ $fullName }}</div>
                                </div>

                                <div class="mb-5 rounded-xl border border-app-border bg-app-bg px-4 py-1">
                                    <x-detail-row label="ID Client" :mono="true">{{ $enrollResult['customerId'] }}</x-detail-row>
                                    <x-detail-row label="Réf. externe" :mono="true">{{ $enrollResult['externalRef'] }}</x-detail-row>
                                    <x-detail-row label="ID Wallet" :mono="true" :border="false">{{ $enrollResult['walletId'] }}</x-detail-row>
                                </div>

                                <button wire:click="finish" wire:navigate type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white md:ml-auto md:w-auto md:min-w-48">
                                    <x-agent-icon name="home" :size="16" />
                                    Terminer
                                </button>
                            </div>

                            @if($offerCardSale && count($cardStock) > 0)
                                <div class="rounded-xl border-[1.5px] border-app-purple bg-app-surface p-4">
                                    <div class="mb-3.5 flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[9px] bg-app-purple-bg">
                                            <x-agent-icon name="card" :size="18" class="text-app-purple" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-app-text">Vendre une carte NFC</div>
                                            <div class="text-[11px] text-app-muted">Sélectionnez une carte à attribuer au client</div>
                                        </div>
                                    </div>

                                    <div class="mb-3.5 flex flex-col gap-2">
                                        @foreach($cardStock as $card)
                                            @php $selected = $selectedNfcUid === $card['nfcUid']; @endphp

                                            <button wire:click="selectCard('{{ $card['nfcUid'] }}')" type="button"
                                                @class([
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
                                                        {{ $card['internalCardNumber'] }}
                                                    </div>
                                                    <div class="mt-0.5 truncate font-mono text-[11px] tracking-[0.05em] text-app-muted">
                                                        NFC: {{ $card['nfcUid'] }}
                                                    </div>
                                                </div>

                                                @if($selected)
                                                    <x-agent-icon name="check" :size="16" class="shrink-0 text-app-purple" />
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>

                                    @if($selectedNfcUid)
                                        <button type="button"
                                            class="w-full cursor-pointer rounded-[10px] border-0 bg-app-purple p-3 text-sm font-bold text-white">
                                            Confirmer la vente
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>

            {{-- Desktop helper panel --}}
            @if($step !== 4)
                <aside class="hidden rounded-xl border border-app-border bg-app-surface p-4 lg:block">
                    <div class="mb-3 text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                        Progression
                    </div>

                    <div class="space-y-3">
                        @foreach($stepLabels as $i => $label)
                            @php
                                $n = $i + 1;
                                $isDone = $step > $n;
                                $isCurrent = $step === $n;
                            @endphp

                            <div class="flex items-center gap-3">
                                <div @class([
                                    'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-extrabold',
                                    'bg-app-green text-white' => $isDone,
                                    'bg-app-accent text-white' => $isCurrent,
                                    'bg-app-border text-app-muted' => ! $isDone && ! $isCurrent,
                                ])>
                                    @if($isDone)
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                            <path d="M3 7l3 3 5-5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @else
                                        {{ $n }}
                                    @endif
                                </div>

                                <div>
                                    <div @class([
                                        'text-[13px] font-semibold',
                                        'text-app-green' => $isDone,
                                        'text-app-accent' => $isCurrent,
                                        'text-app-text' => ! $isDone && ! $isCurrent,
                                    ])>
                                        {{ $label }}
                                    </div>
                                    <div class="text-[11px] text-app-muted">
                                        @switch($n)
                                            @case(1)
                                                Données personnelles du client
                                                @break
                                            @case(2)
                                                Adresse optionnelle
                                                @break
                                            @case(3)
                                                Pièces KYC optionnelles
                                                @break
                                            @case(4)
                                                Résumé et confirmation
                                                @break
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 rounded-lg bg-app-bg p-3 text-xs leading-relaxed text-app-muted">
                        Les champs marqués d’un astérisque sont obligatoires. Les documents KYC peuvent être ajoutés maintenant ou ultérieurement.
                    </div>
                </aside>
            @endif
        </div>
    </div>
</div>