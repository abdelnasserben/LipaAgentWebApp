// Agent Commission Page

function AgentCommissionPage() {
  const commission = window.AGENT_COMMISSION;

  const maxHistory = Math.max(...commission.history.map(h => h.amount));

  return (
    <div style={{ flex: 1, overflowY: 'auto', background: 'var(--bg)', padding: '16px' }}>
      {/* Pending payout hero */}
      <div style={{
        background: 'var(--sidebar-bg)', borderRadius: 14, padding: '20px',
        marginBottom: 16, position: 'relative', overflow: 'hidden',
      }}>
        <div style={{
          position: 'absolute', inset: 0, opacity: 0.04,
          backgroundImage: 'linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px)',
          backgroundSize: '24px 24px',
        }} />
        <div style={{ position: 'relative' }}>
          <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.1em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.4)', marginBottom: 8 }}>
            Pending Payout
          </div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, marginBottom: 16 }}>
            <span style={{ fontSize: 34, fontWeight: 800, color: 'var(--accent)', fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em', lineHeight: 1 }}>
              {commission.pendingPayout.toLocaleString('fr-KM')}
            </span>
            <span style={{ fontSize: 14, color: 'rgba(255,255,255,0.4)', fontWeight: 500 }}>KMF</span>
          </div>

          <div style={{ display: 'flex', gap: 0, borderTop: '1px solid rgba(255,255,255,0.08)', paddingTop: 16 }}>
            {[
              { label: 'Today',      value: commission.today },
              { label: 'This Week',  value: commission.thisWeek },
              { label: 'All Time',   value: commission.totalEarned },
            ].map((item, i) => (
              <div key={item.label} style={{
                flex: 1, paddingLeft: i > 0 ? 12 : 0,
                borderLeft: i > 0 ? '1px solid rgba(255,255,255,0.08)' : 'none',
                marginLeft: i > 0 ? 12 : 0,
              }}>
                <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.35)', fontWeight: 500, marginBottom: 4 }}>{item.label}</div>
                <div style={{ fontSize: 14, fontWeight: 700, color: '#fff', fontFamily: 'DM Mono, monospace', letterSpacing: '-0.02em' }}>
                  {item.value.toLocaleString('fr-KM')}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Last payout */}
      <AgentCard style={{ marginBottom: 16 }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div>
            <SectionLabel>Last Payout</SectionLabel>
            <div style={{ fontSize: 20, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.02em', color: 'var(--text-primary)' }}>
              {commission.lastPayout.amount.toLocaleString('fr-KM')}
              <span style={{ fontSize: 12, fontWeight: 400, color: 'var(--text-secondary)', marginLeft: 6 }}>KMF</span>
            </div>
          </div>
          <div style={{ textAlign: 'right' }}>
            <AgentBadge status="SETTLED" />
            <div style={{ marginTop: 4 }}>
              <AgentMono size={11}>{fmtDate(commission.lastPayout.date)}</AgentMono>
            </div>
            <div style={{ marginTop: 2 }}>
              <AgentMono size={10}>{commission.lastPayout.ref}</AgentMono>
            </div>
          </div>
        </div>
      </AgentCard>

      {/* Breakdown by type */}
      <AgentCard style={{ marginBottom: 16 }}>
        <SectionLabel>This Month's Breakdown</SectionLabel>
        {commission.breakdown.map((item, i) => (
          <div key={item.label} style={{ marginBottom: i < commission.breakdown.length - 1 ? 16 : 0 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: 6 }}>
              <div>
                <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', marginBottom: 2 }}>{item.label}</div>
                <AgentMono size={11}>{item.count} transactions · {item.volume.toLocaleString('fr-KM')} KMF vol.</AgentMono>
              </div>
              <div style={{ fontSize: 16, fontWeight: 800, fontFamily: 'DM Mono, monospace', color: 'var(--accent)', letterSpacing: '-0.02em' }}>
                {item.commission.toLocaleString('fr-KM')}
                <span style={{ fontSize: 10, fontWeight: 400, color: 'var(--text-secondary)', marginLeft: 4 }}>KMF</span>
              </div>
            </div>
            {/* Mini bar */}
            <div style={{ height: 5, background: 'var(--border-color)', borderRadius: 3, overflow: 'hidden' }}>
              <div style={{
                width: `${(item.commission / commission.pendingPayout) * 100}%`,
                height: '100%', background: 'var(--accent)', borderRadius: 3,
              }} />
            </div>
          </div>
        ))}
      </AgentCard>

      {/* Monthly history */}
      <AgentCard style={{ marginBottom: 16 }}>
        <SectionLabel>Monthly History</SectionLabel>
        {/* Bar chart */}
        <div style={{ display: 'flex', alignItems: 'flex-end', gap: 8, height: 80, marginBottom: 8 }}>
          {commission.history.map((month, i) => {
            const h = Math.round((month.amount / maxHistory) * 72);
            const isLatest = i === 0;
            return (
              <div key={month.month} style={{ flex: 1, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 }}>
                <div style={{ fontSize: 9, fontFamily: 'DM Mono, monospace', color: isLatest ? 'var(--accent)' : 'var(--text-secondary)', fontWeight: isLatest ? 700 : 400 }}>
                  {(month.amount / 1000).toFixed(1)}k
                </div>
                <div style={{
                  width: '100%', height: h,
                  background: isLatest ? 'var(--accent)' : 'var(--border-color)',
                  borderRadius: '4px 4px 2px 2px',
                  transition: 'height 0.5s',
                  position: 'relative',
                }}>
                  {month.status === 'PENDING' && (
                    <div style={{
                      position: 'absolute', top: -3, left: '50%', transform: 'translateX(-50%)',
                      width: 6, height: 6, borderRadius: '50%',
                      background: 'var(--amber)',
                    }} />
                  )}
                </div>
              </div>
            );
          })}
        </div>
        {/* Labels */}
        <div style={{ display: 'flex', gap: 8 }}>
          {commission.history.map((month, i) => (
            <div key={month.month} style={{ flex: 1, textAlign: 'center' }}>
              <AgentMono size={9} color={i === 0 ? 'var(--accent)' : 'var(--text-secondary)'}>
                {month.month.split(' ')[0].slice(0, 3)}
              </AgentMono>
            </div>
          ))}
        </div>

        <AgentDivider />

        {/* Table */}
        <div>
          {commission.history.map((month, i) => (
            <div key={month.month} style={{
              display: 'flex', alignItems: 'center', justifyContent: 'space-between',
              padding: '9px 0',
              borderBottom: i < commission.history.length - 1 ? '1px solid var(--border-color)' : 'none',
            }}>
              <div style={{ fontSize: 13, color: 'var(--text-primary)', fontWeight: i === 0 ? 600 : 400 }}>
                {month.month}
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <AgentBadge status={month.status} />
                <span style={{ fontFamily: 'DM Mono, monospace', fontSize: 13, fontWeight: 600, color: i === 0 ? 'var(--accent)' : 'var(--text-primary)' }}>
                  {month.amount.toLocaleString('fr-KM')} <span style={{ fontSize: 10, fontWeight: 400, color: 'var(--text-secondary)' }}>KMF</span>
                </span>
              </div>
            </div>
          ))}
        </div>
      </AgentCard>
    </div>
  );
}

Object.assign(window, { AgentCommissionPage });
