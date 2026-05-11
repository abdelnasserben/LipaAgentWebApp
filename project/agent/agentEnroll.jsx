// ── Agent Enroll Page ─────────────────────────────────────────────────────────
// Enrôlement client (POST /api/v1/agent/customers/enroll)
// Upload KYC docs  (POST /api/v1/agent/customers/{id}/kyc-documents)
// Vente de carte   (POST /api/v1/agent/card-sell) — optionnel

// ─────────────────────────────────────────────────────────────────────────────
// Champ de sélection de fichier stylisé
// ─────────────────────────────────────────────────────────────────────────────
function FilePickerField({ label, hint, onChange, value }) {
  const inputRef = React.useRef();
  return (
    <div>
      {label && (
        <div style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.03em', marginBottom: 6 }}>{label}</div>
      )}
      <div
        onClick={() => inputRef.current.click()}
        style={{
          border: `2px dashed ${value ? 'var(--accent)' : 'var(--border-color)'}`,
          borderRadius: 10, padding: '16px', cursor: 'pointer',
          background: value ? 'var(--accent-bg)' : 'var(--bg)',
          display: 'flex', alignItems: 'center', gap: 12,
          transition: 'all 0.15s',
        }}
      >
        <div style={{
          width: 36, height: 36, borderRadius: 8, flexShrink: 0,
          background: value ? 'var(--accent)' : 'var(--border-color)',
          display: 'flex', alignItems: 'center', justifyContent: 'center',
        }}>
          {value ? (
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M3 8l4 4 6-6" stroke="#fff" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          ) : (
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M8 2v8M5 5l3-3 3 3" stroke="rgba(0,0,0,0.4)" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"/>
              <path d="M2 12h12" stroke="rgba(0,0,0,0.4)" strokeWidth="1.4" strokeLinecap="round"/>
            </svg>
          )}
        </div>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ fontSize: 13, fontWeight: 600, color: value ? 'var(--accent)' : 'var(--text-primary)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
            {value ? value.name : 'Sélectionner un fichier'}
          </div>
          <div style={{ fontSize: 11, color: 'var(--text-secondary)', marginTop: 2 }}>
            {value ? `${(value.size / 1024).toFixed(0)} Ko` : hint || 'JPG, PNG ou PDF · max 10 Mo'}
          </div>
        </div>
        {value && (
          <button
            onClick={e => { e.stopPropagation(); onChange(null); inputRef.current.value = ''; }}
            style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--text-secondary)', padding: 4, display: 'flex', flexShrink: 0 }}
          >
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <path d="M10.5 3.5l-7 7M3.5 3.5l7 7" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round"/>
            </svg>
          </button>
        )}
      </div>
      <input
        ref={inputRef}
        type="file"
        accept=".jpg,.jpeg,.png,.pdf"
        style={{ display: 'none' }}
        onChange={e => onChange(e.target.files[0] || null)}
      />
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Sélecteur de type de document KYC
// ─────────────────────────────────────────────────────────────────────────────
const KYC_DOC_TYPES = [
  { value: 'NATIONAL_ID',       label: "Carte d'identité nationale" },
  { value: 'PASSPORT',          label: 'Passeport'                  },
  { value: 'PROOF_OF_ADDRESS',  label: 'Justificatif de domicile'   },
  { value: 'BUSINESS_LICENSE',  label: "Licence commerciale"        },
  { value: 'OTHER',             label: 'Autre document'             },
];

// ─────────────────────────────────────────────────────────────────────────────
// KYC Document upload block (réutilisable)
// ─────────────────────────────────────────────────────────────────────────────
function KycDocBlock({ index, doc, onChange, onRemove, canRemove }) {
  return (
    <div style={{
      background: 'var(--bg)', borderRadius: 10, padding: '14px',
      border: '1px solid var(--border-color)', marginBottom: 10,
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 }}>
        <span style={{ fontSize: 12, fontWeight: 700, color: 'var(--text-secondary)', textTransform: 'uppercase', letterSpacing: '0.06em' }}>
          Document {index + 1}
        </span>
        {canRemove && (
          <button onClick={onRemove} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'var(--red)', fontSize: 11, fontFamily: 'inherit', fontWeight: 600, display: 'flex', alignItems: 'center', gap: 4 }}>
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M9 3L3 9M3 3l6 6" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round"/>
            </svg>
            Retirer
          </button>
        )}
      </div>

      {/* Type */}
      <div style={{ marginBottom: 10 }}>
        <label style={{ fontSize: 11, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.04em', textTransform: 'uppercase', display: 'block', marginBottom: 6 }}>Type</label>
        <select
          value={doc.documentType}
          onChange={e => onChange({ ...doc, documentType: e.target.value })}
          style={{
            width: '100%', padding: '10px 12px', border: '1.5px solid var(--border-color)',
            borderRadius: 8, background: 'var(--surface)', color: 'var(--text-primary)',
            fontFamily: 'inherit', fontSize: 13, outline: 'none', cursor: 'pointer',
            boxSizing: 'border-box',
          }}
          onFocus={e => e.target.style.borderColor = 'var(--accent)'}
          onBlur={e => e.target.style.borderColor = 'var(--border-color)'}
        >
          {KYC_DOC_TYPES.map(t => (
            <option key={t.value} value={t.value}>{t.label}</option>
          ))}
        </select>
      </div>

      {/* Fichier */}
      <FilePickerField
        value={doc.file}
        onChange={file => onChange({ ...doc, file })}
      />
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// ENROLL PAGE — 4 étapes
// 1. Identité client  2. Adresse  3. Documents KYC  4. Succès + vente carte
// ─────────────────────────────────────────────────────────────────────────────
function AgentEnrollPage() {
  const STEPS = ['Identité', 'Adresse', 'Documents KYC', 'Terminé'];
  const [step, setStep] = React.useState(0);

  // ── Formulaire identité ───────────────────────────────────────────────────
  const [form, setForm] = React.useState({
    fullName:        '',
    dateOfBirth:     '',
    phoneCountryCode:'269',
    phoneNumber:     '',
    nationalIdNumber:'',
    nationalIdType:  'NATIONAL_ID',
    addressIsland:   '',
    addressCity:     '',
    addressDistrict: '',
  });
  const [formErrors, setFormErrors] = React.useState({});

  // ── KYC docs ──────────────────────────────────────────────────────────────
  const [docs, setDocs] = React.useState([
    { documentType: 'NATIONAL_ID', file: null },
  ]);

  // ── Soumission enrôlement ─────────────────────────────────────────────────
  const [enrollLoading, setEnrollLoading]   = React.useState(false);
  const [enrollResult,  setEnrollResult]    = React.useState(null); // {customerId, externalRef, walletId}
  const [kycLoading,    setKycLoading]      = React.useState(false);
  const [kycDone,       setKycDone]         = React.useState(false);
  const [kycError,      setKycError]        = React.useState('');

  // ── Vente carte ───────────────────────────────────────────────────────────
  const [showCardSell,  setShowCardSell]    = React.useState(false);
  const [cardSellDone,  setCardSellDone]    = React.useState(false);
  const [cardSellLoading, setCardSellLoading] = React.useState(false);
  const [selectedStock, setSelectedStock]  = React.useState(null);
  const cardPrice = 5000; // prix fixe pour la démo

  const setField = (key, val) => {
    setForm(f => ({ ...f, [key]: val }));
    setFormErrors(e => ({ ...e, [key]: '' }));
  };

  // ── Validation étape 1 ─────────────────────────────────────────────────────
  const validateStep1 = () => {
    const errs = {};
    if (!form.fullName.trim())         errs.fullName        = 'Requis';
    if (!form.dateOfBirth)             errs.dateOfBirth     = 'Requis';
    if (!form.phoneNumber.trim())      errs.phoneNumber     = 'Requis';
    if (!/^\d{4,15}$/.test(form.phoneNumber.trim())) errs.phoneNumber = '4 à 15 chiffres';
    if (!form.nationalIdNumber.trim()) errs.nationalIdNumber = 'Requis';
    setFormErrors(errs);
    return Object.keys(errs).length === 0;
  };

  // ── Soumission enrôlement (étape 2 → 3) ───────────────────────────────────
  const handleEnroll = () => {
    setEnrollLoading(true);
    // Simulate POST /api/v1/agent/customers/enroll
    setTimeout(() => {
      setEnrollLoading(false);
      setEnrollResult({
        customerId:  'cust_NEW_' + Date.now(),
        externalRef: 'CUST-' + String(Math.floor(Math.random() * 90000) + 10000),
        walletId:    'wlt_NEW_' + Date.now(),
      });
      setStep(2);
    }, 1000);
  };

  // ── Upload KYC docs ────────────────────────────────────────────────────────
  const handleKycUpload = () => {
    const hasDocs = docs.some(d => d.file);
    if (!hasDocs) { setKycError('Ajoutez au moins un document.'); return; }
    setKycError('');
    setKycLoading(true);
    // Simulate POST /api/v1/agent/customers/{id}/kyc-documents (per doc)
    setTimeout(() => {
      setKycLoading(false);
      setKycDone(true);
      setStep(3);
    }, 1200);
  };

  // ── Vente carte ────────────────────────────────────────────────────────────
  const handleCardSell = () => {
    if (!selectedStock) return;
    setCardSellLoading(true);
    // Simulate POST /api/v1/agent/card-sell
    setTimeout(() => {
      setCardSellLoading(false);
      setCardSellDone(true);
      setShowCardSell(false);
    }, 900);
  };

  // ── Reset complet ──────────────────────────────────────────────────────────
  const handleReset = () => {
    setStep(0);
    setForm({ fullName:'', dateOfBirth:'', phoneCountryCode:'269', phoneNumber:'', nationalIdNumber:'', nationalIdType:'NATIONAL_ID', addressIsland:'', addressCity:'', addressDistrict:'' });
    setFormErrors({});
    setDocs([{ documentType: 'NATIONAL_ID', file: null }]);
    setEnrollResult(null);
    setKycDone(false);
    setKycError('');
    setShowCardSell(false);
    setCardSellDone(false);
    setSelectedStock(null);
  };

  // ────────────────────────────────────────────────────────────────────────
  const inputStyle = (err) => ({
    width: '100%', padding: '11px 14px', border: `1.5px solid ${err ? 'var(--red)' : 'var(--border-color)'}`,
    borderRadius: 8, background: 'var(--bg)', color: 'var(--text-primary)',
    fontFamily: 'inherit', fontSize: 14, outline: 'none', boxSizing: 'border-box',
    transition: 'border-color 0.15s',
  });

  return (
    <div style={{ flex: 1, overflowY: 'auto' }}>
      <div style={{ padding: '20px 16px' }}>
        <StepIndicator steps={STEPS} current={step} />

        {/* ══ Étape 0 : Identité ══════════════════════════════════════════ */}
        {step === 0 && (
          <AgentCard>
            <SectionLabel>Informations personnelles</SectionLabel>

            <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
              {/* Nom complet */}
              <div>
                <label style={{ fontSize: 11, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.04em', textTransform: 'uppercase', display: 'block', marginBottom: 5 }}>
                  Nom complet <span style={{ color: 'var(--red)' }}>*</span>
                </label>
                <input
                  type="text"
                  value={form.fullName}
                  onChange={e => setField('fullName', e.target.value)}
                  placeholder="Ali Hassan Abdou"
                  style={inputStyle(formErrors.fullName)}
                  onFocus={e => e.target.style.borderColor = 'var(--accent)'}
                  onBlur={e => e.target.style.borderColor = formErrors.fullName ? 'var(--red)' : 'var(--border-color)'}
                />
                {formErrors.fullName && <p style={{ margin: '4px 0 0', fontSize: 11, color: 'var(--red)' }}>{formErrors.fullName}</p>}
              </div>

              {/* Date de naissance */}
              <div>
                <label style={{ fontSize: 11, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.04em', textTransform: 'uppercase', display: 'block', marginBottom: 5 }}>
                  Date de naissance <span style={{ color: 'var(--red)' }}>*</span>
                </label>
                <input
                  type="date"
                  value={form.dateOfBirth}
                  onChange={e => setField('dateOfBirth', e.target.value)}
                  max={new Date().toISOString().split('T')[0]}
                  style={inputStyle(formErrors.dateOfBirth)}
                  onFocus={e => e.target.style.borderColor = 'var(--accent)'}
                  onBlur={e => e.target.style.borderColor = formErrors.dateOfBirth ? 'var(--red)' : 'var(--border-color)'}
                />
                {formErrors.dateOfBirth && <p style={{ margin: '4px 0 0', fontSize: 11, color: 'var(--red)' }}>{formErrors.dateOfBirth}</p>}
              </div>

              {/* Téléphone */}
              <div>
                <label style={{ fontSize: 11, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.04em', textTransform: 'uppercase', display: 'block', marginBottom: 5 }}>
                  Numéro de téléphone <span style={{ color: 'var(--red)' }}>*</span>
                </label>
                <div style={{ display: 'flex', gap: 8 }}>
                  <input
                    type="text"
                    value={form.phoneCountryCode}
                    onChange={e => setField('phoneCountryCode', e.target.value.replace(/\D/g, ''))}
                    placeholder="269"
                    style={{ ...inputStyle(false), width: 70, flexShrink: 0, fontFamily: 'DM Mono, monospace', textAlign: 'center' }}
                    onFocus={e => e.target.style.borderColor = 'var(--accent)'}
                    onBlur={e => e.target.style.borderColor = 'var(--border-color)'}
                  />
                  <div style={{ flex: 1 }}>
                    <input
                      type="tel"
                      value={form.phoneNumber}
                      onChange={e => setField('phoneNumber', e.target.value.replace(/\D/g, ''))}
                      placeholder="3XXXXXXX"
                      style={{ ...inputStyle(formErrors.phoneNumber), fontFamily: 'DM Mono, monospace' }}
                      onFocus={e => e.target.style.borderColor = 'var(--accent)'}
                      onBlur={e => e.target.style.borderColor = formErrors.phoneNumber ? 'var(--red)' : 'var(--border-color)'}
                    />
                    {formErrors.phoneNumber && <p style={{ margin: '4px 0 0', fontSize: 11, color: 'var(--red)' }}>{formErrors.phoneNumber}</p>}
                  </div>
                </div>
              </div>

              {/* Pièce d'identité */}
              <div>
                <label style={{ fontSize: 11, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.04em', textTransform: 'uppercase', display: 'block', marginBottom: 5 }}>
                  Type de pièce <span style={{ color: 'var(--red)' }}>*</span>
                </label>
                <select
                  value={form.nationalIdType}
                  onChange={e => setField('nationalIdType', e.target.value)}
                  style={{ ...inputStyle(false), cursor: 'pointer' }}
                  onFocus={e => e.target.style.borderColor = 'var(--accent)'}
                  onBlur={e => e.target.style.borderColor = 'var(--border-color)'}
                >
                  <option value="NATIONAL_ID">Carte d'identité nationale</option>
                  <option value="PASSPORT">Passeport</option>
                </select>
              </div>

              {/* Numéro pièce */}
              <div>
                <label style={{ fontSize: 11, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.04em', textTransform: 'uppercase', display: 'block', marginBottom: 5 }}>
                  Numéro de la pièce <span style={{ color: 'var(--red)' }}>*</span>
                </label>
                <input
                  type="text"
                  value={form.nationalIdNumber}
                  onChange={e => setField('nationalIdNumber', e.target.value.toUpperCase())}
                  placeholder="KM-00123456"
                  style={{ ...inputStyle(formErrors.nationalIdNumber), fontFamily: 'DM Mono, monospace' }}
                  onFocus={e => e.target.style.borderColor = 'var(--accent)'}
                  onBlur={e => e.target.style.borderColor = formErrors.nationalIdNumber ? 'var(--red)' : 'var(--border-color)'}
                />
                {formErrors.nationalIdNumber && <p style={{ margin: '4px 0 0', fontSize: 11, color: 'var(--red)' }}>{formErrors.nationalIdNumber}</p>}
              </div>
            </div>

            <div style={{ marginTop: 20 }}>
              <AgentBtn fullWidth onClick={() => { if (validateStep1()) setStep(1); }}>
                Continuer →
              </AgentBtn>
            </div>
          </AgentCard>
        )}

        {/* ══ Étape 1 : Adresse ═══════════════════════════════════════════ */}
        {step === 1 && (
          <>
            {/* Résumé identité */}
            <div style={{ background: 'var(--surface)', border: '1px solid var(--border-color)', borderRadius: 10, padding: '12px 16px', marginBottom: 16, display: 'flex', alignItems: 'center', gap: 12 }}>
              <div style={{ width: 38, height: 38, borderRadius: '50%', background: 'var(--accent-bg)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, fontSize: 15, fontWeight: 800, color: 'var(--accent)' }}>
                {form.fullName.charAt(0)}
              </div>
              <div>
                <div style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)' }}>{form.fullName}</div>
                <div style={{ fontSize: 11, color: 'var(--text-secondary)', fontFamily: 'DM Mono, monospace' }}>+{form.phoneCountryCode} {form.phoneNumber}</div>
              </div>
              <button onClick={() => setStep(0)} style={{ marginLeft: 'auto', background: 'none', border: 'none', cursor: 'pointer', color: 'var(--accent)', fontSize: 12, fontWeight: 600, fontFamily: 'inherit' }}>
                Modifier
              </button>
            </div>

            <AgentCard>
              <SectionLabel>Adresse (optionnel)</SectionLabel>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                {[
                  { key: 'addressIsland',   label: 'Île',          placeholder: 'Grande Comore' },
                  { key: 'addressCity',     label: 'Ville',        placeholder: 'Moroni'        },
                  { key: 'addressDistrict', label: 'Quartier/District', placeholder: 'Centre'   },
                ].map(f => (
                  <div key={f.key}>
                    <label style={{ fontSize: 11, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.04em', textTransform: 'uppercase', display: 'block', marginBottom: 5 }}>{f.label}</label>
                    <input
                      type="text"
                      value={form[f.key]}
                      onChange={e => setField(f.key, e.target.value)}
                      placeholder={f.placeholder}
                      style={inputStyle(false)}
                      onFocus={e => e.target.style.borderColor = 'var(--accent)'}
                      onBlur={e => e.target.style.borderColor = 'var(--border-color)'}
                    />
                  </div>
                ))}
              </div>

              <div style={{ display: 'flex', gap: 10, marginTop: 20 }}>
                <AgentBtn variant="secondary" onClick={() => setStep(0)}>← Retour</AgentBtn>
                <button
                  onClick={handleEnroll}
                  disabled={enrollLoading}
                  style={{
                    flex: 1, padding: '12px 0', borderRadius: 8, border: 'none',
                    background: 'var(--accent)', color: '#fff', fontFamily: 'inherit',
                    fontSize: 14, fontWeight: 700, cursor: enrollLoading ? 'not-allowed' : 'pointer',
                    opacity: enrollLoading ? 0.7 : 1, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                  }}
                >
                  {enrollLoading ? <><Spinner size={16} /> Enrôlement…</> : 'Créer le compte client →'}
                </button>
              </div>
            </AgentCard>
          </>
        )}

        {/* ══ Étape 2 : Upload KYC ════════════════════════════════════════ */}
        {step === 2 && enrollResult && (
          <>
            {/* Client créé — bannière succès */}
            <div style={{
              background: 'var(--green-bg)', border: '1.5px solid var(--green)',
              borderRadius: 12, padding: '14px 16px', marginBottom: 16,
              display: 'flex', alignItems: 'center', gap: 12,
            }}>
              <div style={{ width: 36, height: 36, borderRadius: '50%', background: 'var(--green)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M3 8l4 4 6-6" stroke="#fff" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </div>
              <div>
                <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--green)' }}>Compte créé avec succès</div>
                <div style={{ fontSize: 11, color: 'var(--text-secondary)', fontFamily: 'DM Mono, monospace', marginTop: 2 }}>{enrollResult.externalRef} · KYC_BASIC</div>
              </div>
            </div>

            <AgentCard>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 14 }}>
                <SectionLabel>Documents KYC</SectionLabel>
                {docs.length < 3 && (
                  <button onClick={() => setDocs(d => [...d, { documentType: 'NATIONAL_ID', file: null }])} style={{
                    background: 'none', border: 'none', cursor: 'pointer', color: 'var(--accent)',
                    fontSize: 12, fontWeight: 700, fontFamily: 'inherit', display: 'flex', alignItems: 'center', gap: 4,
                  }}>
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                      <path d="M7 2v10M2 7h10" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                    </svg>
                    Ajouter un document
                  </button>
                )}
              </div>

              {docs.map((doc, i) => (
                <KycDocBlock
                  key={i}
                  index={i}
                  doc={doc}
                  onChange={updated => setDocs(d => d.map((x, j) => j === i ? updated : x))}
                  onRemove={() => setDocs(d => d.filter((_, j) => j !== i))}
                  canRemove={docs.length > 1}
                />
              ))}

              {kycError && (
                <p style={{ fontSize: 12, color: 'var(--red)', marginBottom: 12 }}>{kycError}</p>
              )}

              <p style={{ fontSize: 11, color: 'var(--text-secondary)', marginBottom: 14, lineHeight: 1.5 }}>
                Les documents seront soumis en statut <strong>PENDING_REVIEW</strong>. Le backoffice devra les valider pour monter le niveau KYC du client.
              </p>

              <div style={{ display: 'flex', gap: 10 }}>
                <button
                  onClick={() => setStep(3)}
                  style={{
                    padding: '12px 16px', borderRadius: 8,
                    border: '1.5px solid var(--border-color)',
                    background: 'var(--surface)', color: 'var(--text-secondary)',
                    fontFamily: 'inherit', fontSize: 13, fontWeight: 600, cursor: 'pointer',
                    whiteSpace: 'nowrap',
                  }}
                >
                  Passer
                </button>
                <button
                  onClick={handleKycUpload}
                  disabled={kycLoading}
                  style={{
                    flex: 1, padding: '12px 0', borderRadius: 8, border: 'none',
                    background: 'var(--accent)', color: '#fff', fontFamily: 'inherit',
                    fontSize: 14, fontWeight: 700, cursor: kycLoading ? 'not-allowed' : 'pointer',
                    opacity: kycLoading ? 0.7 : 1, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                  }}
                >
                  {kycLoading ? <><Spinner size={16} /> Envoi…</> : 'Envoyer les documents →'}
                </button>
              </div>
            </AgentCard>
          </>
        )}

        {/* ══ Étape 3 : Terminé ═══════════════════════════════════════════ */}
        {step === 3 && enrollResult && (
          <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 16 }}>
            {/* Hero succès */}
            <div style={{
              width: '100%', background: 'var(--surface)', border: '1px solid var(--border-color)',
              borderRadius: 16, padding: '28px 20px', textAlign: 'center',
            }}>
              <div style={{ width: 60, height: 60, borderRadius: '50%', background: 'var(--green-bg)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
                  <path d="M4 13l7 7 11-11" stroke="var(--green)" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </div>
              <div style={{ fontSize: 16, fontWeight: 800, color: 'var(--text-primary)', marginBottom: 4 }}>{form.fullName}</div>
              <div style={{ fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'DM Mono, monospace', marginBottom: 16 }}>{enrollResult.externalRef}</div>

              <div style={{ display: 'flex', gap: 8, justifyContent: 'center', flexWrap: 'wrap' }}>
                <span style={{ padding: '3px 10px', borderRadius: 4, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', color: 'var(--green)', background: 'var(--green-bg)' }}>ACTIF</span>
                <span style={{ padding: '3px 10px', borderRadius: 4, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', color: 'var(--blue)', background: 'var(--blue-bg)' }}>KYC_BASIC</span>
                {kycDone && <span style={{ padding: '3px 10px', borderRadius: 4, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', color: 'var(--purple)', background: 'var(--purple-bg)' }}>KYC DOCS SOUMIS</span>}
                {cardSellDone && <span style={{ padding: '3px 10px', borderRadius: 4, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', color: 'var(--accent)', background: 'var(--accent-bg)' }}>CARTE VENDUE</span>}
              </div>
            </div>

            {/* Récap infos */}
            <div style={{ width: '100%', background: 'var(--surface)', border: '1px solid var(--border-color)', borderRadius: 12, overflow: 'hidden' }}>
              {[
                { label: 'Référence client', value: enrollResult.externalRef, mono: true },
                { label: 'Téléphone',        value: `+${form.phoneCountryCode} ${form.phoneNumber}`, mono: true },
                { label: 'Pièce',            value: `${form.nationalIdType} · ${form.nationalIdNumber}`, mono: true },
                { label: 'Documents KYC',    value: kycDone ? `${docs.length} doc(s) soumis` : 'Non soumis' },
                { label: 'Carte NFC',        value: cardSellDone ? `${selectedStock?.internalCardNumber} vendue` : 'Non vendue' },
              ].map((r, i) => (
                <div key={i} style={{ display: 'flex', justifyContent: 'space-between', padding: '11px 16px', borderBottom: i < 4 ? '1px solid var(--border-color)' : 'none' }}>
                  <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>{r.label}</span>
                  <span style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-primary)', fontFamily: r.mono ? 'DM Mono, monospace' : 'inherit' }}>{r.value}</span>
                </div>
              ))}
            </div>

            {/* Vente carte — si pas encore fait */}
            {!cardSellDone && window.AGENT_PROFILE.canSellCards && (
              <div style={{ width: '100%' }}>
                {!showCardSell ? (
                  <button onClick={() => setShowCardSell(true)} style={{
                    width: '100%', padding: '14px', borderRadius: 12,
                    border: '1.5px dashed var(--accent)', background: 'var(--accent-bg)',
                    color: 'var(--accent)', fontFamily: 'inherit', fontSize: 13, fontWeight: 700,
                    cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10,
                  }}>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                      <rect x="2" y="5" width="16" height="11" rx="2" stroke="currentColor" strokeWidth="1.5"/>
                      <path d="M2 9h16" stroke="currentColor" strokeWidth="1.5"/>
                      <path d="M5 13h2M9 13h4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
                    </svg>
                    Vendre une carte NFC à ce client
                  </button>
                ) : (
                  <AgentCard>
                    <SectionLabel>Stock de cartes disponibles</SectionLabel>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 14 }}>
                      {window.AGENT_CARD_STOCK.filter(c => c.status === 'ASSIGNED_TO_AGENT').map(card => (
                        <div
                          key={card.id}
                          onClick={() => setSelectedStock(card)}
                          style={{
                            padding: '12px 14px', borderRadius: 10, cursor: 'pointer',
                            border: `1.5px solid ${selectedStock?.id === card.id ? 'var(--accent)' : 'var(--border-color)'}`,
                            background: selectedStock?.id === card.id ? 'var(--accent-bg)' : 'var(--surface)',
                            display: 'flex', alignItems: 'center', gap: 12, transition: 'all 0.12s',
                          }}
                        >
                          <div style={{
                            width: 36, height: 36, borderRadius: 8, flexShrink: 0,
                            background: selectedStock?.id === card.id ? 'var(--accent)' : 'var(--border-color)',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                          }}>
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                              <rect x="1" y="4" width="16" height="10" rx="2" stroke={selectedStock?.id === card.id ? '#fff' : 'rgba(0,0,0,0.35)'} strokeWidth="1.4"/>
                              <path d="M1 8h16" stroke={selectedStock?.id === card.id ? '#fff' : 'rgba(0,0,0,0.35)'} strokeWidth="1.4"/>
                            </svg>
                          </div>
                          <div style={{ flex: 1 }}>
                            <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'DM Mono, monospace' }}>{card.internalCardNumber}</div>
                            <div style={{ fontSize: 11, color: 'var(--text-secondary)', marginTop: 2 }}>UID: {card.nfcUid} · {card.batchRef}</div>
                          </div>
                          {selectedStock?.id === card.id && (
                            <div style={{ width: 20, height: 20, borderRadius: '50%', background: 'var(--accent)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                              <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                                <path d="M2 5l2.5 2.5L8 2.5" stroke="#fff" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"/>
                              </svg>
                            </div>
                          )}
                        </div>
                      ))}
                    </div>

                    {selectedStock && (
                      <div style={{ background: 'var(--bg)', borderRadius: 8, padding: '10px 14px', marginBottom: 14 }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                          <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>Prix de la carte</span>
                          <span style={{ fontSize: 13, fontWeight: 700, fontFamily: 'DM Mono, monospace', color: 'var(--text-primary)' }}>{cardPrice.toLocaleString('fr-KM')} KMF</span>
                        </div>
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 4 }}>
                          <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>Débit sur votre float</span>
                          <span style={{ fontSize: 12, fontWeight: 600, fontFamily: 'DM Mono, monospace', color: 'var(--red)' }}>−{cardPrice.toLocaleString('fr-KM')} KMF</span>
                        </div>
                      </div>
                    )}

                    <div style={{ display: 'flex', gap: 10 }}>
                      <AgentBtn variant="secondary" onClick={() => { setShowCardSell(false); setSelectedStock(null); }}>Annuler</AgentBtn>
                      <button
                        onClick={handleCardSell}
                        disabled={!selectedStock || cardSellLoading}
                        style={{
                          flex: 1, padding: '12px 0', borderRadius: 8, border: 'none',
                          background: selectedStock ? 'var(--accent)' : 'var(--border-color)',
                          color: '#fff', fontFamily: 'inherit', fontSize: 14, fontWeight: 700,
                          cursor: (selectedStock && !cardSellLoading) ? 'pointer' : 'not-allowed',
                          opacity: cardSellLoading ? 0.7 : 1,
                          display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                        }}
                      >
                        {cardSellLoading ? <><Spinner size={16} /> Vente…</> : 'Confirmer la vente'}
                      </button>
                    </div>
                  </AgentCard>
                )}
              </div>
            )}

            {/* Carte vendue — confirmation */}
            {cardSellDone && (
              <div style={{
                width: '100%', padding: '14px 16px', background: 'var(--accent-bg)',
                border: '1.5px solid var(--accent)', borderRadius: 12,
                display: 'flex', alignItems: 'center', gap: 12,
              }}>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <rect x="2" y="5" width="16" height="11" rx="2" fill="var(--accent-bg)" stroke="var(--accent)" strokeWidth="1.5"/>
                  <path d="M2 9h16" stroke="var(--accent)" strokeWidth="1.5"/>
                  <path d="M5 13l2 2 5-4" stroke="var(--accent)" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                <div>
                  <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--accent)' }}>Carte vendue et activée</div>
                  <div style={{ fontSize: 11, color: 'var(--text-secondary)', fontFamily: 'DM Mono, monospace' }}>{selectedStock?.internalCardNumber}</div>
                </div>
              </div>
            )}

            {/* Actions finales */}
            <div style={{ width: '100%', display: 'flex', gap: 10 }}>
              <AgentBtn fullWidth onClick={handleReset}>
                Enrôler un autre client
              </AgentBtn>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

Object.assign(window, { AgentEnrollPage, FilePickerField, KycDocBlock });
