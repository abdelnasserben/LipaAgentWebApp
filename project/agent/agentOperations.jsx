// ── Agent Operations Page ─────────────────────────────────────────────────────
// Cash-in client  (POST /api/v1/agent/cash-in)
// Cash-out marchand (POST /api/v1/agent/cash-out)

// ── Shared step indicator ─────────────────────────────────────────────────────
function StepIndicator({ steps, current }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 0, marginBottom: 24 }}>
      {steps.map((label, i) => {
        const done    = i < current;
        const active  = i === current;
        const color   = done ? 'var(--accent)' : active ? 'var(--accent)' : 'var(--border-color)';
        const textCol = done || active ? 'var(--accent)' : 'var(--text-secondary)';
        return (
          <React.Fragment key={i}>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', flex: i < steps.length - 1 ? undefined : 1 }}>
              <div style={{
                width: 28, height: 28, borderRadius: '50%', border: `2px solid ${color}`,
                background: done ? 'var(--accent)' : active ? 'var(--accent-bg)' : 'var(--surface)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                transition: 'all 0.2s',
              }}>
                {done ? (
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <path d="M2 6l3 3 5-5" stroke="#fff" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                ) : (
                  <span style={{ fontSize: 11, fontWeight: 700, color: active ? 'var(--accent)' : 'var(--text-secondary)' }}>{i + 1}</span>
                )}
              </div>
              <span style={{ fontSize: 9, fontWeight: 600, marginTop: 4, color: textCol, letterSpacing: '0.04em', textTransform: 'uppercase', whiteSpace: 'nowrap' }}>
                {label}
              </span>
            </div>
            {i < steps.length - 1 && (
              <div style={{ flex: 1, height: 2, background: done ? 'var(--accent)' : 'var(--border-color)', margin: '0 4px', marginBottom: 18, transition: 'background 0.2s' }} />
            )}
          </React.Fragment>
        );
      })}
    </div>
  );
}

// ── Customer lookup search box ────────────────────────────────────────────────
function CustomerLookupField({ onFound }) {
  const [phone, setPhone]     = React.useState('');
  const [loading, setLoading] = React.useState(false);
  const [error, setError]     = React.useState('');

  const handleSearch = () => {
    if (!phone.trim()) { setError('Veuillez saisir un numéro de téléphone'); return; }
    if (!/^\d{4,15}$/.test(phone.trim())) { setError('Numéro invalide (4 à 15 chiffres)'); return; }
    setError(''); setLoading(true);
    // Simulate API: GET /api/v1/agent/lookup
    // Known numbers return a specific mock; any valid number returns a generic active customer
    setTimeout(() => {
      setLoading(false);
      const known = window.AGENT_CUSTOMER_LOOKUP[phone.trim()];
      if (known) {
        onFound(known);
      } else {
        // Fallback: generate a plausible active customer for any valid number
        onFound({
          customerId: 'cust_' + phone.trim(),
          fullName: 'Client +269 ' + phone.trim(),
          phoneCountryCode: '269',
          phoneNumber: phone.trim(),
          status: 'ACTIVE',
          kycLevel: 'KYC_BASIC',
        });
      }
    }, 700);
  };

  return (
    <div>
      <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-secondary)', marginBottom: 16 }}>
        Rechercher le client par numéro de téléphone
      </div>
      <div style={{ display: 'flex', gap: 8 }}>
        <div style={{
          display: 'flex', alignItems: 'center',
          background: 'var(--bg)', border: `1.5px solid ${error ? 'var(--red)' : 'var(--border-color)'}`,
          borderRadius: 8, overflow: 'hidden', flex: 1,
        }}>
          <span style={{
            padding: '0 10px', fontSize: 12, fontWeight: 600,
            color: 'var(--text-secondary)', borderRight: '1px solid var(--border-color)',
            height: '100%', display: 'flex', alignItems: 'center', background: 'var(--surface)',
            whiteSpace: 'nowrap',
          }}>+269</span>
          <input
            type="tel"
            value={phone}
            onChange={e => { setPhone(e.target.value); setError(''); }}
            onKeyDown={e => e.key === 'Enter' && handleSearch()}
            placeholder="3XX XXXX"
            style={{
              flex: 1, padding: '12px 12px', border: 'none', outline: 'none',
              background: 'transparent', fontFamily: 'inherit', fontSize: 15,
              color: 'var(--text-primary)', fontFamily: 'DM Mono, monospace',
            }}
          />
        </div>
        <button
          onClick={handleSearch}
          disabled={loading}
          style={{
            padding: '0 18px', borderRadius: 8, border: 'none',
            background: 'var(--accent)', color: '#fff',
            fontFamily: 'inherit', fontWeight: 700, fontSize: 13,
            cursor: loading ? 'not-allowed' : 'pointer', opacity: loading ? 0.7 : 1,
            display: 'flex', alignItems: 'center', gap: 6, flexShrink: 0,
          }}
        >
          {loading ? <Spinner size={16} /> : (
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <circle cx="7" cy="7" r="5" stroke="currentColor" strokeWidth="1.5"/>
              <path d="M11 11l3 3" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
            </svg>
          )}
          {loading ? 'Recherche…' : 'Chercher'}
        </button>
      </div>
      {error && <p style={{ margin: '8px 0 0', fontSize: 12, color: 'var(--red)' }}>{error}</p>}
    </div>
  );
}

