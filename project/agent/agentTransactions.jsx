// Agent Transactions Page + Detail

function AgentTransactionsPage() {
  const allTxns = window.AGENT_TRANSACTIONS;
  const [filter, setFilter] = React.useState('ALL'); // ALL | DEPOSIT | WITHDRAWAL | FAILED
  const [search, setSearch] = React.useState('');
  const [selected, setSelected] = React.useState(null);

  const filtered = allTxns.filter(t => {
    if (filter === 'DEPOSIT'    && t.type !== 'DEPOSIT')    return false;
    if (filter === 'WITHDRAWAL' && t.type !== 'WITHDRAWAL') return false;
    if (filter === 'FAILED'     && t.status !== 'FAILED')   return false;
    if (search) {
      const q = search.toLowerCase();
      return t.counterpartyName.toLowerCase().includes(q) ||
             t.id.toLowerCase().includes(q) ||
             t.reference.toLowerCase().includes(q);
    }
    return true;
  });

  // Group by date
  const grouped = filtered.reduce((acc, txn) => {
    const day = fmtDate(txn.createdAt);
    if (!acc[day]) acc[day] = [];
    acc[day].push(txn);
    return acc;
  }, {});

  const filterTabs = [
    { key: 'ALL',        label: 'All' },
    { key: 'DEPOSIT',    label: 'Cash In' },
    { key: 'WITHDRAWAL', label: 'Cash Out' },
    { key: 'FAILED',     label: 'Failed' },
  ];

  return (
    <div style={{ flex: 1, overflowY: 'auto', background: 'var(--bg)' }}>
      {/* Search + filters */}
      <div style={{ background: 'var(--surface)', borderBottom: '1px solid var(--border-color)', padding: '12px 16px' }}>
        {/* Search */}
        <div style={{ position: 'relative', marginBottom: 10 }}>
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-secondary)' }}>
            <circle cx="7" cy="7" r="5" stroke="currentColor" strokeWidth="1.4"/>
            <path d="M11 11l3 3" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round"/>
          </svg>
          <input
            type="text"
            value={search}
            onChange={e => setSearch(e.target.value)}
            placeholder="Search by name or reference…"
            style={{
              width: '100%', padding: '9px 12px 9px 30px',
              border: '1.5px solid var(--border-color)', borderRadius: 8,
              background: 'var(--bg)', color: 'var(--text-primary)',
              fontFamily: 'inherit', fontSize: 13, outline: 'none', boxSizing: 'border-box',
            }}
            onFocus={e => e.target.style.borderColor = 'var(--accent)'}
            onBlur={e => e.target.style.borderColor = 'var(--border-color)'}
          />
        </div>
        {/* Filter tabs */}
        <div style={{ display: 'flex', gap: 6 }}>
          {filterTabs.map(tab => (
            <button key={tab.key} onClick={() => setFilter(tab.key)} style={{
              padding: '5px 12px', borderRadius: 20,
              border: `1.5px solid ${filter === tab.key ? 'var(--accent)' : 'var(--border-color)'}`,
              background: filter === tab.key ? 'var(--accent-bg)' : 'var(--surface)',
              color: filter === tab.key ? 'var(--accent)' : 'var(--text-secondary)',
              fontFamily: 'inherit', fontSize: 12, fontWeight: 600, cursor: 'pointer',
              transition: 'all 0.12s',
            }}>{tab.label}</button>
          ))}
        </div>
      </div>

      {/* Transaction list */}
      <div style={{ padding: '0 0 16px' }}>
        {Object.keys(grouped).length === 0 ? (
          <AgentEmptyState message="No transactions in this period." />
        ) : (
          Object.entries(grouped).map(([day, txns]) => (
            <div key={day}>
              {/* Date header */}
              <div style={{ padding: '12px 16px 6px', fontSize: 11, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--text-secondary)', background: 'var(--bg)' }}>
                {day}
              </div>
              <div style={{ background: 'var(--surface)', borderTop: '1px solid var(--border-color)', borderBottom: '1px solid var(--border-color)' }}>
                {txns.map((txn, i) => (
                  <div
                    key={txn.id}
                    onClick={() => setSelected(txn)}
                    style={{
                      display: 'flex', alignItems: 'center', gap: 12,
                      padding: '13px 16px',
                      borderBottom: i < txns.length - 1 ? '1px solid var(--border-color)' : 'none',
                      cursor: 'pointer', transition: 'background 0.1s',
                    }}
                    onMouseEnter={e => e.currentTarget.style.background = 'var(--row-hover)'}
                    onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                  >
                    {/* Icon */}
                    <div style={{
                      width: 38, height: 38, borderRadius: 10, flexShrink: 0,
                      background: txn.type === 'DEPOSIT' ? 'var(--green-bg)' : txn.status === 'FAILED' ? 'var(--red-bg)' : 'var(--amber-bg)',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}>
                      {txn.status === 'FAILED' ? (
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                          <circle cx="8" cy="8" r="6" stroke="var(--red)" strokeWidth="1.4"/>
                          <path d="M6 6l4 4M10 6l-4 4" stroke="var(--red)" strokeWidth="1.4" strokeLinecap="round"/>
                        </svg>
                      ) : txn.type === 'DEPOSIT' ? (
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                          <path d="M8 11V5M5 8l3-3 3 3" stroke="var(--green)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                      ) : (
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                          <path d="M8 5v6M5 8l3 3 3-3" stroke="var(--amber)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                        </svg>
                      )}
                    </div>
                    {/* Info */}
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', marginBottom: 3, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {txn.counterpartyName}
                      </div>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <TxnTypePill type={txn.type} />
                        <AgentMono size={11}>{fmtTime(txn.createdAt)}</AgentMono>
                      </div>
                    </div>
                    {/* Amount + status */}
                    <div style={{ textAlign: 'right', flexShrink: 0 }}>
                      <div style={{
                        fontSize: 14, fontWeight: 700, fontFamily: 'DM Mono, monospace',
                        color: txn.status === 'FAILED' ? 'var(--red)' : txn.type === 'DEPOSIT' ? 'var(--green)' : 'var(--text-primary)',
                        letterSpacing: '-0.01em',
                      }}>
                        {txn.type === 'DEPOSIT' ? '+' : '−'}{txn.amount.toLocaleString('fr-KM')}
                      </div>
                      <div style={{ marginTop: 3 }}>
                        {txn.status === 'FAILED' || txn.status === 'PENDING' ? (
                          <AgentBadge status={txn.status} />
                        ) : (
                          <AgentMono size={10}>KMF</AgentMono>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          ))
        )}
      </div>

      {/* Transaction Detail Slide-over */}
      <AgentSlideOver open={!!selected} onClose={() => setSelected(null)} title="Transaction Detail">
        {selected && (
          <div>
            {/* Status + amount hero */}
            <div style={{
              background: selected.status === 'FAILED' ? 'var(--red-bg)' : selected.type === 'DEPOSIT' ? 'var(--green-bg)' : 'var(--amber-bg)',
              borderRadius: 12, padding: '20px 20px', marginBottom: 20, textAlign: 'center',
            }}>
              <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 6,
                color: selected.status === 'FAILED' ? 'var(--red)' : selected.type === 'DEPOSIT' ? 'var(--green)' : 'var(--amber)' }}>
                {selected.type === 'DEPOSIT' ? 'Cash In' : 'Cash Out'} · {selected.status}
              </div>
              <div style={{ fontSize: 32, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em',
                color: selected.status === 'FAILED' ? 'var(--red)' : selected.type === 'DEPOSIT' ? 'var(--green)' : 'var(--text-primary)' }}>
                {selected.type === 'DEPOSIT' ? '+' : '−'}{selected.amount.toLocaleString('fr-KM')}
                <span style={{ fontSize: 14, fontWeight: 500, opacity: 0.6, marginLeft: 6 }}>KMF</span>
              </div>
            </div>

            {/* Details */}
            <AgentCard>
              <SectionLabel>Transaction Details</SectionLabel>
              <DetailRow label="Counterparty">{selected.counterpartyName}</DetailRow>
              <DetailRow label="Phone" mono>{selected.counterpartyPhone}</DetailRow>
              <DetailRow label="Reference" mono>{selected.reference}</DetailRow>
              <DetailRow label="Transaction ID" mono>{selected.id}</DetailRow>
              <DetailRow label="Date & Time">{fmtDateTime(selected.createdAt)}</DetailRow>
              <DetailRow label="Status"><AgentBadge status={selected.status} /></DetailRow>
              <DetailRow label="Fee" border={false}>
                <AgentAmount value={selected.fee} size={13} />
              </DetailRow>
            </AgentCard>

            <AgentDivider />

            <AgentCard>
              <SectionLabel>Your Earnings</SectionLabel>
              <DetailRow label="Commission earned" border={false}>
                <span style={{ fontFamily: 'DM Mono, monospace', fontSize: 16, fontWeight: 700, color: 'var(--accent)' }}>
                  +{selected.commission.toLocaleString('fr-KM')} KMF
                </span>
              </DetailRow>
            </AgentCard>
          </div>
        )}
      </AgentSlideOver>
    </div>
  );
}

Object.assign(window, { AgentTransactionsPage });
