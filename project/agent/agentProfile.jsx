// Agent Profile + Limits Pages

function AgentProfilePage({ onLogout }) {
  const profile = window.AGENT_PROFILE;
  const limits = window.AGENT_LIMITS;
  const [activeTab, setActiveTab] = React.useState('profile'); // 'profile' | 'limits' | 'security'
  const [totpModalOpen, setTotpModalOpen] = React.useState(false);

  const tabs = [
    { key: 'profile',  label: 'Profile' },
    { key: 'limits',   label: 'Limits'  },
    { key: 'security', label: 'Security' },
  ];

  return (
    <div style={{ flex: 1, overflowY: 'auto', background: 'var(--bg)' }}>
      {/* Profile header */}
      <div style={{
        background: 'var(--surface)',
        borderBottom: '1px solid var(--border-color)',
        padding: '20px 20px 0',
      }}>
        {/* Avatar + name */}
        <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 18 }}>
          <div style={{
            width: 54, height: 54, borderRadius: '50%',
            background: 'var(--accent-bg)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            flexShrink: 0,
          }}>
            <span style={{ fontSize: 20, fontWeight: 800, color: 'var(--accent)', letterSpacing: '-0.02em' }}>
              {profile.fullName.split(' ').map(n => n[0]).join('').slice(0, 2)}
            </span>
          </div>
          <div style={{ flex: 1 }}>
            <div style={{ fontSize: 17, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
              {profile.fullName}
            </div>
            <div style={{ fontSize: 12, color: 'var(--text-secondary)', marginTop: 2, display: 'flex', alignItems: 'center', gap: 8 }}>
              <AgentMono size={12}>{profile.externalRef}</AgentMono>
              <span>·</span>
              <span>{profile.zone}</span>
            </div>
            <div style={{ marginTop: 5, display: 'flex', gap: 5 }}>
              <AgentBadge status={profile.status} />
              <AgentBadge status={profile.kycLevel} />
            </div>
          </div>
        </div>

        {/* Tabs */}
        <div style={{ display: 'flex', borderBottom: 'none', gap: 0 }}>
          {tabs.map(tab => (
            <button
              key={tab.key}
              onClick={() => setActiveTab(tab.key)}
              style={{
                flex: 1, background: 'none', border: 'none', cursor: 'pointer',
                padding: '8px 0', fontFamily: 'inherit', fontSize: 13, fontWeight: 600,
                color: activeTab === tab.key ? 'var(--accent)' : 'var(--text-secondary)',
                borderBottom: `2px solid ${activeTab === tab.key ? 'var(--accent)' : 'transparent'}`,
                transition: 'all 0.15s', marginBottom: -1,
              }}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* Tab content */}
      <div style={{ padding: '16px' }}>
        {/* PROFILE TAB */}
        {activeTab === 'profile' && (
          <div>
            <AgentCard style={{ marginBottom: 12 }}>
              <SectionLabel>Contact Information</SectionLabel>
              <DetailRow label="Full Name">{profile.fullName}</DetailRow>
              <DetailRow label="Phone" mono>+{profile.phoneCountryCode} {profile.phoneNumber}</DetailRow>
              <DetailRow label="Zone">{profile.zone}</DetailRow>
              <DetailRow label="Contract" mono border={false}>{profile.contractRef}</DetailRow>
            </AgentCard>

            <AgentCard style={{ marginBottom: 12 }}>
              <SectionLabel>Account Details</SectionLabel>
              <DetailRow label="Agent ID" mono>{profile.id.slice(0, 24)}…</DetailRow>
              <DetailRow label="Reference" mono>{profile.externalRef}</DetailRow>
              <DetailRow label="KYC Level"><AgentBadge status={profile.kycLevel} /></DetailRow>
              <DetailRow label="Status"><AgentBadge status={profile.status} /></DetailRow>
              <DetailRow label="Member since" border={false}>{fmtDate(profile.createdAt)}</DetailRow>
            </AgentCard>

            <AgentCard style={{ marginBottom: 12 }}>
              <SectionLabel>Permissions</SectionLabel>
              {[
                { label: 'Cash In (Deposits)',    enabled: profile.canDoCashIn  },
                { label: 'Cash Out (Withdrawals)',enabled: profile.canDoCashOut },
                { label: 'Sell Cards',            enabled: profile.canSellCards },
              ].map((p, i, arr) => (
                <div key={p.label} style={{
                  display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                  padding: '11px 0',
                  borderBottom: i < arr.length - 1 ? '1px solid var(--border-color)' : 'none',
                }}>
                  <span style={{ fontSize: 13, color: 'var(--text-primary)' }}>{p.label}</span>
                  <span style={{
                    display: 'inline-flex', alignItems: 'center', gap: 5,
                    padding: '2px 8px', borderRadius: 4, fontSize: 11, fontWeight: 600,
                    color: p.enabled ? 'var(--green)' : 'var(--red)',
                    background: p.enabled ? 'var(--green-bg)' : 'var(--red-bg)',
                    letterSpacing: '0.03em', textTransform: 'uppercase',
                  }}>
                    <span style={{ width: 5, height: 5, borderRadius: '50%', background: 'currentColor' }}></span>
                    {p.enabled ? 'Enabled' : 'Disabled'}
                  </span>
                </div>
              ))}
            </AgentCard>

            {/* Logout */}
            <AgentBtn variant="danger" fullWidth onClick={onLogout} size="lg">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style={{ marginRight: 4 }}>
                <path d="M6 14H3a1 1 0 01-1-1V3a1 1 0 011-1h3" stroke="currentColor" strokeWidth="1.3" strokeLinecap="round"/>
                <path d="M10 11l4-3-4-3M14 8H6" stroke="currentColor" strokeWidth="1.3" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              Sign out
            </AgentBtn>
          </div>
        )}

        {/* LIMITS TAB */}
        {activeTab === 'limits' && (
          <div>
            {/* Float */}
            <AgentCard style={{ marginBottom: 12 }}>
              <SectionLabel>Float Balance</SectionLabel>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: 12 }}>
                <div>
                  <div style={{ fontSize: 22, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.02em', color: 'var(--text-primary)' }}>
                    {limits.float.current.toLocaleString('fr-KM')}
                    <span style={{ fontSize: 12, fontWeight: 400, color: 'var(--text-secondary)', marginLeft: 6 }}>KMF</span>
                  </div>
                  <div style={{ fontSize: 11, color: 'var(--text-secondary)', marginTop: 3 }}>
                    Alert threshold: {limits.float.alert.toLocaleString('fr-KM')} KMF
                  </div>
                </div>
                <AgentBadge
                  status={limits.float.current < limits.float.alert ? 'FROZEN' : 'ACTIVE'}
                  custom={limits.float.current < limits.float.alert ? 'Low Float' : 'Healthy'}
                />
              </div>
              <LimitBar used={limits.float.current} limit={limits.float.max} />
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 8 }}>
                <span style={{ fontSize: 11, color: 'var(--text-secondary)' }}>Min: {limits.float.min.toLocaleString('fr-KM')} KMF</span>
                <span style={{ fontSize: 11, color: 'var(--text-secondary)' }}>Max: {limits.float.max.toLocaleString('fr-KM')} KMF</span>
              </div>
            </AgentCard>

            {/* Cash In limits */}
            {[
              { title: 'Cash In Limits', data: limits.cashIn },
              { title: 'Cash Out Limits', data: limits.cashOut },
            ].map(({ title, data }) => (
              <AgentCard key={title} style={{ marginBottom: 12 }}>
                <SectionLabel>{title}</SectionLabel>
                {[
                  { label: 'Daily',   ...data.daily },
                  { label: 'Weekly',  ...data.weekly },
                  { label: 'Monthly', ...data.monthly },
                ].map((item, i, arr) => (
                  <div key={item.label} style={{ marginBottom: i < arr.length - 1 ? 16 : 0 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 6 }}>
                      <span style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)' }}>{item.label}</span>
                      <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>
                        {Math.round((item.used / item.limit) * 100)}% used
                      </span>
                    </div>
                    <LimitBar used={item.used} limit={item.limit} />
                  </div>
                ))}
              </AgentCard>
            ))}
          </div>
        )}

        {/* SECURITY TAB */}
        {activeTab === 'security' && (
          <div>
            <AgentCard style={{ marginBottom: 12 }}>
              <SectionLabel>Authentication</SectionLabel>

              <div style={{
                display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                padding: '14px 0', borderBottom: '1px solid var(--border-color)',
              }}>
                <div>
                  <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', marginBottom: 3 }}>SMS OTP</div>
                  <div style={{ fontSize: 12, color: 'var(--text-secondary)' }}>One-time code via SMS</div>
                </div>
                <AgentBadge status="ACTIVE" custom="Active" />
              </div>

              <div style={{
                display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                padding: '14px 0',
              }}>
                <div>
                  <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', marginBottom: 3 }}>Authenticator App (TOTP)</div>
                  <div style={{ fontSize: 12, color: 'var(--text-secondary)' }}>More secure, works offline</div>
                </div>
                <AgentBtn variant="secondary" size="sm" onClick={() => setTotpModalOpen(true)}>
                  Set up
                </AgentBtn>
              </div>
            </AgentCard>

            <AgentCard style={{ marginBottom: 12 }}>
              <SectionLabel>Session Info</SectionLabel>
              <DetailRow label="Token lifetime">8 hours</DetailRow>
              <DetailRow label="Refresh token">7 days</DetailRow>
              <DetailRow label="Last login" border={false}>{fmtDateTime(new Date(Date.now() - 1000 * 60 * 14).toISOString())}</DetailRow>
            </AgentCard>

            <div style={{
              background: 'var(--blue-bg)', border: '1px solid var(--blue)',
              borderRadius: 10, padding: '12px 14px',
              display: 'flex', alignItems: 'flex-start', gap: 10,
            }}>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style={{ flexShrink: 0, marginTop: 1 }}>
                <circle cx="8" cy="8" r="7" stroke="var(--blue)" strokeWidth="1.3"/>
                <path d="M8 6v2M8 10v.5" stroke="var(--blue)" strokeWidth="1.3" strokeLinecap="round"/>
              </svg>
              <p style={{ margin: 0, fontSize: 12, color: 'var(--blue)', lineHeight: 1.6 }}>
                All sessions are monitored. Contact your supervisor if you notice unusual activity on your account.
              </p>
            </div>
          </div>
        )}
      </div>

      {/* TOTP Setup modal */}
      {totpModalOpen && (
        <>
          <div onClick={() => setTotpModalOpen(false)} style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.4)', zIndex: 300 }} />
          <div style={{
            position: 'fixed', bottom: 0, left: 0, right: 0,
            background: 'var(--surface)', zIndex: 301,
            borderRadius: '16px 16px 0 0',
            padding: '24px 20px 36px',
            maxWidth: 600, margin: '0 auto',
          }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
              <h3 style={{ margin: 0, fontSize: 16, fontWeight: 700, color: 'var(--text-primary)' }}>Set Up Authenticator</h3>
              <button onClick={() => setTotpModalOpen(false)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-secondary)' }}>
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                  <path d="M13 5L5 13M5 5l8 8" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                </svg>
              </button>
            </div>
            {/* QR placeholder */}
            <div style={{
              width: 140, height: 140, margin: '0 auto 16px',
              background: 'var(--bg)', border: '1px solid var(--border-color)',
              borderRadius: 10, display: 'flex', alignItems: 'center', justifyContent: 'center',
            }}>
              <div style={{ textAlign: 'center' }}>
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" style={{ color: 'var(--border-color)', marginBottom: 6 }}>
                  <rect x="4" y="4" width="14" height="14" rx="2" stroke="currentColor" strokeWidth="2"/>
                  <rect x="22" y="4" width="14" height="14" rx="2" stroke="currentColor" strokeWidth="2"/>
                  <rect x="4" y="22" width="14" height="14" rx="2" stroke="currentColor" strokeWidth="2"/>
                  <rect x="7" y="7" width="8" height="8" rx="1" fill="currentColor" fillOpacity=".4"/>
                  <rect x="25" y="7" width="8" height="8" rx="1" fill="currentColor" fillOpacity=".4"/>
                  <rect x="7" y="25" width="8" height="8" rx="1" fill="currentColor" fillOpacity=".4"/>
                  <path d="M25 25h8M25 31h4M29 28v3" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
                </svg>
                <div style={{ fontSize: 9, color: 'var(--text-secondary)', fontFamily: 'DM Mono, monospace' }}>QR code</div>
              </div>
            </div>
            <p style={{ fontSize: 13, color: 'var(--text-secondary)', textAlign: 'center', lineHeight: 1.6, margin: '0 0 20px' }}>
              Scan this QR code with Google Authenticator or Authy to enable TOTP login.
            </p>
            <AgentBtn variant="primary" fullWidth size="lg" onClick={() => setTotpModalOpen(false)}>
              I've scanned the QR code →
            </AgentBtn>
          </div>
        </>
      )}
    </div>
  );
}

Object.assign(window, { AgentProfilePage });
