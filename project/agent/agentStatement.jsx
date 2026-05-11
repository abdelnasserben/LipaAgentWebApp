// Agent Statement Page

function AgentStatementPage() {
  const statements = window.AGENT_STATEMENTS;
  const balance = window.AGENT_BALANCE;
  const [selected, setSelected] = React.useState(null);

  // Group by date
  const grouped = statements.reduce((acc, s) => {
    const day = fmtDate(s.postedAt);
    if (!acc[day]) acc[day] = [];
    acc[day].push(s);
    return acc;
  }, {});

  const typeLabel = {
    DEPOSIT:      'Cash In',
    WITHDRAWAL:   'Cash Out',
    TRANSFER_IN:  'Transfer In',
    TRANSFER_OUT: 'Transfer Out',
    PAYMENT:      'Payment',
    REVERSAL:     'Reversal',
  };

  return (
    <div style={{ flex: 1, overflowY: 'auto', background: 'var(--bg)' }}>
      {/* Balance summary */}
      <div style={{
        background: 'var(--surface)', borderBottom: '1px solid var(--border-color)',
        padding: '16px 20px',
        display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      }}>
        <div>
          <div style={{ fontSize: 11, fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--text-secondary)', marginBottom: 4 }}>Current Balance</div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: 6 }}>
            <span style={{ fontSize: 24, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em', color: 'var(--text-primary)' }}>
              {balance.availableBalance.toLocaleString('fr-KM')}
            </span>
            <span style={{ fontSize: 12, color: 'var(--text-secondary)', fontWeight: 500 }}>KMF</span>
          </div>
        </div>
        <div style={{ textAlign: 'right' }}>
          <div style={{ fontSize: 11, color: 'var(--text-secondary)', marginBottom: 3 }}>Wallet</div>
          <AgentMono size={11}>{balance.walletId.slice(0, 18)}…</AgentMono>
          <div style={{ marginTop: 4 }}>
            <AgentBadge status={balance.walletStatus} />
          </div>
        </div>
      </div>

      {/* Statement entries */}
      <div style={{ padding: '0 0 16px' }}>
        {Object.entries(grouped).map(([day, entries]) => (
          <div key={day}>
            <div style={{ padding: '12px 16px 6px', fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--text-secondary)' }}>
              {day}
            </div>
            <div style={{ background: 'var(--surface)', borderTop: '1px solid var(--border-color)', borderBottom: '1px solid var(--border-color)' }}>
              {entries.map((entry, i) => {
                const isPositive = entry.amount > 0;
                return (
                  <div
                    key={entry.id}
                    onClick={() => setSelected(entry)}
                    style={{
                      display: 'flex', alignItems: 'center', gap: 12,
                      padding: '13px 16px',
                      borderBottom: i < entries.length - 1 ? '1px solid var(--border-color)' : 'none',
                      cursor: 'pointer', transition: 'background 0.1s',
                    }}
                    onMouseEnter={e => e.currentTarget.style.background = 'var(--row-hover)'}
                    onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                  >
                    {/* Icon */}
                    <div style={{
                      width: 38, height: 38, borderRadius: 10, flexShrink: 0,
                      background: entry.status === 'FAILED' ? 'var(--red-bg)' : isPositive ? 'var(--green-bg)' : 'var(--amber-bg)',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}>
                      {entry.status === 'FAILED' ? (
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
                          <circle cx="8" cy="8" r="6" stroke="var(--red)" strokeWidth="1.4"/>
                          <path d="M6 6l4 4M10 6l-4 4" stroke="var(--red)" strokeWidth="1.4" strokeLinecap="round"/>
                        </svg>
                      ) : isPositive ? (
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
                          <path d="M8 11V5M5 8l3-3 3 3" stroke="var(--green)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                      ) : (
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none">
                          <path d="M8 5v6M5 8l3 3 3-3" stroke="var(--amber)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                      )}
                    </div>

                    {/* Info */}
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', marginBottom: 2, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {entry.counterpartyName}
                      </div>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <span style={{ fontSize: 10, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.03em', textTransform: 'uppercase' }}>
                          {typeLabel[entry.type] || entry.type}
                        </span>
                        <span style={{ color: 'var(--border-color)' }}>·</span>
                        <AgentMono size={10}>{fmtTime(entry.postedAt)}</AgentMono>
                      </div>
                    </div>

                    {/* Amount + balance after */}
                    <div style={{ textAlign: 'right', flexShrink: 0 }}>
                      <div style={{
                        fontSize: 14, fontWeight: 700, fontFamily: 'DM Mono, monospace',
                        color: entry.status === 'FAILED' ? 'var(--red)' : isPositive ? 'var(--green)' : 'var(--text-primary)',
                        letterSpacing: '-0.01em',
                      }}>
                        {isPositive ? '+' : '−'}{Math.abs(entry.amount).toLocaleString('fr-KM')}
                      </div>
                      <div style={{ fontSize: 10, color: 'var(--text-secondary)', marginTop: 2, fontFamily: 'DM Mono, monospace' }}>
                        → {entry.balanceAfter.toLocaleString('fr-KM')}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        ))}
      </div>

      {/* Statement Detail Slide-over */}
      <AgentSlideOver open={!!selected} onClose={() => setSelected(null)} title="Statement Entry">
        {selected && (() => {
          const isPos = selected.amount > 0;
          return (
            <div>
              {/* Hero */}
              <div style={{
                background: isPos ? 'var(--green-bg)' : 'var(--amber-bg)',
                borderRadius: 12, padding: '20px', marginBottom: 20, textAlign: 'center',
              }}>
                <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 6, color: isPos ? 'var(--green)' : 'var(--amber)' }}>
                  {typeLabel[selected.type] || selected.type}
                </div>
                <div style={{ fontSize: 30, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em', color: isPos ? 'var(--green)' : 'var(--text-primary)' }}>
                  {isPos ? '+' : '−'}{Math.abs(selected.amount).toLocaleString('fr-KM')}
                  <span style={{ fontSize: 14, fontWeight: 500, opacity: 0.6, marginLeft: 6 }}>KMF</span>
                </div>
              </div>

              <AgentCard>
                <SectionLabel>Entry Details</SectionLabel>
                <DetailRow label="Counterparty">{selected.counterpartyName}</DetailRow>
                <DetailRow label="Reference" mono>{selected.counterpartyRef}</DetailRow>
                <DetailRow label="Entry ID" mono>{selected.id}</DetailRow>
                <DetailRow label="Posted at">{fmtDateTime(selected.postedAt)}</DetailRow>
                <DetailRow label="Status"><AgentBadge status={selected.status} /></DetailRow>
              </AgentCard>

              <AgentDivider />

              <AgentCard>
                <SectionLabel>Balance Movement</SectionLabel>
                <DetailRow label="Before">
                  <AgentMono size={13} color="var(--text-primary)">{selected.balanceBefore.toLocaleString('fr-KM')} KMF</AgentMono>
                </DetailRow>
                <DetailRow label="Change">
                  <span style={{ fontFamily: 'DM Mono, monospace', fontSize: 13, fontWeight: 600, color: isPos ? 'var(--green)' : 'var(--red)' }}>
                    {isPos ? '+' : '−'}{Math.abs(selected.amount).toLocaleString('fr-KM')} KMF
                  </span>
                </DetailRow>
                <DetailRow label="After" border={false}>
                  <AgentMono size={13} color="var(--text-primary)">{selected.balanceAfter.toLocaleString('fr-KM')} KMF</AgentMono>
                </DetailRow>
              </AgentCard>
            </div>
          );
        })()}
      </AgentSlideOver>
    </div>
  );
}

Object.assign(window, { AgentStatementPage });
