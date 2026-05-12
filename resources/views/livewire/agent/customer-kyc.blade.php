<div>
    <x-api-error-alert :message="$apiError" class="mx-4 mt-4" />

    <div class="mx-auto max-w-5xl px-4 py-5 md:px-6 md:py-6">
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
            <div class="min-w-0 space-y-4">

                {{-- STEP: lookup --}}
                @if ($step === 'lookup')
                    <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                        <div class="mb-5">
                            <div class="mb-1 text-[15px] font-bold text-app-text md:text-base">
                                Rechercher un client
                            </div>
                            <div class="text-xs text-app-muted">
                                Entrez le numéro de téléphone du client pour gérer ses documents KYC.
                            </div>
                        </div>

                        @if ($lookupError)
                            <div class="mb-4 flex items-center gap-2 rounded-lg border border-app-red bg-app-red-bg px-3.5 py-2.5 text-[13px] text-app-red">
                                <x-agent-icon name="warning" :size="14" />
                                {{ $lookupError }}
                            </div>
                        @endif

                        <div class="mb-5">
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                Numéro de téléphone
                            </label>
                            <div class="flex w-full items-stretch gap-2">
                                <div class="flex shrink-0 items-center justify-center rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 font-mono text-sm font-bold text-app-text">
                                    +{{ $phoneCountryCode }}
                                </div>
                                <input type="tel" wire:model="phoneNumber" placeholder="3XX XXXX"
                                    inputmode="numeric" wire:keydown.enter="lookupCustomer"
                                    class="box-border min-w-0 flex-1 rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base tracking-[0.05em] text-app-text outline-none focus:border-app-accent" />
                            </div>
                            @error('phoneNumber') <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end">
                            <button wire:click="lookupCustomer" wire:loading.attr="disabled" type="button"
                                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70 md:w-auto md:min-w-48">
                                <span wire:loading.remove wire:target="lookupCustomer" class="flex items-center gap-2">
                                    <x-agent-icon name="search" :size="16" /> Rechercher
                                </span>
                                <span wire:loading.flex wire:target="lookupCustomer" class="hidden items-center gap-2">
                                    <x-spinner :size="16" /> Recherche…
                                </span>
                            </button>
                        </div>
                    </div>

                {{-- STEP: manage --}}
                @else
                    <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                        <button wire:click="backToLookup" type="button"
                            class="mb-5 flex cursor-pointer items-center gap-1.5 border-0 bg-transparent p-0 text-[13px] font-semibold text-app-muted">
                            <x-agent-icon name="back" :size="16" />
                            Changer de client
                        </button>

                        <div class="mb-5 flex items-center gap-3 rounded-xl border-[1.5px] border-app-border bg-app-bg p-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-app-accent-bg text-xl font-extrabold text-app-accent">
                                {{ mb_strtoupper(mb_substr($customer['fullName'] ?? '?', 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-base font-extrabold tracking-[-0.02em] text-app-text">
                                    {{ $customer['fullName'] ?? '—' }}
                                </div>
                                <div class="mt-0.5 font-mono text-[12px] text-app-muted">
                                    +{{ $customer['phoneCountryCode'] ?? '' }} {{ $customer['phoneNumber'] ?? '' }}
                                </div>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <x-agent-badge :status="$customer['status'] ?? 'ACTIVE'" />
                                    <x-agent-badge :status="$customer['kycLevel'] ?? 'KYC_BASIC'" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Upload form --}}
                    <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                        <div class="mb-4">
                            <div class="mb-1 text-[15px] font-bold text-app-text">Ajouter un document KYC</div>
                            <div class="text-xs text-app-muted">
                                Téléversez une nouvelle pièce justificative pour ce client (max. 10 Mo).
                            </div>
                        </div>

                        @if ($uploadSuccess)
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-app-green bg-app-green-bg px-3 py-2 text-[12px] text-app-green">
                                <x-agent-icon name="check" :size="14" />
                                {{ $uploadSuccess }}
                            </div>
                        @endif

                        @if ($uploadError)
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-app-red bg-app-red-bg px-3 py-2 text-[12px] text-app-red">
                                <x-agent-icon name="warning" :size="14" />
                                {{ $uploadError }}
                            </div>
                        @endif

                        <div class="grid gap-2 md:grid-cols-[1fr_1fr_auto]">
                            <select wire:model="kycDocType"
                                class="min-w-0 cursor-pointer appearance-none rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 text-[13px] text-app-text outline-none focus:border-app-accent">
                                <option value="NATIONAL_ID">Carte Nationale d'Identité</option>
                                <option value="PASSPORT">Passeport</option>
                                <option value="PROOF_OF_ADDRESS">Justificatif de domicile</option>
                                <option value="BUSINESS_LICENSE">Licence commerciale</option>
                                <option value="OTHER">Autre document</option>
                            </select>

                            <input type="file" wire:model="kycFile" accept="image/*,application/pdf"
                                class="min-w-0 cursor-pointer rounded-lg border-[1.5px] border-app-border bg-app-surface px-3 py-2.5 text-[12px] text-app-text" />

                            <button wire:click="uploadDocument" wire:loading.attr="disabled" type="button"
                                class="flex shrink-0 cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg border-[1.5px] border-app-accent bg-app-accent-bg px-4 py-3 text-[13px] font-bold text-app-accent disabled:opacity-70">
                                <span wire:loading.remove wire:target="uploadDocument" class="flex items-center gap-1.5">
                                    <x-agent-icon name="upload" :size="14" />
                                    Téléverser
                                </span>
                                <span wire:loading wire:target="uploadDocument" class="flex items-center gap-1.5">
                                    <x-spinner :size="14" /> Envoi…
                                </span>
                            </button>
                        </div>
                        @error('kycFile') <p class="mt-2 text-[11px] text-app-red">{{ $message }}</p> @enderror
                    </div>

                    {{-- Document list --}}
                    <div class="rounded-xl border border-app-border bg-app-surface p-4 md:p-5">
                        <div class="mb-4">
                            <div class="mb-1 text-[15px] font-bold text-app-text">Documents KYC du client</div>
                            <div class="text-xs text-app-muted">
                                Liste des pièces déjà enregistrées pour ce client.
                            </div>
                        </div>

                        @if (empty($documents))
                            <div class="rounded-[10px] border border-dashed border-app-border bg-app-bg p-8 text-center">
                                <div class="text-xs text-app-muted">Aucun document KYC enregistré.</div>
                            </div>
                        @else
                            @php
                                $docLabels = [
                                    'NATIONAL_ID' => "Carte Nationale d'Identité",
                                    'PASSPORT' => 'Passeport',
                                    'PROOF_OF_ADDRESS' => 'Justificatif de domicile',
                                    'BUSINESS_LICENSE' => 'Licence commerciale',
                                    'OTHER' => 'Autre document',
                                ];
                            @endphp
                            <div class="overflow-hidden rounded-[10px] border border-app-border bg-app-bg">
                                @foreach ($documents as $i => $doc)
                                    <div @class([
                                        'flex items-center gap-3 px-3.5 py-3',
                                        'border-t border-app-border' => $i > 0,
                                    ])>
                                        <div class="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-lg bg-app-blue-bg">
                                            <x-agent-icon name="card" :size="16" class="text-app-blue" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-[13px] font-semibold text-app-text">
                                                {{ $docLabels[$doc['documentType'] ?? 'OTHER'] ?? ($doc['documentType'] ?? '—') }}
                                            </div>
                                            <div class="mt-0.5 truncate font-mono text-[11px] text-app-muted">
                                                {{ $doc['contentHash'] ?? '—' }}
                                            </div>
                                            <div class="mt-0.5 text-[11px] text-app-muted">
                                                Téléversé le
                                                @if (!empty($doc['uploadedAt']))
                                                    {{ \Carbon\Carbon::parse($doc['uploadedAt'])->format('d M Y H:i') }}
                                                @else — @endif
                                            </div>
                                        </div>
                                        <x-agent-badge :status="$doc['status'] ?? 'PENDING_REVIEW'" />
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <aside class="hidden rounded-xl border border-app-border bg-app-surface p-4 lg:block">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-app-accent-bg text-app-accent">
                    <x-agent-icon name="enroll" :size="20" />
                </div>
                <div class="mb-1 text-sm font-bold text-app-text">Documents KYC</div>
                <p class="mb-4 mt-0 text-xs leading-relaxed text-app-muted">
                    Recherchez un client par numéro pour consulter ses documents KYC et en ajouter de nouveaux.
                </p>
                <div class="rounded-lg bg-app-bg p-3 text-xs leading-relaxed text-app-muted">
                    Les documents sont soumis à validation par le backoffice après téléversement.
                </div>
            </aside>
        </div>
    </div>
</div>
