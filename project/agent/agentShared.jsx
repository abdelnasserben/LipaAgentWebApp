// Shared primitives for Lipa Agent Web App
// Reuses the exact same CSS variable system as the BO

const AGENT_STATUS_CONFIG = {
  ACTIVE:           { label: 'Active',         color: 'var(--green)',  bg: 'var(--green-bg)'  },
  SUSPENDED:        { label: 'Suspended',      color: 'var(--amber)',  bg: 'var(--amber-bg)'  },
  CLOSED:           { label: 'Closed',         color: 'var(--red)',    bg: 'var(--red-bg)'    },
  PENDING:          { label: 'Pending',        color: 'var(--blue)',   bg: 'var(--blue-bg)'   },
  SETTLED:          { label: 'Settled',        color: 'var(--green)',  bg: 'var(--green-bg)'  },
  FAILED:           { label: 'Failed',         color: 'var(--red)',    bg: 'var(--red-bg)'    },
  FROZEN:           { label: 'Frozen',         color: 'var(--amber)',  bg: 'var(--amber-bg)'  },
  LEVEL_1:          { label: 'KYC L1',        color: 'var(--blue)',   bg: 'var(--blue-bg)'   },
  LEVEL_2:          { label: 'KYC L2',        color: 'var(--purple)', bg: 'var(--purple-bg)' },
  LEVEL_3:          { label: 'KYC L3',        color: 'var(--green)',  bg: 'var(--green-bg)'  },
  KYC_BASIC:        { label: 'KYC Basic',     color: 'var(--blue)',   bg: 'var(--blue-bg)'   },
  KYC_VERIFIED:     { label: 'KYC Verified',  color: 'var(--purple)', bg: 'var(--purple-bg)' },
  KYC_ENHANCED:     { label: 'KYC Enhanced',  color: 'var(--green)',  bg: 'var(--green-bg)'  },
  KYC_NONE:         { label: 'KYC None',      color: 'var(--text-secondary)', bg: 'var(--border-color)' },
  COMPLETED:        { label: 'Completed',     color: 'var(--green)',  bg: 'var(--green-bg)'  },
  PENDING_APPROVAL: { label: 'Approbation',   color: 'var(--amber)',  bg: 'var(--amber-bg)'  },
  CASH_IN:          { label: 'Cash In',       color: 'var(--green)',  bg: 'var(--green-bg)'  },
  CASH_OUT:         { label: 'Cash Out',      color: 'var(--amber)',  bg: 'var(--amber-bg)'  },
  CARD_SALE:        { label: 'Vente carte',   color: 'var(--purple)', bg: 'var(--purple-bg)' },
};

function AgentBadge({ status, custom }) {
  const cfg = AGENT_STATUS_CONFIG[status] || {
    label: status || '—',
    color: 'var(--text-secondary)',
    bg: 'var(--border-color)',
  };
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 5,
      padding: '2px 8px', borderRadius: 4, fontSize: 11, fontWeight: 600,
      letterSpacing: '0.03em', textTransform: 'uppercase',
      color: cfg.color, background: cfg.bg,
    }}>
      <span style={{ width: 5, height: 5, borderRadius: '50%', background: cfg.color, flexShrink: 0 }}></span>
      {custom || cfg.label}
    </span>
  );
}

function AgentMono({ children, size, color }) {
  return (
    <span style={{
      fontFamily: 'DM Mono, monospace',
      fontSize: size || 12,
      color: color || 'var(--text-secondary)',
      letterSpacing: '-0.01em',
    }}>{children}</span>
  );
}

function AgentAmount({ value, currency = 'KMF', size = 13, colored }) {
  const abs = Math.abs(value);
  const formatted = abs.toLocaleString('fr-KM');
  const sign = value < 0 ? '−' : '';
  const col = colored ? (value < 0 ? 'var(--red)' : 'var(--green)') : 'var(--text-primary)';
  return (
    <span style={{ fontFamily: 'DM Mono, monospace', fontSize: size, fontWeight: 500, color: col }}>
      {sign}{formatted}{' '}
      <span style={{ fontWeight: 400, color: 'var(--text-secondary)', fontSize: size - 1 }}>{currency}</span>
    </span>
  );
}

