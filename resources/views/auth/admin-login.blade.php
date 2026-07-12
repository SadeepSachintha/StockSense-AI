<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SaaS Administrator Login - StockSense AI</title>
  <link rel="stylesheet" href="/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #0b0f19;
      background-image: 
        radial-gradient(at 0% 0%, rgba(244, 63, 94, 0.12) 0px, transparent 50%),
        radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.12) 0px, transparent 50%);
      background-attachment: fixed;
    }
    .login-glow {
      position: absolute;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle, rgba(244, 63, 94, 0.15) 0%, rgba(244, 63, 94, 0) 70%);
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 0;
      pointer-events: none;
    }
    .glass-card-login {
      background: rgba(17, 24, 39, 0.45);
      backdrop-filter: blur(25px) saturate(120%);
      border: 1px solid rgba(255, 255, 255, 0.08);
      box-shadow: var(--shadow-lg), 0 0 50px rgba(244, 63, 94, 0.08);
      z-index: 10;
      position: relative;
      border-color: rgba(244, 63, 94, 0.25);
    }
    .demo-pill {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.06);
      color: var(--text-secondary);
      border-radius: 20px;
      padding: 0.45rem 1rem;
      font-size: 0.72rem;
      cursor: pointer;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      font-weight: 500;
    }
    .demo-pill:hover {
      background: rgba(244, 63, 94, 0.1);
      border-color: rgba(244, 63, 94, 0.3);
      color: var(--text-primary);
      transform: translateY(-1px);
    }
    .input-wrapper-pass {
      position: relative;
      display: flex;
      align-items: center;
    }
    .pass-toggle-btn {
      position: absolute;
      right: 0.85rem;
      background: transparent;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      padding: 0.25rem;
      display: flex;
      align-items: center;
      transition: color 0.15s;
    }
    .pass-toggle-btn:hover {
      color: var(--text-primary);
    }
    .flash-highlight {
      animation: flash-outline-rose 0.6s ease-out;
    }
    @keyframes flash-outline-rose {
      0%, 100% { border-color: rgba(255, 255, 255, 0.08); box-shadow: none; }
      50% { border-color: var(--accent-rose); box-shadow: 0 0 12px rgba(244, 63, 94, 0.3); }
    }
  </style>
</head>
<body class="font-sans text-slate-100" style="display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1.5rem;box-sizing:border-box;position:relative;overflow:hidden">

  <!-- Floating Radial Glow behind Card -->
  <div class="login-glow"></div>
  
  <div class="card glass-card-login" style="width:100%;max-width:440px;padding:2.75rem;box-sizing:border-box;border-radius:16px;">
    <div style="text-align:center;margin-bottom:2rem">
      <div style="width:52px;height:52px;background:var(--gradient-rose);border-radius:14px;margin:0 auto 1.25rem;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 20px -4px rgba(244, 63, 94, 0.4)">
        <svg style="width:26px;height:26px;stroke:#ffffff;fill:none;stroke-width:2" viewBox="0 0 24 24">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <h2 style="font-family:'Outfit',sans-serif;font-size:1.85rem;font-weight:800;margin:0 0 0.5rem;color:var(--text-primary);letter-spacing:-0.02em;">SaaS Admin Login</h2>
      <p style="font-size:0.85rem;color:var(--text-secondary);margin:0">Authenticate to enter the Tenant Control Panel console.</p>
    </div>

    @if($errors->any())
      <div style="background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.25);color:#fb7185;padding:0.75rem 1rem;border-radius:8px;font-size:0.8rem;margin-bottom:1.5rem">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="/saas-admin/login" id="loginForm">
      @csrf
      <div style="margin-bottom:1.25rem">
        <label style="font-size:0.72rem;color:var(--text-muted);display:block;margin-bottom:0.45rem;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;">Admin Username</label>
        <input 
          type="text" 
          name="username" 
          id="usernameInput"
          class="admin-input" 
          placeholder="e.g. admin" 
          value="{{ old('username', 'admin') }}" 
          style="width:100%;box-sizing:border-box" 
          required 
          autofocus
        >
      </div>

      <div style="margin-bottom:1.75rem">
        <label style="font-size:0.72rem;color:var(--text-muted);display:block;margin-bottom:0.45rem;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;">Admin Password</label>
        <div class="input-wrapper-pass">
          <input 
            type="password" 
            name="password" 
            id="passwordInput"
            class="admin-input" 
            placeholder="••••••••" 
            value="admin123"
            style="width:100%;box-sizing:border-box;padding-right:2.5rem" 
            required
          >
          <button type="button" class="pass-toggle-btn" id="togglePasswordBtn" title="Toggle Password Visibility">
            <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24" id="eyeIcon">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.85rem;font-size:1rem;font-weight:700;border-radius:10px;background:var(--gradient-rose);border-color:var(--accent-rose);box-shadow:0 4px 15px rgba(244, 63, 94, 0.2)">
        Access Admin Console
      </button>
    </form>

    <div style="margin-top:1.75rem;padding-top:1.5rem;border-top:1px dashed rgba(255,255,255,0.08);text-align:center">
      <div style="font-size:0.75rem;color:var(--text-muted);font-weight:600;margin-bottom:0.75rem;text-transform:uppercase;letter-spacing:0.03em;">Quick Demo Pre-fills</div>
      <div style="display:flex;justify-content:center;margin-bottom:1.25rem;">
        <button type="button" class="demo-pill" onclick="preFillDemo()">
          <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          Administrator Account
        </button>
      </div>
      <div style="font-size:0.7rem;color:var(--text-muted)">
        A product of <a href="https://ss-labz.pages.dev/" target="_blank" style="color:var(--accent-purple);text-decoration:none;font-weight:600;transition:color 0.2s;" onmouseover="this.style.color='#f43f5e'" onmouseout="this.style.color='#8b5cf6'">SS Labz</a>
      </div>
    </div>
  </div>

  <script>
    // Toggle Password Visibility
    const passwordInput = document.getElementById('passwordInput');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePasswordBtn.addEventListener('click', () => {
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = `
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
          <line x1="1" y1="1" x2="23" y2="23"/>
        `;
      } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = `
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
          <circle cx="12" cy="12" r="3"/>
        `;
      }
    });

    // Pre-fill Demo Credentials Helper
    function preFillDemo() {
      const userField = document.getElementById('usernameInput');
      const passField = document.getElementById('passwordInput');

      userField.value = 'admin';
      passField.value = 'admin123';

      // Visual highlight outline flash
      [userField, passField].forEach(field => {
        field.classList.remove('flash-highlight');
        void field.offsetWidth; // trigger reflow
        field.classList.add('flash-highlight');
      });
    }
  </script>
</body>
</html>
