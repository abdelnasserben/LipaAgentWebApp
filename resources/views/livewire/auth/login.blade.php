<div class="px-8 py-10">
    {{-- Logo --}}
    <div class="mb-9 text-center">
        <div class="mb-3.5 inline-flex h-[52px] w-[52px] items-center justify-center rounded-[14px] bg-app-sidebar">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M3 7h18v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" fill="white" fill-opacity=".15" stroke="white" stroke-width="1.5"/>
                <path d="M8 7V5a4 4 0 018 0v2" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M9 13l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="mb-1 mt-0 text-[22px] font-extrabold tracking-[-0.03em] text-app-text">
            Lipa Agent
        </h1>

        <p class="m-0 text-[13px] text-app-muted">
            Portail opérateur sécurisé
        </p>
    </div>

    @if($error)
        <div class="mb-5 rounded-lg border border-app-red bg-app-red-bg px-3.5 py-2.5 text-[13px] text-app-red">
            {{ $error }}
        </div>
    @endif

    @if($step === 'phone')
        {{-- Step 1: Phone number --}}
        <form wire:submit="requestOtp">
            <div class="mb-5">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.03em] text-app-muted">
                    Numéro de téléphone
                </label>

                <div class="flex items-stretch gap-2">
                    <div class="flex shrink-0 items-center justify-center rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 font-mono text-sm font-bold text-app-text">
                        +{{ $phoneCountryCode }}
                    </div>

                    <input
                        type="tel"
                        wire:model="phoneNumber"
                        placeholder="3XX XXXX"
                        inputmode="numeric"
                        pattern="\d*"
                        autofocus
                        class="box-border min-w-0 flex-1 rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base tracking-[0.05em] text-app-text outline-none focus:border-app-accent"
                    />
                </div>

                @error('phoneCountryCode')
                    <p class="mt-1.5 text-[11px] text-app-red">{{ $message }}</p>
                @enderror

                @error('phoneNumber')
                    <p class="mt-1.5 text-[11px] text-app-red">{{ $message }}</p>
                @enderror

                <p class="mt-1.5 text-[11px] text-app-muted">
                    Entrez votre numéro d'agent enregistré
                </p>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70"
            >
                <span wire:loading.remove>Recevoir le code</span>
                <span wire:loading.flex class="hidden items-center gap-2">
                    <x-spinner :size="16" />
                    Envoi en cours…
                </span>
            </button>
        </form>
    @else
        {{-- Step 2: OTP code --}}
        <div>
            <div class="mb-7 flex items-center gap-2.5">
                <button
                    wire:click="back"
                    type="button"
                    class="flex cursor-pointer rounded-md border-0 bg-transparent p-1 text-app-muted"
                >
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div>
                    <div class="text-[15px] font-bold text-app-text">
                        Vérification
                    </div>
                    <div class="mt-0.5 text-xs text-app-muted">
                        Code envoyé au +{{ $phoneCountryCode }} {{ $phoneNumber }}
                    </div>
                </div>
            </div>

            <form wire:submit="verifyOtp">
                <div class="mb-6">
                    <label class="mb-3 block text-center text-xs font-semibold uppercase tracking-[0.03em] text-app-muted">
                        Entrez le code à 6 chiffres
                    </label>

                    <input
                        type="text"
                        wire:model.live="otpCode"
                        inputmode="numeric"
                        pattern="\d*"
                        maxlength="6"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        class="w-full rounded-xl border-2 border-app-border bg-app-surface p-[18px] text-center font-mono text-[28px] font-bold tracking-[0.3em] text-app-text outline-none transition-colors focus:border-app-accent"
                    />

                    <p class="mt-2 text-center text-[11px] text-app-muted">
                        Code valide 5 minutes. Vérifiez vos SMS.
                    </p>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="mb-4 flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span wire:loading.remove>Valider</span>
                    <span wire:loading.flex class="hidden items-center gap-2">
                        <x-spinner :size="16" />
                        Vérification…
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="resend"
                    class="w-full cursor-pointer rounded-[10px] border border-app-border bg-transparent p-3 text-sm font-semibold text-app-muted"
                >
                    Renvoyer le code
                </button>
            </form>
        </div>
    @endif

    <p class="mt-8 text-center text-[11px] text-app-muted opacity-70">
        Lipa Agent Portal · Accès sécurisé
    </p>
</div>