function AgentBtn({ children, variant = 'primary', size = 'md', onClick, disabled, fullWidth }) {
  const base = {
    display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 6,
    border: 'none', cursor: disabled ? 'not-allowed' : 'pointer',
    fontFamily: 'inherit', fontWeight: 600, borderRadius: 8,
    transition: 'all 0.15s', opacity: disabled ? 0.5 : 1,
    outline: 'none', whiteSpace: 'nowrap',
    width: fullWidth ? '100%' : undefined,
  };
  const sizes = {
    sm: { fontSize: 12, padding: '6px 12px' },
    md: { fontSize: 14, padding: '10px 18px' },
    lg: { fontSize: 15, padding: '14px 24px' },
  };
  const variants = {
    primary:   { background: 'var(--accent)', color: '#fff' },
    secondary: { background: 'var(--surface)', color: 'var(--text-primary)', border: '1px solid var(--border-color)' },
    ghost:     { background: 'transparent', color: 'var(--text-secondary)', border: '1px solid transparent' },
    danger:    { background: 'var(--red-bg)', color: 'var(--red)', border: '1px solid var(--red)' },
  };
  return (
    <button onClick={disabled ? undefined : onClick} style={{ ...base, ...sizes[size], ...variants[variant] }}>
      {children}
    </button>
  );
}

function AgentCard({ children, style, pad = 16 }) {
  return (
    <div style={{
      background: 'var(--surface)', borderRadius: 12,
      border: '1px solid var(--border-color)',
      boxShadow: '0 1px 4px rgba(0,0,0,0.04)',
      padding: pad, ...style,
    }}>
      {children}
    </div>
  );
}

function AgentInput({ label, value, onChange, placeholder, type = 'text', error, hint, large }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      {label && (
        <label style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.03em' }}>
          {label}
        </label>
      )}
      <input
        type={type}
        value={value}
        onChange={e => onChange(e.target.value)}
        placeholder={placeholder}
        style={{
          padding: large ? '14px 16px' : '10px 14px',
          borderRadius: 8,
          border: `1.5px solid ${error ? 'var(--red)' : 'var(--border-color)'}`,
          background: 'var(--surface)',
          color: 'var(--text-primary)',
          fontFamily: 'inherit',
          fontSize: large ? 16 : 14,
          outline: 'none',
          transition: 'border-color 0.15s',
          width: '100%',
          boxSizing: 'border-box',
        }}
        onFocus={e => e.target.style.borderColor = 'var(--accent)'}
        onBlur={e => e.target.style.borderColor = error ? 'var(--red)' : 'var(--border-color)'}
      />
      {(error || hint) && (
        <span style={{ fontSize: 11, color: error ? 'var(--red)' : 'var(--text-secondary)' }}>
          {error || hint}
        </span>
      )}
    </div>
  );
}

function AgentDivider() {
  return <div style={{ height: 1, background: 'var(--border-color)', margin: '16px 0' }} />;
}

function AgentToast({ message, type = 'success', onClose }) {
  React.useEffect(() => {
    const t = setTimeout(onClose, 3000);
    return () => clearTimeout(t);
  }, [onClose]);
  const colors = { success: 'var(--green)', error: 'var(--red)', info: 'var(--blue)' };
  return (
    <div style={{
      position: 'fixed', bottom: 88, left: '50%', transform: 'translateX(-50%)',
      zIndex: 500, background: 'var(--sidebar-bg)', color: '#fff',
      padding: '12px 18px', borderRadius: 10,
      boxShadow: '0 8px 24px rgba(0,0,0,0.22)',
      display: 'flex', alignItems: 'center', gap: 10, fontSize: 13,
      borderLeft: `3px solid ${colors[type]}`,
      maxWidth: 340, width: 'calc(100% - 40px)',
    }}>
      <span style={{ flex: 1 }}>{message}</span>
      <button onClick={onClose} style={{ background: 'none', border: 'none', cursor: 'pointer', color: 'rgba(255,255,255,0.6)', padding: 0, display: 'flex' }}>
        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
          <path d="M9 3L3 9M3 3l6 6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
        </svg>
      </button>
    </div>
  );
}

