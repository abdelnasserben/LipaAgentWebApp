// Agent Dashboard Page

function AgentDashboardPage({ onNavigate: _onNavigate }) {
  // Wrap to support optional tab parameter for operations page
  const onNavigate = (page, tab) => _onNavigate(page, tab);
  const profile = window.AGENT_PROFILE;
  const balance = window.AGENT_BALANCE;
  const summary = window.AGENT_SUMMARY;
  const transactions = window.AGENT_TRANSACTIONS;
  const [balanceVisible, setBalanceVisible] = React.useState(true);

  const recentTxns = transactions.slice(0, 5);
  const floatLow = balance.availableBalance < profile.floatAlertThreshold;

  return (
    <div style={{ flex: 1, overflowY: 'auto', background: 'var(--bg)' }}>
      {/* Balance hero card */}
      <div style={{
        background: 'var(--sidebar-bg)',
        padding: '24px 20px 28px',
        position: 'relative', overflow: 'hidden',
      }}>
        {/* Subtle grid texture */}
        <div style={{
          position: 'absolute', inset: 0, opacity: 0.04,
          backgroundImage: 'linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px)',
          backgroundSize: '24px 24px',
        }} />
        <div style={{ position: 'relative' }}>
          {/* Greeting */}
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 20 }}>
            <div>
              <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.45)', fontWeight: 500, marginBottom: 2 }}>
                {new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })}
              </div>
              <div style={{ fontSize: 16, fontWeight: 700, color: '#fff' }}>
                Bonjour, {profile.fullName.split(' ')[0]} 👋
              </div>
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
              <AgentBadge status={profile.status} />
            </div>
          </div>

          {/* Balance */}
          <div style={{ marginBottom: 20 }}>
            <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: '0.1em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.4)', marginBottom: 8, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <span>Float Balance</span>
              <button onClick={() => setBalanceVisible(v => !v)} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'rgba(255,255,255,0.4)', display: 'flex', padding: 0 }}>
                {balanceVisible ? (
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" strokeWidth="1.3"/>
                    <circle cx="8" cy="8" r="2" stroke="currentColor" strokeWidth="1.3"/>
                  </svg>
                ) : (
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" strokeWidth="1.3"/>
                    <circle cx="8" cy="8" r="2" stroke="currentColor" strokeWidth="1.3"/>
                    <path d="M2 2l12 12" stroke="currentColor" strokeWidth="1.3" strokeLinecap="round"/>
                  </svg>
                )}
              </button>
            </div>
            {balanceVisible ? (
              <div style={{ display: 'flex', alignItems: 'baseline', gap: 8 }}>
                <span style={{ fontSize: 36, fontWeight: 800, color: '#fff', fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em', lineHeight: 1 }}>
                  {balance.availableBalance.toLocaleString('fr-KM')}
                </span>
                <span style={{ fontSize: 14, fontWeight: 500, color: 'rgba(255,255,255,0.5)' }}>KMF</span>
              </div>
            ) : (
              <div style={{ fontSize: 28, fontWeight: 800, color: 'rgba(255,255,255,0.3)', letterSpacing: '0.15em' }}>••••••</div>
            )}
            {balance.frozenBalance > 0 && (
              <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.4)', marginTop: 4 }}>
                + {balance.frozenBalance.toLocaleString('fr-KM')} KMF frozen
              </div>
            )}
          </div>

          {/* Float low warning */}
          {floatLow && (
            <div style={{
              background: 'rgba(234,179,8,0.15)', border: '1px solid rgba(234,179,8,0.3)',
              borderRadius: 8, padding: '8px 12px', marginBottom: 16,
              display: 'flex', alignItems: 'center', gap: 8,
            }}>
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <path d="M7 1L1.5 12h11L7 1z" stroke="rgba(234,179,8,0.9)" strokeWidth="1.3" strokeLinejoin="round"/>
                <path d="M7 5.5v3M7 9.5v.5" stroke="rgba(234,179,8,0.9)" strokeWidth="1.3" strokeLinecap="round"/>
              </svg>
              <span style={{ fontSize: 12, color: 'rgba(234,179,8,0.9)' }}>
                Float balance is below your alert threshold ({profile.floatAlertThreshold.toLocaleString('fr-KM')} KMF)
              </span>
            </div>
          )}

          {/* Today mini stats */}
          <div style={{ display: 'flex', gap: 0, borderTop: '1px solid rgba(255,255,255,0.08)', paddingTop: 16 }}>
            {[
              { label: "Today's In",   value: summary.todayCashIn,      color: 'var(--green)'  },
              { label: "Today's Out",  value: summary.todayCashOut,     color: 'var(--amber)'  },
              { label: 'Transactions', value: summary.todayTransactions, color: '#fff', raw: true },
            ].map((item, i) => (
              <div key={item.label} style={{ flex: 1, paddingLeft: i > 0 ? 12 : 0, borderLeft: i > 0 ? '1px solid rgba(255,255,255,0.08)' : 'none', marginLeft: i > 0 ? 12 : 0 }}>
                <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.4)', fontWeight: 500, marginBottom: 4, letterSpacing: '0.03em' }}>{item.label}</div>
                <div style={{ fontSize: 15, fontWeight: 700, color: item.color, fontFamily: item.raw ? 'inherit' : 'DM Mono, monospace', letterSpacing: item.raw ? '-0.01em' : '-0.02em' }}>
                  {item.raw ? item.value : item.value.toLocaleString('fr-KM')}
                  {!item.raw && <span style={{ fontSize: 10, fontWeight: 400, color: 'rgba(255,255,255,0.35)', marginLeft: 3 }}>KMF</span>}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Content */}
      <div style={{ padding: '16px 16px 8px' }}>
        {/* Today's commission callout */}
        <div onClick={() => onNavigate('commission')} style={{
          background: 'var(--accent-bg)', border: '1px solid var(--accent)',
          borderRadius: 10, padding: '14px 16px', marginBottom: 16,
          display: 'flex', alignItems: 'center', justifyContent: 'space-between', cursor: 'pointer',
        }}>
          <div>
            <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.05em', textTransform: 'uppercase', color: 'var(--accent)', marginBottom: 4 }}>
              Today's Commission
            </div>
            <div style={{ fontSize: 22, fontWeight: 800, color: 'var(--accent)', fontFamily: 'DM Mono, monospace', letterSpacing: '-0.02em' }}>
              {summary.todayCommission.toLocaleString('fr-KM')}
              <span style={{ fontSize: 12, fontWeight: 500, marginLeft: 6, opacity: 0.7 }}>KMF</span>
            </div>
          </div>
          <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 4 }}>
            <div style={{ fontSize: 11, color: 'var(--accent)', fontWeight: 500 }}>
              This month: {summary.monthlyCommission.toLocaleString('fr-KM')} KMF
            </div>
            <div style={{ color: 'var(--accent)', fontSize: 12, fontWeight: 700, display: 'flex', alignItems: 'center', gap: 4 }}>
              View all
              <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                <path d="M4.5 3l3 3-3 3" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </div>
          </div>
        </div>

        {/* Recent transactions */}
        <div style={{ marginBottom: 16 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
            <SectionLabel>Recent Transactions</SectionLabel>
            <button onClick={() => onNavigate('transactions')} style={{ background: 'none', border: 'none', cursor: 'pointer', fontSize: 12, color: 'var(--accent)', fontFamily: 'inherit', fontWeight: 600 }}>
              View all →
            </button>
          </div>

          <AgentCard pad={0}>
            {recentTxns.map((txn, i) => (
              <div
                key={txn.id}
                style={{
                  display: 'flex', alignItems: 'center', gap: 12,
                  padding: '12px 16px',
                  borderBottom: i < recentTxns.length - 1 ? '1px solid var(--border-color)' : 'none',
                  cursor: 'pointer',
                }}
                onClick={() => onNavigate('transactions')}
                onMouseEnter={e => e.currentTarget.style.background = 'var(--row-hover)'}
                onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
              >
                {/* Icon */}
                <div style={{
                  width: 36, height: 36, borderRadius: 10, flexShrink: 0,
                  background: txn.type === 'DEPOSIT' ? 'var(--green-bg)' : 'var(--amber-bg)',
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                  {txn.type === 'DEPOSIT' ? (
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <path d="M8 12V4M5 7l3-3 3 3" stroke="var(--green)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  ) : (
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <path d="M8 4v8M5 9l3 3 3-3" stroke="var(--amber)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                  )}
                </div>
                {/* Info */}
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', marginBottom: 2, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {txn.counterpartyName}
                  </div>
                  <div style={{ fontSize: 11, color: 'var(--text-secondary)' }}>
                    <TxnTypePill type={txn.type} />
                    <span style={{ marginLeft: 6, fontFamily: 'DM Mono, monospace' }}>{fmtTime(txn.createdAt)}</span>
                  </div>
                </div>
                {/* Amount */}
                <div style={{ textAlign: 'right', flexShrink: 0 }}>
                  <div style={{ fontSize: 13, fontWeight: 700, color: txn.type === 'DEPOSIT' ? 'var(--green)' : 'var(--text-primary)', fontFamily: 'DM Mono, monospace' }}>
                    {txn.type === 'DEPOSIT' ? '+' : '−'}{txn.amount.toLocaleString('fr-KM')}
                  </div>
                  <div style={{ fontSize: 10, color: 'var(--text-secondary)', marginTop: 2 }}>KMF</div>
                </div>
              </div>
            ))}
          </AgentCard>
        </div>

        {/* Quick actions */}
        <div style={{ marginBottom: 16 }}>
          <SectionLabel>Actions rapides</SectionLabel>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10 }}>
            {[
              {
                key: 'operations', tab: 'cashin',
                label: 'Cash-in client',
                sub: 'Déposer du float',
                color: 'var(--green)', bg: 'var(--green-bg)',
                icon: (
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 14V6M7 9l3-3 3 3" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    <rect x="2" y="4" width="16" height="12" rx="2" stroke="currentColor" strokeWidth="1.5"/>
                  </svg>
                ),
              },
              {
                key: 'operations', tab: 'cashout',
                label: 'Cash-out marchand',
                sub: 'Retrait marchand',
                color: 'var(--amber)', bg: 'var(--amber-bg)',
                icon: (
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 6v8M7 11l3 3 3-3" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                    <rect x="2" y="4" width="16" height="12" rx="2" stroke="currentColor" strokeWidth="1.5"/>
                  </svg>
                ),
              },
              {
                key: 'enroll',
                label: 'Enrôler un client',
                sub: 'Nouveau compte',
                color: 'var(--blue)', bg: 'var(--blue-bg)',
                icon: (
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <circle cx="8" cy="7" r="3.5" stroke="currentColor" strokeWidth="1.5"/>
                    <path d="M2 17c0-3 2.7-5.5 6-5.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                    <path d="M14 12v6M11 15h6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                  </svg>
                ),
              },
              {
                key: 'transactions',
                label: 'Transactions',
                sub: 'Historique',
                color: 'var(--purple)', bg: 'var(--purple-bg)',
                icon: (
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M3 6h14M3 10h9M3 14h6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                    <circle cx="16" cy="14" r="3" stroke="currentColor" strokeWidth="1.5"/>
                  </svg>
                ),
              },
            ].map((action) => (
              <button
                key={action.label}
                onClick={() => onNavigate(action.key, action.tab)}
                style={{
                  background: action.bg,
                  border: `1.5px solid ${action.color}22`,
                  borderRadius: 12, padding: '14px',
                  cursor: 'pointer', fontFamily: 'inherit',
                  textAlign: 'left', transition: 'all 0.15s',
                  display: 'flex', flexDirection: 'column', gap: 8,
                }}
                onMouseEnter={e => { e.currentTarget.style.transform = 'translateY(-1px)'; e.currentTarget.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)'; }}
                onMouseLeave={e => { e.currentTarget.style.transform = 'none'; e.currentTarget.style.boxShadow = 'none'; }}
              >
                <div style={{ color: action.color, display: 'flex' }}>{action.icon}</div>
                <div>
                  <div style={{ fontSize: 12, fontWeight: 700, color: 'var(--text-primary)', lineHeight: 1.2 }}>{action.label}</div>
                  <div style={{ fontSize: 10, color: 'var(--text-secondary)', marginTop: 2 }}>{action.sub}</div>
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Agent info strip */}
        <AgentCard style={{ marginBottom: 16 }}>
          <SectionLabel>Agent Info</SectionLabel>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            {[
              { label: 'Zone',        value: profile.zone },
              { label: 'Contract',    value: profile.contractRef, mono: true },
              { label: 'KYC Level',   value: <AgentBadge status={profile.kycLevel} /> },
              { label: 'Agent Ref',   value: profile.externalRef, mono: true },
            ].map(item => (
              <div key={item.label}>
                <div style={{ fontSize: 10, fontWeight: 600, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--text-secondary)', marginBottom: 3 }}>{item.label}</div>
                <div style={{ fontSize: 12, color: 'var(--text-primary)', fontFamily: item.mono ? 'DM Mono, monospace' : 'inherit' }}>
                  {item.value}
                </div>
              </div>
            ))}
          </div>
        </AgentCard>
      </div>
    </div>
  );
}

Object.assign(window, { AgentDashboardPage });
