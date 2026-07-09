<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StockSense AI - Predictive Inventory & Demand Forecasting SaaS</title>
  <link rel="stylesheet" href="/style.css?v={{ time() }}">
  <!-- Load Vue 3 and Chart.js from CDN -->
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div id="app" class="app-container" v-cloak>
    
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
      <div class="logo-container">
        <div class="logo-icon">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="logo-text">StockSense <span style="font-weight: 300;">AI</span></div>
        <span class="logo-badge">SaaS</span>
      </div>

      <nav>
        <ul class="nav-menu">
          <li class="nav-item" :class="{ active: activeTab === 'overview' }">
            <button @click="activeTab = 'overview'">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="3" width="7" height="9" rx="1"/>
                <rect x="14" y="3" width="7" height="5" rx="1"/>
                <rect x="14" y="12" width="7" height="9" rx="1"/>
                <rect x="3" y="16" width="7" height="5" rx="1"/>
              </svg>
              Overview
            </button>
          </li>
          <li class="nav-item" :class="{ active: activeTab === 'forecast' }">
            <button @click="activeTab = 'forecast'">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 3v18h18" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18.5 8.5L13.5 13.5L9.5 9.5L3.5 15.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Demand Forecast
            </button>
          </li>
          <li class="nav-item" :class="{ active: activeTab === 'restock' }">
            <button @click="activeTab = 'restock'">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7l10 5 10-5-10-5z" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 17l10 5 10-5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              Restock Center
            </button>
          </li>
          <li class="nav-item" :class="{ active: activeTab === 'pos' }">
            <button @click="activeTab = 'pos'">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="3" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 8h10M7 12h10M7 16h4" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              POS Terminal
            </button>
          </li>
          <li class="nav-item" :class="{ active: activeTab === 'mlops' }">
            <button @click="activeTab = 'mlops'">
              <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              MLOps Health
            </button>
          </li>
        </ul>
      </nav>

      <div class="sidebar-footer">
        <div class="tenant-selector-card">
          <div class="tenant-avatar">GR</div>
          <div class="tenant-info">
            <div class="tenant-name">Global Goods</div>
            <div class="tenant-role">ID: tenant_001</div>
          </div>
          <svg style="width:16px;height:16px;margin-left:auto;stroke:var(--text-muted);fill:none;stroke-width:2" viewBox="0 0 24 24">
            <path d="M6 9l6 6 6-6"/>
          </svg>
        </div>
        <div style="display:flex;gap:0.5rem;margin-top:0.75rem">
          <a href="/saas-admin" class="btn-primary" style="flex:1;justify-content:center;font-size:0.7rem;gap:0.25rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);padding:0.45rem 0.2rem">
            <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            SaaS Admin
          </a>
          <a href="/logout" class="btn-primary" style="flex:1;justify-content:center;font-size:0.7rem;gap:0.25rem;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.25);color:#fb7185;padding:0.45rem 0.2rem">
            <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
            </svg>
            Logout
          </a>
        </div>
        <div class="branding-footer" style="margin-top: 1rem; text-align: center; font-size: 0.75rem; color: var(--text-muted);">
          A product of <a href="https://ss-labz.pages.dev/" target="_blank" style="color: var(--accent-purple); text-decoration: none; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='#8b5cf6'">SS Labz</a>
        </div>
      </div>
    </aside>

    <!-- Main Content Panel -->
    <main class="main-content">
      
      <!-- Top header -->
      <header class="dashboard-header">
        <div class="header-title-section">
          <h1 v-if="activeTab === 'overview'">SaaS Operations Overview</h1>
          <h1 v-else-if="activeTab === 'forecast'">Predictive Demand Forecaster</h1>
          <h1 v-else-if="activeTab === 'restock'">Inventory & Restocking Center</h1>
          <h1 v-else-if="activeTab === 'pos'">POS Checkout Terminal</h1>
          <h1 v-else-if="activeTab === 'mlops'">MLOps Pipelines & Drift Observability</h1>
          <p v-if="activeTab === 'overview'">Real-time telemetry, ingest status, and recent actions.</p>
          <p v-else-if="activeTab === 'forecast'">Optuna-tuned LightGBM forecasts with price elasticity simulator.</p>
          <p v-else-if="activeTab === 'restock'">Automated supplier notifications and safety stock tracking.</p>
          <p v-else-if="activeTab === 'pos'">Checkout client orders and sync inventory in real-time.</p>
          <p v-else-if="activeTab === 'mlops'">Apache Airflow retraining workflows, MLflow metrics, and drift scoring.</p>
        </div>
        
        <div class="header-actions">
          <div class="status-indicator">
            <span class="pulse-dot" :class="systemStatusClass"></span>
            <span>Status: <strong>@{{ systemStatus }}</strong></span>
          </div>
          <button v-if="activeTab === 'overview'" class="btn-primary" @click="triggerPOSUpload">
            <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
            </svg>
            Ingest POS CSV
          </button>
          <button v-else-if="activeTab === 'mlops'" class="btn-primary" :disabled="isRetraining" @click="triggerRetraining">
            <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
              <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
            @{{ isRetraining ? 'Retraining Models...' : 'Retrain Pipeline' }}
          </button>
        </div>
      </header>

      <!-- KPI Grid -->
      <section class="kpi-grid" v-if="activeTab === 'overview' || activeTab === 'forecast'">
        <!-- Capital Freed -->
        <div class="card emerald">
          <div class="card-header-flex">
            <span class="card-label">Working Capital Saved</span>
            <div class="card-icon-container">
              <svg viewBox="0 0 24 24">
                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
            </div>
          </div>
          <div class="card-value">$@{{ savedCapital.toLocaleString() }}</div>
          <div class="card-subtext">
            <span class="trend-badge positive">+$340 today</span>
            <span>from holding optimizations</span>
          </div>
        </div>

        <!-- Stockout Risks -->
        <div class="card rose">
          <div class="card-header-flex">
            <span class="card-label">Stockout Risks</span>
            <div class="card-icon-container">
              <svg viewBox="0 0 24 24">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01"/>
              </svg>
            </div>
          </div>
          <div class="card-value" :class="{ 'text-rose': stockoutRisks > 0 }">@{{ stockoutRisks }} SKUs</div>
          <div class="card-subtext">
            <span class="trend-badge" :class="stockoutRisks > 0 ? 'negative' : 'positive'">
              @{{ stockoutRisks > 0 ? 'Action Required' : 'All Clear' }}
            </span>
            <span>below safety limits</span>
          </div>
        </div>

        <!-- Model Forecast Accuracy -->
        <div class="card purple">
          <div class="card-header-flex">
            <span class="card-label">Forecast Error (WMAPE)</span>
            <div class="card-icon-container">
              <svg viewBox="0 0 24 24">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
              </svg>
            </div>
          </div>
          <div class="card-value">@{{ wmapeAvg }}%</div>
          <div class="card-subtext">
            <span class="trend-badge positive">-0.4% improvement</span>
            <span>via LightGBM model</span>
          </div>
        </div>

        <!-- Active SKUs -->
        <div class="card blue">
          <div class="card-header-flex">
            <span class="card-label">SKUs Monitored</span>
            <div class="card-icon-container">
              <svg viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l-7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/>
              </svg>
            </div>
          </div>
          <div class="card-value">@{{ activeSkusCount }} items</div>
          <div class="card-subtext">
            <span class="trend-badge positive">100% active</span>
            <span>across categories</span>
          </div>
        </div>
      </section>

      <!-- Panel 1: Overview Dashboard -->
      <div v-if="activeTab === 'overview'" class="dashboard-grid-2x1">
        
        <!-- Left Pane: Quick Forecasting Insight -->
        <div class="card">
          <div class="dashboard-card-title">
            <span>Demand Predictions Visualizer</span>
            <button class="btn-secondary" style="font-size:0.75rem;padding:0.35rem 0.75rem" @click="activeTab = 'forecast'">
              Deep SKU Analytics &rarr;
            </button>
          </div>
          <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:1rem">
            Showing quick forecast telemetry for: 
            <select v-model="selectedSkuId" class="select-input" style="padding:0.25rem 0.5rem;font-size:0.8rem">
              <option v-for="item in skus" :key="item.id" :value="item.id">@{{ item.name }}</option>
            </select>
          </p>
          <div style="height: 280px; position: relative;">
            <canvas id="forecastChartCanvas"></canvas>
          </div>
        </div>

        <!-- Right Pane: SIMULATORS & ALERTS -->
        <div style="display:flex;flex-direction:column;gap:1.5rem">
          <!-- POS Uploader Ingest simulator -->
          <div class="card">
            <h3 class="dashboard-card-title">POS CSV Ingestion Layer</h3>
            <div v-if="simulatedUploadStatus === 'idle'" class="uploader-box" @click="triggerPOSUpload">
              <svg class="uploader-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
              </svg>
              <div class="uploader-title">Ingest Raw Sales Transaction Data</div>
              <div class="uploader-desc">Click to simulate daily client POS CSV upload.</div>
            </div>
            
            <div v-else-if="simulatedUploadStatus === 'uploading'" class="uploader-box" style="border-color:var(--accent-purple);cursor:default">
              <div class="uploader-title" style="color:var(--accent-purple);margin-bottom:0.75rem">Uploading & Validating...</div>
              <div style="margin-bottom:0.5rem;font-size:0.8rem;color:var(--text-secondary)">@{{ simulatedUploadFileName }}</div>
              <div class="progress-bar-container">
                <div class="progress-bar-fill normal" :style="{ width: simulatedUploadProgress + '%' }"></div>
              </div>
              <div class="uploader-desc" style="margin-top:0.75rem">Running Great Expectations schema validations...</div>
            </div>

            <div v-else-if="simulatedUploadStatus === 'success'" class="uploader-box" style="border-color:var(--accent-emerald);background:rgba(16,185,129,0.02);cursor:default">
              <svg class="uploader-icon" style="color:var(--accent-emerald)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
              </svg>
              <div class="uploader-title" style="color:var(--accent-emerald)">Ingestion & Silver Parquet conversion successful</div>
              <div class="uploader-desc">Basmati Rice stock recalculated. Notice Stockout alert raised!</div>
            </div>
          </div>

          <!-- Supplier Alert timeline feed -->
          <div class="card" style="flex:1">
            <h3 class="dashboard-card-title">Supplier Notifications</h3>
            <div class="alerts-timeline">
              <div v-for="alert in supplierAlerts.slice(0, 3)" :key="alert.id" class="timeline-item">
                <div class="timeline-icon">
                  <svg v-if="alert.channel === 'WhatsApp'" viewBox="0 0 24 24" style="stroke:#25d366;fill:none;stroke-width:2">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" style="stroke:#3b82f6;fill:none;stroke-width:2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                </div>
                <div class="timeline-content">
                  <div class="timeline-meta">
                    <span>@{{ alert.time }}</span>
                    <span class="badge" :class="{ 'success': alert.status === 'Confirmed', 'warning': alert.status === 'Dispatched', 'danger': alert.status === 'Awaiting action' }">
                      @{{ alert.status }}
                    </span>
                  </div>
                  <div class="timeline-title">@{{ alert.skuName }}</div>
                  <div class="timeline-desc">@{{ alert.desc }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Panel 2: Demand Forecast deep analytics -->
      <div v-else-if="activeTab === 'forecast'" class="dashboard-grid-1x2">
        <!-- Control board / elastic price simulation -->
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between">
          <div>
            <h3 class="dashboard-card-title">SKU Analytics Selector</h3>
            <div style="margin-bottom:1.5rem">
              <label style="font-size:0.8rem;color:var(--text-muted);display:block;margin-bottom:0.5rem">Select Item</label>
              <select v-model="selectedSkuId" class="select-input" style="width:100%">
                <option v-for="item in skus" :key="item.id" :value="item.id">@{{ item.name }}</option>
              </select>
            </div>
            
            <div v-if="selectedSku" class="elasticity-accent-card" style="margin-bottom:1.5rem">
              <div style="font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);font-weight:600">Model Stats</div>
              <div style="display:flex;justify-content:space-between;margin-top:0.4rem">
                <span style="font-size:0.85rem">LGBM SKU Error (WMAPE):</span>
                <strong style="color:var(--accent-purple)">@{{ selectedSku.wmape }}%</strong>
              </div>
              <div style="display:flex;justify-content:space-between;margin-top:0.4rem">
                <span style="font-size:0.85rem">Price Elasticity Beta:</span>
                <strong style="color:var(--accent-blue)">@{{ selectedSku.elasticity }}</strong>
              </div>
              <div style="display:flex;justify-content:space-between;margin-top:0.4rem">
                <span style="font-size:0.85rem">Base Retail Price:</span>
                <strong>$@{{ selectedSku.price.toFixed(2) }}</strong>
              </div>
            </div>
          </div>

          <!-- Price Elasticity Interactive Simulator -->
          <div class="simulator-panel">
            <div class="simulator-header">
              <svg style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              <span>Price Elasticity Simulator</span>
            </div>
            <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:1.25rem">
              Adjust price dynamically to preview how sales volumes shift based on client-trained model elasticity logs.
            </p>

            <div class="slider-container">
              <label>Price Shift</label>
              <input type="range" min="-20" max="20" step="1" v-model.number="priceAdjustment" class="range-slider">
              <span class="slider-val">@{{ priceAdjustment > 0 ? '+' : '' }}@{{ priceAdjustment }}%</span>
            </div>

            <div class="elasticity-result">
              <span>Adjusted SKU Price:</span>
              <strong style="color:var(--text-primary)">$@{{ simulatedPrice }}</strong>
            </div>
            
            <div class="elasticity-result" style="border:none;margin-top:0.25rem">
              <span>Expected Sales Shift:</span>
              <span class="highlight" :class="{ negative: demandShiftPercent < 0 }">
                @{{ demandShiftPercent > 0 ? '+' : '' }}@{{ demandShiftPercent }}%
              </span>
            </div>
          </div>
        </div>

        <!-- The main chart card -->
        <div class="card">
          <div class="dashboard-card-title" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem">
            <div>
              <span>Dynamic Demand Forecast & Confidence Bounds</span>
              <div style="font-size:0.75rem;color:var(--text-muted);font-weight:normal;margin-top:0.25rem">
                <span style="display:inline-block;width:12px;height:12px;background:rgba(139,92,246,0.15);border:1px dashed var(--accent-purple);margin-right:4px;vertical-align:middle"></span>
                95% Confidence Interval (Drift Bounds)
              </div>
            </div>
            <div style="display:inline-flex;gap:0.5rem;align-items:center">
              <label style="font-size:0.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase">Active Model:</label>
              <select v-model="activeModel" class="select-input" style="padding:0.35rem 0.75rem;font-size:0.75rem;width:auto">
                <option value="lightgbm">LightGBM (Optimal)</option>
                <option value="prophet">Prophet (Seasonal)</option>
                <option value="lstm">LSTM (Deep Learning)</option>
              </select>
            </div>
          </div>
          <div style="height: 380px; position: relative;">
            <canvas id="forecastChartCanvas"></canvas>
          </div>
          <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1rem;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border-glass)">
            <div style="text-align:center">
              <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:0.25rem">Current Safety Stock</div>
              <strong style="font-size:1.1rem;color:var(--accent-rose)">@{{ selectedSku.safetyStock }} units</strong>
            </div>
            <div style="text-align:center">
              <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:0.25rem">Days of Inventory Left</div>
              <strong style="font-size:1.1rem;color:var(--accent-amber)">@{{ Math.ceil(selectedSku.currentStock / (selectedSku.forecast ? (selectedSku.forecast[0] || 10) : 10)) }} Days</strong>
            </div>
            <div style="text-align:center">
              <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:0.25rem">Recommended Order Qty</div>
              <strong style="font-size:1.1rem;color:var(--accent-emerald)">@{{ selectedSku.reorderQty }} units</strong>
            </div>
          </div>

          <!-- Model comparison info grid -->
          <div class="model-specs-grid">
            <div class="model-spec-card" :class="{ active: activeModel === 'lightgbm' }">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
                <strong style="font-size:0.85rem">LightGBM</strong>
                <span class="model-comparison-badge lgb" v-if="activeModel === 'lightgbm'">Active</span>
                <span class="model-comparison-badge" style="background:rgba(255,255,255,0.03);color:var(--text-muted);border:1px solid rgba(255,255,255,0.08)" v-else>Overlay</span>
              </div>
              <div style="font-size:0.72rem;color:var(--text-secondary);line-height:1.4">
                Best for pricing elasticities and tabular attributes. High sensitivity to price adjustments.
              </div>
            </div>

            <div class="model-spec-card" :class="{ active: activeModel === 'prophet' }">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
                <strong style="font-size:0.85rem">Prophet</strong>
                <span class="model-comparison-badge prophet" v-if="activeModel === 'prophet'">Active</span>
                <span class="model-comparison-badge" style="background:rgba(255,255,255,0.03);color:var(--text-muted);border:1px solid rgba(255,255,255,0.08)" v-else>Overlay</span>
              </div>
              <div style="font-size:0.72rem;color:var(--text-secondary);line-height:1.4">
                Best for strong holiday seasonal patterns. Additive regression model with Fourier smoothing.
              </div>
            </div>

            <div class="model-spec-card" :class="{ active: activeModel === 'lstm' }">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem">
                <strong style="font-size:0.85rem">LSTM (RNN)</strong>
                <span class="model-comparison-badge lstm" v-if="activeModel === 'lstm'">Active</span>
                <span class="model-comparison-badge" style="background:rgba(255,255,255,0.03);color:var(--text-muted);border:1px solid rgba(255,255,255,0.08)" v-else>Overlay</span>
              </div>
              <div style="font-size:0.72rem;color:var(--text-secondary);line-height:1.4">
                Deep learning recurrent neural networks. Captures non-linear dependencies in transaction records.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel 3: Restock Center (Table & Auto restock dispatch) -->
      <div v-else-if="activeTab === 'restock'" class="dashboard-grid-1x1">
        <div class="card">
          <h3 class="dashboard-card-title">
            <span>Inventory SKUs & Automation Triggers</span>
            <div class="card-header-actions">
              <button class="btn-secondary" style="font-size:0.8rem" @click="triggerPOSUpload">Simulate Sales Increase (POS Upload)</button>
            </div>
          </h3>

          <div class="data-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product Details</th>
                  <th>Stock Levels & Safety thresholds</th>
                  <th>Lead Time</th>
                  <th>Auto-PO Target</th>
                  <th>Supplier Channels</th>
                  <th>System Status</th>
                  <th style="text-align:right">Action Dispatcher</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sku in skus" :key="sku.id" :class="{ 'highlight-flash': sku.currentStock <= sku.safetyStock && sku.restockStatus === 'Low Stock' }">
                  <td>
                    <div class="sku-name-cell">
                      <strong>@{{ sku.name }}</strong>
                      <span class="sku-code">@{{ sku.sku }} | <i>@{{ sku.category }}</i></span>
                    </div>
                  </td>
                  <td>
                    <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:0.25rem">
                      <span>Stock: <strong>@{{ sku.currentStock }}</strong> / @{{ sku.maxStock }} max</span>
                      <span style="color:var(--text-muted)">Safety: @{{ sku.safetyStock }}</span>
                    </div>
                    <div class="progress-bar-container">
                      <div 
                        class="progress-bar-fill" 
                        :class="{ 
                          'danger': sku.currentStock <= sku.safetyStock / 2, 
                          'warning': sku.currentStock <= sku.safetyStock && sku.currentStock > sku.safetyStock / 2, 
                          'normal': sku.currentStock > sku.safetyStock 
                        }" 
                        :style="{ width: Math.min((sku.currentStock / sku.maxStock) * 100, 100) + '%' }"
                      ></div>
                    </div>
                  </td>
                  <td><strong>@{{ sku.leadTime }} Days</strong></td>
                  <td><strong>@{{ sku.reorderQty }} units</strong></td>
                  <td>
                    <div style="display:flex;flex-direction:column;font-size:0.8rem">
                      <strong>@{{ sku.supplier }}</strong>
                      <span style="color:var(--text-muted)">@{{ sku.alertChannel }} (@{{ sku.supplierContact }})</span>
                    </div>
                  </td>
                  <td>
                    <span v-if="sku.restockStatus === 'Normal'" class="badge success">Healthy</span>
                    <span v-else-if="sku.restockStatus === 'Low Stock'" class="badge danger">Safety Violated</span>
                    <span v-else-if="sku.restockStatus === 'Dispatched'" class="badge warning">Auto-PO Dispatched</span>
                    <span v-else-if="sku.restockStatus === 'Confirmed'" class="badge success">PO Confirmed</span>
                  </td>
                  <td style="text-align:right">
                    <button 
                      class="btn-primary" 
                      style="font-size:0.75rem;padding:0.4rem 0.8rem;margin-left:auto"
                      :disabled="sku.restockStatus === 'Dispatched' || sku.restockStatus === 'Confirmed'"
                      :style="{ opacity: (sku.restockStatus === 'Dispatched' || sku.restockStatus === 'Confirmed') ? 0.6 : 1 }"
                      @click="sendRestockAlert(sku)"
                    >
                      <svg v-if="sku.alertChannel === 'WhatsApp'" viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                      </svg>
                      <svg v-else viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                      </svg>
                      @{{ sku.restockStatus === 'Normal' ? 'Dispatch PO' : 
                         sku.restockStatus === 'Low Stock' ? 'Trigger PO Alert' : 
                         sku.restockStatus === 'Dispatched' ? 'Pending Supplier...' : 'Supplier Confirmed' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Panel: POS Terminal -->
      <div v-else-if="activeTab === 'pos'">
        
        <!-- Offline Warning Banner -->
        <div v-if="!isOnline" class="offline-banner">
          <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
            <line x1="1" y1="1" x2="23" y2="23"/>
            <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.5M5 12.5a10.94 10.94 0 0 1 5.83-2.84"/>
            <path d="M12 18.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>
          </svg>
          <span>Offline Mode Active. Transactions will be cached locally and synced on reconnection.</span>
        </div>

        <!-- Barcode Reader Control Panel -->
        <div class="barcode-scanner-container">
          <div class="barcode-input-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 18H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2M19 6h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2M12 6v12M8 6v12M16 6v12"/>
            </svg>
            <input 
              type="text" 
              class="barcode-input" 
              :class="barcodeScanClass"
              placeholder="Scan item barcode or type SKU (e.g. SKU-RICE-001) & press Enter..." 
              v-model="barcodeQuery"
              @focus="showBarcodeSuggestions = true"
              @keydown.down.prevent="moveSuggestionActive(1)"
              @keydown.up.prevent="moveSuggestionActive(-1)"
              @keydown.enter.prevent="selectActiveSuggestion"
            >
            
            <!-- Smart Suggestions Dropdown -->
            <div class="barcode-suggestions-dropdown" v-if="showBarcodeSuggestions && barcodeSuggestions.length > 0">
              <div 
                v-for="(product, idx) in barcodeSuggestions" 
                :key="product.id"
                class="barcode-suggestion-item"
                :class="{ active: idx === activeSuggestionIndex }"
                @click="addSkuByBarcode(product.sku); showBarcodeSuggestions = false;"
                @mouseenter="activeSuggestionIndex = idx"
              >
                <div class="suggestion-info">
                  <span class="suggestion-name">@{{ product.name }}</span>
                  <span class="suggestion-sku">SKU: @{{ product.sku }}</span>
                </div>
                <div class="suggestion-meta">
                  <span class="badge" :class="product.currentStock > 10 ? 'success' : 'warning'" style="font-size: 0.65rem; padding: 0.1rem 0.35rem; border-radius: 4px;">
                    @{{ product.currentStock }} in stock
                  </span>
                  <span class="suggestion-price">$@{{ product.price.toFixed(2) }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="scanner-badge">
            <div class="scanner-pulse"></div>
            <span>POS Laser Reader Active</span>
          </div>
        </div>

        <div class="pos-terminal-grid">
        
        <!-- Left Pane: Product Catalog -->
        <div class="card">
          <h3 class="dashboard-card-title" style="display:flex;justify-content:space-between;align-items:center;">
            <span>Product Catalog</span>
            <button class="btn-primary" style="padding:0.35rem 0.75rem;font-size:0.75rem;gap:0.35rem;background:var(--gradient-purple)" @click="showAddProductModal = true">
              <svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
              Add Item
            </button>
          </h3>
          <div class="pos-catalog-grid">
            <div v-for="product in skus" :key="product.id" class="pos-product-card">
              <div>
                <div style="font-weight:700;font-size:1.05rem;color:var(--text-primary);">@{{ product.name }}</div>
                <div class="pos-product-meta">SKU: @{{ product.sku }}</div>
                <div class="pos-product-meta">Category: <i>@{{ product.category }}</i></div>
              </div>
              
              <div style="margin-top:1rem;display:flex;justify-content:space-between;align-items:center;">
                <div class="pos-product-price">$@{{ product.price.toFixed(2) }}</div>
                <div>
                  <span v-if="product.currentStock <= 0" class="badge danger">Out of Stock</span>
                  <span v-else-if="product.currentStock <= product.safetyStock" class="badge warning">Low Stock (@{{ product.currentStock }})</span>
                  <span v-else class="badge success">Stock: @{{ product.currentStock }}</span>
                </div>
              </div>

              <button 
                class="btn-primary" 
                style="font-size:0.8rem;padding:0.4rem 0.8rem;margin-top:1rem;width:100%;justify-content:center;"
                :disabled="product.currentStock <= 0"
                @click="addToCart(product)"
              >
                Add to Cart
              </button>
            </div>
          </div>
        </div>

        <!-- Right Pane: Checkout Cart -->
        <div class="card pos-cart-panel">
          <div>
            <h3 class="dashboard-card-title">
              <span>Checkout Cart</span>
              <span class="badge purple" style="font-size:0.75rem;">@{{ cart.length }} items</span>
            </h3>

            <div v-if="cart.length === 0" style="text-align:center;padding:3rem 0;color:var(--text-muted);">
              <svg style="width:48px;height:48px;margin:0 auto 1rem;stroke:currentColor;fill:none;stroke-width:1.5" viewBox="0 0 24 24">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
              </svg>
              <div>Your cart is empty.</div>
              <div style="font-size:0.75rem;margin-top:0.25rem;">Select products from the catalog to add them here.</div>
            </div>

            <div v-else class="cart-items-list">
              <div v-for="item in cart" :key="item.product.id" class="cart-item-row">
                <div style="flex:1;padding-right:0.5rem;">
                  <div style="font-weight:600;font-size:0.85rem;">@{{ item.product.name }}</div>
                  <div style="font-size:0.75rem;color:var(--text-muted);">$@{{ item.product.price.toFixed(2) }} each</div>
                </div>

                <div class="qty-counter">
                  <button class="qty-btn" @click="removeFromCart(item)">&minus;</button>
                  <span style="font-weight:600;font-size:0.85rem;min-width:16px;text-align:center;">@{{ item.quantity }}</span>
                  <button class="qty-btn" @click="addToCart(item.product)">&plus;</button>
                </div>

                <div style="min-width:60px;text-align:right;font-weight:600;font-size:0.85rem;margin-left:0.75rem;">
                  $@{{ (item.product.price * item.quantity).toFixed(2) }}
                </div>

                <button 
                  style="background:transparent;border:none;color:var(--accent-rose);cursor:pointer;margin-left:0.5rem;display:flex;align-items:center;"
                  @click="deleteFromCart(item)"
                >
                  <svg style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div>
            <!-- Discount Option Panel -->
            <div v-if="cart.length > 0" style="margin-bottom:1rem;padding:0.75rem 1rem;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);border-radius:8px;">
              <div style="font-size:0.7rem;font-weight:700;color:var(--text-muted);margin-bottom:0.4rem;text-transform:uppercase;letter-spacing:0.025em;display:flex;justify-content:space-between;align-items:center;">
                <span>Cart Discount</span>
                <span v-if="discountValue > 0" style="color:#fb7185;font-weight:700;">
                  - $@{{ discountAmount.toFixed(2) }}
                </span>
              </div>
              <div style="display:flex;gap:0.5rem;">
                <div style="display:flex;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:6px;padding:0.15rem;width:80px;">
                  <button 
                    type="button" 
                    style="flex:1;border:none;border-radius:4px;font-size:0.7rem;font-weight:700;cursor:pointer;transition:all 0.15s;padding:0.15rem 0;"
                    :style="discountType === 'percent' ? 'background:var(--accent-purple);color:#fff;' : 'background:transparent;color:var(--text-secondary);'"
                    @click="discountType = 'percent'"
                  >
                    %
                  </button>
                  <button 
                    type="button" 
                    style="flex:1;border:none;border-radius:4px;font-size:0.7rem;font-weight:700;cursor:pointer;transition:all 0.15s;padding:0.15rem 0;"
                    :style="discountType === 'value' ? 'background:var(--accent-purple);color:#fff;' : 'background:transparent;color:var(--text-secondary);'"
                    @click="discountType = 'value'"
                  >
                    $
                  </button>
                </div>
                <div style="position:relative;flex:1;">
                  <input 
                    type="number" 
                    class="admin-input" 
                    style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%;box-sizing:border-box;"
                    min="0"
                    :max="discountType === 'percent' ? 100 : cartSubtotal"
                    placeholder="Discount value..."
                    v-model.number="discountValue"
                  >
                  <span style="position:absolute;right:0.6rem;top:50%;transform:translateY(-50%);font-size:0.75rem;color:var(--text-muted);pointer-events:none;">
                    @{{ discountType === 'percent' ? '%' : '$' }}
                  </span>
                </div>
              </div>
            </div>

            <div class="cart-totals-section">
              <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--text-secondary);">Subtotal:</span>
                <strong>$@{{ cartSubtotal.toFixed(2) }}</strong>
              </div>
              <div v-if="discountValue > 0" style="display:flex;justify-content:space-between;color:#fb7185;">
                <span>Discount (@{{ discountType === 'percent' ? discountValue + '%' : '$' + discountValue }}):</span>
                <strong>- $@{{ discountAmount.toFixed(2) }}</strong>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <span style="color:var(--text-secondary);">Sales Tax (8%):</span>
                <strong>$@{{ cartTax.toFixed(2) }}</strong>
              </div>
              <div style="display:flex;justify-content:space-between;border-top:1px solid rgba(255,255,255,0.05);padding-top:0.5rem;font-size:1.05rem;color:var(--text-primary);">
                <span>Total Amount:</span>
                <strong style="color:var(--accent-emerald);">$@{{ cartTotal.toFixed(2) }}</strong>
              </div>
            </div>

            <button 
              class="btn-primary" 
              style="width:100%;margin-top:1.25rem;justify-content:center;font-size:0.95rem;padding:0.75rem;"
              :disabled="cart.length === 0"
              @click="submitCheckout"
            >
              Complete Sale (Checkout)
            </button>
          </div>
        </div>

      </div>

      </div>

      <!-- Panel 4: MLOps Model Health & Drift Monitor -->
      <div v-else-if="activeTab === 'mlops'" class="dashboard-grid-1x2">
        
        <!-- ML Telemetry Observables -->
        <div style="display:flex;flex-direction:column;gap:1.5rem">
          <!-- Drift Score Gauge -->
          <div class="card">
            <h3 class="dashboard-card-title">Model Drift Score</h3>
            <div style="text-align:center;padding:1.5rem 0">
              <div 
                style="font-family:var(--font-display);font-size:3.5rem;font-weight:800;line-height:1"
                :class="modelDriftScore >= 5.0 ? 'text-rose' : 'text-emerald'"
              >
                @{{ modelDriftScore }}%
              </div>
              <p style="font-size:0.8rem;color:var(--text-secondary);margin-top:0.5rem">
                Acceptable limits: <strong>&lt; 5.0%</strong>
              </p>
              
              <div style="margin-top:1.5rem">
                <span v-if="modelDriftScore >= 5.0" class="badge danger" style="padding:0.5rem 1rem">
                  Drift Threshold Exceeded - Retraining Required
                </span>
                <span v-else class="badge success" style="padding:0.5rem 1rem">
                  Inference Models Performing Stable
                </span>
              </div>
            </div>
          </div>

          <!-- Drift Autorecovery Threshold Configuration -->
          <div class="card drift-config-card">
            <h3 class="dashboard-card-title">Drift Autorecovery Limits</h3>
            <p style="font-size:0.75rem;color:var(--text-muted);line-height:1.4">
              Configure the threshold tolerance for automatic model recalibration triggers. If actual sales diverge beyond limits, the Airflow pipeline fires auto-recovery retraining.
            </p>
            
            <div class="drift-slider-container">
              <span style="font-size:0.8rem;color:var(--text-secondary)">Autorecovery Limit:</span>
              <span class="drift-limit-badge">@{{ driftLimit.toFixed(1) }}% Drift</span>
            </div>
            
            <input 
              type="range" 
              min="1.0" 
              max="9.0" 
              step="0.5" 
              v-model.number="driftLimit" 
              class="range-slider" 
              style="width:100%;margin-top:0.25rem"
            >
            
            <div 
              class="drift-status-indicator"
              :class="modelDriftScore > driftLimit ? 'critical' : 'normal'"
            >
              <div class="scanner-pulse" style="background:#f43f5e;animation-duration:1s" v-if="modelDriftScore > driftLimit"></div>
              <div class="scanner-pulse" style="background:#10b981" v-else></div>
              <span>
                @{{ modelDriftScore > driftLimit ? 'CRITICAL DRIFT - RECOVERY RETRAIN QUEUED' : 'STABLE - WITHIN OPERATION TOLERANCES' }}
              </span>
            </div>
          </div>

          <!-- MLflow Registry Telemetry -->
          <div class="card" style="flex:1">
            <h3 class="dashboard-card-title">Model Artifacts Registry</h3>
            <div class="elasticity-accent-card" style="margin-bottom:1rem">
              <div style="font-size:0.8rem">Forecasting Model: <strong>LightGBM Regressor</strong></div>
              <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem">Optuna Auto-hyperparameter Tuning</div>
            </div>
            
            <div style="display:flex;flex-direction:column;gap:0.75rem;font-size:0.85rem">
              <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.03);padding-bottom:0.5rem">
                <span style="color:var(--text-secondary)">MinIO Data Lake:</span>
                <span style="font-family:monospace">s3://tenant_1/silver/</span>
              </div>
              <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.03);padding-bottom:0.5rem">
                <span style="color:var(--text-secondary)">MLflow Run:</span>
                <span style="font-family:monospace" :class="{ 'text-purple': !isRetraining }">@{{ isRetraining ? 'running...' : 'run_89a01x' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.03);padding-bottom:0.5rem">
                <span style="color:var(--text-secondary)">Optuna Trials:</span>
                <span>50 rounds / SKU</span>
              </div>
              <div style="display:flex;justify-content:space-between">
                <span style="color:var(--text-secondary)">Drift Detection Interval:</span>
                <span>Every 24 hrs (Airflow DAG)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Airflow Orchestration Console Logs -->
        <div class="card">
          <div class="dashboard-card-title">
            <span>Airflow Orchestration Console Logs</span>
            <span class="badge warning" v-if="isRetraining">DAG running</span>
            <span class="badge success" v-else>DAG idle</span>
          </div>
          <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:1rem">
            Tracks nightly pipelines: POS Ingest &rarr; Data Integrity Checks (Great Expectations) &rarr; Parquet compression &rarr; Feature Engineering (lag aggregates) &rarr; LightGBM fit.
          </p>

          <div class="console-box" id="consoleBox">
            <div v-for="(log, idx) in mlopsLogs" :key="idx" class="console-line">
              <span class="console-time">[@{{ log.time }}]</span>
              <span class="console-text" :class="log.type">&gt;&gt; @{{ log.text }}</span>
            </div>
            <div class="console-line" v-if="isRetraining">
              <span class="console-time">[@{{ new Date().toLocaleTimeString() }}]</span>
              <span class="console-text info" style="animation:pulse 1s infinite">&gt;&gt; Processing...</span>
            </div>
          </div>

          <div style="margin-top:1.25rem;display:flex;justify-content:flex-end">
            <button class="btn-primary" :disabled="isRetraining" @click="triggerRetraining">
              <svg style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
              </svg>
              @{{ isRetraining ? 'Retraining Models (Streaming Logs)...' : 'Trigger Nightly Retrain Pipeline' }}
            </button>
          </div>
        </div>
      </div>

    </main>

    <!-- Simulated Whatsapp/Email alert floating Toast notifications -->
    <div class="restock-alert-simulation-toast" :class="{ 'show': toastAlert.show }">
      <div class="toast-header">
        <span>@{{ toastAlert.title }}</span>
        <span v-if="toastAlert.channel.includes('WhatsApp')" class="badge-whatsapp">WhatsApp Cloud API</span>
        <span v-else class="badge" style="background:rgba(59,130,246,0.15);color:#60a5fa;border:1px solid rgba(59,130,246,0.3);font-size:0.65rem;padding:0.05rem 0.25rem;border-radius:4px">SMTP Server</span>
        <span class="toast-close" @click="toastAlert.show = false">&times;</span>
      </div>
      <div class="toast-body">
        @{{ toastAlert.msg }}
      </div>
    </div>

    <!-- Receipt Invoice Modal Overlay -->
    <div class="modal-overlay" v-if="showReceiptModal && receiptData">
      <div class="receipt-modal">
        <div style="text-align:center;">
          <div style="font-size:1.5rem;font-weight:800;font-family:var(--font-display);color:var(--accent-purple);">StockSense AI</div>
          <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:0.25rem;">A Product of SS Labz</div>
          <div v-if="receiptData.isOffline" class="badge danger" style="margin-top:0.5rem;font-size:0.65rem;">Offline Checkout Cash Receipt</div>
          <div v-else class="badge success" style="margin-top:0.5rem;font-size:0.65rem;">Official Cloud Checkout Invoice</div>
        </div>

        <div class="receipt-divider"></div>

        <div style="font-size:0.8rem;color:var(--text-secondary);display:flex;flex-direction:column;gap:0.25rem;">
          <div style="display:flex;justify-content:space-between;">
            <span>Receipt ID:</span>
            <span style="color:var(--text-primary);font-weight:600;">@{{ receiptData.id }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span>Date & Time:</span>
            <span style="color:var(--text-primary);">@{{ receiptData.time }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span>Tenant Scope:</span>
            <span style="color:var(--text-primary);font-weight:600;">@{{ receiptData.tenant_id.toUpperCase() }}</span>
          </div>
        </div>

        <div class="receipt-divider"></div>

        <!-- Itemized details -->
        <div style="display:flex;flex-direction:column;gap:0.5rem;max-height:150px;overflow-y:auto;margin:1rem 0;">
          <div v-for="item in receiptData.items" :key="item.sku" style="display:flex;justify-content:space-between;font-size:0.85rem;">
            <span>@{{ item.name }} (x@{{ item.quantity }})</span>
            <strong style="color:var(--text-primary);">$@{{ item.subtotal.toFixed(2) }}</strong>
          </div>
        </div>

        <div class="receipt-divider"></div>

        <div style="font-size:0.85rem;color:var(--text-secondary);display:flex;flex-direction:column;gap:0.35rem;">
          <div style="display:flex;justify-content:space-between;">
            <span>Subtotal:</span>
            <span style="color:var(--text-primary);">$@{{ receiptData.subtotal.toFixed(2) }}</span>
          </div>
          <div v-if="receiptData.discount > 0" style="display:flex;justify-content:space-between;color:#fb7185;">
            <span>Discount Applied:</span>
            <strong>- $@{{ receiptData.discount.toFixed(2) }}</strong>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span>Sales Tax (8%):</span>
            <span style="color:var(--text-primary);">$@{{ receiptData.tax.toFixed(2) }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:1.1rem;color:var(--text-primary);border-top:1px solid rgba(255,255,255,0.05);padding-top:0.5rem;margin-top:0.25rem;">
            <span>Total Paid:</span>
            <strong style="color:var(--accent-emerald);">$@{{ receiptData.total.toFixed(2) }}</strong>
          </div>
        </div>

        <div style="margin-top:2rem;display:flex;gap:0.75rem;">
          <button class="btn-secondary" style="flex:1;justify-content:center;padding:0.65rem;" @click="showReceiptModal = false">
            Close Panel
          </button>
          <button class="btn-primary" style="flex:1;justify-content:center;padding:0.65rem;" @click="printReceipt">
            Print Invoice
          </button>
        </div>
      </div>
    </div>

    <!-- Printable Receipt Layout (Hidden on Screen via CSS) -->
    <div class="print-only-receipt" v-if="receiptData">
      <div style="text-align:center;font-weight:bold;font-size:16px;">STOCKSENSE AI</div>
      <div style="text-align:center;font-size:11px;">A PRODUCT OF SS LABZ</div>
      <div style="text-align:center;font-size:11px;margin-bottom:15px;">TENANT SCOPE: @{{ receiptData.tenant_id.toUpperCase() }}</div>
      
      <div>========================================</div>
      <div>RECEIPT ID: @{{ receiptData.id }}</div>
      <div>DATE/TIME : @{{ receiptData.time }}</div>
      <div v-if="receiptData.isOffline">MODE      : OFFLINE CACHED TRANSACTION</div>
      <div v-else>MODE      : ONLINE CLOUD APPROVED</div>
      <div>========================================</div>
      
      <div style="margin:10px 0;">
        <div v-for="item in receiptData.items" :key="item.sku" style="display:flex;justify-content:space-between;">
          <span>@{{ item.name.toUpperCase() }} x@{{ item.quantity }}</span>
          <span>$@{{ item.subtotal.toFixed(2) }}</span>
        </div>
      </div>
      
      <div>========================================</div>
      <div style="display:flex;justify-content:space-between;">
        <span>SUBTOTAL</span>
        <span>$@{{ receiptData.subtotal.toFixed(2) }}</span>
      </div>
      <div v-if="receiptData.discount > 0" style="display:flex;justify-content:space-between;">
        <span>DISCOUNT</span>
        <span>- $@{{ receiptData.discount.toFixed(2) }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;">
        <span>SALES TAX (8%)</span>
        <span>$@{{ receiptData.tax.toFixed(2) }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px;margin-top:5px;">
        <span>TOTAL PAID</span>
        <span>$@{{ receiptData.total.toFixed(2) }}</span>
      </div>
      <div>========================================</div>
      
      <div style="text-align:center;margin-top:20px;font-size:10px;">
        THANK YOU FOR YOUR BUSINESS!
      </div>
    </div>

    <!-- Add Product Modal Overlay -->
    <div class="modal-overlay" v-if="showAddProductModal">
      <div class="receipt-modal" :style="addProductTab === 'batch' ? 'max-width: 95%; width: 1100px; padding: 2rem;' : 'max-width: 500px; padding: 2.25rem;'">
        <div style="text-align:center;margin-bottom:1rem">
          <div style="font-size:1.5rem;font-weight:800;font-family:var(--font-display);color:var(--accent-purple);">Add Product to Catalog</div>
          <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:0.25rem;">Provision SKUs under the active tenant context.</div>
        </div>

        <!-- Tab Switcher -->
        <div style="display:flex;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:8px;padding:0.25rem;margin-bottom:1.5rem">
          <button 
            type="button" 
            style="flex:1;padding:0.5rem;border-radius:6px;font-size:0.8rem;font-weight:600;border:none;cursor:pointer;transition:all 0.2s;"
            :style="addProductTab === 'single' ? 'background:var(--accent-purple);color:#fff;' : 'background:transparent;color:var(--text-secondary);'"
            @click="addProductTab = 'single'"
          >
            Single SKU Form
          </button>
          <button 
            type="button" 
            style="flex:1;padding:0.5rem;border-radius:6px;font-size:0.8rem;font-weight:600;border:none;cursor:pointer;transition:all 0.2s;"
            :style="addProductTab === 'batch' ? 'background:var(--accent-purple);color:#fff;' : 'background:transparent;color:var(--text-secondary);'"
            @click="addProductTab = 'batch'"
          >
            Batch List Table
          </button>
        </div>

        <!-- Tab A: Single SKU Form -->
        <form @submit.prevent="submitNewProduct" v-if="addProductTab === 'single'">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
            <div>
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Product Name</label>
              <input type="text" class="admin-input" v-model="newProductData.name" placeholder="e.g. Organic Jasmine Rice" style="width:100%;box-sizing:border-box" required>
            </div>
            <div>
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">SKU Code</label>
              <input type="text" class="admin-input" v-model="newProductData.sku" placeholder="e.g. SKU-RICE-002" style="width:100%;box-sizing:border-box" required>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
            <div>
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Category</label>
              <input type="text" class="admin-input" v-model="newProductData.category" placeholder="e.g. Grains" style="width:100%;box-sizing:border-box" required>
            </div>
            <div>
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Price ($)</label>
              <input type="number" step="0.01" min="0.01" class="admin-input" v-model.number="newProductData.price" style="width:100%;box-sizing:border-box" required>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem;margin-bottom:1rem">
            <div>
              <label style="font-size:0.7rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Initial Stock</label>
              <input type="number" min="0" class="admin-input" v-model.number="newProductData.current_stock" style="width:100%;box-sizing:border-box" required>
            </div>
            <div>
              <label style="font-size:0.7rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Safety Stock</label>
              <input type="number" min="0" class="admin-input" v-model.number="newProductData.safety_stock" style="width:100%;box-sizing:border-box" required>
            </div>
            <div>
              <label style="font-size:0.7rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Max Capacity</label>
              <input type="number" min="1" class="admin-input" v-model.number="newProductData.max_stock" style="width:100%;box-sizing:border-box" required>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
            <div>
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Reorder Qty</label>
              <input type="number" min="1" class="admin-input" v-model.number="newProductData.reorder_qty" style="width:100%;box-sizing:border-box" required>
            </div>
            <div>
              <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Lead Time (Days)</label>
              <input type="number" min="1" class="admin-input" v-model.number="newProductData.lead_time_days" style="width:100%;box-sizing:border-box" required>
            </div>
          </div>

          <div style="margin-bottom:1.5rem">
            <label style="font-size:0.75rem;color:var(--text-secondary);display:block;margin-bottom:0.4rem;text-transform:uppercase;font-weight:600">Supplier Connection</label>
            <select class="select-input" style="width:100%" v-model="newProductData.supplier_id" required>
              <option value="" disabled>Select Supplier...</option>
              <option value="1">AgriBulk Corp</option>
              <option value="2">GrownOrganic Farms</option>
              <option value="3">VeggieDistributors</option>
            </select>
          </div>

          <div style="display:flex;gap:0.75rem">
            <button type="button" class="btn-secondary" style="flex:1;justify-content:center;padding:0.65rem" @click="showAddProductModal = false">
              Cancel
            </button>
            <button type="submit" class="btn-primary" style="flex:1;justify-content:center;padding:0.65rem;background:var(--gradient-purple)">
              Create SKU Item
            </button>
          </div>
        </form>

        <!-- Tab B: Batch List Table -->
        <form @submit.prevent="submitNewProductBatch" v-else-if="addProductTab === 'batch'">
          <div class="data-table-container" style="max-height: 380px; overflow-y: auto; margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.06); border-radius: 8px;">
            <table class="data-table">
              <thead>
                <tr>
                  <th style="min-width: 140px; font-size: 0.75rem;">Product Name</th>
                  <th style="min-width: 110px; font-size: 0.75rem;">SKU Code</th>
                  <th style="min-width: 90px; font-size: 0.75rem;">Category</th>
                  <th style="width: 80px; font-size: 0.75rem;">Price ($)</th>
                  <th style="width: 70px; font-size: 0.75rem;">Stock</th>
                  <th style="width: 70px; font-size: 0.75rem;">Safety</th>
                  <th style="width: 70px; font-size: 0.75rem;">Max</th>
                  <th style="width: 70px; font-size: 0.75rem;">Reorder</th>
                  <th style="width: 75px; font-size: 0.75rem;">Lead (Days)</th>
                  <th style="min-width: 120px; font-size: 0.75rem;">Supplier</th>
                  <th style="width: 40px; text-align: center;"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, index) in batchProductList" :key="index">
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="text" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model="prod.name" placeholder="Name" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="text" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model="prod.sku" placeholder="SKU" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="text" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model="prod.category" placeholder="Category" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="number" step="0.01" min="0.01" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model.number="prod.price" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="number" min="0" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model.number="prod.current_stock" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="number" min="0" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model.number="prod.safety_stock" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="number" min="1" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model.number="prod.max_stock" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="number" min="1" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model.number="prod.reorder_qty" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <input type="number" min="1" class="admin-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%" v-model.number="prod.lead_time_days" required>
                  </td>
                  <td style="padding: 0.4rem 0.25rem;">
                    <select class="select-input" style="padding:0.35rem 0.5rem;font-size:0.75rem;width:100%;height:auto" v-model="prod.supplier_id" required>
                      <option value="" disabled>Select...</option>
                      <option value="1">AgriBulk Corp</option>
                      <option value="2">GrownOrganic Farms</option>
                      <option value="3">VeggieDistributors</option>
                    </select>
                  </td>
                  <td style="padding: 0.4rem 0.25rem; text-align: center;">
                    <button type="button" class="btn-mini danger" style="padding:0.35rem;border-radius:4px" :disabled="batchProductList.length <= 1" @click="removeBatchRow(index)">
                      <svg style="width:12px;height:12px;fill:none;stroke:currentColor;stroke-width:2" viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
            <button type="button" class="btn-primary" style="padding:0.45rem 0.85rem;font-size:0.75rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:var(--text-primary)" @click="addBatchRow">
              <svg style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
              Add Product Row
            </button>
            <div style="font-size:0.8rem;color:var(--text-muted)">
              Total List Size: <span style="color:var(--accent-purple);font-weight:700">@{{ batchProductList.length }} products</span>
            </div>
          </div>

          <div style="display:flex;gap:0.75rem;justify-content:flex-end">
            <button type="button" class="btn-secondary" style="width:150px;justify-content:center;padding:0.65rem" @click="showAddProductModal = false">
              Cancel
            </button>
            <button type="submit" class="btn-primary" style="width:220px;justify-content:center;padding:0.65rem;background:var(--gradient-purple)">
              Create SKU List
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <script src="/app.js?v={{ time() }}"></script>
</body>
</html>
