<div>
    {{-- Page header --}}
    <x-page-header title="Enrôler un client" subtitle="Créer un nouveau compte client" />

    {{-- Progress stepper --}}
    <div style="padding:20px 16px 0;background:var(--surface);border-bottom:1px solid var(--border-color);">
        <div style="display:flex;align-items:center;justify-content:center;gap:0;max-width:320px;margin:0 auto 16px;">
            @php
                $stepLabels = ['Identité', 'Adresse', 'Documents', 'Résumé'];
            @endphp
            @foreach($stepLabels as $i => $label)
                @php $n = $i + 1; @endphp
                {{-- Step circle --}}
                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;position:relative;z-index:1;">
                    <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;
                        {{ $step > $n ? 'background:var(--green);color:#fff;' : ($step === $n ? 'background:var(--accent);color:#fff;' : 'background:var(--border-color);color:var(--text-secondary);') }}">
                        @if($step > $n)
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M3 7l3 3 5-5" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @else
                            {{ $n }}
                        @endif
                    </div>
                    <div style="font-size:10px;font-weight:600;color:{{ $step === $n ? 'var(--accent)' : ($step > $n ? 'var(--green)' : 'var(--text-secondary)') }};white-space:nowrap;">{{ $label }}</div>
                </div>
                {{-- Connector line --}}
                @if($n < 4)
                    <div style="flex:1;height:2px;background:{{ $step > $n ? 'var(--green)' : 'var(--border-color)' }};margin:0 4px;margin-bottom:18px;"></div>
                @endif
            @endforeach
        </div>
    </div>

    <div style="padding:20px 16px;">

        {{-- ═══════════════════════════════════════════════════════════
             STEP 1 – Identité
        ════════════════════════════════════════════════════════════ --}}
        @if($step === 1)
            <div>
                <div style="margin-bottom:20px;">
                    <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">Informations personnelles</div>
                    <div style="font-size:12px;color:var(--text-secondary);">Renseignez l'identité du client</div>
                </div>

                {{-- Full name --}}
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:6px;">Nom complet <span style="color:var(--red);">*</span></label>
                    <input type="text" wire:model="fullName"
                        placeholder="Prénom Nom"
                        style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:inherit;font-size:14px;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='var(--accent)'"
                        onblur="this.style.borderColor='var(--border-color)'" />
                    @error('fullName') <p style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                {{-- Date of birth --}}
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:6px;">Date de naissance <span style="color:var(--red);">*</span></label>
                    <input type="date" wire:model="dateOfBirth"
                        style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:inherit;font-size:14px;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='var(--accent)'"
                        onblur="this.style.borderColor='var(--border-color)'" />
                    @error('dateOfBirth') <p style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                {{-- Phone --}}
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:6px;">Numéro de téléphone <span style="color:var(--red);">*</span></label>
                    <div style="display:flex;gap:8px;align-items:stretch;">
                        <div style="display:flex;align-items:center;justify-content:center;padding:0 14px;flex-shrink:0;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:14px;font-weight:700;">
                            +{{ $phoneCountryCode }}
                        </div>
                        <input type="tel" wire:model="phoneNumber"
                            placeholder="3XX XXXX"
                            style="flex:1;min-width:0;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:16px;outline:none;letter-spacing:0.05em;box-sizing:border-box;"
                            inputmode="numeric" />
                    </div>
                    @error('phoneNumber') <p style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                {{-- ID type --}}
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:6px;">Type de pièce d'identité <span style="color:var(--red);">*</span></label>
                    <select wire:model="nationalIdType"
                        style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:inherit;font-size:14px;outline:none;appearance:none;cursor:pointer;">
                        <option value="NATIONAL_ID">Carte Nationale d'Identité</option>
                        <option value="PASSPORT">Passeport</option>
                        <option value="OTHER">Autre</option>
                    </select>
                    @error('nationalIdType') <p style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                {{-- ID number --}}
                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:6px;">Numéro de pièce <span style="color:var(--red);">*</span></label>
                    <input type="text" wire:model="nationalIdNumber"
                        placeholder="ex. KM-1234567"
                        style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:14px;outline:none;box-sizing:border-box;letter-spacing:0.04em;"
                        onfocus="this.style.borderColor='var(--accent)'"
                        onblur="this.style.borderColor='var(--border-color)'" />
                    @error('nationalIdNumber') <p style="font-size:11px;color:var(--red);margin-top:4px;">{{ $message }}</p> @enderror
                </div>

                <button wire:click="nextStep" type="button"
                    style="width:100%;padding:14px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;">
                    Suivant →
                </button>
            </div>

        {{-- ═══════════════════════════════════════════════════════════
             STEP 2 – Adresse (optionnel)
        ════════════════════════════════════════════════════════════ --}}
        @elseif($step === 2)
            <div>
                <div style="margin-bottom:16px;">
                    <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">Adresse du client</div>
                    <div style="font-size:12px;color:var(--text-secondary);">Cette étape est optionnelle</div>
                </div>

                <div style="background:var(--blue-bg);border:1px solid var(--blue);border-radius:8px;padding:10px 14px;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <circle cx="7" cy="7" r="6" stroke="var(--blue)" stroke-width="1.3"/>
                        <path d="M7 6v4" stroke="var(--blue)" stroke-width="1.3" stroke-linecap="round"/>
                        <circle cx="7" cy="4.5" r=".7" fill="var(--blue)"/>
                    </svg>
                    <span style="font-size:12px;color:var(--blue);font-weight:500;">Vous pouvez passer cette étape sans saisir d'adresse</span>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:6px;">Île</label>
                    <input type="text" wire:model="addressIsland"
                        placeholder="Grande Comore, Anjouan, Mohéli…"
                        style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:inherit;font-size:14px;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='var(--accent)'"
                        onblur="this.style.borderColor='var(--border-color)'" />
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:6px;">Ville</label>
                    <input type="text" wire:model="addressCity"
                        placeholder="Moroni, Mutsamudu…"
                        style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:inherit;font-size:14px;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='var(--accent)'"
                        onblur="this.style.borderColor='var(--border-color)'" />
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:6px;">Quartier</label>
                    <input type="text" wire:model="addressDistrict"
                        placeholder="Nom du quartier"
                        style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:inherit;font-size:14px;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='var(--accent)'"
                        onblur="this.style.borderColor='var(--border-color)'" />
                </div>

                <div style="display:grid;grid-template-columns:1fr 2fr;gap:10px;">
                    <button wire:click="goToStep(1)" type="button"
                        style="padding:14px;background:transparent;color:var(--text-secondary);border:1.5px solid var(--border-color);border-radius:10px;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <x-agent-icon name="back" :size="14" />
                        Retour
                    </button>
                    <button wire:click="nextStep" type="button"
                        style="padding:14px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;">
                        Suivant →
                    </button>
                </div>
            </div>

        {{-- ═══════════════════════════════════════════════════════════
             STEP 3 – Documents KYC
        ════════════════════════════════════════════════════════════ --}}
        @elseif($step === 3)
            <div>
                <div style="margin-bottom:20px;">
                    <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">Documents KYC</div>
                    <div style="font-size:12px;color:var(--text-secondary);">Ajoutez les pièces justificatives du client</div>
                </div>

                {{-- Doc type selector + add button --}}
                <div style="display:flex;gap:8px;margin-bottom:16px;">
                    <select wire:model="kycDocType"
                        style="flex:1;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:inherit;font-size:13px;outline:none;appearance:none;cursor:pointer;">
                        <option value="NATIONAL_ID">Carte Nationale d'Identité</option>
                        <option value="PASSPORT">Passeport</option>
                        <option value="PROOF_OF_ADDRESS">Justificatif de domicile</option>
                        <option value="BUSINESS_LICENSE">Licence commerciale</option>
                        <option value="OTHER">Autre document</option>
                    </select>
                    <button wire:click="addKycDoc" type="button"
                        style="padding:12px 16px;background:var(--accent-bg);color:var(--accent);border:1.5px solid var(--accent);border-radius:8px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:6px;">
                        <x-agent-icon name="upload" :size="14" />
                        Ajouter
                    </button>
                </div>

                {{-- Document list --}}
                @if(count($kycDocuments) > 0)
                    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:10px;overflow:hidden;margin-bottom:16px;">
                        @foreach($kycDocuments as $i => $doc)
                            @php
                                $docLabels = [
                                    'NATIONAL_ID'      => "Carte Nationale d'Identité",
                                    'PASSPORT'         => 'Passeport',
                                    'PROOF_OF_ADDRESS' => 'Justificatif de domicile',
                                    'BUSINESS_LICENSE' => 'Licence commerciale',
                                    'OTHER'            => 'Autre document',
                                ];
                            @endphp
                            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;{{ $i > 0 ? 'border-top:1px solid var(--border-color);' : '' }}">
                                <div style="width:34px;height:34px;border-radius:8px;background:var(--blue-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <x-agent-icon name="card" :size="16" style="color:var(--blue);" />
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $docLabels[$doc['type']] ?? $doc['type'] }}
                                    </div>
                                    <div style="font-size:11px;color:var(--text-secondary);font-family:'DM Mono',monospace;margin-top:2px;">{{ $doc['filename'] }}</div>
                                </div>
                                <x-agent-badge status="PENDING_REVIEW" />
                                <button wire:click="removeKycDoc({{ $i }})" type="button"
                                    style="background:none;border:none;cursor:pointer;color:var(--text-secondary);padding:4px;display:flex;border-radius:4px;"
                                    onmouseover="this.style.color='var(--red)'" onmouseout="this.style.color='var(--text-secondary)'">
                                    <x-agent-icon name="close" :size="16" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="background:var(--surface);border:1px dashed var(--border-color);border-radius:10px;padding:24px;text-align:center;margin-bottom:16px;">
                        <div style="color:var(--text-secondary);font-size:12px;">Aucun document ajouté</div>
                    </div>
                @endif

                <div style="background:var(--amber-bg);border:1px solid var(--amber);border-radius:8px;padding:10px 14px;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                    <x-agent-icon name="warning" :size="14" />
                    <span style="font-size:12px;color:var(--amber);font-weight:500;">Vous pouvez continuer sans ajouter de documents — ils peuvent être fournis ultérieurement</span>
                </div>

                <div style="display:grid;grid-template-columns:1fr 2fr;gap:10px;">
                    <button wire:click="goToStep(2)" type="button"
                        style="padding:14px;background:transparent;color:var(--text-secondary);border:1.5px solid var(--border-color);border-radius:10px;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                        <x-agent-icon name="back" :size="14" />
                        Retour
                    </button>
                    <button wire:click="nextStep" wire:loading.attr="disabled" type="button"
                        style="padding:14px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <span wire:loading.remove wire:target="nextStep">Soumettre</span>
                        <span wire:loading.flex wire:target="nextStep" style="display:none;align-items:center;gap:8px;">
                            <x-spinner :size="16" />
                            Enrôlement…
                        </span>
                    </button>
                </div>
            </div>

        {{-- ═══════════════════════════════════════════════════════════
             STEP 4 – Récapitulatif
        ════════════════════════════════════════════════════════════ --}}
        @elseif($step === 4)
            @if(! $enrollResult)
                {{-- Loading state --}}
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;gap:16px;">
                    <x-spinner :size="32" />
                    <div style="font-size:14px;color:var(--text-secondary);">Enrôlement en cours…</div>
                </div>
            @else
                <div>
                    {{-- Success header --}}
                    <div style="text-align:center;padding:16px 0 28px;">
                        <div style="width:72px;height:72px;border-radius:50%;background:var(--green-bg);border:2px solid var(--green);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                                <path d="M8 17l6 6 12-12" stroke="var(--green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div style="font-size:20px;font-weight:800;color:var(--text-primary);letter-spacing:-0.02em;margin-bottom:4px;">Client enrôlé avec succès !</div>
                        <div style="font-size:13px;color:var(--text-secondary);">{{ $fullName }}</div>
                    </div>

                    {{-- IDs --}}
                    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:12px;padding:4px 16px;margin-bottom:20px;">
                        <x-detail-row label="ID Client" :mono="true">{{ $enrollResult['customerId'] }}</x-detail-row>
                        <x-detail-row label="Réf. externe" :mono="true">{{ $enrollResult['externalRef'] }}</x-detail-row>
                        <x-detail-row label="ID Wallet" :mono="true" :border="false">{{ $enrollResult['walletId'] }}</x-detail-row>
                    </div>

                    {{-- Card sale section --}}
                    @if($offerCardSale && count($cardStock) > 0)
                        <div style="background:var(--surface);border:1.5px solid var(--purple);border-radius:12px;padding:16px;margin-bottom:20px;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                                <div style="width:36px;height:36px;border-radius:9px;background:var(--purple-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <x-agent-icon name="card" :size="18" style="color:var(--purple);" />
                                </div>
                                <div>
                                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);">Vendre une carte NFC</div>
                                    <div style="font-size:11px;color:var(--text-secondary);">Sélectionnez une carte à attribuer au client</div>
                                </div>
                            </div>

                            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
                                @foreach($cardStock as $card)
                                    <button wire:click="selectCard('{{ $card['nfcUid'] }}')" type="button"
                                        style="width:100%;display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;border:{{ $selectedNfcUid === $card['nfcUid'] ? '2px solid var(--purple)' : '1.5px solid var(--border-color)' }};background:{{ $selectedNfcUid === $card['nfcUid'] ? 'var(--purple-bg)' : 'var(--bg)' }};cursor:pointer;text-align:left;">
                                        <div style="width:8px;height:8px;border-radius:50%;border:2px solid {{ $selectedNfcUid === $card['nfcUid'] ? 'var(--purple)' : 'var(--text-secondary)' }};background:{{ $selectedNfcUid === $card['nfcUid'] ? 'var(--purple)' : 'transparent' }};flex-shrink:0;"></div>
                                        <div style="flex:1;">
                                            <div style="font-size:12px;font-weight:700;color:var(--text-primary);font-family:'DM Mono',monospace;">{{ $card['internalCardNumber'] }}</div>
                                            <div style="font-size:11px;color:var(--text-secondary);font-family:'DM Mono',monospace;margin-top:2px;letter-spacing:0.05em;">NFC: {{ $card['nfcUid'] }}</div>
                                        </div>
                                        @if($selectedNfcUid === $card['nfcUid'])
                                            <x-agent-icon name="check" :size="16" style="color:var(--purple);flex-shrink:0;" />
                                        @endif
                                    </button>
                                @endforeach
                            </div>

                            @if($selectedNfcUid)
                                <button type="button"
                                    style="width:100%;padding:12px;background:var(--purple);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;">
                                    Confirmer la vente
                                </button>
                            @endif
                        </div>
                    @endif

                    <button wire:click="finish" wire:navigate type="button"
                        style="width:100%;padding:14px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <x-agent-icon name="home" :size="16" />
                        Terminer
                    </button>
                </div>
            @endif
        @endif

    </div>
</div>
