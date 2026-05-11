<div style="padding:40px 32px;">
    {{-- Logo --}}
    <div style="text-align:center;margin-bottom:36px;">
        <div style="display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:14px;background:var(--sidebar-bg);margin-bottom:14px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M3 7h18v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" fill="white" fill-opacity=".15" stroke="white" stroke-width="1.5"/>
                <path d="M8 7V5a4 4 0 018 0v2" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M9 13l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h1 style="font-size:22px;font-weight:800;color:var(--text-primary);letter-spacing:-0.03em;margin:0 0 4px;">Lipa Agent</h1>
        <p style="font-size:13px;color:var(--text-secondary);margin:0;">Portail opérateur sécurisé</p>
    </div>

    @if($error)
        <div style="background:var(--red-bg);border:1px solid var(--red);border-radius:8px;padding:10px 14px;margin-bottom:20px;font-size:13px;color:var(--red);">
            {{ $error }}
        </div>
    @endif

    @if($step === 'phone')
        {{-- Step 1: Phone number --}}
        <form wire:submit="requestOtp">
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);letter-spacing:0.03em;margin-bottom:6px;">INDICATIF PAYS</label>
                <div style="display:flex;gap:8px;">
                    <div style="position:relative;width:90px;flex-shrink:0;">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text-secondary);">+</span>
                        <input type="text" wire:model="phoneCountryCode" maxlength="5"
                            style="width:100%;padding:12px 14px 12px 24px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:inherit;font-size:14px;outline:none;"
                            inputmode="numeric" pattern="\d*" />
                    </div>
                    <div style="flex:1;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);letter-spacing:0.03em;margin-bottom:6px;">NUMÉRO DE TÉLÉPHONE</label>
                        <input type="tel" wire:model="phoneNumber" placeholder="3201234"
                            style="width:100%;padding:12px 14px;border-radius:8px;border:1.5px solid var(--border-color);background:var(--surface);color:var(--text-primary);font-family:'DM Mono',monospace;font-size:16px;outline:none;letter-spacing:0.05em;"
                            inputmode="numeric" pattern="\d*" autofocus />
                    </div>
                </div>
                <p style="font-size:11px;color:var(--text-secondary);margin-top:6px;">Entrez votre numéro d'agent enregistré</p>
            </div>

            <button type="submit" wire:loading.attr="disabled"
                style="width:100%;padding:14px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                <span wire:loading.remove>Recevoir le code</span>
                <span wire:loading style="display:flex;align-items:center;gap:8px;">
                    <x-spinner :size="16" />
                    Envoi en cours…
                </span>
            </button>
        </form>

    @else
        {{-- Step 2: OTP code --}}
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:28px;">
                <button wire:click="back" type="button"
                    style="background:none;border:none;cursor:pointer;color:var(--text-secondary);display:flex;padding:4px;border-radius:6px;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div>
                    <div style="font-size:15px;font-weight:700;color:var(--text-primary);">Vérification</div>
                    <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">Code envoyé au +{{ $phoneCountryCode }} {{ $phoneNumber }}</div>
                </div>
            </div>

            <form wire:submit="verifyOtp">
                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--text-secondary);letter-spacing:0.03em;margin-bottom:12px;text-align:center;">ENTREZ LE CODE À 6 CHIFFRES</label>

                    <input type="text" wire:model.live="otpCode"
                        inputmode="numeric" pattern="\d*" maxlength="6" autocomplete="one-time-code"
                        placeholder="000000"
                        style="width:100%;padding:18px;text-align:center;font-family:'DM Mono',monospace;font-size:28px;font-weight:700;letter-spacing:0.3em;border-radius:12px;border:2px solid var(--border-color);background:var(--surface);color:var(--text-primary);outline:none;transition:border-color 0.15s;"
                        onfocus="this.style.borderColor='var(--accent)'"
                        onblur="this.style.borderColor='var(--border-color)'" />

                    <p style="font-size:11px;color:var(--text-secondary);text-align:center;margin-top:8px;">
                        Code valide 5 minutes. Vérifiez vos SMS.
                    </p>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    style="width:100%;padding:14px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:16px;">
                    <span wire:loading.remove>Valider</span>
                    <span wire:loading style="display:flex;align-items:center;gap:8px;">
                        <x-spinner :size="16" />
                        Vérification…
                    </span>
                </button>

                <button type="button" wire:click="resend"
                    style="width:100%;padding:12px;background:transparent;color:var(--text-secondary);border:1px solid var(--border-color);border-radius:10px;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;">
                    Renvoyer le code
                </button>
            </form>
        </div>
    @endif

    <p style="text-align:center;font-size:11px;color:var(--text-secondary);margin-top:32px;opacity:0.7;">
        Lipa Agent Portal · Accès sécurisé
    </p>
</div>
