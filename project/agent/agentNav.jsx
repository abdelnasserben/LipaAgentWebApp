// Bottom tab navigation for Lipa Agent Web App (mobile only)

// Bottom nav tabs — mobile only shows 4 primary items
// Opérations + Enrôlement sont accessibles via le dashboard et la sidebar desktop
const NAV_TABS = [
  { key: 'dashboard',    label: 'Home',         icon: AGENT_ICONS.home         },
  { key: 'operations',   label: 'Opérations',   icon: AGENT_ICONS.operations   },
  { key: 'enroll',       label: 'Enrôlement',   icon: AGENT_ICONS.enroll       },
  { key: 'transactions', label: 'Transactions', icon: AGENT_ICONS.transactions },
  { key: 'statement',    label: 'Relevé',       icon: AGENT_ICONS.statement    },
  { key: 'commission',   label: 'Commission',   icon: AGENT_ICONS.commission   },
  { key: 'profile',      label: 'Profil',       icon: AGENT_ICONS.profile      },
];

// Only 4 tabs shown in bottom nav on mobile — the most important ones
const BOTTOM_NAV_TABS = [
  { key: 'dashboard',    label: 'Home',       icon: AGENT_ICONS.home       },
  { key: 'operations',   label: 'Opérations', icon: AGENT_ICONS.operations },
  { key: 'enroll',       label: 'Enrôlement', icon: AGENT_ICONS.enroll     },
  { key: 'profile',      label: 'Profil',     icon: AGENT_ICONS.profile    },
];

function AgentNav({ activePage, onNavigate }) {
  return (
    <nav style={{
      width: '100%',
      background: 'var(--surface)',
      borderTop: '1px solid var(--border-color)',
      display: 'flex',
      alignItems: 'stretch',
      height: 64,
      paddingBottom: 'env(safe-area-inset-bottom)',
    }}>
      {BOTTOM_NAV_TABS.map(tab => {
        const isActive = activePage === tab.key;
        return (
          <button
            key={tab.key}
            onClick={() => onNavigate(tab.key)}
            style={{
              flex: 1, display: 'flex', flexDirection: 'column',
              alignItems: 'center', justifyContent: 'center', gap: 3,
              background: 'none', border: 'none', cursor: 'pointer',
              color: isActive ? 'var(--accent)' : 'var(--text-secondary)',
              fontFamily: 'inherit', padding: '6px 4px',
              position: 'relative', transition: 'color 0.15s',
            }}
          >
            {isActive && (
              <div style={{
                position: 'absolute', top: 0, left: '50%', transform: 'translateX(-50%)',
                width: 24, height: 2.5, borderRadius: '0 0 3px 3px',
                background: 'var(--accent)',
              }} />
            )}
            <span style={{
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              width: 28, height: 28, borderRadius: 8,
              background: isActive ? 'var(--accent-bg)' : 'transparent',
              transition: 'background 0.15s',
            }}>
              {tab.icon}
            </span>
            <span style={{ fontSize: 10, fontWeight: isActive ? 700 : 500, lineHeight: 1, letterSpacing: '0.01em' }}>
              {tab.label}
            </span>
          </button>
        );
      })}
    </nav>
  );
}

// ── Sidebar navigation (tablet / desktop) ─────────────────────────────────────

