<div>
    {{-- Page header --}}
    <x-page-header title="Opérations" subtitle="Cash-in client · Cash-out marchand" />

    {{-- Tab bar --}}
    <div style="display:flex;border-bottom:2px solid var(--border-color);margin:0;background:var(--surface);">
        <button wire:click="switchTab('cash-in')" type="button"
            style="flex:1;padding:14px 16px;border:none;background:none;cursor:pointer;font-family:inherit;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:8px;position:relative;color:{{ $activeTab === 'cash-in' ? 'var(--green)' : 'var(--text-secondary)' }};">
            <x-agent-icon name="cash-in" :size="16" />
            Cash-in
            @if($activeTab === 'cash-in')
                <span style="position:absolute;bottom:-2px;left:0;right:0;height:2px;background:var(--green);border-radius:2px 2px 0 0;"></span>
            @endif
        </button>
        <button wire:click="switchTab('cash-out')" type="button"
            style="flex:1;padding:14px 16px;border:none;background:none;cursor:pointer;font-family:inherit;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:8px;position:relative;color:{{ $activeTab === 'cash-out' ? 'var(--amber)' : 'var(--text-secondary)' }};">
            <x-agent-icon name="cash-out" :size="16" />
            Cash-out
            @if($activeTab === 'cash-out')
                <span style="position:absolute;bottom:-2px;left:0;right:0;height:2px;background:var(--amber);border-radius:2px 2px 0 0;"></span>
            @endif
        </button>
    </div>

    <div style="padding:20px 16px;">

        {{-- ═══════════════════════════════════════════════════════════
             CASH-IN TAB
        ════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'cash-in')

            {{-- ── lookup step ── --}}
            @if($ciStep === 'lookup')
                <div>
                    <div style="margin-bottom:20px;">
                        <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">Rechercher un client</div>
                        <div style="font-size:12px;color:var(--text-secondary);">Entrez le numéro de téléphone du client</div>
                    </div>

                    @if($ciError)
                        <div style="background:var(--red-bg);border:1px solid var(--red);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:var(--red);display:flex;align-items:center;gap:8px;">
                            <x-agent-icon name="warning" :size="14" />
                            {{ $ciError }}
                        </div>
                    @endif

                    <div style="margin-bottom:20px;">
                        <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:8px;">Numéro de téléphone</label>
                        <div style="display:flex;gap:8px;">
                            <div style="position:relative;width:88px;flex-shrink:0;">
                                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text-secondary);pointer-events:none;">+</span>
                                <input type="text" wire:model="ciPhoneCountryCode" maxlength="5"
                                    style="width:100%;padding:12px 14px 12px 26px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:13px;outline:none;box-sizing:border-box;"
                                    inputmode="numeric" />
                            </div>
                            <input type="tel" wire:model="ciPhoneNumber"
                                placeholder="3201234"
                                style="flex:1;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:16px;outline:none;letter-spacing:0.05em;"
                                inputmode="numeric"
                                wire:keydown.enter="lookupCustomer" />
                        </div>
                    </div>

                    <button wire:click="lookupCustomer" wire:loading.attr="disabled" type="button"
                        style="width:100%;padding:14px;background:var(--green);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <span wire:loading.remove wire:target="lookupCustomer" style="display:flex;align-items:center;gap:8px;">
                            <x-agent-icon name="search" :size="16" />
                            Rechercher
                        </span>
                        <span wire:loading wire:target="lookupCustomer" style="display:flex;align-items:center;gap:8px;">
                            <x-spinner :size="16" />
                            Recherche en cours…
                        </span>
                    </button>
                </div>

            {{-- ── confirm step ── --}}
            @elseif($ciStep === 'confirm')
                <div>
                    <button wire:click="backToLookup" type="button"
                        style="display:flex;align-items:center;gap:6px;background:none;border:none;cursor:pointer;color:var(--text-secondary);font-family:inherit;font-size:13px;font-weight:600;padding:0;margin-bottom:20px;">
                        <x-agent-icon name="back" :size="16" />
                        Retour
                    </button>

                    <div style="margin-bottom:20px;">
                        <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">Confirmer le client</div>
                        <div style="font-size:12px;color:var(--text-secondary);">Vérifiez les informations avant de continuer</div>
                    </div>

                    {{-- Customer card --}}
                    <div style="background:var(--surface);border:1.5px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:20px;">
                        <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                            <div style="width:48px;height:48px;border-radius:50%;background:var(--accent-bg);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:var(--accent);flex-shrink:0;">
                                {{ mb_strtoupper(mb_substr($ciCustomer['fullName'], 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:18px;font-weight:800;color:var(--text-primary);letter-spacing:-0.02em;">{{ $ciCustomer['fullName'] }}</div>
                                <div style="font-size:13px;color:var(--text-secondary);font-family:'DM Mono',monospace;margin-top:2px;">
                                    +{{ $ciCustomer['phoneCountryCode'] }} {{ $ciCustomer['phoneNumber'] }}
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <x-agent-badge :status="$ciCustomer['status']" />
                            <x-agent-badge :status="$ciCustomer['kycLevel']" />
                        </div>
                        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border-color);">
                            <x-detail-row label="ID Client" :mono="true" :border="false">{{ $ciCustomer['customerId'] }}</x-detail-row>
                        </div>
                    </div>

                    <button wire:click="confirmCustomer" type="button"
                        style="width:100%;padding:14px;background:var(--green);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:12px;">
                        <x-agent-icon name="check" :size="16" />
                        Confirmer ce client
                    </button>

                    <button wire:click="backToLookup" type="button"
                        style="width:100%;padding:12px;background:transparent;color:var(--text-secondary);border:1.5px solid var(--border-color);border-radius:10px;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;">
                        Ce n'est pas lui
                    </button>
                </div>

            {{-- ── amount step ── --}}
            @elseif($ciStep === 'amount')
                <div>
                    {{-- Mini customer row --}}
                    <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--green-bg);border:1px solid var(--green);border-radius:10px;margin-bottom:20px;">
                        <div style="width:34px;height:34px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0;">
                            {{ mb_strtoupper(mb_substr($ciCustomer['fullName'], 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text-primary);">{{ $ciCustomer['fullName'] }}</div>
                            <div style="font-size:11px;color:var(--text-secondary);font-family:'DM Mono',monospace;">
                                +{{ $ciCustomer['phoneCountryCode'] }} {{ $ciCustomer['phoneNumber'] }}
                            </div>
                        </div>
                        <div style="margin-left:auto;">
                            <x-agent-badge :status="$ciCustomer['status']" />
                        </div>
                    </div>

                    @if($ciError)
                        <div style="background:var(--red-bg);border:1px solid var(--red);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:var(--red);display:flex;align-items:center;gap:8px;">
                            <x-agent-icon name="warning" :size="14" />
                            {{ $ciError }}
                        </div>
                    @endif

                    {{-- Amount input --}}
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:8px;">Montant en KMF</label>
                        <div style="position:relative;">
                            <input type="number" wire:model.live="ciAmount" min="1"
                                placeholder="0"
                                style="width:100%;padding:16px 56px 16px 16px;border-radius:10px;border:2px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:28px;font-weight:700;outline:none;box-sizing:border-box;letter-spacing:-0.02em;"
                                onfocus="this.style.borderColor='var(--green)'"
                                onblur="this.style.borderColor='var(--border-color)'" />
                            <span style="position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:600;color:var(--text-secondary);">KMF</span>
                        </div>
                    </div>

                    {{-- Quick amounts --}}
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:20px;">
                        @foreach([5000, 10000, 25000, 50000] as $quick)
                            <button wire:click="setCashInAmount({{ $quick }})" type="button"
                                style="padding:8px 4px;background:{{ (int)$ciAmount === $quick ? 'var(--green)' : 'var(--surface)' }};color:{{ (int)$ciAmount === $quick ? '#fff' : 'var(--text-secondary)' }};border:1.5px solid {{ (int)$ciAmount === $quick ? 'var(--green)' : 'var(--border-color)' }};border-radius:8px;font-family:'DM Mono',monospace;font-size:12px;font-weight:700;cursor:pointer;">
                                {{ number_format($quick, 0, ',', ' ') }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Fee preview --}}
                    @if((int)$ciAmount > 0)
                        @php
                            $fee    = (int) floor((int)$ciAmount * 0.01);
                            $comm   = $fee;
                            $net    = (int)$ciAmount - $fee;
                        @endphp
                        <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:10px;padding:14px;margin-bottom:20px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border-color);">
                                <span style="font-size:12px;color:var(--text-secondary);">Frais estimés</span>
                                <span style="font-size:13px;font-weight:600;font-family:'DM Mono',monospace;color:var(--text-primary);">{{ number_format($fee, 0, ',', ' ') }} KMF</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border-color);">
                                <span style="font-size:12px;color:var(--text-secondary);">Commission</span>
                                <span style="font-size:13px;font-weight:600;font-family:'DM Mono',monospace;color:var(--green);">+ {{ number_format($comm, 0, ',', ' ') }} KMF</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;">
                                <span style="font-size:12px;font-weight:700;color:var(--text-primary);">Net client</span>
                                <span style="font-size:14px;font-weight:800;font-family:'DM Mono',monospace;color:var(--text-primary);">{{ number_format($net, 0, ',', ' ') }} KMF</span>
                            </div>
                        </div>
                    @else
                        <div style="height:20px;margin-bottom:20px;"></div>
                    @endif

                    <button wire:click="submitCashIn" wire:loading.attr="disabled" type="button"
                        style="width:100%;padding:16px;background:var(--green);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <span wire:loading.remove wire:target="submitCashIn" style="display:flex;align-items:center;gap:8px;">
                            <x-agent-icon name="cash-in" :size="16" />
                            Effectuer le Cash-in
                        </span>
                        <span wire:loading wire:target="submitCashIn" style="display:flex;align-items:center;gap:8px;">
                            <x-spinner :size="16" />
                            Traitement…
                        </span>
                    </button>
                </div>

            {{-- ── success step ── --}}
            @elseif($ciStep === 'success')
                <div style="text-align:center;padding:12px 0 24px;">
                    <div style="width:72px;height:72px;border-radius:50%;background:var(--green-bg);border:2px solid var(--green);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                        <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                            <path d="M8 17l6 6 12-12" stroke="var(--green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div style="font-size:22px;font-weight:800;color:var(--text-primary);letter-spacing:-0.02em;margin-bottom:4px;">Cash-in réussi !</div>
                    <div style="font-size:13px;color:var(--text-secondary);margin-bottom:24px;">La transaction a été effectuée avec succès</div>

                    <div style="background:var(--green-bg);border:1px solid var(--green);border-radius:12px;padding:20px;margin-bottom:20px;display:inline-block;width:100%;box-sizing:border-box;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--green);margin-bottom:6px;">Montant crédité</div>
                        <div style="font-size:36px;font-weight:800;color:var(--text-primary);font-family:'DM Mono',monospace;letter-spacing:-0.03em;">
                            {{ number_format($ciResult['netAmountToDestination'], 0, ',', ' ') }}
                            <span style="font-size:16px;font-weight:500;color:var(--text-secondary);">KMF</span>
                        </div>
                    </div>

                    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:12px;padding:4px 16px;margin-bottom:24px;text-align:left;">
                        <x-detail-row label="ID Transaction" :mono="true">{{ $ciResult['transactionId'] }}</x-detail-row>
                        <x-detail-row label="Montant demandé" :mono="true">{{ number_format($ciResult['requestedAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                        <x-detail-row label="Frais" :mono="true">{{ number_format($ciResult['feeAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                        <x-detail-row label="Commission" :mono="true">
                            <span style="color:var(--green);">+ {{ number_format($ciResult['commissionAmount'], 0, ',', ' ') }} KMF</span>
                        </x-detail-row>
                        <x-detail-row label="Statut" :border="false">
                            <x-agent-badge status="COMPLETED" />
                        </x-detail-row>
                    </div>

                    <button wire:click="resetCashIn" type="button"
                        style="width:100%;padding:14px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;">
                        Nouvelle opération
                    </button>
                </div>
            @endif

        {{-- ═══════════════════════════════════════════════════════════
             CASH-OUT TAB
        ════════════════════════════════════════════════════════════ --}}
        @elseif($activeTab === 'cash-out')

            {{-- ── form step ── --}}
            @if($coStep === 'form')
                <div>
                    <div style="margin-bottom:20px;">
                        <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">Cash-out Marchand</div>
                        <div style="font-size:12px;color:var(--text-secondary);">Saisissez la référence et le montant</div>
                    </div>

                    @if($coError)
                        <div style="background:var(--red-bg);border:1px solid var(--red);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:var(--red);display:flex;align-items:center;gap:8px;">
                            <x-agent-icon name="warning" :size="14" />
                            {{ $coError }}
                        </div>
                    @endif

                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:8px;">Référence marchand</label>
                        <input type="text" wire:model="coMerchantRef"
                            placeholder="ex. MARCH-001"
                            style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:14px;outline:none;box-sizing:border-box;letter-spacing:0.03em;"
                            onfocus="this.style.borderColor='var(--amber)'"
                            onblur="this.style.borderColor='var(--border-color)'" />
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:8px;">Montant en KMF</label>
                        <div style="position:relative;">
                            <input type="number" wire:model="coAmount" min="1"
                                placeholder="0"
                                style="width:100%;padding:16px 56px 16px 16px;border-radius:10px;border:2px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:28px;font-weight:700;outline:none;box-sizing:border-box;letter-spacing:-0.02em;"
                                onfocus="this.style.borderColor='var(--amber)'"
                                onblur="this.style.borderColor='var(--border-color)'" />
                            <span style="position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:600;color:var(--text-secondary);">KMF</span>
                        </div>
                    </div>

                    <div style="background:var(--amber-bg);border:1px solid var(--amber);border-radius:8px;padding:10px 14px;margin-bottom:20px;display:flex;align-items:flex-start;gap:8px;">
                        <x-agent-icon name="warning" :size="14" style="color:var(--amber);flex-shrink:0;margin-top:1px;" />
                        <span style="font-size:12px;color:var(--amber);font-weight:500;">Montants &gt; 100 000 KMF nécessitent une approbation backoffice</span>
                    </div>

                    <button wire:click="lookupMerchant" wire:loading.attr="disabled" type="button"
                        style="width:100%;padding:14px;background:var(--amber);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <span wire:loading.remove wire:target="lookupMerchant">Soumettre</span>
                        <span wire:loading wire:target="lookupMerchant" style="display:flex;align-items:center;gap:8px;">
                            <x-spinner :size="16" />
                            Vérification…
                        </span>
                    </button>
                </div>

            {{-- ── confirm step ── --}}
            @elseif($coStep === 'confirm')
                <div>
                    <div style="margin-bottom:20px;">
                        <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">Confirmer le Cash-out</div>
                        <div style="font-size:12px;color:var(--text-secondary);">Vérifiez les détails avant de valider</div>
                    </div>

                    <div style="background:var(--amber-bg);border:1.5px solid var(--amber);border-radius:12px;padding:16px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
                        <div style="width:42px;height:42px;border-radius:10px;background:var(--amber);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <x-agent-icon name="cash-out" :size="20" style="color:#fff;" />
                        </div>
                        <div>
                            <div style="font-size:16px;font-weight:700;color:var(--text-primary);">{{ $coMerchant['businessName'] }}</div>
                            <div style="font-size:11px;color:var(--text-secondary);font-family:'DM Mono',monospace;margin-top:2px;">{{ $coMerchantRef }}</div>
                        </div>
                        <div style="margin-left:auto;">
                            <x-agent-badge status="ACTIVE" />
                        </div>
                    </div>

                    @php
                        $coFee  = (int) floor((int)$coAmount * 0.01);
                        $coComm = $coFee;
                        $coNet  = (int)$coAmount - $coFee;
                    @endphp
                    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:10px;padding:14px;margin-bottom:20px;">
                        <div style="text-align:center;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--border-color);">
                            <div style="font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:4px;">Montant</div>
                            <div style="font-size:32px;font-weight:800;font-family:'DM Mono',monospace;color:var(--text-primary);letter-spacing:-0.02em;">
                                {{ number_format((int)$coAmount, 0, ',', ' ') }}
                                <span style="font-size:16px;font-weight:500;color:var(--text-secondary);">KMF</span>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border-color);">
                            <span style="font-size:12px;color:var(--text-secondary);">Frais</span>
                            <span style="font-size:12px;font-weight:600;font-family:'DM Mono',monospace;color:var(--text-primary);">{{ number_format($coFee, 0, ',', ' ') }} KMF</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border-color);">
                            <span style="font-size:12px;color:var(--text-secondary);">Commission agent</span>
                            <span style="font-size:12px;font-weight:600;font-family:'DM Mono',monospace;color:var(--green);">+ {{ number_format($coComm, 0, ',', ' ') }} KMF</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:5px 0;">
                            <span style="font-size:12px;font-weight:700;color:var(--text-primary);">Net marchand</span>
                            <span style="font-size:13px;font-weight:800;font-family:'DM Mono',monospace;color:var(--text-primary);">{{ number_format($coNet, 0, ',', ' ') }} KMF</span>
                        </div>
                    </div>

                    @if((int)$coAmount > 100000)
                        <div style="background:var(--amber-bg);border:1px solid var(--amber);border-radius:8px;padding:10px 14px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <x-agent-icon name="warning" :size="14" />
                            <span style="font-size:12px;color:var(--amber);font-weight:500;">Ce montant nécessitera une approbation backoffice</span>
                        </div>
                    @endif

                    <button wire:click="submitCashOut" wire:loading.attr="disabled" type="button"
                        style="width:100%;padding:14px;background:var(--amber);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;">
                        <span wire:loading.remove wire:target="submitCashOut" style="display:flex;align-items:center;gap:8px;">
                            <x-agent-icon name="check" :size="16" />
                            Confirmer
                        </span>
                        <span wire:loading wire:target="submitCashOut" style="display:flex;align-items:center;gap:8px;">
                            <x-spinner :size="16" />
                            Traitement…
                        </span>
                    </button>

                    <button wire:click="resetCashOut" type="button"
                        style="width:100%;padding:12px;background:transparent;color:var(--text-secondary);border:1.5px solid var(--border-color);border-radius:10px;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;">
                        Annuler
                    </button>
                </div>

            {{-- ── success step ── --}}
            @elseif($coStep === 'success')
                <div style="text-align:center;padding:12px 0 24px;">
                    @if($coStatus === 200)
                        <div style="width:72px;height:72px;border-radius:50%;background:var(--green-bg);border:2px solid var(--green);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                            <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
                                <path d="M8 17l6 6 12-12" stroke="var(--green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div style="font-size:22px;font-weight:800;color:var(--green);letter-spacing:-0.02em;margin-bottom:4px;">Cash-out effectué !</div>
                        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:24px;">La transaction a été effectuée avec succès</div>
                    @else
                        <div style="width:72px;height:72px;border-radius:50%;background:var(--amber-bg);border:2px solid var(--amber);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                            <x-agent-icon name="warning" :size="30" />
                        </div>
                        <div style="font-size:22px;font-weight:800;color:var(--amber);letter-spacing:-0.02em;margin-bottom:4px;">En attente d'approbation</div>
                        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:24px;">Ce montant nécessite une validation backoffice</div>
                    @endif

                    <div style="background:{{ $coStatus === 200 ? 'var(--green-bg)' : 'var(--amber-bg)' }};border:1px solid {{ $coStatus === 200 ? 'var(--green)' : 'var(--amber)' }};border-radius:12px;padding:20px;margin-bottom:20px;display:inline-block;width:100%;box-sizing:border-box;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:{{ $coStatus === 200 ? 'var(--green)' : 'var(--amber)' }};margin-bottom:6px;">Montant</div>
                        <div style="font-size:36px;font-weight:800;color:var(--text-primary);font-family:'DM Mono',monospace;letter-spacing:-0.03em;">
                            {{ number_format($coResult['requestedAmount'], 0, ',', ' ') }}
                            <span style="font-size:16px;font-weight:500;color:var(--text-secondary);">KMF</span>
                        </div>
                    </div>

                    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:12px;padding:4px 16px;margin-bottom:24px;text-align:left;">
                        <x-detail-row label="ID Transaction" :mono="true">{{ $coResult['transactionId'] }}</x-detail-row>
                        @if($coStatus === 202)
                            <x-detail-row label="Réf. approbation" :mono="true">{{ $coResult['transactionId'] }}</x-detail-row>
                        @endif
                        <x-detail-row label="Frais" :mono="true">{{ number_format($coResult['feeAmount'], 0, ',', ' ') }} KMF</x-detail-row>
                        <x-detail-row label="Commission" :mono="true">
                            <span style="color:var(--green);">+ {{ number_format($coResult['commissionAmount'], 0, ',', ' ') }} KMF</span>
                        </x-detail-row>
                        <x-detail-row label="Statut" :border="false">
                            <x-agent-badge :status="$coResult['status']" />
                        </x-detail-row>
                    </div>

                    <button wire:click="resetCashOut" type="button"
                        style="width:100%;padding:14px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;">
                        Nouvelle opération
                    </button>
                </div>
            @endif

        @endif
    </div>
</div>
