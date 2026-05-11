<div>
    {{-- Tabs --}}
    <div style="background:var(--surface);border-bottom:1px solid var(--border-color);padding:0 16px;display:flex;gap:0;">
        @foreach([['key' => 'profile', 'label' => 'Profil'], ['key' => 'limits', 'label' => 'Limites'], ['key' => 'security', 'label' => 'Sécurité']] as $tab)
            <button wire:click="switchTab('{{ $tab['key'] }}')" type="button"
                style="padding:14px 18px;background:none;border:none;cursor:pointer;font-family:inherit;font-size:13px;font-weight:600;color:{{ $activeTab === $tab['key'] ? 'var(--accent)' : 'var(--text-secondary)' }};border-bottom:2px solid {{ $activeTab === $tab['key'] ? 'var(--accent)' : 'transparent' }};transition:all 0.15s;white-space:nowrap;">
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    <div style="padding:16px;max-width:640px;">

        {{-- PROFILE TAB --}}
        @if($activeTab === 'profile')
            {{-- Avatar + name --}}
            <div style="display:flex;align-items:center;gap:16px;padding:20px;background:var(--surface);border-radius:12px;border:1px solid var(--border-color);margin-bottom:16px;">
                <div style="width:52px;height:52px;border-radius:50%;background:var(--accent-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="font-size:20px;font-weight:800;color:var(--accent);">{{ strtoupper(substr($profile['fullName'], 0, 1)) }}</span>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:17px;font-weight:700;color:var(--text-primary);letter-spacing:-0.02em;">{{ $profile['fullName'] }}</div>
                    <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;font-family:'DM Mono',monospace;">{{ $profile['externalRef'] }}</div>
                </div>
                <x-agent-badge :status="$profile['status']" />
            </div>

            {{-- Contact details --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);margin-bottom:16px;overflow:hidden;">
                <div style="padding:12px 16px;border-bottom:1px solid var(--border-color);">
                    <div style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-secondary);">Informations de contact</div>
                </div>
                <div style="padding:0 16px;">
                    <x-detail-row label="Téléphone">
                        <span style="font-family:'DM Mono',monospace;">+{{ $profile['phoneCountryCode'] }} {{ $profile['phoneNumber'] }}</span>
                    </x-detail-row>
                    <x-detail-row label="Zone">{{ $profile['zone'] }}</x-detail-row>
                    <x-detail-row label="Niveau KYC">
                        <x-agent-badge :status="$profile['kycLevel']" />
                    </x-detail-row>
                    <x-detail-row label="Contrat" :mono="true">{{ $profile['contractRef'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Membre depuis" :border="false">{{ \Carbon\Carbon::parse($profile['createdAt'])->format('d M Y') }}</x-detail-row>
                </div>
            </div>

            {{-- Permissions/capabilities --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;">
                <div style="padding:12px 16px;border-bottom:1px solid var(--border-color);">
                    <div style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-secondary);">Capacités</div>
                </div>
                <div style="padding:16px;">
                    @php
                        $caps = [
                            ['label' => 'Cash-in client', 'enabled' => $profile['canDoCashIn']],
                            ['label' => 'Cash-out marchand', 'enabled' => $profile['canDoCashOut']],
                            ['label' => 'Vente de cartes NFC', 'enabled' => $profile['canSellCards']],
                        ];
                    @endphp
                    @foreach($caps as $cap)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-color);">
                            <span style="font-size:13px;color:var(--text-primary);">{{ $cap['label'] }}</span>
                            @if($cap['enabled'])
                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--green);">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <circle cx="7" cy="7" r="6" fill="var(--green-bg)" stroke="var(--green)" stroke-width="1.2"/>
                                        <path d="M4 7l2 2 4-4" stroke="var(--green)" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Activé
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--text-secondary);">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <circle cx="7" cy="7" r="6" fill="var(--border-color)" stroke="var(--border-color)" stroke-width="1.2"/>
                                        <path d="M5 5l4 4M9 5l-4 4" stroke="var(--text-secondary)" stroke-width="1.3" stroke-linecap="round"/>
                                    </svg>
                                    Désactivé
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        {{-- LIMITS TAB --}}
        @elseif($activeTab === 'limits')
            {{-- Float info --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);padding:16px;margin-bottom:16px;">
                <div style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-secondary);margin-bottom:12px;">Float</div>
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px;">
                    <span style="font-size:13px;color:var(--text-secondary);">Solde actuel</span>
                    <span style="font-family:'DM Mono',monospace;font-size:15px;font-weight:700;color:var(--text-primary);">{{ number_format($limits['float']['current'], 0, ',', ' ') }} KMF</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-secondary);margin-bottom:10px;">
                    <span>Min: {{ number_format($limits['float']['min'], 0, ',', ' ') }} KMF</span>
                    <span>Max: {{ number_format($limits['float']['max'], 0, ',', ' ') }} KMF</span>
                </div>
                @php $floatPct = min(($limits['float']['current'] / $limits['float']['max']) * 100, 100); @endphp
                <div style="height:6px;background:var(--border-color);border-radius:3px;overflow:hidden;">
                    <div style="width:{{ $floatPct }}%;height:100%;background:var(--accent);border-radius:3px;"></div>
                </div>
            </div>

            {{-- Cash-in limits --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;margin-bottom:16px;">
                <div style="padding:12px 16px;border-bottom:1px solid var(--border-color);">
                    <div style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-secondary);">Limites Cash-In</div>
                </div>
                <div style="padding:16px;display:flex;flex-direction:column;gap:16px;">
                    @foreach([['label' => 'Journalier', 'data' => $limits['cashIn']['daily']], ['label' => 'Hebdomadaire', 'data' => $limits['cashIn']['weekly']], ['label' => 'Mensuel', 'data' => $limits['cashIn']['monthly']]] as $period)
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--text-primary);margin-bottom:6px;">{{ $period['label'] }}</div>
                            <x-limit-bar :used="$period['data']['used']" :limit="$period['data']['limit']" />
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Cash-out limits --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;">
                <div style="padding:12px 16px;border-bottom:1px solid var(--border-color);">
                    <div style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-secondary);">Limites Cash-Out</div>
                </div>
                <div style="padding:16px;display:flex;flex-direction:column;gap:16px;">
                    @foreach([['label' => 'Journalier', 'data' => $limits['cashOut']['daily']], ['label' => 'Hebdomadaire', 'data' => $limits['cashOut']['weekly']], ['label' => 'Mensuel', 'data' => $limits['cashOut']['monthly']]] as $period)
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--text-primary);margin-bottom:6px;">{{ $period['label'] }}</div>
                            <x-limit-bar :used="$period['data']['used']" :limit="$period['data']['limit']" />
                        </div>
                    @endforeach
                </div>
            </div>

        {{-- SECURITY TAB --}}
        @else
            {{-- Session info --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;margin-bottom:16px;">
                <div style="padding:12px 16px;border-bottom:1px solid var(--border-color);">
                    <div style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-secondary);">Session active</div>
                </div>
                <div style="padding:0 16px;">
                    <x-detail-row label="Type d'authentification">OTP / TOTP</x-detail-row>
                    <x-detail-row label="Durée session">8 heures</x-detail-row>
                    <x-detail-row label="Expiration token">{{ now()->addHours(8)->format('H:i') }}</x-detail-row>
                    <x-detail-row label="User-Agent" :border="false">
                        <span style="font-family:'DM Mono',monospace;font-size:11px;">{{ \Illuminate\Support\Str::limit(request()->userAgent() ?? 'Unknown', 40) }}</span>
                    </x-detail-row>
                </div>
            </div>

            {{-- TOTP setup --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;margin-bottom:16px;">
                <div style="padding:12px 16px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
                    <div style="font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-secondary);">Authentification 2FA (TOTP)</div>
                </div>
                <div style="padding:16px;">
                    <p style="font-size:13px;color:var(--text-secondary);margin:0 0 12px;">
                        Activez l'authentification TOTP pour remplacer les SMS OTP par une app d'authentification (Google Authenticator, etc.).
                    </p>
                    <button wire:click="toggleTotpSetup" type="button"
                        style="padding:10px 18px;background:var(--accent);color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">
                        Configurer le TOTP
                    </button>
                </div>
            </div>

            @if($totpSetupOpen)
                <div style="background:var(--blue-bg);border:1px solid var(--blue);border-radius:10px;padding:16px;margin-bottom:16px;">
                    <div style="font-size:13px;font-weight:600;color:var(--blue);margin-bottom:8px;">Configuration TOTP</div>
                    <p style="font-size:12px;color:var(--text-secondary);margin:0 0 10px;">
                        En mode production, un QR code sera généré ici. Scannez-le avec votre app d'authentification.
                    </p>
                    <div style="font-family:'DM Mono',monospace;font-size:13px;background:var(--surface);border:1px solid var(--border-color);padding:10px 14px;border-radius:6px;letter-spacing:0.1em;text-align:center;">
                        SECRET-DEMO-TOTP-KEY-123
                    </div>
                </div>
            @endif

            {{-- Sign out --}}
            <div style="background:var(--surface);border-radius:12px;border:1px solid var(--red);overflow:hidden;">
                <div style="padding:16px;">
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:6px;">Se déconnecter</div>
                    <p style="font-size:12px;color:var(--text-secondary);margin:0 0 12px;">
                        Vous serez redirigé vers la page de connexion.
                    </p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            style="padding:10px 18px;background:var(--red-bg);color:var(--red);border:1px solid var(--red);border-radius:8px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;">
                            Se déconnecter
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