// ── Customer confirmation card ─────────────────────────────────────────────────
function CustomerConfirmCard({ customer, onReset }) {
  const statusColors = {
    ACTIVE:    { color: 'var(--green)',  bg: 'var(--green-bg)',  label: 'Actif'     },
    SUSPENDED: { color: 'var(--amber)',  bg: 'var(--amber-bg)',  label: 'Suspendu'  },
    CLOSED:    { color: 'var(--red)',    bg: 'var(--red-bg)',    label: 'Fermé'     },
    FROZEN:    { color: 'var(--blue)',   bg: 'var(--blue-bg)',   label: 'Gelé'      },
  };
  const st = statusColors[customer.status] || { color: 'var(--text-secondary)', bg: 'var(--border-color)', label: customer.status };
  return (
    <div style={{
      background: 'var(--surface)', border: '1.5px solid var(--accent)',
      borderRadius: 12, padding: '16px', marginTop: 14,
      display: 'flex', alignItems: 'center', gap: 14,
    }}>
      {/* Avatar */}
      <div style={{
        width: 44, height: 44, borderRadius: '50%', background: 'var(--accent-bg)',
        display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
        fontSize: 16, fontWeight: 800, color: 'var(--accent)',
      }}>
        {customer.fullName.charAt(0)}
      </div>
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)', marginBottom: 2 }}>
          {customer.fullName}
        </div>
        <div style={{ fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'DM Mono, monospace' }}>
          +{customer.phoneCountryCode} {customer.phoneNumber}
        </div>
        <div style={{ display: 'flex', gap: 6, marginTop: 6, flexWrap: 'wrap' }}>
          <span style={{ padding: '2px 8px', borderRadius: 4, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.04em', color: st.color, background: st.bg }}>{st.label}</span>
          <span style={{ padding: '2px 8px', borderRadius: 4, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.04em', color: 'var(--blue)', background: 'var(--blue-bg)' }}>{customer.kycLevel.replace('KYC_', '')}</span>
        </div>
      </div>
      <button
        onClick={onReset}
        title="Changer de client"
        style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-secondary)', padding: 6, borderRadius: 6, display: 'flex', flexShrink: 0 }}
      >
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M11 3L3 11M3 3l8 8" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
        </svg>
      </button>
    </div>
  );
}

// ── SUCCESS SCREEN shared ─────────────────────────────────────────────────────
function OperationSuccess({ title, amount, lines, onDone, accentColor }) {
  const col = accentColor || 'var(--green)';
  const bg  = accentColor ? accentColor.replace(')', '-bg)').replace('var(--', 'var(--') : 'var(--green-bg)';
  return (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '32px 20px', gap: 16 }}>
      {/* Checkmark circle */}
      <div style={{
        width: 64, height: 64, borderRadius: '50%', background: bg,
        display: 'flex', alignItems: 'center', justifyContent: 'center',
      }}>
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
          <path d="M5 14l7 7 11-11" stroke={col} strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
      </div>
      <div style={{ textAlign: 'center' }}>
        <div style={{ fontSize: 13, fontWeight: 700, color: col, letterSpacing: '0.05em', textTransform: 'uppercase', marginBottom: 6 }}>{title}</div>
        <div style={{ fontSize: 32, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em', color: 'var(--text-primary)', lineHeight: 1.1 }}>
          {amount.toLocaleString('fr-KM')}
          <span style={{ fontSize: 14, fontWeight: 500, color: 'var(--text-secondary)', marginLeft: 6 }}>KMF</span>
        </div>
      </div>
      {/* Detail lines */}
      <div style={{ width: '100%', background: 'var(--surface)', border: '1px solid var(--border-color)', borderRadius: 12, overflow: 'hidden' }}>
        {lines.map((l, i) => (
          <div key={i} style={{
            display: 'flex', justifyContent: 'space-between', padding: '11px 16px',
            borderBottom: i < lines.length - 1 ? '1px solid var(--border-color)' : 'none',
          }}>
            <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{l.label}</span>
            <span style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-primary)', fontFamily: l.mono ? 'DM Mono, monospace' : 'inherit' }}>{l.value}</span>
          </div>
        ))}
      </div>
      <AgentBtn fullWidth onClick={onDone}>Terminer</AgentBtn>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// CASH-IN PAGE
// Flow: (1) Lookup client → (2) Saisie montant → (3) Confirmation → (4) Succès
// ─────────────────────────────────────────────────────────────────────────────
function CashInPage({ onDone }) {
  const [step, setStep]         = React.useState(0); // 0=lookup 1=amount 2=confirm 3=success
  const [customer, setCustomer] = React.useState(null);
  const [amount, setAmount]     = React.useState('');
  const [amountErr, setAmountErr] = React.useState('');
  const [loading, setLoading]   = React.useState(false);
  const [result, setResult]     = React.useState(null);

  const agentBalance = window.AGENT_BALANCE.availableBalance;

  const STEPS = ['Client', 'Montant', 'Confirmation', 'Succès'];

  // ── fee simulation: 1% ────────────────────────────────────────────────────
  const amt    = parseInt(amount.replace(/\s/g, ''), 10) || 0;
  const fee    = Math.round(amt * 0.01);
  const commission = Math.round(amt * 0.01);
  const total  = amt + fee; // agent débit

  const handleCustomerFound = (c) => {
    if (c.status !== 'ACTIVE') return; // bloqué visuellement plus bas
    setCustomer(c);
    setStep(1);
  };

  const handleAmountNext = () => {
    if (!amt || amt <= 0) { setAmountErr('Montant invalide'); return; }
    if (total > agentBalance) { setAmountErr('Float insuffisant pour ce montant'); return; }
    setAmountErr('');
    setStep(2);
  };

  const handleConfirm = () => {
    setLoading(true);
    // Simulate POST /api/v1/agent/cash-in
    setTimeout(() => {
      setLoading(false);
      setResult({
        transactionId: 'txn_CI_' + Date.now(),
        status: 'COMPLETED',
        requestedAmount: amt,
        feeAmount: fee,
        commissionAmount: commission,
        netAmountToDestination: amt - fee,
        currency: 'KMF',
        completedAt: new Date().toISOString(),
        replayed: false,
      });
      setStep(3);
    }, 1200);
  };

  return (
    <div style={{ flex: 1, overflowY: 'auto' }}>
      <div style={{ padding: '20px 16px' }}>
        <StepIndicator steps={STEPS} current={step} />

        {/* ── Étape 0 : Recherche client ── */}
        {step === 0 && (
          <AgentCard>
            <CustomerLookupField onFound={cust => {
              setCustomer(cust);
              // Affiche la carte de confirmation inline
            }} />
            {customer && (
              <>
                <CustomerConfirmCard customer={customer} onReset={() => setCustomer(null)} />
                {customer.status !== 'ACTIVE' && (
                  <div style={{ marginTop: 12, padding: '10px 14px', background: 'var(--red-bg)', borderRadius: 8, fontSize: 12, color: 'var(--red)', fontWeight: 600 }}>
                    Ce client est {customer.status === 'SUSPENDED' ? 'suspendu' : 'fermé'} — cash-in impossible.
                  </div>
                )}
                {customer.status === 'ACTIVE' && (
                  <div style={{ marginTop: 16 }}>
                    <AgentBtn fullWidth onClick={() => setStep(1)}>
                      Continuer →
                    </AgentBtn>
                  </div>
                )}
              </>
            )}
          </AgentCard>
        )}

        {/* ── Étape 1 : Saisie du montant ── */}
        {step === 1 && customer && (
          <>
            <CustomerConfirmCard customer={customer} onReset={() => { setCustomer(null); setStep(0); }} />
            <div style={{ marginTop: 16 }}>
              <AgentCard>
                <div style={{ marginBottom: 16 }}>
                  <label style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.03em', display: 'block', marginBottom: 8 }}>
                    MONTANT À DÉPOSER (KMF)
                  </label>
                  <div style={{
                    display: 'flex', alignItems: 'center',
                    border: `2px solid ${amountErr ? 'var(--red)' : 'var(--accent)'}`,
                    borderRadius: 10, overflow: 'hidden', background: 'var(--surface)',
                  }}>
                    <input
                      type="number"
                      min="1"
                      value={amount}
                      onChange={e => { setAmount(e.target.value); setAmountErr(''); }}
                      placeholder="0"
                      style={{
                        flex: 1, padding: '16px 16px', border: 'none', outline: 'none',
                        fontFamily: 'DM Mono, monospace', fontSize: 28, fontWeight: 700,
                        color: 'var(--text-primary)', background: 'transparent',
                        textAlign: 'right',
                      }}
                    />
                    <span style={{ padding: '0 16px 0 8px', fontSize: 14, fontWeight: 600, color: 'var(--text-secondary)' }}>KMF</span>
                  </div>
                  {amountErr && <p style={{ margin: '6px 0 0', fontSize: 12, color: 'var(--red)' }}>{amountErr}</p>}
                </div>

                {/* Montant rapides */}
                <div style={{ marginBottom: 16 }}>
                  <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--text-secondary)', marginBottom: 8 }}>Montants rapides</div>
                  <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap' }}>
                    {[5000, 10000, 25000, 50000, 100000].map(v => (
                      <button key={v} onClick={() => { setAmount(String(v)); setAmountErr(''); }} style={{
                        padding: '6px 12px', borderRadius: 20, border: '1.5px solid var(--border-color)',
                        background: amt === v ? 'var(--accent-bg)' : 'var(--surface)',
                        color: amt === v ? 'var(--accent)' : 'var(--text-secondary)',
                        fontFamily: 'DM Mono, monospace', fontSize: 12, fontWeight: 600, cursor: 'pointer',
                        transition: 'all 0.1s',
                      }}>
                        {v.toLocaleString('fr-KM')}
                      </button>
                    ))}
                  </div>
                </div>

                {/* Récap frais */}
                {amt > 0 && (
                  <div style={{ background: 'var(--bg)', borderRadius: 8, padding: '12px 14px', marginBottom: 16 }}>
                    {[
                      { label: 'Montant déposé au client', value: `${amt.toLocaleString('fr-KM')} KMF` },
                      { label: 'Frais (1%)',               value: `${fee.toLocaleString('fr-KM')} KMF` },
                      { label: 'Votre commission',         value: `+${commission.toLocaleString('fr-KM')} KMF`, accent: true },
                      { label: 'Débit de votre float',     value: `${total.toLocaleString('fr-KM')} KMF`, bold: true },
                    ].map((r, i) => (
                      <div key={i} style={{ display: 'flex', justifyContent: 'space-between', padding: '5px 0', borderBottom: i < 3 ? '1px solid var(--border-color)' : 'none' }}>
                        <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{r.label}</span>
                        <span style={{ fontSize: 12, fontWeight: r.bold ? 700 : 600, fontFamily: 'DM Mono, monospace', color: r.accent ? 'var(--accent)' : r.bold ? 'var(--text-primary)' : 'var(--text-secondary)' }}>{r.value}</span>
                      </div>
                    ))}
                  </div>
                )}

                {/* Float check */}
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16, padding: '10px 14px', background: total > agentBalance ? 'var(--red-bg)' : 'var(--green-bg)', borderRadius: 8 }}>
                  <span style={{ fontSize: 12, fontWeight: 600, color: total > agentBalance ? 'var(--red)' : 'var(--green)' }}>Float disponible</span>
                  <span style={{ fontSize: 12, fontWeight: 700, fontFamily: 'DM Mono, monospace', color: total > agentBalance ? 'var(--red)' : 'var(--green)' }}>{agentBalance.toLocaleString('fr-KM')} KMF</span>
                </div>

                <AgentBtn fullWidth onClick={handleAmountNext}>Continuer →</AgentBtn>
              </AgentCard>
            </div>
          </>
        )}

        {/* ── Étape 2 : Confirmation ── */}
        {step === 2 && customer && (
          <AgentCard>
            <div style={{ textAlign: 'center', marginBottom: 20 }}>
              <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--text-secondary)', marginBottom: 8 }}>Récapitulatif cash-in</div>
              <div style={{ fontSize: 38, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em', color: 'var(--green)', lineHeight: 1 }}>
                {amt.toLocaleString('fr-KM')}
                <span style={{ fontSize: 16, fontWeight: 500, color: 'var(--text-secondary)', marginLeft: 8 }}>KMF</span>
              </div>
            </div>

            <div style={{ background: 'var(--bg)', borderRadius: 10, overflow: 'hidden', marginBottom: 20 }}>
              {[
                { label: 'Client',                value: customer.fullName },
                { label: 'Téléphone',             value: `+${customer.phoneCountryCode} ${customer.phoneNumber}`, mono: true },
                { label: 'Montant net au client', value: `${(amt - fee).toLocaleString('fr-KM')} KMF`, mono: true },
                { label: 'Frais',                 value: `${fee.toLocaleString('fr-KM')} KMF`, mono: true },
                { label: 'Commission agent',      value: `+${commission.toLocaleString('fr-KM')} KMF`, mono: true, accent: true },
                { label: 'Débit float',           value: `${total.toLocaleString('fr-KM')} KMF`, mono: true, bold: true },
              ].map((r, i) => (
                <div key={i} style={{ display: 'flex', justifyContent: 'space-between', padding: '11px 16px', borderBottom: i < 5 ? '1px solid var(--border-color)' : 'none' }}>
                  <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{r.label}</span>
                  <span style={{ fontSize: 13, fontWeight: r.bold ? 700 : 600, fontFamily: r.mono ? 'DM Mono, monospace' : 'inherit', color: r.accent ? 'var(--accent)' : r.bold ? 'var(--text-primary)' : 'var(--text-secondary)' }}>{r.value}</span>
                </div>
              ))}
            </div>

            <div style={{ display: 'flex', gap: 10 }}>
              <AgentBtn variant="secondary" onClick={() => setStep(1)}>← Modifier</AgentBtn>
              <button
                onClick={handleConfirm}
                disabled={loading}
                style={{
                  flex: 1, padding: '12px 0', borderRadius: 8, border: 'none',
                  background: 'var(--green)', color: '#fff', fontFamily: 'inherit',
                  fontSize: 14, fontWeight: 700, cursor: loading ? 'not-allowed' : 'pointer',
                  opacity: loading ? 0.7 : 1, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                  transition: 'opacity 0.15s',
                }}
              >
                {loading ? <><Spinner size={16} /> Traitement…</> : '✓ Confirmer le cash-in'}
              </button>
            </div>
          </AgentCard>
        )}

        {/* ── Étape 3 : Succès ── */}
        {step === 3 && result && (
          <OperationSuccess
            title="Cash-in effectué"
            amount={result.requestedAmount}
            lines={[
              { label: 'Client',           value: customer.fullName },
              { label: 'Net au client',    value: `${result.netAmountToDestination.toLocaleString('fr-KM')} KMF`, mono: true },
              { label: 'Frais',            value: `${result.feeAmount.toLocaleString('fr-KM')} KMF`, mono: true },
              { label: 'Commission',       value: `+${result.commissionAmount.toLocaleString('fr-KM')} KMF`, mono: true },
              { label: 'Réf. transaction', value: result.transactionId, mono: true },
              { label: 'Heure',            value: fmtDateTime(result.completedAt) },
            ]}
            accentColor="var(--green)"
            onDone={onDone}
          />
        )}
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// CASH-OUT PAGE
// Flow: (1) Saisie merchantId + montant → (2) Confirmation → (3) Succès / En attente
// ─────────────────────────────────────────────────────────────────────────────
function CashOutPage({ onDone }) {
  const [step, setStep]           = React.useState(0); // 0=saisie 1=confirm 2=result
  const [merchantRef, setMerchantRef] = React.useState('');
  const [merchant, setMerchant]   = React.useState(null);
  const [merchantErr, setMerchantErr] = React.useState('');
  const [amount, setAmount]       = React.useState('');
  const [amountErr, setAmountErr] = React.useState('');
  const [loading, setLoading]     = React.useState(false);
  const [result, setResult]       = React.useState(null);

  const canDoCashOut = window.AGENT_PROFILE.canDoCashOut;
  const STEPS = ['Marchand & Montant', 'Confirmation', 'Résultat'];

  const amt  = parseInt(amount.replace(/\s/g, ''), 10) || 0;
  const fee  = Math.round(amt * 0.005);
  const commission = Math.round(amt * 0.005);

  const handleLookupMerchant = () => {
    if (!merchantRef.trim()) { setMerchantErr('Saisissez une référence marchand'); return; }
    const m = window.AGENT_MERCHANTS[merchantRef.trim()];
    if (!m) { setMerchantErr('Marchand introuvable'); return; }
    setMerchantErr('');
    setMerchant(m);
  };

  const handleNext = () => {
    if (!merchant) { setMerchantErr('Veuillez identifier le marchand'); return; }
    if (!amt || amt <= 0) { setAmountErr('Montant invalide'); return; }
    if (merchant.status !== 'ACTIVE') { setMerchantErr('Ce marchand est inactif'); return; }
    setAmountErr('');
    setStep(1);
  };

  const handleConfirm = () => {
    setLoading(true);
    // Simulate POST /api/v1/agent/cash-out
    // 202 for large amount > 100000 (LARGE_CASH_OUT threshold)
    const large = amt > 100000;
    setTimeout(() => {
      setLoading(false);
      if (large) {
        setResult({
          pending: true,
          approvalId: 'appr_' + Date.now(),
          requestedAmount: amt,
          status: 'PENDING_APPROVAL',
        });
      } else {
        setResult({
          pending: false,
          transactionId: 'txn_CO_' + Date.now(),
          status: 'COMPLETED',
          requestedAmount: amt,
          feeAmount: fee,
          commissionAmount: commission,
          netAmountToDestination: amt - fee,
          currency: 'KMF',
          completedAt: new Date().toISOString(),
        });
      }
      setStep(2);
    }, 1200);
  };

  if (!canDoCashOut) {
    return (
      <div style={{ padding: 20 }}>
        <AgentCard>
          <div style={{ textAlign: 'center', padding: '24px 0' }}>
            <div style={{ fontSize: 14, fontWeight: 600, color: 'var(--red)', marginBottom: 8 }}>Cash-out non autorisé</div>
            <p style={{ fontSize: 13, color: 'var(--text-secondary)' }}>Votre profil agent n'est pas activé pour les opérations cash-out marchand.</p>
          </div>
        </AgentCard>
      </div>
    );
  }

  return (
    <div style={{ flex: 1, overflowY: 'auto' }}>
      <div style={{ padding: '20px 16px' }}>
        <StepIndicator steps={STEPS} current={step} />

        {/* ── Étape 0 : Saisie marchand + montant ── */}
        {step === 0 && (
          <AgentCard>
            {/* Marchand lookup */}
            <div style={{ marginBottom: 20 }}>
              <label style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.03em', display: 'block', marginBottom: 8 }}>RÉFÉRENCE MARCHAND</label>
              <div style={{ display: 'flex', gap: 8 }}>
                <input
                  type="text"
                  value={merchantRef}
                  onChange={e => { setMerchantRef(e.target.value.toUpperCase()); setMerchantErr(''); setMerchant(null); }}
                  onKeyDown={e => e.key === 'Enter' && handleLookupMerchant()}
                  placeholder="MRCH-001"
                  style={{
                    flex: 1, padding: '11px 14px', border: `1.5px solid ${merchantErr ? 'var(--red)' : 'var(--border-color)'}`,
                    borderRadius: 8, background: 'var(--bg)', color: 'var(--text-primary)',
                    fontFamily: 'DM Mono, monospace', fontSize: 14, outline: 'none', boxSizing: 'border-box',
                  }}
                  onFocus={e => e.target.style.borderColor = 'var(--accent)'}
                  onBlur={e => e.target.style.borderColor = merchantErr ? 'var(--red)' : 'var(--border-color)'}
                />
                <button onClick={handleLookupMerchant} style={{
                  padding: '0 16px', borderRadius: 8, border: 'none',
                  background: 'var(--accent)', color: '#fff',
                  fontFamily: 'inherit', fontWeight: 700, fontSize: 13, cursor: 'pointer', flexShrink: 0,
                }}>Vérifier</button>
              </div>
              {merchantErr && <p style={{ margin: '6px 0 0', fontSize: 12, color: 'var(--red)' }}>{merchantErr}</p>}

              {/* Marchands rapides */}
              <div style={{ marginTop: 10 }}>
                <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--text-secondary)', marginBottom: 6 }}>Marchands récents</div>
                <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
                  {Object.values(window.AGENT_MERCHANTS).filter(m => m.status === 'ACTIVE').map(m => (
                    <button key={m.merchantId} onClick={() => { setMerchantRef(m.externalRef); setMerchant(m); setMerchantErr(''); }} style={{
                      padding: '5px 12px', borderRadius: 20,
                      border: `1.5px solid ${merchant?.merchantId === m.merchantId ? 'var(--accent)' : 'var(--border-color)'}`,
                      background: merchant?.merchantId === m.merchantId ? 'var(--accent-bg)' : 'var(--surface)',
                      color: merchant?.merchantId === m.merchantId ? 'var(--accent)' : 'var(--text-secondary)',
                      fontFamily: 'inherit', fontSize: 12, fontWeight: 600, cursor: 'pointer', transition: 'all 0.1s',
                    }}>{m.businessName}</button>
                  ))}
                </div>
              </div>
            </div>

            {/* Carte marchand confirmé */}
            {merchant && (
              <div style={{ background: 'var(--accent-bg)', border: '1.5px solid var(--accent)', borderRadius: 10, padding: '12px 14px', marginBottom: 20, display: 'flex', alignItems: 'center', gap: 12 }}>
                <div style={{ width: 38, height: 38, borderRadius: 9, background: 'var(--accent)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <rect x="2" y="4" width="14" height="10" rx="2" stroke="white" strokeWidth="1.4"/>
                    <path d="M2 7h14" stroke="white" strokeWidth="1.4"/>
                    <path d="M5 11h2M9 11h4" stroke="white" strokeWidth="1.4" strokeLinecap="round"/>
                  </svg>
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--text-primary)' }}>{merchant.businessName}</div>
                  <div style={{ fontSize: 11, color: 'var(--text-secondary)', fontFamily: 'DM Mono, monospace' }}>{merchant.externalRef}</div>
                </div>
                <span style={{ padding: '2px 8px', borderRadius: 4, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', color: 'var(--green)', background: 'var(--green-bg)' }}>Actif</span>
              </div>
            )}

            {/* Montant */}
            <div style={{ marginBottom: 16 }}>
              <label style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.03em', display: 'block', marginBottom: 8 }}>MONTANT DU CASH-OUT (KMF)</label>
              <div style={{
                display: 'flex', alignItems: 'center',
                border: `2px solid ${amountErr ? 'var(--red)' : amt > 0 ? 'var(--accent)' : 'var(--border-color)'}`,
                borderRadius: 10, overflow: 'hidden', background: 'var(--surface)',
                transition: 'border-color 0.15s',
              }}>
                <input
                  type="number"
                  min="1"
                  value={amount}
                  onChange={e => { setAmount(e.target.value); setAmountErr(''); }}
                  placeholder="0"
                  style={{
                    flex: 1, padding: '16px 16px', border: 'none', outline: 'none',
                    fontFamily: 'DM Mono, monospace', fontSize: 28, fontWeight: 700,
                    color: 'var(--text-primary)', background: 'transparent', textAlign: 'right',
                  }}
                />
                <span style={{ padding: '0 16px 0 8px', fontSize: 14, fontWeight: 600, color: 'var(--text-secondary)' }}>KMF</span>
              </div>
              {amountErr && <p style={{ margin: '6px 0 0', fontSize: 12, color: 'var(--red)' }}>{amountErr}</p>}

              {/* Quick amounts */}
              <div style={{ display: 'flex', gap: 8, marginTop: 10, flexWrap: 'wrap' }}>
                {[10000, 25000, 50000, 100000, 150000].map(v => (
                  <button key={v} onClick={() => { setAmount(String(v)); setAmountErr(''); }} style={{
                    padding: '6px 12px', borderRadius: 20, border: '1.5px solid var(--border-color)',
                    background: amt === v ? 'var(--accent-bg)' : 'var(--surface)',
                    color: amt === v ? 'var(--accent)' : 'var(--text-secondary)',
                    fontFamily: 'DM Mono, monospace', fontSize: 12, fontWeight: 600, cursor: 'pointer',
                  }}>
                    {v.toLocaleString('fr-KM')}
                  </button>
                ))}
              </div>
            </div>

            {/* Large cash-out warning */}
            {amt > 100000 && (
              <div style={{ padding: '10px 14px', background: 'var(--amber-bg)', border: '1px solid var(--amber)', borderRadius: 8, marginBottom: 16, display: 'flex', gap: 10, alignItems: 'flex-start' }}>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style={{ flexShrink: 0, marginTop: 1 }}>
                  <path d="M8 1.5L1.5 13.5h13L8 1.5z" stroke="var(--amber)" strokeWidth="1.3" strokeLinejoin="round"/>
                  <path d="M8 6v3.5M8 10.5v.5" stroke="var(--amber)" strokeWidth="1.3" strokeLinecap="round"/>
                </svg>
                <span style={{ fontSize: 12, color: 'var(--amber)', fontWeight: 500 }}>
                  Montant &gt; 100 000 KMF — cette opération nécessitera une approbation backoffice (<em>LARGE_CASH_OUT</em>).
                </span>
              </div>
            )}

            {/* Frais preview */}
            {amt > 0 && (
              <div style={{ background: 'var(--bg)', borderRadius: 8, padding: '12px 14px', marginBottom: 16 }}>
                {[
                  { label: 'Montant cash-out marchand', value: `${amt.toLocaleString('fr-KM')} KMF` },
                  { label: 'Frais (0.5%)',              value: `${fee.toLocaleString('fr-KM')} KMF` },
                  { label: 'Commission agent',          value: `+${commission.toLocaleString('fr-KM')} KMF`, accent: true },
                  { label: 'Crédit sur votre float',   value: `+${(amt - fee).toLocaleString('fr-KM')} KMF`, bold: true },
                ].map((r, i) => (
                  <div key={i} style={{ display: 'flex', justifyContent: 'space-between', padding: '5px 0', borderBottom: i < 3 ? '1px solid var(--border-color)' : 'none' }}>
                    <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{r.label}</span>
                    <span style={{ fontSize: 12, fontWeight: r.bold ? 700 : 600, fontFamily: 'DM Mono, monospace', color: r.accent ? 'var(--accent)' : r.bold ? 'var(--green)' : 'var(--text-secondary)' }}>{r.value}</span>
                  </div>
                ))}
              </div>
            )}

            <AgentBtn fullWidth onClick={handleNext}>Continuer →</AgentBtn>
          </AgentCard>
        )}

        {/* ── Étape 1 : Confirmation ── */}
        {step === 1 && merchant && (
          <AgentCard>
            <div style={{ textAlign: 'center', marginBottom: 20 }}>
              <div style={{ fontSize: 12, fontWeight: 700, letterSpacing: '0.06em', textTransform: 'uppercase', color: 'var(--text-secondary)', marginBottom: 8 }}>Récapitulatif cash-out</div>
              <div style={{ fontSize: 38, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em', color: 'var(--amber)', lineHeight: 1 }}>
                {amt.toLocaleString('fr-KM')}
                <span style={{ fontSize: 16, fontWeight: 500, color: 'var(--text-secondary)', marginLeft: 8 }}>KMF</span>
              </div>
              {amt > 100000 && (
                <span style={{ marginTop: 8, display: 'inline-block', padding: '3px 10px', borderRadius: 20, background: 'var(--amber-bg)', color: 'var(--amber)', fontSize: 11, fontWeight: 700 }}>
                  Approbation requise
                </span>
              )}
            </div>

            <div style={{ background: 'var(--bg)', borderRadius: 10, overflow: 'hidden', marginBottom: 20 }}>
              {[
                { label: 'Marchand',              value: merchant.businessName },
                { label: 'Référence',             value: merchant.externalRef, mono: true },
                { label: 'Net reçu par marchand', value: `${(amt - fee).toLocaleString('fr-KM')} KMF`, mono: true },
                { label: 'Frais',                 value: `${fee.toLocaleString('fr-KM')} KMF`, mono: true },
                { label: 'Commission agent',      value: `+${commission.toLocaleString('fr-KM')} KMF`, mono: true, accent: true },
                { label: 'Crédit votre float',    value: `+${(amt - fee).toLocaleString('fr-KM')} KMF`, mono: true, bold: true },
              ].map((r, i) => (
                <div key={i} style={{ display: 'flex', justifyContent: 'space-between', padding: '11px 16px', borderBottom: i < 5 ? '1px solid var(--border-color)' : 'none' }}>
                  <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{r.label}</span>
                  <span style={{ fontSize: 13, fontWeight: r.bold ? 700 : 600, fontFamily: r.mono ? 'DM Mono, monospace' : 'inherit', color: r.accent ? 'var(--accent)' : r.bold ? 'var(--green)' : 'var(--text-secondary)' }}>{r.value}</span>
                </div>
              ))}
            </div>

            <div style={{ display: 'flex', gap: 10 }}>
              <AgentBtn variant="secondary" onClick={() => setStep(0)}>← Modifier</AgentBtn>
              <button
                onClick={handleConfirm}
                disabled={loading}
                style={{
                  flex: 1, padding: '12px 0', borderRadius: 8, border: 'none',
                  background: 'var(--amber)', color: '#fff', fontFamily: 'inherit',
                  fontSize: 14, fontWeight: 700, cursor: loading ? 'not-allowed' : 'pointer',
                  opacity: loading ? 0.7 : 1, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                }}
              >
                {loading ? <><Spinner size={16} /> Traitement…</> : '✓ Confirmer le cash-out'}
              </button>
            </div>
          </AgentCard>
        )}

        {/* ── Étape 2 : Résultat ── */}
        {step === 2 && result && (
          result.pending ? (
            // PENDING_APPROVAL (202)
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '32px 20px', gap: 16 }}>
              <div style={{ width: 64, height: 64, borderRadius: '50%', background: 'var(--amber-bg)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                  <circle cx="14" cy="14" r="11" stroke="var(--amber)" strokeWidth="2"/>
                  <path d="M14 8v7M14 17v2" stroke="var(--amber)" strokeWidth="2" strokeLinecap="round"/>
                </svg>
              </div>
              <div style={{ textAlign: 'center' }}>
                <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--amber)', letterSpacing: '0.05em', textTransform: 'uppercase', marginBottom: 6 }}>En attente d'approbation</div>
                <div style={{ fontSize: 32, fontWeight: 800, fontFamily: 'DM Mono, monospace', letterSpacing: '-0.03em', lineHeight: 1.1 }}>
                  {result.requestedAmount.toLocaleString('fr-KM')}
                  <span style={{ fontSize: 14, fontWeight: 500, color: 'var(--text-secondary)', marginLeft: 6 }}>KMF</span>
                </div>
              </div>
              <div style={{ width: '100%', background: 'var(--surface)', border: '1px solid var(--border-color)', borderRadius: 12, overflow: 'hidden' }}>
                {[
                  { label: 'Marchand',         value: merchant.businessName },
                  { label: 'Statut',           value: 'PENDING_APPROVAL' },
                  { label: 'Réf. approbation', value: result.approvalId, mono: true },
                ].map((l, i) => (
                  <div key={i} style={{ display: 'flex', justifyContent: 'space-between', padding: '11px 16px', borderBottom: i < 2 ? '1px solid var(--border-color)' : 'none' }}>
                    <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{l.label}</span>
                    <span style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-primary)', fontFamily: l.mono ? 'DM Mono, monospace' : 'inherit' }}>{l.value}</span>
                  </div>
                ))}
              </div>
              <p style={{ fontSize: 12, color: 'var(--text-secondary)', textAlign: 'center', lineHeight: 1.5 }}>
                Le backoffice doit approuver cette opération. Les wallets ne seront mouvementés qu'après approbation.
              </p>
              <AgentBtn fullWidth onClick={onDone}>Terminer</AgentBtn>
            </div>
          ) : (
            <OperationSuccess
              title="Cash-out effectué"
              amount={result.requestedAmount}
              accentColor="var(--amber)"
              lines={[
                { label: 'Marchand',         value: merchant.businessName },
                { label: 'Net marchand',     value: `${result.netAmountToDestination.toLocaleString('fr-KM')} KMF`, mono: true },
                { label: 'Frais',            value: `${result.feeAmount.toLocaleString('fr-KM')} KMF`, mono: true },
                { label: 'Commission',       value: `+${result.commissionAmount.toLocaleString('fr-KM')} KMF`, mono: true },
                { label: 'Réf. transaction', value: result.transactionId, mono: true },
                { label: 'Heure',            value: fmtDateTime(result.completedAt) },
              ]}
              onDone={onDone}
            />
          )
        )}
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// OPERATIONS HUB — page conteneur avec sous-navigation cash-in / cash-out
// ─────────────────────────────────────────────────────────────────────────────
function AgentOperationsPage({ initialTab }) {
  const [tab, setTab] = React.useState(initialTab || 'cashin'); // 'cashin' | 'cashout'
  const [key, setKey] = React.useState(0); // reset the flow

  // Sync if parent changes initialTab (e.g. quick action from dashboard)
  React.useEffect(() => {
    if (initialTab) { setTab(initialTab); setKey(k => k + 1); }
  }, [initialTab]);

  const handleDone = () => setKey(k => k + 1);

  const tabs = [
    { key: 'cashin',  label: 'Cash-in client',    color: 'var(--green)'  },
    { key: 'cashout', label: 'Cash-out marchand',  color: 'var(--amber)'  },
  ];

  return (
    <div style={{ flex: 1, display: 'flex', flexDirection: 'column', overflow: 'hidden' }}>
      {/* Sub-tab bar */}
      <div style={{
        background: 'var(--surface)', borderBottom: '1px solid var(--border-color)',
        padding: '0 16px', display: 'flex', gap: 0, flexShrink: 0,
      }}>
        {tabs.map(t => (
          <button key={t.key} onClick={() => { setTab(t.key); setKey(k => k + 1); }} style={{
            padding: '14px 20px', border: 'none', background: 'none',
            fontFamily: 'inherit', fontSize: 13, fontWeight: tab === t.key ? 700 : 500,
            cursor: 'pointer', color: tab === t.key ? t.color : 'var(--text-secondary)',
            borderBottom: tab === t.key ? `2.5px solid ${t.color}` : '2.5px solid transparent',
            transition: 'all 0.15s', whiteSpace: 'nowrap',
          }}>{t.label}</button>
        ))}
      </div>

      {/* Content */}
      <div style={{ flex: 1, overflowY: 'auto' }}>
        {tab === 'cashin'  && <CashInPage  key={`ci-${key}`} onDone={handleDone} />}
        {tab === 'cashout' && <CashOutPage key={`co-${key}`} onDone={handleDone} />}
      </div>
    </div>
  );
}

Object.assign(window, {
  AgentOperationsPage, StepIndicator, CustomerLookupField,
  CustomerConfirmCard, OperationSuccess,
});
