<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StockSense AI - Predictive Inventory & Demand Forecasting SaaS</title>
  <link rel="stylesheet" href="/style.css">
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
          <h1 v-else-if="activeTab === 'mlops'">MLOps Pipelines & Drift Observability</h1>
          <p v-if="activeTab === 'overview'">Real-time telemetry, ingest status, and recent actions.</p>
          <p v-else-if="activeTab === 'forecast'">Optuna-tuned LightGBM forecasts with price elasticity simulator.</p>
          <p v-else-if="activeTab === 'restock'">Automated supplier notifications and safety stock tracking.</p>
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
      <section class="kpi-grid">
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
          <div class="dashboard-card-title">
            <span>Dynamic Demand Forecast & Confidence Bounds</span>
            <div style="font-size:0.75rem;color:var(--text-muted);font-weight:normal">
              <span style="display:inline-block;width:12px;height:12px;background:rgba(139,92,246,0.15);border:1px dashed var(--accent-purple);margin-right:4px;vertical-align:middle"></span>
              95% Confidence Interval (Drift Bounds)
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

  </div>

  <script src="/app.js"></script>
</body>
</html>