// Transaction type label + color
function TxnTypePill({ type }) {
  const cfg = {
    DEPOSIT:    { label: 'Cash In',   color: 'var(--green)',  bg: 'var(--green-bg)'  },
    WITHDRAWAL: { label: 'Cash Out',  color: 'var(--amber)',  bg: 'var(--amber-bg)'  },
    TRANSFER_IN:  { label: 'In',      color: 'var(--blue)',   bg: 'var(--blue-bg)'   },
    TRANSFER_OUT: { label: 'Out',     color: 'var(--indigo)', bg: 'var(--indigo-bg)' },
    PAYMENT:    { label: 'Payment',   color: 'var(--purple)', bg: 'var(--purple-bg)' },
    REVERSAL:   { label: 'Reversal',  color: 'var(--red)',    bg: 'var(--red-bg)'    },
  }[type] || { label: type, color: 'var(--text-secondary)', bg: 'var(--border-color)' };
  return (
    <span style={{
      display: 'inline-block',
      padding: '2px 8px', borderRadius: 4,
      fontSize: 11, fontWeight: 600, letterSpacing: '0.03em',
      color: cfg.color, background: cfg.bg,
    }}>{cfg.label}</span>
  );
}

// Limit progress bar
function LimitBar({ used, limit, currency = 'KMF' }) {
  const pct = Math.min((used / limit) * 100, 100);
  const color = pct > 85 ? 'var(--red)' : pct > 65 ? 'var(--amber)' : 'var(--accent)';
  return (
    <div>
      <div style={{ height: 6, background: 'var(--border-color)', borderRadius: 3, overflow: 'hidden', marginBottom: 6 }}>
        <div style={{ width: `${pct}%`, height: '100%', background: color, borderRadius: 3, transition: 'width 0.5s' }} />
      </div>
      <div style={{ display: 'flex', justifyContent: 'space-between' }}>
        <AgentMono size={11}>{used.toLocaleString('fr-KM')} used</AgentMono>
        <AgentMono size={11}>{limit.toLocaleString('fr-KM')} {currency}</AgentMono>
      </div>
    </div>
  );
}

// Page header inside the scrollable area
function PageHeader({ title, subtitle, action }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'flex-start',
      justifyContent: 'space-between', marginBottom: 20,
    }}>
      <div>
        <h2 style={{ margin: 0, fontSize: 18, fontWeight: 700, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>{title}</h2>
        {subtitle && <p style={{ margin: '3px 0 0', fontSize: 13, color: 'var(--text-secondary)' }}>{subtitle}</p>}
      </div>
      {action && <div>{action}</div>}
    </div>
  );
}

// Spinner
function Spinner({ size = 18 }) {
  return (
    <svg style={{ animation: 'spin 0.8s linear infinite' }} width={size} height={size} viewBox="0 0 18 18" fill="none">
      <circle cx="9" cy="9" r="7" stroke="rgba(0,0,0,0.1)" strokeWidth="2"/>
      <path d="M9 2a7 7 0 017 7" stroke="var(--accent)" strokeWidth="2" strokeLinecap="round"/>
    </svg>
  );
}

// Empty state
function AgentEmptyState({ message }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', padding: '48px 24px', color: 'var(--text-secondary)' }}>
      <div style={{
        width: 44, height: 44, borderRadius: '50%',
        background: 'var(--border-color)',
        display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 12,
      }}>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <rect x="3" y="5" width="14" height="11" rx="2" stroke="currentColor" strokeWidth="1.4"/>
          <path d="M6 9h8M6 12h5" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round"/>
        </svg>
      </div>
      <p style={{ margin: 0, fontSize: 13 }}>{message}</p>
    </div>
  );
}

// Section label (UPPERCASED small label)
function SectionLabel({ children }) {
  return (
    <div style={{
      fontSize: 10, fontWeight: 700, letterSpacing: '0.1em',
      textTransform: 'uppercase', color: 'var(--text-secondary)',
      marginBottom: 10,
    }}>{children}</div>
  );
}