function AgentSidebar({ activePage, onNavigate, onLogout }) {
  // We use the CSS class .app-sidebar — display:none on mobile, flex on tablet+
  return (
    <aside className="app-sidebar">
      {/* Logo mark */}
      <div style={{
        display: 'flex', alignItems: 'center', gap: 10,
        padding: '0 14px 20px',
        borderBottom: '1px solid rgba(255,255,255,0.07)',
        marginBottom: 8, flexShrink: 0,
        overflow: 'hidden',
      }}>
        <div style={{
          width: 32, height: 32, borderRadius: 9, background: 'var(--accent)',
          display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
        }}>
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M2 4h12v8a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" fill="white" fillOpacity=".2" stroke="white" strokeWidth="1.2"/>
            <path d="M5 4V3a3 3 0 016 0v1" stroke="white" strokeWidth="1.2" strokeLinecap="round"/>
            <path d="M5.5 8.5l1.5 1.5 3.5-3" stroke="white" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </div>
        {/* Label — only visible when sidebar is wide (≥900px) */}
        <div className="sidebar-label-block" style={{ overflow: 'hidden', whiteSpace: 'nowrap' }}>
          <div style={{ color: '#fff', fontWeight: 800, fontSize: 15, letterSpacing: '-0.03em', lineHeight: 1 }}>Lipa</div>
          <div style={{ color: 'rgba(255,255,255,0.32)', fontSize: 9, fontWeight: 600, letterSpacing: '0.1em', textTransform: 'uppercase', marginTop: 2 }}>Agent Portal</div>
        </div>
      </div>

      {/* Nav items */}
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2, padding: '0 8px', overflowY: 'auto' }}>
        {NAV_TABS.map(tab => {
          const isActive = activePage === tab.key;
          return (
            <button
              key={tab.key}
              onClick={() => onNavigate(tab.key)}
              title={tab.label}
              style={{
                display: 'flex', alignItems: 'center', gap: 10,
                padding: '9px 8px', borderRadius: 9, border: 'none', cursor: 'pointer',
                background: isActive ? 'rgba(255,255,255,0.10)' : 'transparent',
                color: isActive ? '#fff' : 'rgba(255,255,255,0.45)',
                fontFamily: 'inherit', fontWeight: isActive ? 700 : 500,
                transition: 'all 0.15s', whiteSpace: 'nowrap', overflow: 'hidden',
              }}
              onMouseEnter={e => { if (!isActive) e.currentTarget.style.background = 'rgba(255,255,255,0.06)'; e.currentTarget.style.color = '#fff'; }}
              onMouseLeave={e => { e.currentTarget.style.background = isActive ? 'rgba(255,255,255,0.10)' : 'transparent'; e.currentTarget.style.color = isActive ? '#fff' : 'rgba(255,255,255,0.45)'; }}
            >
              <span style={{
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                width: 30, height: 30, borderRadius: 8, flexShrink: 0,
                background: isActive ? 'var(--accent)' : 'transparent',
                color: isActive ? '#fff' : 'inherit',
                transition: 'background 0.15s',
              }}>
                {tab.icon}
              </span>
              <span className="sidebar-label-text" style={{ fontSize: 13 }}>{tab.label}</span>
              {isActive && (
                <span style={{
                  marginLeft: 'auto', width: 5, height: 5, borderRadius: '50%',
                  background: 'var(--accent)', flexShrink: 0,
                }} className="sidebar-label-text" />
              )}
            </button>
          );
        })}
      </div>

      {/* Logout at bottom */}
      <div style={{ padding: '12px 8px 0', borderTop: '1px solid rgba(255,255,255,0.07)', flexShrink: 0 }}>
        <button
          onClick={onLogout}
          title="Sign out"
          style={{
            display: 'flex', alignItems: 'center', gap: 10,
            padding: '9px 8px', borderRadius: 9, border: 'none', cursor: 'pointer',
            background: 'transparent', color: 'rgba(255,255,255,0.35)',
            fontFamily: 'inherit', fontWeight: 500, width: '100%',
            transition: 'all 0.15s', whiteSpace: 'nowrap', overflow: 'hidden',
          }}
          onMouseEnter={e => { e.currentTarget.style.background = 'rgba(255,80,80,0.12)'; e.currentTarget.style.color = '#ff7b72'; }}
          onMouseLeave={e => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = 'rgba(255,255,255,0.35)'; }}
        >
          <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: 30, height: 30, flexShrink: 0 }}>
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M7 3H4a1 1 0 00-1 1v10a1 1 0 001 1h3" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
              <path d="M12 12l3-3-3-3M15 9H7" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </span>
          <span className="sidebar-label-text" style={{ fontSize: 13 }}>Sign out</span>
        </button>
      </div>
    </aside>
  );
}

// Inject sidebar-specific responsive styles once
(function() {
  const id = 'agent-sidebar-styles';
  if (document.getElementById(id)) return;
  const el = document.createElement('style');
  el.id = id;
  el.textContent = `
    /* Icon-only mode: hide text labels */
    @media (max-width: 899px) {
      .sidebar-label-text { display: none !important; }
      .sidebar-label-block { display: none !important; }
      .app-sidebar { justify-content: flex-start; align-items: center; }
    }
    /* Wide mode: show text labels */
    @media (min-width: 900px) {
      .sidebar-label-text { display: inline !important; }
      .sidebar-label-block { display: block !important; }
    }
  `;
  document.head.appendChild(el);
})();

// ── Top app bar ───────────────────────────────────────────────────────────────

function AgentTopBar({ title, subtitle, onBack, action }) {
  return (
    <div style={{
      height: 56, flexShrink: 0,
      background: 'var(--surface)',
      borderBottom: '1px solid var(--border-color)',
      display: 'flex', alignItems: 'center',
      padding: '0 16px', gap: 10,
      paddingTop: 'env(safe-area-inset-top)',
    }}>
      {onBack && (
        <button onClick={onBack} style={{
          background: 'none', border: 'none', cursor: 'pointer',
          color: 'var(--text-secondary)', display: 'flex', padding: 4, borderRadius: 6,
        }}>
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M12.5 5L7.5 10l5 5" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </button>
      )}
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 15, fontWeight: 700, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
          {title}
        </div>
        {subtitle && (
          <div style={{ fontSize: 11, color: 'var(--text-secondary)', marginTop: 1 }}>{subtitle}</div>
        )}
      </div>
      {action && <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>{action}</div>}
    </div>
  );
}

Object.assign(window, { AgentNav, AgentSidebar, AgentTopBar });
