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

        <h1 class="mb-1 mt-0 text-[22px] font-extrabold text-app-text">
            Lipa Agent
        </h1>

        <p class="m-0 text-[13px] text-app-muted">
            Portail operateur securise
        </p>
    </div>

    @if($error)
        <div class="mb-5 rounded-lg border border-app-red bg-app-red-bg px-3.5 py-2.5 text-[13px] text-app-red">
            {{ $error }}
        </div>
    @endif

    @if($step === 'credentials')
        <form wire:submit="login">
            <div class="mb-5">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.03em] text-app-muted">
                    Numero de telephone
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
                        autocomplete="tel-national"
                        autofocus
                        class="box-border min-w-0 flex-1 rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base text-app-text outline-none focus:border-app-accent"
                    />
                </div>

                @error('phoneCountryCode')
                    <p class="mt-1.5 text-[11px] text-app-red">{{ $message }}</p>
                @enderror

                @error('phoneNumber')
                    <p class="mt-1.5 text-[11px] text-app-red">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.03em] text-app-muted">
                    PIN
                </label>

                <input
                    type="password"
                    wire:model="pin"
                    inputmode="numeric"
                    pattern="\d*"
                    maxlength="8"
                    autocomplete="current-password"
                    placeholder="4 a 8 chiffres"
                    class="box-border w-full rounded-lg border-[1.5px] border-app-border bg-app-surface px-3.5 py-3 font-mono text-base text-app-text outline-none focus:border-app-accent"
                />

                @error('pin')
                    <p class="mt-1.5 text-[11px] text-app-red">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="login"
                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70"
            >
                <span wire:loading.remove wire:target="login">Se connecter</span>
                <span wire:loading.flex wire:target="login" class="hidden items-center gap-2">
                    <x-spinner :size="16" />
                    Connexion...
                </span>
            </button>
        </form>
    @else
        <div>
            <div class="mb-7 flex items-center gap-2.5">
                <button
                    wire:click="back"
                    type="button"
                    class="flex cursor-pointer rounded-md border-0 bg-transparent p-1 text-app-muted"
                    aria-label="Retour"
                >
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <div>
                    <div class="text-[15px] font-bold text-app-text">
                        Verification TOTP
                    </div>
                    <div class="mt-0.5 text-xs text-app-muted">
                        Entrez le code de votre app d'authentification
                    </div>
                </div>
            </div>

            <form wire:submit="verifyMfa">
                <div class="mb-6">
                    <label class="mb-3 block text-center text-xs font-semibold uppercase tracking-[0.03em] text-app-muted">
                        Code TOTP a 6 chiffres
                    </label>

                    <input
                        type="text"
                        wire:model.live="totpCode"
                        inputmode="numeric"
                        pattern="\d*"
                        maxlength="6"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        class="w-full rounded-xl border-2 border-app-border bg-app-surface p-[18px] text-center font-mono text-[28px] font-bold tracking-[0.3em] text-app-text outline-none transition-colors focus:border-app-accent"
                    />

                    @error('totpCode')
                        <p class="mt-2 text-center text-[11px] text-app-red">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-center text-[11px] text-app-muted">
                        Challenge valable 5 minutes.
                    </p>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="verifyMfa"
                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-[10px] border-0 bg-app-accent p-3.5 text-[15px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span wire:loading.remove wire:target="verifyMfa">Verifier</span>
                    <span wire:loading.flex wire:target="verifyMfa" class="hidden items-center gap-2">
                        <x-spinner :size="16" />
                        Verification...
                    </span>
                </button>
            </form>
        </div>
    @endif

    <p class="mt-8 text-center text-[11px] text-app-muted opacity-70">
        Lipa Agent Portal &middot; Acces securise
    </p>
</div>
