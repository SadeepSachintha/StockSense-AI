<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SaaS Administrator Login - StockSense AI</title>
  <link rel="stylesheet" href="/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-950 font-sans text-slate-100" style="display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1.5rem;box-sizing:border-box">
  
  <div class="card" style="width:100%;max-width:420px;padding:2.5rem;box-sizing:border-box;box-shadow:0 8px 32px rgba(0,0,0,0.4);border-color:var(--accent-rose)">
    <div style="text-align:center;margin-bottom:2rem">
      <div style="width:48px;height:48px;background:var(--gradient-rose);border-radius:12px;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center">
        <svg style="width:24px;height:24px;stroke:#ffffff;fill:none;stroke-width:2" viewBox="0 0 24 24">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <h2 style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:700;margin:0 0 0.5rem">SaaS Admin Login</h2>
      <p style="font-size:0.85rem;color:var(--text-muted);margin:0">Authenticate to enter the Tenant Control Panel console.</p>
    </div>

    @if($errors->any())
      <div style="background:rgba(244,63,94,0.15);border:1px solid rgba(244,63,94,0.3);color:#fb7185;padding:0.75rem 1rem;border-radius:6px;font-size:0.8rem;margin-bottom:1.5rem">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="/saas-admin/login">
      @csrf
      <div style="margin-bottom:1.25rem">
        <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Admin Username</label>
        <input 
          type="text" 
          name="username" 
          class="admin-input" 
          placeholder="e.g. admin" 
          value="{{ old('username', 'admin') }}" 
          style="width:100%;box-sizing:border-box" 
          required 
          autofocus
        >
      </div>

      <div style="margin-bottom:1.75rem">
        <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Admin Password</label>
        <input 
          type="password" 
          name="password" 
          class="admin-input" 
          placeholder="••••••••" 
          value="admin123"
          style="width:100%;box-sizing:border-box" 
          required
        >
      </div>

      <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.75rem;font-size:0.95rem;background:var(--gradient-rose);border-color:var(--accent-rose)">
        Access Admin Console
      </button>
    </form>

    <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border-glass);text-align:center">
      <div style="font-size:0.72rem;color:var(--text-muted);line-height:1.4">
        <strong>Demo Hint:</strong> Use username <code style="color:var(--accent-rose)">admin</code> and password <code style="color:var(--accent-rose)">admin123</code>.
      </div>
      <div style="margin-top:1rem;font-size:0.7rem;color:var(--text-muted)">
        A product of <a href="https://ss-labz.pages.dev/" target="_blank" style="color:var(--accent-purple);text-decoration:none;font-weight:600">SS Labz</a>
      </div>
    </div>
  </div>

</body>
</html>
