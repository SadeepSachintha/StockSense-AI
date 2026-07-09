<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cashier Terminal Login - StockSense AI</title>
  <link rel="stylesheet" href="/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-950 font-sans text-slate-100" style="display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1.5rem;box-sizing:border-box">
  
  <div class="card" style="width:100%;max-width:420px;padding:2.5rem;box-sizing:border-box;box-shadow:0 8px 32px rgba(0,0,0,0.4)">
    <div style="text-align:center;margin-bottom:2rem">
      <div style="width:48px;height:48px;background:var(--gradient-purple);border-radius:12px;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center">
        <svg style="width:24px;height:24px;stroke:#ffffff;fill:none;stroke-width:2" viewBox="0 0 24 24">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
      <h2 style="font-family:'Outfit',sans-serif;font-size:1.75rem;font-weight:700;margin:0 0 0.5rem">Cashier Login</h2>
      <p style="font-size:0.85rem;color:var(--text-muted);margin:0">Enter your credentials to enter the checkout terminal.</p>
    </div>

    @if($errors->any())
      <div style="background:rgba(244,63,94,0.15);border:1px solid rgba(244,63,94,0.3);color:#fb7185;padding:0.75rem 1rem;border-radius:6px;font-size:0.8rem;margin-bottom:1.5rem">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="/login">
      @csrf
      <div style="margin-bottom:1.25rem">
        <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Tenant Account ID</label>
        <input 
          type="text" 
          name="tenant_id" 
          class="admin-input" 
          placeholder="e.g. tenant_001" 
          value="{{ old('tenant_id', 'tenant_001') }}" 
          style="width:100%;box-sizing:border-box" 
          required 
          autofocus
        >
      </div>

      <div style="margin-bottom:1.75rem">
        <div style="display:flex;justify-content:between;align-items:center;margin-bottom:0.4rem">
          <label style="font-size:0.75rem;color:var(--text-secondary);text-transform:uppercase;font-weight:600">Operator Password</label>
        </div>
        <input 
          type="password" 
          name="password" 
          class="admin-input" 
          placeholder="••••••••" 
          value="password"
          style="width:100%;box-sizing:border-box" 
          required
        >
      </div>

      <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.75rem;font-size:0.95rem;background:var(--gradient-purple)">
        Access Cashier Terminal
      </button>
    </form>

    <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border-glass);text-align:center">
      <div style="font-size:0.72rem;color:var(--text-muted);line-height:1.4">
        <strong>Demo Hint:</strong> Use Tenant ID <code style="color:var(--accent-purple)">tenant_001</code> or <code style="color:var(--accent-purple)">tenant_002</code> with password <code style="color:var(--accent-purple)">password</code>.
      </div>
      <div style="margin-top:1rem;font-size:0.7rem;color:var(--text-muted)">
        A product of <a href="https://ss-labz.pages.dev/" target="_blank" style="color:var(--accent-purple);text-decoration:none;font-weight:600">SS Labz</a>
      </div>
    </div>
  </div>

</body>
</html>
