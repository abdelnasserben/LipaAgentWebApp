<div>
    <x-api-error-alert :message="$apiError" class="mx-4 mt-4" />

    {{-- Tabs --}}
    <div class="border-b border-app-border bg-app-surface">
        <div class="flex gap-0 overflow-x-auto px-4 md:mx-auto md:max-w-5xl md:px-6">
            @foreach ([['key' => 'profile', 'label' => 'Profil'], ['key' => 'limits', 'label' => 'Limites'], ['key' => 'security', 'label' => 'Sécurité']] as $tab)
                <button wire:click="switchTab('{{ $tab['key'] }}')" type="button" @class([
                    'cursor-pointer whitespace-nowrap border-0 border-b-2 bg-transparent px-[18px] py-3.5 text-[13px] font-semibold transition-colors',
                    'border-app-accent text-app-accent' => $activeTab === $tab['key'],
                    'border-transparent text-app-muted' => $activeTab !== $tab['key'],
                ])>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="p-4 md:mx-auto md:max-w-5xl md:px-6 md:py-6">
        {{-- PROFILE TAB --}}
        @if ($activeTab === 'profile')
            <div class="grid gap-4 md:grid-cols-[1.1fr_0.9fr] md:items-start">
                <div class="space-y-4">
                    {{-- Avatar + name --}}
                    <div class="flex items-center gap-4 rounded-xl border border-app-border bg-app-surface p-5">
                        <div class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-full bg-app-accent-bg">
                            <span class="text-xl font-extrabold text-app-accent">
                                {{ strtoupper(substr($profile['fullName'], 0, 1)) }}
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="text-[17px] font-bold tracking-[-0.02em] text-app-text">
                                {{ $profile['fullName'] }}
                            </div>
                            <div class="mt-0.5 font-mono text-xs text-app-muted">
                                {{ $profile['externalRef'] }}
                            </div>
                        </div>

                        <x-agent-badge :status="$profile['status']" />
                    </div>

                    {{-- Contact details --}}
                    <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                        <div class="border-b border-app-border px-4 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                                Informations de contact
                            </div>
                        </div>

                        <div class="px-4">
                            <x-detail-row label="Téléphone">
                                <span class="font-mono">+{{ $profile['phoneCountryCode'] }} {{ $profile['phoneNumber'] }}</span>
                            </x-detail-row>
                            <x-detail-row label="Zone">{{ $profile['zone'] }}</x-detail-row>
                            <x-detail-row label="Niveau KYC">
                                <x-agent-badge :status="$profile['kycLevel']" />
                            </x-detail-row>
                            <x-detail-row label="Contrat" :mono="true">{{ $profile['contractRef'] ?? '—' }}</x-detail-row>
                            <x-detail-row label="Membre depuis" :border="false">
                                {{ \Carbon\Carbon::parse($profile['createdAt'])->format('d M Y') }}
                            </x-detail-row>
                        </div>
                    </div>
                </div>

                {{-- Permissions/capabilities --}}
                <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                    <div class="border-b border-app-border px-4 py-3">
                        <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                            Capacités
                        </div>
                    </div>

                    <div class="p-4">
                        @php
                            $caps = [
                                ['label' => 'Cash-in client', 'enabled' => $profile['canDoCashIn']],
                                ['label' => 'Cash-out marchand', 'enabled' => $profile['canDoCashOut']],
                                ['label' => 'Vente de cartes NFC', 'enabled' => $profile['canSellCards']],
                            ];
                        @endphp

                        @foreach ($caps as $cap)
                            <div @class([
                                'flex items-center justify-between py-2.5',
                                'border-b border-app-border' => ! $loop->last,
                            ])>
                                <span class="text-[13px] text-app-text">{{ $cap['label'] }}</span>

                                @if ($cap['enabled'])
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-app-green">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="text-app-green">
                                            <circle cx="7" cy="7" r="6" fill="currentColor" fill-opacity="0.12" stroke="currentColor" stroke-width="1.2" />
                                            <path d="M4 7l2 2 4-4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Activé
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-app-muted">
                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="text-app-muted">
                                            <circle cx="7" cy="7" r="6" fill="currentColor" fill-opacity="0.12" stroke="currentColor" stroke-width="1.2" />
                                            <path d="M5 5l4 4M9 5l-4 4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                                        </svg>
                                        Désactivé
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        {{-- LIMITS TAB --}}
        @elseif($activeTab === 'limits')
            @php
                $fmt = fn ($v) => $v === null ? '—' : number_format((int) $v, 0, ',', ' ') . ' KMF';
                $count = fn ($v) => $v === null ? '—' : (string) (int) $v;
            @endphp

            <div class="grid gap-4 md:grid-cols-2 md:items-start">
                <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface md:col-span-2">
                    <div class="border-b border-app-border px-4 py-3">
                        <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                            Profil de limites
                        </div>
                    </div>
                    <div class="px-4">
                        <x-detail-row label="Profil">{{ $limits['profileName'] ?? '—' }}</x-detail-row>
                        <x-detail-row label="Niveau KYC requis">
                            <x-agent-badge :status="$limits['requiredKycLevel'] ?? 'KYC_NONE'" />
                        </x-detail-row>
                        <x-detail-row label="ID Profil" :mono="true" :border="false">
                            {{ $limits['limitProfileId'] ?? '—' }}
                        </x-detail-row>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                    <div class="border-b border-app-border px-4 py-3">
                        <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                            Limites par montant
                        </div>
                    </div>
                    <div class="px-4">
                        <x-detail-row label="Montant min. / opération" :mono="true">
                            {{ $fmt($limits['minTransactionAmount'] ?? null) }}
                        </x-detail-row>
                        <x-detail-row label="Montant max. / opération" :mono="true">
                            {{ $fmt($limits['maxTransactionAmount'] ?? null) }}
                        </x-detail-row>
                        <x-detail-row label="Plafond journalier" :mono="true">
                            {{ $fmt($limits['maxDailyAmount'] ?? null) }}
                        </x-detail-row>
                        <x-detail-row label="Plafond hebdomadaire" :mono="true">
                            {{ $fmt($limits['maxWeeklyAmount'] ?? null) }}
                        </x-detail-row>
                        <x-detail-row label="Plafond mensuel" :mono="true" :border="false">
                            {{ $fmt($limits['maxMonthlyAmount'] ?? null) }}
                        </x-detail-row>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                    <div class="border-b border-app-border px-4 py-3">
                        <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                            Limites en volume
                        </div>
                    </div>
                    <div class="px-4">
                        <x-detail-row label="Nb. max. d'opérations / jour" :mono="true">
                            {{ $count($limits['maxDailyTransactionCount'] ?? null) }}
                        </x-detail-row>
                        <x-detail-row label="Nb. max. d'opérations / mois" :mono="true" :border="false">
                            {{ $count($limits['maxMonthlyTransactionCount'] ?? null) }}
                        </x-detail-row>
                    </div>
                </div>
            </div>

        {{-- SECURITY TAB --}}
        @else
            <div class="grid gap-4 lg:grid-cols-[1fr_1fr] lg:items-start">
                <div class="space-y-4">
                    {{-- Session info --}}
                    <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                        <div class="border-b border-app-border px-4 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                                Session active
                            </div>
                        </div>

                        <div class="px-4">
                            <x-detail-row label="Type d'authentification">OTP / TOTP</x-detail-row>
                            <x-detail-row label="Durée session">8 heures</x-detail-row>
                            <x-detail-row label="Expiration token">{{ now()->addHours(8)->format('H:i') }}</x-detail-row>
                            <x-detail-row label="User-Agent" :border="false">
                                <span class="font-mono text-[11px]">
                                    {{ \Illuminate\Support\Str::limit(request()->userAgent() ?? 'Unknown', 40) }}
                                </span>
                            </x-detail-row>
                        </div>
                    </div>

                    {{-- Sign out --}}
                    <div class="overflow-hidden rounded-xl border border-app-red bg-app-surface">
                        <div class="p-4">
                            <div class="mb-1.5 text-[13px] font-semibold text-app-text">Se déconnecter</div>

                            <p class="mb-3 mt-0 text-xs text-app-muted">
                                Vous serez redirigé vers la page de connexion.
                            </p>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="cursor-pointer rounded-lg border border-app-red bg-app-red-bg px-[18px] py-2.5 text-[13px] font-semibold text-app-red">
                                    Se déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    {{-- TOTP setup --}}
                    <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                        <div class="flex items-center justify-between border-b border-app-border px-4 py-3">
                            <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                                Authentification 2FA (TOTP)
                            </div>

                            @if ($totpEnrolled)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-app-green bg-app-green-bg px-2 py-0.5 text-[10px] font-semibold text-app-green">
                                    <x-agent-icon name="check" :size="12" />
                                    Activé
                                </span>
                            @endif
                        </div>

                        <div class="p-4">
                            @if ($totpSuccess)
                                <div class="mb-3 flex items-center gap-2 rounded-lg border border-app-green bg-app-green-bg px-3 py-2 text-[12px] text-app-green">
                                    <x-agent-icon name="check" :size="14" />
                                    {{ $totpSuccess }}
                                </div>
                            @endif

                            @if ($totpEnrolled)
                                <div class="mb-3 flex items-start gap-2 rounded-lg border border-app-border bg-app-bg px-3 py-2.5 text-[12px] text-app-text">
                                    <x-agent-icon name="check" :size="14" class="mt-0.5 text-app-green" />
                                    <div>
                                        <div class="font-semibold">TOTP déjà configuré</div>
                                        <div class="mt-0.5 text-app-muted">
                                            Vos connexions sont protégées par votre application d'authentification.
                                        </div>
                                    </div>
                                </div>

                                <button wire:click="toggleTotpRevoke" wire:loading.attr="disabled" type="button"
                                    class="cursor-pointer rounded-lg border border-app-red bg-app-red-bg px-[18px] py-2.5 text-[13px] font-semibold text-app-red disabled:opacity-70">
                                    {{ $totpRevokeOpen ? 'Annuler' : 'Révoquer le TOTP' }}
                                </button>
                            @else
                                <p class="mb-3 mt-0 text-[13px] text-app-muted">
                                    Activez l'authentification TOTP pour remplacer les SMS OTP par une app d'authentification
                                    (Google Authenticator, etc.).
                                </p>

                                <button wire:click="toggleTotpSetup" wire:loading.attr="disabled" type="button"
                                    class="cursor-pointer rounded-lg border-0 bg-app-accent px-[18px] py-2.5 text-[13px] font-semibold text-white disabled:opacity-70">
                                    <span wire:loading.remove wire:target="toggleTotpSetup">
                                        {{ $totpSetupOpen ? 'Annuler la configuration' : 'Configurer le TOTP' }}
                                    </span>
                                    <span wire:loading wire:target="toggleTotpSetup">Préparation…</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($totpEnrolled && $totpRevokeOpen)
                        <div class="overflow-hidden rounded-xl border border-app-red bg-app-surface">
                            <div class="border-b border-app-border px-4 py-3">
                                <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-red">
                                    Révoquer le TOTP
                                </div>
                            </div>

                            <div class="p-4">
                                <p class="mb-5 mt-0 text-[13px] leading-relaxed text-app-muted">
                                    Saisissez le code à 6 chiffres généré par votre application d'authentification pour
                                    confirmer la révocation. Après révocation, vos prochaines connexions ne demanderont
                                    plus de code TOTP jusqu'à ce que vous le reconfiguriez.
                                </p>

                                <div class="mb-5">
                                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Code de vérification
                                    </label>

                                    <input type="text" wire:model="totpRevokeCode" inputmode="numeric" maxlength="6"
                                        placeholder="******"
                                        class="box-border w-full rounded-xl border-[1.5px] border-app-border bg-app-bg px-4 py-3 text-center font-mono text-xl tracking-[0.25em] text-app-text outline-none focus:border-app-accent" />

                                    @error('totpRevokeCode')
                                        <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if ($totpError)
                                    <div class="mb-3 flex items-center gap-2 rounded-lg border border-app-red bg-app-red-bg px-3 py-2 text-[12px] text-app-red">
                                        <x-agent-icon name="warning" :size="14" />
                                        {{ $totpError }}
                                    </div>
                                @endif

                                <button wire:click="confirmTotpRevoke" wire:loading.attr="disabled" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-app-red bg-app-red px-5 py-3 text-[14px] font-bold text-white transition-opacity hover:opacity-95 disabled:opacity-70">
                                    <span wire:loading.remove wire:target="confirmTotpRevoke">Confirmer la révocation</span>
                                    <span wire:loading wire:target="confirmTotpRevoke" class="flex items-center gap-2">
                                        <x-spinner :size="14" />
                                        Révocation…
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if (! $totpEnrolled && $totpSetupOpen)
                        <div class="overflow-hidden rounded-xl border border-app-border bg-app-surface">
                            <div class="border-b border-app-border px-4 py-3">
                                <div class="text-[10px] font-bold uppercase tracking-[0.1em] text-app-muted">
                                    Configuration TOTP
                                </div>
                            </div>

                            <div class="p-4">
                                <p class="mb-5 mt-0 text-[13px] leading-relaxed text-app-muted">
                                    Scannez ce QR code avec votre application d'authentification (Google Authenticator,
                                    Authy, 1Password…) puis saisissez le code à 6 chiffres généré pour activer la
                                    protection 2FA.
                                </p>

                                @if (!empty($totpQrCodeSvg))
                                    <div class="mb-5 flex justify-center">
                                        <div class="rounded-2xl border border-app-border bg-app-bg p-4">
                                            <div class="rounded-xl bg-white p-3 shadow-sm">
                                                {!! $totpQrCodeSvg !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($totpQrUri))
                                    <div class="mb-5">
                                        <div class="mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                            URI à scanner ou coller dans l'app
                                        </div>
                                        <div class="break-all rounded-lg border border-app-border bg-app-bg px-3.5 py-3 font-mono text-[11px] text-app-text">
                                            {{ $totpQrUri }}
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($totpSecret))
                                    <div class="mb-5">
                                        <div class="mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                            Clé manuelle
                                        </div>

                                        <div class="rounded-lg border border-app-border bg-app-bg px-3.5 py-3 text-center font-mono text-[13px] tracking-[0.08em] text-app-text">
                                            {{ $totpSecret }}
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-5">
                                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.08em] text-app-muted">
                                        Code de vérification
                                    </label>

                                    <input type="text" wire:model="totpCode" inputmode="numeric" maxlength="6"
                                        placeholder="******"
                                        class="box-border w-full rounded-xl border-[1.5px] border-app-border bg-app-bg px-4 py-3 text-center font-mono text-xl tracking-[0.25em] text-app-text outline-none focus:border-app-accent" />

                                    @error('totpCode')
                                        <p class="mt-1 text-[11px] text-app-red">{{ $message }}</p>
                                    @enderror
                                </div>

                                @if ($totpError)
                                    <div class="mb-3 flex items-center gap-2 rounded-lg border border-app-red bg-app-red-bg px-3 py-2 text-[12px] text-app-red">
                                        <x-agent-icon name="warning" :size="14" />
                                        {{ $totpError }}
                                    </div>
                                @endif

                                <button wire:click="confirmTotpSetup" wire:loading.attr="disabled" type="button"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-0 bg-app-accent px-5 py-3 text-[14px] font-bold text-white transition-opacity hover:opacity-95 disabled:opacity-70">
                                    <span wire:loading.remove wire:target="confirmTotpSetup">Activer le TOTP</span>
                                    <span wire:loading wire:target="confirmTotpSetup" class="flex items-center gap-2">
                                        <x-spinner :size="14" />
                                        Activation…
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
