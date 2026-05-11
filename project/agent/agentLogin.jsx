// Agent Login — OTP flow (Phone → OTP code)

function AgentLoginPage({ onLogin }) {
  const [step, setStep] = React.useState('phone'); // 'phone' | 'otp'
  const [phone, setPhone] = React.useState('');
  const [otp, setOtp] = React.useState(['', '', '', '', '', '']);
  const [loading, setLoading] = React.useState(false);
  const [error, setError] = React.useState('');
  const [countdown, setCountdown] = React.useState(0);
  const inputRefs = React.useRef([]);

  // Countdown timer for resend
  React.useEffect(() => {
    if (countdown <= 0) return;
    const t = setInterval(() => setCountdown(c => c - 1), 1000);
    return () => clearInterval(t);
  }, [countdown]);

  const handlePhoneSubmit = (e) => {
    e && e.preventDefault();
    setError('');
    if (!phone || phone.length < 7) {
      setError('Please enter a valid phone number.');
      return;
    }
    setLoading(true);
    setTimeout(() => {
      setLoading(false);
      setStep('otp');
      setCountdown(60);
      setTimeout(() => inputRefs.current[0] && inputRefs.current[0].focus(), 100);
    }, 1000);
  };

  const handleOtpChange = (i, val) => {
    if (!/^\d?$/.test(val)) return;
    const next = [...otp];
    next[i] = val;
    setOtp(next);
    if (val && i < 5) inputRefs.current[i + 1] && inputRefs.current[i + 1].focus();
  };

  const handleOtpKeyDown = (i, e) => {
    if (e.key === 'Backspace' && !otp[i] && i > 0) {
      inputRefs.current[i - 1] && inputRefs.current[i - 1].focus();
    }
  };

  const handleOtpPaste = (e) => {
    const text = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    if (text.length === 6) {
      setOtp(text.split(''));
      e.preventDefault();
    }
  };

  const handleOtpSubmit = (e) => {
    e && e.preventDefault();
    const code = otp.join('');
    if (code.length < 6) { setError('Please enter all 6 digits.'); return; }
    setError('');
    setLoading(true);
    setTimeout(() => {
      setLoading(false);
      if (code === '000000') { setError('Incorrect code. Try again.'); setOtp(['','','','','','']); inputRefs.current[0] && inputRefs.current[0].focus(); return; }
      onLogin(window.AGENT_PROFILE);
    }, 1100);
  };

  const otpComplete = otp.every(d => d !== '');

  // Responsive breakpoint helper (pure inline — no media queries in JS)
  // We use a CSS class injection approach instead
  const loginCSS = `
    .login-outer {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: var(--bg);
      font-family: 'Bricolage Grotesque', sans-serif;
    }

    /* Tablet / desktop: center a card */
    @media (min-width: 600px) {
      .login-outer {
        justify-content: center;
        align-items: center;
        background: oklch(0.94 0.008 255);
        padding: 40px 24px;
        /* Override the app-shell flex-row that would squish us */
        position: fixed;
        inset: 0;
        z-index: 100;
      }
      .login-card {
        width: 100%;
        max-width: 460px;
        background: var(--surface);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.04), 0 12px 40px rgba(0,0,0,0.10);
        border: 1px solid var(--border-color);
      }
    }

    /* Mobile: full-width, no card chrome */
    @media (max-width: 599px) {
      .login-card {
        width: 100%;
        flex: 1;
        display: flex;
        flex-direction: column;
      }
    }

    .login-header {
      background: var(--sidebar-bg);
      padding: 32px 28px 36px;
      position: relative;
      overflow: hidden;
    }

    .login-body {
      padding: 28px 28px 24px;
      flex: 1;
    }

    .otp-row {
      display: flex;
      gap: 8px;
      justify-content: center;
    }

    .otp-input {
      width: 44px;
      height: 52px;
      border-radius: 8px;
      text-align: center;
      font-family: 'DM Mono', monospace;
      font-size: 20px;
      font-weight: 700;
      outline: none;
      transition: all 0.12s;
      /* No flex: 1 — fixed size so it never shrinks */
    }

    @media (min-width: 380px) {
      .otp-input {
        width: 48px;
        height: 56px;
        font-size: 22px;
      }
    }

    @media (min-width: 440px) {
      .otp-input {
        width: 56px;
        height: 60px;
      }
    }

    @media (min-width: 600px) {
      .otp-row {
        gap: 10px;
      }
      .otp-input {
        width: 56px;
        height: 60px;
        font-size: 22px;
      }
    }
  `;

  // Inject styles once
  React.useEffect(() => {
    const id = 'agent-login-styles';
    if (!document.getElementById(id)) {
      const el = document.createElement('style');
      el.id = id;
      el.textContent = loginCSS;
      document.head.appendChild(el);
    }
  }, []);

  return (
    <div className="login-outer">
      <div className="login-card">
        {/* Header stripe */}
        <div className="login-header">
          {/* Subtle grid */}
          <div style={{
            position: 'absolute', inset: 0, opacity: 0.04,
            backgroundImage: 'linear-gradient(rgba(255,255,255,.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.6) 1px, transparent 1px)',
            backgroundSize: '28px 28px',
          }} />
          <div style={{ position: 'relative', display: 'flex', alignItems: 'center', gap: 12, marginBottom: 24 }}>
            <div style={{
              width: 36, height: 36, borderRadius: 10,
              background: 'var(--accent)',
              display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
            }}>
              <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                <path d="M2 4h12v8a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" fill="white" fillOpacity=".2" stroke="white" strokeWidth="1.2"/>
                <path d="M5 4V3a3 3 0 016 0v1" stroke="white" strokeWidth="1.2" strokeLinecap="round"/>
                <path d="M5.5 8.5l1.5 1.5 3.5-3" stroke="white" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </div>
            <div>
              <div style={{ color: '#fff', fontWeight: 800, fontSize: 18, letterSpacing: '-0.03em', lineHeight: 1 }}>Lipa</div>
              <div style={{ color: 'rgba(255,255,255,0.35)', fontSize: 10, fontWeight: 600, letterSpacing: '0.1em', textTransform: 'uppercase', marginTop: 1 }}>Agent Portal</div>
            </div>
          </div>
          <div style={{ position: 'relative' }}>
            <h1 style={{ color: '#fff', fontSize: 24, fontWeight: 800, letterSpacing: '-0.03em', lineHeight: 1.2, margin: '0 0 8px' }}>
              {step === 'phone' ? 'Welcome back' : 'Verify your number'}
            </h1>
            <p style={{ color: 'rgba(255,255,255,0.45)', fontSize: 13, margin: 0, lineHeight: 1.6 }}>
              {step === 'phone'
                ? 'Sign in to your agent account to manage float and transactions.'
                : `We sent a 6-digit code to +269 ${phone}`}
            </p>
          </div>
        </div>

        {/* Form area */}
        <div className="login-body">
          {error && (
            <div style={{
              background: 'var(--red-bg)', border: '1px solid var(--red)',
              borderRadius: 8, padding: '10px 14px', marginBottom: 20,
              fontSize: 13, color: 'var(--red)', display: 'flex', alignItems: 'center', gap: 8,
            }}>
              <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                <circle cx="7" cy="7" r="6" stroke="currentColor" strokeWidth="1.3"/>
                <path d="M7 4v3M7 9.5v.5" stroke="currentColor" strokeWidth="1.3" strokeLinecap="round"/>
              </svg>
              {error}
            </div>
          )}

          {step === 'phone' ? (
            <form onSubmit={handlePhoneSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
              <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                <label style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.03em' }}>
                  Phone number
                </label>
                <div style={{ display: 'flex', gap: 0, border: '1.5px solid var(--border-color)', borderRadius: 8, overflow: 'hidden', background: 'var(--surface)', transition: 'border-color 0.15s' }}
                  onFocusCapture={e => e.currentTarget.style.borderColor = 'var(--accent)'}
                  onBlurCapture={e => e.currentTarget.style.borderColor = 'var(--border-color)'}
                >
                  <div style={{
                    display: 'flex', alignItems: 'center', padding: '0 14px',
                    background: 'var(--bg)', borderRight: '1px solid var(--border-color)',
                    fontSize: 14, fontWeight: 600, color: 'var(--text-primary)',
                    fontFamily: 'DM Mono, monospace', flexShrink: 0, userSelect: 'none',
                  }}>+269</div>
                  <input
                    type="tel"
                    value={phone}
                    onChange={e => setPhone(e.target.value.replace(/\D/g, ''))}
                    placeholder="321 45 67"
                    maxLength={10}
                    autoFocus
                    style={{
                      flex: 1, padding: '14px 14px', border: 'none', outline: 'none',
                      background: 'transparent', color: 'var(--text-primary)',
                      fontFamily: 'DM Mono, monospace', fontSize: 16, letterSpacing: '0.05em',
                      minWidth: 0,
                    }}
                  />
                </div>
              </div>

              <button type="submit" disabled={loading} style={{
                padding: '14px', borderRadius: 8,
                background: loading ? 'oklch(0.5 0.15 148)' : 'var(--accent)',
                color: '#fff', border: 'none', cursor: loading ? 'wait' : 'pointer',
                fontFamily: 'inherit', fontSize: 15, fontWeight: 700,
                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
              }}>
                {loading ? (
                  <>
                    <svg style={{ animation: 'spin 0.8s linear infinite' }} width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <circle cx="8" cy="8" r="6" stroke="rgba(255,255,255,0.3)" strokeWidth="1.8"/>
                      <path d="M8 2a6 6 0 016 6" stroke="white" strokeWidth="1.8" strokeLinecap="round"/>
                    </svg>
                    Sending code…
                  </>
                ) : 'Send verification code'}
              </button>
            </form>
          ) : (
            <form onSubmit={handleOtpSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
              <div>
                <label style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)', letterSpacing: '0.03em', display: 'block', marginBottom: 14 }}>
                  6-digit verification code
                </label>
                <div className="otp-row" onPaste={handleOtpPaste}>
                  {otp.map((digit, i) => (
                    <input
                      key={i}
                      ref={el => inputRefs.current[i] = el}
                      type="text"
                      inputMode="numeric"
                      maxLength={1}
                      value={digit}
                      onChange={e => handleOtpChange(i, e.target.value)}
                      onKeyDown={e => handleOtpKeyDown(i, e)}
                      className="otp-input"
                      style={{
                        border: `1.5px solid ${error ? 'var(--red)' : digit ? 'var(--accent)' : 'var(--border-color)'}`,
                        background: digit ? 'var(--accent-bg)' : 'var(--surface)',
                        color: 'var(--text-primary)',
                      }}
                      onFocus={e => { e.target.style.borderColor = 'var(--accent)'; e.target.style.boxShadow = '0 0 0 3px var(--accent-bg)'; }}
                      onBlur={e => { e.target.style.borderColor = digit ? 'var(--accent)' : 'var(--border-color)'; e.target.style.boxShadow = 'none'; }}
                    />
                  ))}
                </div>
              </div>

              <button type="submit" disabled={loading || !otpComplete} style={{
                padding: '14px', borderRadius: 8,
                background: (!otpComplete || loading) ? 'oklch(0.5 0.15 148)' : 'var(--accent)',
                color: '#fff', border: 'none', cursor: (!otpComplete || loading) ? 'not-allowed' : 'pointer',
                fontFamily: 'inherit', fontSize: 15, fontWeight: 700, opacity: !otpComplete ? 0.6 : 1,
                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                transition: 'all 0.15s',
              }}>
                {loading ? (
                  <>
                    <svg style={{ animation: 'spin 0.8s linear infinite' }} width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <circle cx="8" cy="8" r="6" stroke="rgba(255,255,255,0.3)" strokeWidth="1.8"/>
                      <path d="M8 2a6 6 0 016 6" stroke="white" strokeWidth="1.8" strokeLinecap="round"/>
                    </svg>
                    Verifying…
                  </>
                ) : 'Verify & sign in'}
              </button>

              <div style={{ textAlign: 'center' }}>
                {countdown > 0 ? (
                  <span style={{ fontSize: 13, color: 'var(--text-secondary)' }}>
                    Resend in <AgentMono size={13} color="var(--text-primary)">{countdown}s</AgentMono>
                  </span>
                ) : (
                  <button type="button" onClick={() => { setCountdown(60); }} style={{
                    background: 'none', border: 'none', cursor: 'pointer',
                    fontSize: 13, color: 'var(--accent)', fontFamily: 'inherit', fontWeight: 600,
                  }}>Resend code</button>
                )}
              </div>

              <button type="button" onClick={() => { setStep('phone'); setOtp(['','','','','','']); setError(''); }} style={{
                background: 'none', border: 'none', cursor: 'pointer',
                fontSize: 13, color: 'var(--text-secondary)', fontFamily: 'inherit',
                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
              }}>
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M8.5 3L4.5 7l4 4" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                Change phone number
              </button>
            </form>
          )}

          <p style={{ color: 'var(--text-secondary)', fontSize: 11, textAlign: 'center', marginTop: 28, lineHeight: 1.7 }}>
            Lipa Agent Portal · Secure access only<br />
            All sessions are logged and monitored
          </p>
        </div>
      </div>
    </div>
  );
}

Object.assign(window, { AgentLoginPage });
