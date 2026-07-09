<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SaaS Admin Control Panel - StockSense AI</title>
  <link rel="stylesheet" href="/style.css?v={{ time() }}">
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body class="bg-slate-950 font-sans text-slate-100">
  <div id="app" class="app-container" v-cloak>
    
    <!-- Sidebar Navigation for Admin -->
    <aside class="sidebar">
      <div class="logo-container">
        <div class="logo-icon" style="background:var(--gradient-rose)">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="3" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="logo-text">SaaS <span style="font-weight: 300;">Admin</span></div>
        <span class="logo-badge" style="background:var(--accent-rose)">Console</span>
      </div>

      <nav style="flex:1">
        <ul class="nav-menu">
          <li class="nav-item active">
            <button>
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
              </svg>
              Tenants Registry
            </button>
          </li>
        </ul>
      </nav>

      <div class="sidebar-footer">
        <div style="display:flex;gap:0.5rem;width:100%">
          <a :href="'/?tenant_id=' + activeTenantId" class="btn-primary" style="flex:1;justify-content:center;font-size:0.75rem;gap:0.3rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1)">
            <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
              <polyline points="9 10 4 15 9 20"/>
              <path d="M20 4v7a4 4 0 0 1-4 4H4"/>
            </svg>
            Cashier
          </a>
          <a href="/saas-admin/logout" class="btn-primary" style="flex:1;justify-content:center;font-size:0.75rem;gap:0.3rem;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.25);color:#fb7185">
            <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            Logout
          </a>
        </div>
        <div style="margin-top:1rem;font-size:0.7rem;color:var(--text-muted);text-align:center">
          Branded as a product of <a href="https://ss-labz.pages.dev/" target="_blank" style="color:var(--accent-purple);text-decoration:none;font-weight:600">SS Labz</a>
        </div>
      </div>
    </aside>

    <!-- Main Workspace -->
    <main class="main-content">
      
      <!-- Header bar -->
      <header class="main-header" style="justify-content:space-between">
        <div>
          <h1 class="main-title">SaaS Admin Control Panel</h1>
          <p class="main-subtitle">Administer tenant profiles, manage subscription tiers, and switch tenant scopes.</p>
        </div>
        <div class="header-right">
          <div class="system-status-pill" :class="systemStatusClass">
            <span class="status-pulse"></span>
            <span>Status: @{{ systemStatus }}</span>
          </div>
        </div>
      </header>

      <!-- Panel: SaaS Tenant Admin Control Panel -->
      <div class="admin-grid">
        
        <!-- Left Pane: Register New Tenant Form -->
        <div class="card">
          <h3 class="dashboard-card-title">Provision Tenant</h3>
          <form @submit.prevent="registerNewTenant">
            <div class="admin-form-group" style="margin-bottom:1rem">
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem">Tenant ID / Handle</label>
              <input type="text" class="admin-input" v-model="newTenant.id" placeholder="e.g., tenant_003" style="width:100%" required>
            </div>
            
            <div class="admin-form-group" style="margin-bottom:1rem">
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem">Administrator Name</label>
              <input type="text" class="admin-input" v-model="newTenant.name" placeholder="e.g., John Connor" style="width:100%" required>
            </div>

            <div class="admin-form-group" style="margin-bottom:1rem">
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem">Company Name</label>
              <input type="text" class="admin-input" v-model="newTenant.company_name" placeholder="e.g., Tech Solutions LLC" style="width:100%">
            </div>

            <div class="admin-form-group" style="margin-bottom:1rem">
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem">Subscription Tier</label>
              <select class="select-input" style="width:100%" v-model="newTenant.subscription_tier" required>
                <option value="free">Free Trial</option>
                <option value="basic">Basic SaaS</option>
                <option value="premium">Premium Predictive (Enterprise)</option>
              </select>
            </div>

            <div class="admin-form-group" style="margin-bottom:1.5rem">
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem">VAT / Sales Tax Rate (%)</label>
              <input type="number" step="0.1" min="0" max="100" class="admin-input" v-model.number="newTenant.tax_rate" style="width:100%" placeholder="e.g., 8.0" required>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:1rem;background:var(--gradient-rose);border-color:var(--accent-rose)">
              Register & Initialize Tenant
            </button>
          </form>
        </div>

        <!-- Right Pane: Active Tenant Profiles Grid -->
        <div class="card">
          <h3 class="dashboard-card-title">
            <span>Customer Accounts Registry</span>
            <span class="badge" style="background:rgba(244,63,94,0.15);color:#fb7185;border:1px solid rgba(244,63,94,0.3)">@{{ tenantsList.length }} Registered</span>
          </h3>
          
          <div class="data-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Tenant ID</th>
                  <th>Admin Name</th>
                  <th>Company</th>
                  <th>SaaS Tier</th>
                  <th>VAT Rate</th>
                  <th>System Status</th>
                  <th style="text-align:right">Management Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tenant in tenantsList" :key="tenant.id" :style="tenant.id === activeTenantId ? 'background:rgba(244,63,94,0.03)' : ''">
                  <td>
                    <div style="font-weight:700">@{{ tenant.id }}</div>
                    <span v-if="tenant.id === activeTenantId" class="badge success" style="font-size:0.6rem;padding:0.1rem 0.3rem">Active Context</span>
                  </td>
                  <td>@{{ tenant.name }}</td>
                  <td>@{{ tenant.company_name || 'N/A' }}</td>
                  <td>
                    <select 
                      class="select-input" 
                      style="padding:0.25rem 0.5rem;font-size:0.75rem"
                      v-model="tenant.subscription_tier"
                      @change="changeTenantTier(tenant, tenant.subscription_tier)"
                    >
                      <option value="free">Free</option>
                      <option value="basic">Basic</option>
                      <option value="premium">Premium</option>
                    </select>
                  </td>
                  <td>
                    <div style="display:flex;align-items:center;gap:0.25rem;">
                      <input 
                        type="number" 
                        step="0.1" 
                        min="0" 
                        max="100" 
                        class="admin-input" 
                        style="padding:0.25rem 0.4rem;font-size:0.75rem;width:60px;height:auto" 
                        v-model.number="tenant.tax_rate"
                        @change="changeTenantTaxRate(tenant, tenant.tax_rate)"
                      >
                      <span style="font-size:0.75rem;color:var(--text-secondary);">%</span>
                    </div>
                  </td>
                  <td>
                    <span v-if="tenant.status === 'active'" class="badge success">Active</span>
                    <span v-else class="badge danger">Suspended</span>
                  </td>
                  <td style="text-align:right">
                    <div style="display:inline-flex;gap:0.5rem">
                      <button 
                        class="btn-mini"
                        :class="tenant.id === activeTenantId ? 'success' : 'purple'"
                        :disabled="tenant.status === 'suspended'"
                        @click="switchTenantContext(tenant.id)"
                      >
                        @{{ tenant.id === activeTenantId ? 'Active Scope' : 'Switch Context' }}
                      </button>
                      <button 
                        class="btn-mini"
                        :class="tenant.status === 'active' ? 'danger' : 'success'"
                        @click="toggleTenantStatus(tenant)"
                      >
                        @{{ tenant.status === 'active' ? 'Suspend' : 'Activate' }}
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </main>

    <!-- Toast alerts -->
    <div class="restock-alert-simulation-toast" :class="{ 'show': toastAlert.show }">
      <div class="toast-header">
        <span>@{{ toastAlert.title }}</span>
        <span class="toast-close" @click="toastAlert.show = false">&times;</span>
      </div>
      <div class="toast-body">
        @{{ toastAlert.msg }}
      </div>
    </div>

  </div>

  <script src="/app.js?v={{ time() }}"></script>
  <script>
    // Force Vue app to query tenants on mount since we are on the separate admin view
    window.addEventListener('DOMContentLoaded', () => {
      const checkInterval = setInterval(() => {
        if (window.vm && window.vm.fetchTenants) {
          window.vm.fetchTenants();
          clearInterval(checkInterval);
        }
      }, 50);
    });
  </script>
</body>
</html>