// Slide-over detail panel (full screen overlay on mobile)
function AgentSlideOver({ open, onClose, title, children }) {
  React.useEffect(() => {
    const handler = e => { if (e.key === 'Escape') onClose(); };
    if (open) document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }, [open, onClose]);

  return (
    <>
      <div onClick={onClose} style={{
        position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.3)',
        zIndex: 200, opacity: open ? 1 : 0,
        pointerEvents: open ? 'all' : 'none',
        transition: 'opacity 0.2s',
      }} />
      <div style={{
        position: 'fixed', inset: 0,
        background: 'var(--bg)',
        zIndex: 201,
        display: 'flex', flexDirection: 'column',
        transform: open ? 'translateY(0)' : 'translateY(100%)',
        transition: 'transform 0.28s cubic-bezier(0.4,0,0.2,1)',
        maxWidth: 600, margin: '0 auto',
      }}>
        {/* Header */}
        <div style={{
          padding: '16px 20px',
          background: 'var(--surface)',
          borderBottom: '1px solid var(--border-color)',
          display: 'flex', alignItems: 'center', gap: 12, flexShrink: 0,
        }}>
          <button onClick={onClose} style={{
            background: 'none', border: 'none', cursor: 'pointer',
            color: 'var(--text-secondary)', display: 'flex', padding: 4,
          }}>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </button>
          <h3 style={{ margin: 0, fontSize: 15, fontWeight: 700, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>{title}</h3>
        </div>
        {/* Body */}
        <div style={{ flex: 1, overflowY: 'auto', padding: '20px' }}>
          {children}
        </div>
      </div>
    </>
  );
}

// Detail row (label + value)
function DetailRow({ label, value, mono, children, border = true }) {
  return (
    <div style={{
      display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start',
      padding: '12px 0',
      borderBottom: border ? '1px solid var(--border-color)' : 'none',
    }}>
      <span style={{ fontSize: 12, color: 'var(--text-secondary)', flexShrink: 0, marginRight: 16 }}>{label}</span>
      <span style={{
        fontSize: 13, color: 'var(--text-primary)', fontWeight: 500, textAlign: 'right',
        fontFamily: mono ? 'DM Mono, monospace' : 'inherit',
      }}>
        {children || value || <span style={{ color: 'var(--text-secondary)' }}>—</span>}
      </span>
    </div>
  );
}

// Format date/time
function fmtDateTime(iso) {
  return new Date(iso).toLocaleString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
}
function fmtDate(iso) {
  return new Date(iso).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric',
  });
}
function fmtTime(iso) {
  return new Date(iso).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

// Agent icons (inline SVG)
const AGENT_ICONS = {
  home: (
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
      <path d="M3 9.5L11 3l8 6.5V19a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z" stroke="currentColor" strokeWidth="1.5" strokeLinejoin="round"/>
      <path d="M8 20v-7h6v7" stroke="currentColor" strokeWidth="1.5" strokeLinejoin="round"/>
    </svg>
  ),
  operations: (
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
      <rect x="3" y="6" width="16" height="11" rx="2" stroke="currentColor" strokeWidth="1.5"/>
      <path d="M3 10h16" stroke="currentColor" strokeWidth="1.5"/>
      <path d="M7 14h2M12 14h3" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
      <path d="M11 3v3" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
    </svg>
  ),
  enroll: (
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
      <circle cx="9" cy="8" r="4" stroke="currentColor" strokeWidth="1.5"/>
      <path d="M3 19c0-3.3 2.7-6 6-6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
      <path d="M16 13v6M13 16h6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
    </svg>
  ),
  transactions: (
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
      <path d="M4 7h14M4 11h10M4 15h7" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
      <circle cx="17" cy="15" r="3" stroke="currentColor" strokeWidth="1.5"/>
    </svg>
  ),
  statement: (
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
      <rect x="4" y="2" width="14" height="18" rx="2" stroke="currentColor" strokeWidth="1.5"/>
      <path d="M8 7h6M8 11h6M8 15h4" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
    </svg>
  ),
  commission: (
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
      <circle cx="8" cy="8" r="3" stroke="currentColor" strokeWidth="1.5"/>
      <circle cx="14" cy="14" r="3" stroke="currentColor" strokeWidth="1.5"/>
      <path d="M5 17l12-12" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
    </svg>
  ),
  profile: (
    <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
      <circle cx="11" cy="8" r="4" stroke="currentColor" strokeWidth="1.5"/>
      <path d="M3 19c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
    </svg>
  ),
};

Object.assign(window, {
  AGENT_STATUS_CONFIG, AgentBadge, AgentMono, AgentAmount, AgentBtn,
  AgentCard, AgentInput, AgentDivider, AgentToast, TxnTypePill, LimitBar,
  PageHeader, Spinner, AgentEmptyState, SectionLabel,
  AgentSlideOver, DetailRow, fmtDateTime, fmtDate, fmtTime, AGENT_ICONS,
});
