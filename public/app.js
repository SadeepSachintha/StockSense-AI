const { createApp, ref, computed, onMounted, watch } = Vue;

const getCode39SVG = (value) => {
  const code39Map = {
    '0': '000110100', '1': '100100001', '2': '001100001', '3': '101100000',
    '4': '000110001', '5': '100110000', '6': '001110000', '7': '000100101',
    '8': '100100100', '9': '001100100', 'A': '100001001', 'B': '001001001',
    'C': '101001000', 'D': '000011001', 'E': '100011000', 'F': '001011000',
    'G': '000001101', 'H': '100001100', 'I': '001001100', 'J': '000011100',
    'K': '100000011', 'L': '001000011', 'M': '101000010', 'N': '000010011',
    'O': '100010010', 'P': '001010010', 'Q': '000000111', 'R': '100000110',
    'S': '001000110', 'T': '000010110', 'U': '110000001', 'V': '011000001',
    'W': '111000000', 'X': '010010001', 'Y': '110010000', 'Z': '011010000',
    '-': '010000101', '.': '110000010', ' ': '011000010', '*': '010010100',
    '$': '010101000', '/': '010100010', '+': '010001010', '%': '000101010'
  };
  
  if (!value) return '';
  const text = '*' + value.toUpperCase() + '*';
  let svgPaths = '';
  let x = 10;
  const height = 45;
  
  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    const pattern = code39Map[char] || code39Map[' '];
    
    for (let j = 0; j < 9; j++) {
      const isBar = (j % 2 === 0);
      const isWide = (pattern[j] === '1');
      const width = isWide ? 3 : 1;
      
      if (isBar) {
        svgPaths += `<rect x="${x}" y="5" width="${width}" height="${height}" fill="black" />`;
      }
      x += width;
    }
    x += 1;
  }
  
  return `<svg width="100%" height="75" viewBox="0 0 ${x + 10} 75" xmlns="http://www.w3.org/2000/svg" style="max-width:100%;">
    ${svgPaths}
    <text x="${(x + 20) / 2}" y="65" font-family="monospace" font-size="11" text-anchor="middle" fill="black" font-weight="bold">${value.toUpperCase()}</text>
  </svg>`;
};

window.vm = createApp({
  setup() {
    // Operator Role & Session Details
    const userRole = ref(window.userRole || 'owner');
    const userName = ref(window.userName || 'System Operator');
    const cleanedUserName = computed(() => {
      return userName.value.replace(/\s*\((owner|cashier)\)/i, '');
    });
    const tenantName = ref(window.tenantName || 'Global Goods Retailers');
    const subscriptionTier = ref(window.subscriptionTier || 'premium');

    // Nav & System status
    const initialTab = window.location.pathname.includes('/saas-admin') 
      ? 'admin' 
      : (window.userRole === 'cashier' ? 'pos' : 'overview');
    const activeTab = ref(initialTab);
    const systemStatus = ref('ETL Pipeline: Success');
    const systemStatusClass = ref('emerald');
    const activeTenantId = ref(new URLSearchParams(window.location.search).get('tenant_id') || localStorage.getItem('active_tenant_id') || 'tenant_001');

    // Tenant Administration UI bindings
    const tenantsList = ref([]);
    const newTenant = ref({ id: '', name: '', company_name: '', subscription_tier: 'free', tax_rate: 8.0 });
    const activeTenantTaxRate = ref(8.0);
    
    // Supplier Management UI bindings
    const suppliersList = ref([]);
    const newSupplier = ref({ name: '', email: '', phone: '', alert_channel: 'Email' });
    const showAddSupplierModal = ref(false);
    
    // Core KPIs
    const isOnline = ref(navigator.onLine);
    const showReceiptModal = ref(false);
    const receiptData = ref(null);
    const barcodeQuery = ref('');
    const barcodeScanClass = ref('');
    const showBarcodeSuggestions = ref(false);
    const activeSuggestionIndex = ref(-1);
    const activeModel = ref('lightgbm');
    const driftLimit = ref(5.0);
    const showAddProductModal = ref(false);
    const addProductTab = ref('single');

    const productTemplates = [
      { name: 'Organic Fuji Apples', sku: 'FR-APP-FUJ', category: 'Fruits', price: 2.49, safety_stock: 30, max_stock: 400, reorder_qty: 80, lead_time_days: 5, supplier_id: 2 },
      { name: 'Cavendish Bananas', sku: 'FR-BAN-CAV', category: 'Fruits', price: 1.89, safety_stock: 40, max_stock: 500, reorder_qty: 100, lead_time_days: 3, supplier_id: 1 },
      { name: 'Fresh Strawberries', sku: 'FR-STR-RAW', category: 'Fruits', price: 3.49, safety_stock: 20, max_stock: 200, reorder_qty: 50, lead_time_days: 4, supplier_id: 2 },
      { name: 'Jasmine Rice', sku: 'GR-RIC-JAS', category: 'Grains', price: 8.99, safety_stock: 50, max_stock: 500, reorder_qty: 120, lead_time_days: 6, supplier_id: 1 },
      { name: 'Basmati Rice', sku: 'GR-RIC-BAS', category: 'Grains', price: 9.99, safety_stock: 40, max_stock: 450, reorder_qty: 100, lead_time_days: 5, supplier_id: 1 },
      { name: 'Whole Wheat Oats', sku: 'GR-OAT-WHL', category: 'Grains', price: 4.89, safety_stock: 30, max_stock: 300, reorder_qty: 80, lead_time_days: 5, supplier_id: 3 },
      { name: 'Fresh Baby Spinach', sku: 'VG-SPI-BAB', category: 'Vegetables', price: 3.99, safety_stock: 15, max_stock: 250, reorder_qty: 50, lead_time_days: 3, supplier_id: 3 },
      { name: 'Red Tomatoes', sku: 'VG-TOM-RED', category: 'Vegetables', price: 2.99, safety_stock: 25, max_stock: 300, reorder_qty: 60, lead_time_days: 4, supplier_id: 2 },
      { name: 'Organic Broccoli', sku: 'VG-BRO-ORG', category: 'Vegetables', price: 3.29, safety_stock: 20, max_stock: 200, reorder_qty: 45, lead_time_days: 3, supplier_id: 3 },
      { name: 'Whole Milk 1L', sku: 'DY-MLK-WHL', category: 'Dairy', price: 1.99, safety_stock: 50, max_stock: 600, reorder_qty: 150, lead_time_days: 2, supplier_id: 1 },
      { name: 'Greek Yogurt 500g', sku: 'DY-YOG-GRK', category: 'Dairy', price: 4.49, safety_stock: 25, max_stock: 250, reorder_qty: 60, lead_time_days: 3, supplier_id: 1 },
      { name: 'Salted Butter 250g', sku: 'DY-BUT-SLT', category: 'Dairy', price: 3.79, safety_stock: 30, max_stock: 300, reorder_qty: 75, lead_time_days: 4, supplier_id: 1 },
      { name: 'Green Tea 20 Bags', sku: 'BV-TEA-GRN', category: 'Beverages', price: 3.89, safety_stock: 15, max_stock: 150, reorder_qty: 40, lead_time_days: 4, supplier_id: 3 },
      { name: 'Espresso Beans 1kg', sku: 'BV-COF-ESP', category: 'Beverages', price: 18.99, safety_stock: 10, max_stock: 100, reorder_qty: 25, lead_time_days: 5, supplier_id: 1 },
      { name: 'Spring Water 500ml', sku: 'BV-WTR-SPR', category: 'Beverages', price: 0.99, safety_stock: 100, max_stock: 1200, reorder_qty: 300, lead_time_days: 3, supplier_id: 1 }
    ];

    const showSingleSuggestions = ref(false);
    const activeBatchSuggestionRow = ref(-1);
    const createBlankProduct = () => ({
      sku: '',
      name: '',
      category: '',
      price: 9.99,
      current_stock: 50,
      safety_stock: 15,
      max_stock: 120,
      reorder_qty: 40,
      lead_time_days: 3,
      supplier_id: ''
    });
    const newProductData = ref(createBlankProduct());
    const batchProductList = ref([createBlankProduct()]);
    const savedCapital = ref(12450);
    const stockoutRisks = ref(0);
    const wmapeAvg = ref(3.8);
    const activeSkusCount = ref(0);
    const discountType = ref('percent'); // 'percent' or 'value'
    const discountValue = ref(0);
    
    // SKU List (Fetched dynamically from Laravel API)
    const skus = ref([]);
    const selectedSkuId = ref('');
    const selectedSku = computed(() => skus.value.find(s => s.id === selectedSkuId.value));
    
    // Elasticity Pricing Simulation
    const priceAdjustment = ref(0); // percent adjustment (-20 to +20)
    
    const simulatedPrice = computed(() => {
      if (!selectedSku.value) return 0;
      return (selectedSku.value.price * (1 + priceAdjustment.value / 100)).toFixed(2);
    });
    
    const demandShiftPercent = computed(() => {
      if (!selectedSku.value) return 0;
      return (priceAdjustment.value * selectedSku.value.elasticity).toFixed(1);
    });
    
    const adjustedForecast = computed(() => {
      if (!selectedSku.value || !selectedSku.value.forecast) return [];
      const shift = 1 + (priceAdjustment.value * selectedSku.value.elasticity) / 100;
      return selectedSku.value.forecast.map(v => Math.round(v * shift));
    });

    // Ingest POS Data Simulation
    const simulatedUploadStatus = ref('idle'); // idle, uploading, success
    const simulatedUploadProgress = ref(0);
    const simulatedUploadFileName = ref('');

    // Supplier notifications logs
    const supplierAlerts = ref([
      {
        id: 1,
        time: 'Today, 08:30 AM',
        skuName: 'NPK Fertilizer',
        supplier: 'AgriBulk Corp',
        channel: 'Email',
        status: 'Confirmed',
        desc: 'Safety stock boundary checked. Local warehouse coordinates verified.'
      }
    ]);

    // MLOps Retraining logs & Drift State
    const modelDriftScore = ref(2.4);
    const isRetraining = ref(false);
    const mlopsLogs = ref([
      { time: '12:00:05', text: 'System standing by. MLflow registered model lgbm_v1.0.0 active.', type: 'info' },
      { time: '12:00:06', text: 'Drift monitor triggered: Actual vs Predicted sales distributions align.', type: 'success' }
    ]);

    // Toast Notification variables
    const toastAlert = ref({
      show: false,
      title: '',
      msg: '',
      skuName: '',
      channel: ''
    });

    // Chart.js Instance
    let forecastChart = null;

    // Helper: generate dates for labels
    const generateChartLabels = () => {
      const labels = [];
      const today = new Date();
      
      // 30 days back
      for (let i = 30; i > 0; i--) {
        const d = new Date(today);
        d.setDate(today.getDate() - i);
        labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
      }
      
      // 14 days forward
      for (let i = 0; i < 14; i++) {
        const d = new Date(today);
        d.setDate(today.getDate() + i);
        labels.push(d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' (FC)');
      }
      return labels;
    };

    // Render/update forecasting chart
    const renderChart = () => {
      const ctx = document.getElementById('forecastChartCanvas');
      if (!ctx) return;

      const sku = selectedSku.value;
      if (!sku || !sku.salesHistory || !sku.forecast) return;

      if (forecastChart && (!forecastChart.ctx || !document.body.contains(forecastChart.ctx.canvas))) {
        try {
          forecastChart.destroy();
        } catch (e) {
          console.warn("Error destroying chart instance:", e);
        }
        forecastChart = null;
      }

      const labels = generateChartLabels();
      
      const salesHistoryDataset = [...sku.salesHistory, ...Array(14).fill(null)];
      
      // Calculate Prophet curve based on baseline forecast
      const prophetForecast = adjustedForecast.value.map((v, i) => {
        const seasonalFactor = Math.sin(i / 1.5) * 4;
        return Math.max(0, Math.round(v + seasonalFactor));
      });

      // Calculate LSTM curve based on baseline forecast
      const lstmForecast = adjustedForecast.value.map((v, i) => {
        const noise = Math.cos(i) * 3 - (i * 0.15);
        return Math.max(0, Math.round(v * 0.95 + noise));
      });

      const baselineForecastPadded = [
        ...Array(30).fill(null),
        sku.salesHistory[sku.salesHistory.length - 1], // link point
        ...sku.forecast.slice(1)
      ];

      const adjustedForecastPadded = [
        ...Array(30).fill(null),
        sku.salesHistory[sku.salesHistory.length - 1], // link point
        ...adjustedForecast.value.slice(1)
      ];

      const prophetForecastPadded = [
        ...Array(30).fill(null),
        sku.salesHistory[sku.salesHistory.length - 1],
        ...prophetForecast.slice(1)
      ];

      const lstmForecastPadded = [
        ...Array(30).fill(null),
        sku.salesHistory[sku.salesHistory.length - 1],
        ...lstmForecast.slice(1)
      ];

      const errorMargin = 0.15; // 15% confidence interval band
      const confidenceUpperPadded = [
        ...Array(30).fill(null),
        sku.salesHistory[sku.salesHistory.length - 1],
        ...adjustedForecast.value.slice(1).map(v => Math.round(v * (1 + errorMargin)))
      ];
      const confidenceLowerPadded = [
        ...Array(30).fill(null),
        sku.salesHistory[sku.salesHistory.length - 1],
        ...adjustedForecast.value.slice(1).map(v => Math.round(v * (1 - errorMargin)))
      ];

      const safetyStockThreshold = Array(labels.length).fill(sku.safetyStock);

      const data = {
        labels: labels,
        datasets: [
          {
            label: 'Actual Sales History',
            data: salesHistoryDataset,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            pointRadius: 2,
            tension: 0.3,
            fill: true
          },
          {
            label: 'LightGBM Predictor',
            data: adjustedForecastPadded,
            borderColor: activeModel.value === 'lightgbm' ? '#10b981' : 'rgba(16, 185, 129, 0.25)',
            borderDash: activeModel.value === 'lightgbm' ? [] : [4, 4],
            borderWidth: activeModel.value === 'lightgbm' ? 3.5 : 1.5,
            pointRadius: activeModel.value === 'lightgbm' ? 2 : 0,
            tension: 0.3,
            fill: false
          },
          {
            label: 'Prophet Seasonality',
            data: prophetForecastPadded,
            borderColor: activeModel.value === 'prophet' ? '#3b82f6' : 'rgba(59, 130, 246, 0.25)',
            borderDash: activeModel.value === 'prophet' ? [] : [4, 4],
            borderWidth: activeModel.value === 'prophet' ? 3.5 : 1.5,
            pointRadius: activeModel.value === 'prophet' ? 2 : 0,
            tension: 0.35,
            fill: false
          },
          {
            label: 'LSTM Deep Learning',
            data: lstmForecastPadded,
            borderColor: activeModel.value === 'lstm' ? '#8b5cf6' : 'rgba(139, 92, 246, 0.25)',
            borderDash: activeModel.value === 'lstm' ? [] : [4, 4],
            borderWidth: activeModel.value === 'lstm' ? 3.5 : 1.5,
            pointRadius: activeModel.value === 'lstm' ? 2 : 0,
            tension: 0.25,
            fill: false
          },
          {
            label: 'Safety Stock Limit',
            data: safetyStockThreshold,
            borderColor: 'rgba(244, 63, 94, 0.6)',
            borderWidth: 2,
            borderDash: [2, 4],
            pointRadius: 0,
            fill: false
          },
          {
            label: 'Upper Bound CI',
            data: confidenceUpperPadded,
            borderColor: 'transparent',
            backgroundColor: 'rgba(139, 92, 246, 0.03)',
            pointRadius: 0,
            fill: '+1',
            tension: 0.3
          },
          {
            label: 'Lower Bound CI',
            data: confidenceLowerPadded,
            borderColor: 'transparent',
            backgroundColor: 'rgba(139, 92, 246, 0.03)',
            pointRadius: 0,
            fill: false,
            tension: 0.3
          }
        ]
      };

      if (forecastChart) {
        forecastChart.data = data;
        forecastChart.update('none');
      } else {
        forecastChart = new Chart(ctx, {
          type: 'line',
          data: data,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                labels: {
                  color: '#9ca3af',
                  font: { family: 'Inter', size: 11 },
                  filter: (item) => !item.text.includes('Bound CI') && !item.text.includes('Baseline')
                }
              },
              tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                titleFont: { family: 'Outfit', size: 13, weight: 'bold' },
                bodyFont: { family: 'Inter', size: 12 },
                borderColor: 'rgba(255, 255, 255, 0.08)',
                borderWidth: 1
              }
            },
            scales: {
              x: {
                grid: { color: 'rgba(255, 255, 255, 0.03)', borderColor: 'rgba(255, 255, 255, 0.05)' },
                ticks: { color: '#6b7280', font: { size: 10 }, maxTicksLimit: 12 }
              },
              y: {
                grid: { color: 'rgba(255, 255, 255, 0.03)', borderColor: 'rgba(255, 255, 255, 0.05)' },
                ticks: { color: '#6b7280', font: { size: 10 } }
              }
            }
          }
        });
      }
    };

    // Load Live products list on boot
    const fetchLiveProducts = async () => {
      try {
        const res = await fetch(`/api/products?tenant_id=${activeTenantId.value}`);
        const data = await res.json();
        
        if (data.status === 'success') {
          // Map Laravel structure into frontend list
          skus.value = data.data.map(p => ({
            id: p.id.toString(),
            sku: p.sku,
            name: p.name,
            category: p.category,
            price: parseFloat(p.price),
            costPrice: p.cost_price ? parseFloat(p.cost_price) : parseFloat(p.price) * 0.7,
            elasticity: parseFloat(p.elasticity),
            currentStock: parseInt(p.current_stock),
            safetyStock: parseInt(p.safety_stock),
            maxStock: parseInt(p.max_stock),
            reorderQty: parseInt(p.reorder_qty),
            leadTime: parseInt(p.lead_time_days),
            supplier: p.supplier.name,
            supplierId: p.supplier_id,
            supplierContact: p.supplier.email,
            alertChannel: p.supplier.alert_channel,
            restockStatus: parseInt(p.current_stock) <= parseInt(p.safety_stock) ? 'Low Stock' : 'Normal',
            wmape: p.wmape ? parseFloat(p.wmape) : 3.8,
            // Lazy load forecast data on select
            salesHistory: null,
            forecast: null
          }));

          activeSkusCount.value = skus.value.length;
          
          if (skus.value.length > 0) {
            selectedSkuId.value = skus.value[0].id;
            await loadForecastForSelected();
          }
          
          // Calculate stockout counts
          stockoutRisks.value = skus.value.filter(s => s.currentStock <= s.safetyStock).length;
          if (stockoutRisks.value > 0) {
            systemStatus.value = `${stockoutRisks.value} SKUs below safety stock.`;
            systemStatusClass.value = 'warning';
          }
        }
      } catch (err) {
        console.error('Error fetching live products:', err);
        systemStatus.value = 'Failed to fetch database products.';
        systemStatusClass.value = 'danger';
      }
    };

    // Load Live forecast predictions from FastAPI microservice
    const loadForecastForSelected = async () => {
      const sku = selectedSku.value;
      if (!sku) return;
      
      // If already loaded, just render chart
      if (sku.salesHistory && sku.forecast) {
        renderChart();
        return;
      }

      systemStatus.value = `Loading forecast for ${sku.name}...`;
      systemStatusClass.value = 'warning';

      try {
        const res = await fetch(`/api/products/${sku.id}/forecast?tenant_id=${activeTenantId.value}`);
        const data = await res.json();
        
        if (data.status === 'success') {
          // Provide mock fallback history if DB transactions are sparse
          sku.salesHistory = [22, 19, 21, 24, 28, 25, 23, 20, 26, 28, 30, 25, 22, 21, 25, 26, 29, 27, 24, 22, 25, 28, 30, 27, 23, 22, 24, 26, 28, sku.currentStock];
          sku.forecast = data.forecast;
          sku.safetyStock = data.safety_stock;
          sku.reorderPoint = data.reorder_point;
          sku.wmape = data.wmape;

          systemStatus.value = 'ETL Pipeline: Success';
          systemStatusClass.value = 'emerald';
          renderChart();
        } else {
          systemStatus.value = 'FastAPI connection failed.';
          systemStatusClass.value = 'danger';
        }
      } catch (err) {
        console.error('Error loading forecast:', err);
        systemStatus.value = 'ML microservice offline.';
        systemStatusClass.value = 'danger';
      }
    };

    // Watch tab change to render chart or fetch tenants
    watch(activeTab, async (newTab) => {
      if (newTab === 'forecast' || newTab === 'overview') {
        setTimeout(renderChart, 50);
      } else if (newTab === 'admin') {
        await fetchTenants();
      }
    });

    // Watch SKU selection and Price Slider adjustments to update chart
    watch(selectedSkuId, async () => {
      priceAdjustment.value = 0;
      await loadForecastForSelected();
    });

    watch(priceAdjustment, () => {
      renderChart();
    });

    watch(activeModel, () => {
      renderChart();
    });

    // Ingest POS CSV simulation using real POST requests
    const triggerPOSUpload = async () => {
      if (simulatedUploadStatus.value === 'uploading' || skus.value.length === 0) return;
      
      const targetSku = skus.value[0]; // Rice
      simulatedUploadFileName.value = 'pos_transactions_today.csv';
      simulatedUploadStatus.value = 'uploading';
      simulatedUploadProgress.value = 30;
      
      try {
        // Call the real /api/products/ingest endpoint in the cloud
        const res = await fetch(`/api/products/ingest?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify({
            transactions: [
              {
                sku: targetSku.sku,
                quantity: 125, // Drop stock below safety limits
                price_sold: targetSku.price,
                transaction_date: new Date().toISOString().slice(0, 19).replace('T', ' ')
              }
            ]
          })
        });

        const data = await res.json();
        simulatedUploadProgress.value = 80;

        if (data.status === 'success') {
          setTimeout(() => {
            simulatedUploadProgress.value = 100;
            simulatedUploadStatus.value = 'success';
            
            // Reload database products list to capture updated stocks
            setTimeout(async () => {
              await fetchLiveProducts();
              
              // Trigger warnings
              if (data.warnings && data.warnings.length > 0) {
                stockoutRisks.value = skus.value.filter(s => s.currentStock <= s.safetyStock).length;
                systemStatus.value = 'Safety Limit Violated. PO Dispatch recommended.';
                systemStatusClass.value = 'danger';

                supplierAlerts.value.unshift({
                  id: Date.now(),
                  time: 'Just Now',
                  skuName: targetSku.name,
                  supplier: targetSku.supplier,
                  channel: targetSku.alertChannel,
                  status: 'Awaiting action',
                  desc: 'POS Ingest completed. Stock fell below safety thresholds.'
                });
              }

              simulatedUploadStatus.value = 'idle';
              simulatedUploadProgress.value = 0;
            }, 1000);
          }, 800);
        }
      } catch (err) {
        console.error('POS Ingestion error:', err);
        simulatedUploadStatus.value = 'idle';
      }
    };

    // Auto-restocking Supplier WhatsApp/Email simulation
    const sendRestockAlert = (sku) => {
      if (sku.restockStatus === 'Dispatched' || sku.restockStatus === 'Confirmed') return;
      
      sku.restockStatus = 'Dispatched';
      
      const alertId = Date.now();
      supplierAlerts.value.unshift({
        id: alertId,
        time: 'Just Now',
        skuName: sku.name,
        supplier: sku.supplier,
        channel: sku.alertChannel,
        status: 'Dispatched',
        desc: `Auto-PO restock alert broadcast via ${sku.alertChannel}. Requesting ${sku.reorderQty} units.`
      });

      toastAlert.value = {
        show: true,
        title: `${sku.alertChannel} PO Broadcasted`,
        msg: `Sent restocking order of ${sku.reorderQty} units for ${sku.name} directly to ${sku.supplier}.`,
        skuName: sku.name,
        channel: sku.alertChannel
      };

      setTimeout(() => {
        if (toastAlert.value.skuName === sku.name) {
          toastAlert.value.show = false;
        }
      }, 4000);

      // Simulate Supplier Confirmation
      setTimeout(async () => {
        try {
          const res = await fetch(`/api/products/${sku.id}/replenish`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: sku.reorderQty })
          });
          const data = await res.json();
          if (data.status === 'success') {
            sku.currentStock = parseInt(data.data.current_stock);
          } else {
            sku.currentStock += sku.reorderQty;
          }
        } catch (err) {
          console.error("Error persisting restock quantity:", err);
          sku.currentStock += sku.reorderQty;
        }

        sku.restockStatus = 'Confirmed';
        
        const alertIdx = supplierAlerts.value.findIndex(a => a.skuName === sku.name && a.status === 'Dispatched');
        if (alertIdx !== -1) {
          supplierAlerts.value[alertIdx].status = 'Confirmed';
          supplierAlerts.value[alertIdx].time = '3s ago';
          supplierAlerts.value[alertIdx].desc += ` Delivery scheduled in ${sku.leadTime} days. Stock replenished.`;
        }

        toastAlert.value = {
          show: true,
          title: `Supplier Confirmed PO`,
          msg: `${sku.supplier} accepted order. Stock replenished with ${sku.reorderQty} units.`,
          skuName: sku.name,
          channel: sku.alertChannel
        };

        savedCapital.value += 120;
        stockoutRisks.value = skus.value.filter(s => s.currentStock <= s.safetyStock).length;

        if (stockoutRisks.value === 0) {
          systemStatus.value = 'ETL Pipeline: Success';
          systemStatusClass.value = 'emerald';
        }

        setTimeout(() => {
          if (toastAlert.value.skuName === sku.name) {
            toastAlert.value.show = false;
          }
        }, 5000);

      }, 3500);
    };

    // Airflow Retraining Simulation logs
    const triggerRetraining = () => {
      if (isRetraining.value) return;
      isRetraining.value = true;
      
      const appendLog = (text, type = 'info', delay = 0) => {
        return new Promise(resolve => {
          setTimeout(() => {
            const time = new Date().toLocaleTimeString();
            mlopsLogs.value.push({ time, text, type });
            const consoleBox = document.getElementById('consoleBox');
            if (consoleBox) consoleBox.scrollTop = consoleBox.scrollHeight;
            resolve();
          }, delay);
        });
      };

      (async () => {
        await appendLog('Initializing Airflow Orchestration DAG: nightly_retrain_lgbm...', 'info', 300);
        await appendLog('Executing Task [extract_raw_pos] - Fetching S3 client buffers...', 'info', 500);
        await appendLog('Data lake connection: Connected to bucket /artifacts/stock-sense/', 'success', 300);
        await appendLog('Executing Task [validate_schema] - Running Great Expectations validations...', 'info', 400);
        await appendLog('Data Quality schema check: PASSED. 0 anomalies.', 'success', 200);
        await appendLog('Executing Task [convert_parquet] - Compacting files into Snappy Parquet format...', 'info', 400);
        await appendLog('Executing Task [feature_engineering] - Rebuilding demand averages & pricing elasticities...', 'info', 500);
        await appendLog('Executing Task [train_lgbm] - Fitting LightGBM regressor models...', 'info', 600);
        await appendLog('Optuna: 50 parameter trials complete. Best loss (WMAPE): 3.52%', 'success', 400);
        await appendLog('Executing Task [register_model] - Logging run to MLflow Registry...', 'info', 400);
        await appendLog('Executing Task [verify_drift] - Drift verify completed (0.78% score).', 'success', 300);
        await appendLog('DAG completed successfully. FastAPI prediction cached reloaded.', 'success', 400);
        
        modelDriftScore.value = 0.8;
        wmapeAvg.value = 3.5;
        isRetraining.value = false;
      })();
    };

    const checkDriftAutorecovery = () => {
      if (modelDriftScore.value > driftLimit.value && !isRetraining.value) {
        // Trigger auto-recovery!
        const time = new Date().toLocaleTimeString();
        mlopsLogs.value.unshift({
          time: time,
          text: `WARNING: Model drift score (${modelDriftScore.value}%) exceeds threshold (${driftLimit.value}%). Autorecovery triggered.`,
          type: 'danger'
        });
        
        triggerRetraining();
      }
    };

    watch(driftLimit, () => {
      checkDriftAutorecovery();
    });

    // POS Terminal Cart state management
    const cart = ref([]);

    const addToCart = (product) => {
      if (product.currentStock <= 0) {
        alert("This item is currently out of stock!");
        return;
      }
      const item = cart.value.find(i => i.product.id === product.id);
      if (item) {
        if (item.quantity >= product.currentStock) {
          alert("Cannot exceed available stock limit!");
          return;
        }
        item.quantity++;
      } else {
        cart.value.push({ product, quantity: 1 });
      }
    };

    const removeFromCart = (item) => {
      const idx = cart.value.findIndex(i => i.product.id === item.product.id);
      if (idx !== -1) {
        if (cart.value[idx].quantity > 1) {
          cart.value[idx].quantity--;
        } else {
          cart.value.splice(idx, 1);
        }
      }
    };

    const deleteFromCart = (item) => {
      const idx = cart.value.findIndex(i => i.product.id === item.product.id);
      if (idx !== -1) {
        cart.value.splice(idx, 1);
      }
    };

    const clearCart = () => {
      if (confirm("Are you sure you want to clear all items in your cart?")) {
        cart.value = [];
        discountValue.value = 0;
        playPOSSound('trash');
      }
    };

    const playPOSSound = (type) => {
      try {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return;
        const ctx = new AudioContextClass();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        if (type === 'success') {
          osc.type = 'sine';
          osc.frequency.setValueAtTime(1450, ctx.currentTime);
          gain.gain.setValueAtTime(0.15, ctx.currentTime);
          gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.08);
          osc.start(ctx.currentTime);
          osc.stop(ctx.currentTime + 0.08);
        } else if (type === 'error') {
          osc.type = 'sawtooth';
          osc.frequency.setValueAtTime(120, ctx.currentTime);
          gain.gain.setValueAtTime(0.15, ctx.currentTime);
          gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.25);
          osc.start(ctx.currentTime);
          osc.stop(ctx.currentTime + 0.25);
        } else if (type === 'trash') {
          osc.type = 'triangle';
          osc.frequency.setValueAtTime(250, ctx.currentTime);
          osc.frequency.exponentialRampToValueAtTime(80, ctx.currentTime + 0.3);
          gain.gain.setValueAtTime(0.2, ctx.currentTime);
          gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.3);
          osc.start(ctx.currentTime);
          osc.stop(ctx.currentTime + 0.3);
        }
      } catch (err) {
        console.warn("Audio feedback block:", err);
      }
    };

    const addSkuByBarcode = (barcode) => {
      if (!barcode) return;
      const cleanBarcode = barcode.trim();
      const product = skus.value.find(s => s.sku.toUpperCase() === cleanBarcode.toUpperCase() || s.id.toString() === cleanBarcode);
      
      if (product) {
        if (product.currentStock <= 0) {
          playPOSSound('error');
          barcodeScanClass.value = 'scan-error';
          setTimeout(() => { barcodeScanClass.value = ''; }, 600);
          
          toastAlert.value = {
            show: true,
            title: "Out of Stock",
            msg: `SKU ${product.sku} is completely sold out. Unable to add to cart.`,
            skuName: product.name,
            channel: "Barcode Reader"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          return;
        }
        
        // Add to cart
        addToCart(product);
        playPOSSound('success');
        barcodeScanClass.value = 'scan-success';
        setTimeout(() => { barcodeScanClass.value = ''; }, 600);
        barcodeQuery.value = '';
      } else {
        // No match found
        playPOSSound('error');
        barcodeScanClass.value = 'scan-error';
        setTimeout(() => { barcodeScanClass.value = ''; }, 600);
        
        toastAlert.value = {
          show: true,
          title: "SKU Not Found",
          msg: `No product matched barcode query: "${cleanBarcode}"`,
          skuName: cleanBarcode,
          channel: "Barcode Reader"
        };
        setTimeout(() => { toastAlert.value.show = false; }, 4000);
      }
    };

    const barcodeSuggestions = computed(() => {
      const query = barcodeQuery.value.trim().toLowerCase();
      if (!query) {
        // Suggest top 5 in-stock products if search query is empty
        return skus.value.filter(s => s.currentStock > 0).slice(0, 5);
      }
      return skus.value.filter(s => 
        s.sku.toLowerCase().includes(query) || 
        s.name.toLowerCase().includes(query)
      ).slice(0, 8);
    });

    const moveSuggestionActive = (direction) => {
      const suggestions = barcodeSuggestions.value;
      if (suggestions.length === 0) return;
      activeSuggestionIndex.value = (activeSuggestionIndex.value + direction + suggestions.length) % suggestions.length;
      
      // Keep active element in visible viewport
      setTimeout(() => {
        const el = document.querySelector('.barcode-suggestion-item.active');
        if (el) {
          el.scrollIntoView({ block: 'nearest' });
        }
      }, 10);
    };

    const selectActiveSuggestion = () => {
      const index = activeSuggestionIndex.value;
      const suggestions = barcodeSuggestions.value;
      if (index >= 0 && index < suggestions.length) {
        addSkuByBarcode(suggestions[index].sku);
      } else {
        // Fallback to searching typed input directly
        addSkuByBarcode(barcodeQuery.value);
      }
      activeSuggestionIndex.value = -1;
      showBarcodeSuggestions.value = false;
    };

    const cartSubtotal = computed(() => {
      return cart.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
    });

    const discountAmount = computed(() => {
      if (discountValue.value <= 0) return 0;
      if (discountType.value === 'percent') {
        const val = Math.min(100, discountValue.value);
        return cartSubtotal.value * (val / 100);
      } else {
        return Math.min(cartSubtotal.value, discountValue.value);
      }
    });

    const cartTax = computed(() => {
      const net = Math.max(0, cartSubtotal.value - discountAmount.value);
      return net * (activeTenantTaxRate.value / 100);
    });

    const cartTotal = computed(() => {
      const net = Math.max(0, cartSubtotal.value - discountAmount.value);
      return net + cartTax.value;
    });

    const submitCheckout = async (paymentMethod = 'cash') => {
      if (cart.value.length === 0) return;

      const subtotalVal = cartSubtotal.value;
      const taxVal = cartTax.value;
      const totalVal = cartTotal.value;
      const checkoutItems = cart.value.map(item => ({
        name: item.product.name,
        sku: item.product.sku,
        quantity: item.quantity,
        price: item.product.price,
        subtotal: item.product.price * item.quantity
      }));

      // Construct Receipt details first
      const receipt = {
        id: 'TX-' + Math.floor(100000 + Math.random() * 900000),
        time: new Date().toLocaleString(),
        tenant_id: activeTenantId.value,
        items: checkoutItems,
        subtotal: subtotalVal,
        discount: discountAmount.value,
        tax: taxVal,
        total: totalVal,
        isOffline: !isOnline.value,
        payment_method: paymentMethod,
        cash_tendered: paymentMethod === 'cash' ? parseFloat(cashTendered.value || 0) : totalVal,
        change_due: paymentMethod === 'cash' ? Math.max(0, parseFloat(cashTendered.value || 0) - totalVal) : 0
      };

      const payload = {
        receipt_id: receipt.id,
        discount_amount: receipt.discount,
        tax_amount: receipt.tax,
        payment_method: paymentMethod,
        transactions: cart.value.map(item => ({
          sku: item.product.sku,
          quantity: item.quantity,
          price_sold: item.product.price,
          transaction_date: new Date().toISOString().slice(0, 19).replace('T', ' ')
        }))
      };

      if (!isOnline.value) {
        // Offline Flow: Cache transaction in LocalStorage
        const cached = JSON.parse(localStorage.getItem('offline_transactions') || '[]');
        cached.push(payload);
        localStorage.setItem('offline_transactions', JSON.stringify(cached));

        // Deduct inventory levels reactively in the UI
        cart.value.forEach(item => {
          const product = skus.value.find(s => s.sku === item.product.sku);
          if (product) {
            product.currentStock = Math.max(0, product.currentStock - item.quantity);
          }
        });

        // Clear cart & reset discount
        cart.value = [];
        discountValue.value = 0;
        receiptData.value = receipt;
        showReceiptModal.value = true;

        toastAlert.value = {
          show: true,
          title: "Offline Transaction Cached",
          msg: "Order cached locally. Inventory levels updated. Will sync when back online.",
          skuName: "Offline Cache",
          channel: "Local Storage"
        };
        setTimeout(() => { toastAlert.value.show = false; }, 5000);
        return;
      }

      // Online Flow
      systemStatus.value = "Processing checkout...";
      systemStatusClass.value = "warning";

      try {
        const res = await fetch(`/api/products/ingest?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.status === 'success') {
          // Clear cart & reset discount
          cart.value = [];
          discountValue.value = 0;
          showPaymentModal.value = false;
          
          // Populate receipt & show modal
          receiptData.value = receipt;
          showReceiptModal.value = true;

          // Toast Notification
          toastAlert.value = {
            show: true,
            title: "Transaction Complete",
            msg: `Successfully processed checkout of ${data.ingested_count} item(s). Inventory synced.`,
            skuName: "POS Checkout",
            channel: "POS Terminal"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          
          // Reload products list & transaction history
          await fetchLiveProducts();
          await fetchTransactionHistory();
        } else {
          alert("Checkout failed: " + data.message);
          systemStatus.value = "Checkout failed.";
          systemStatusClass.value = "danger";
        }
      } catch (err) {
        console.error("Checkout error:", err);
        // Fallback to offline caching if connection fails mid-operation!
        const cached = JSON.parse(localStorage.getItem('offline_transactions') || '[]');
        cached.push(payload);
        localStorage.setItem('offline_transactions', JSON.stringify(cached));

        // Deduct inventory levels reactively in the UI
        cart.value.forEach(item => {
          const product = skus.value.find(s => s.sku === item.product.sku);
          if (product) {
            product.currentStock = Math.max(0, product.currentStock - item.quantity);
          }
        });

        cart.value = [];
        receiptData.value = receipt;
        showReceiptModal.value = true;

        toastAlert.value = {
          show: true,
          title: "Server Error - Cached",
          msg: "Server connection failed. Order cached locally and will auto-sync on recovery.",
          skuName: "Offline Cache",
          channel: "Local Storage"
        };
        setTimeout(() => { toastAlert.value.show = false; }, 6000);
      }
    };

    const submitNewProduct = async () => {
      try {
        const res = await fetch(`/api/products?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify(newProductData.value)
        });
        const data = await res.json();
        if (data.status === 'success') {
          showAddProductModal.value = false;
          
          toastAlert.value = {
            show: true,
            title: "Product Created",
            msg: `Successfully added ${newProductData.value.name} to the POS inventory catalog.`,
            skuName: newProductData.value.sku,
            channel: "Inventory Scope Manager"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);

          newProductData.value = {
            sku: '',
            name: '',
            category: '',
            price: 9.99,
            current_stock: 50,
            safety_stock: 15,
            max_stock: 120,
            reorder_qty: 40,
            lead_time_days: 3,
            supplier_id: ''
          };

          playPOSSound('success');
          await fetchLiveProducts();
        } else {
          let errMsg = data.message || "Unknown validation error.";
          if (data.errors) {
            const list = Object.values(data.errors).flat();
            if (list.length > 0) {
              errMsg += "\n\nDetails:\n- " + list.join("\n- ");
            }
          }
          alert("Failed to create product: " + errMsg);
        }
      } catch (err) {
        console.error("Error creating product:", err);
        alert("Server communication error. Please check formatting.");
      }
    };

    const combinedCatalogSuggestions = computed(() => {
      const existing = skus.value.map(s => ({
        name: s.name,
        sku: s.sku,
        category: s.category,
        price: s.price,
        safety_stock: s.safetyStock,
        max_stock: s.maxStock,
        reorder_qty: s.reorderQty,
        lead_time_days: s.leadTime,
        supplier_id: suppliersList.value.find(sup => sup.name === s.supplier)?.id || ''
      }));

      const uniqueTemplates = productTemplates.filter(t => !existing.some(e => e.name.toLowerCase() === t.name.toLowerCase()));
      return [...existing, ...uniqueTemplates];
    });

    const handleSingleNameInput = () => {
      const q = (newProductData.value.name || '').trim().toLowerCase();
      const matched = combinedCatalogSuggestions.value.find(t => t.name.toLowerCase() === q);
      if (matched) {
        selectSingleTemplate(matched);
      }
    };

    const selectSingleTemplate = (tpl) => {
      newProductData.value.name = tpl.name;
      newProductData.value.sku = tpl.sku;
      newProductData.value.category = tpl.category;
      newProductData.value.price = tpl.price;
      newProductData.value.safety_stock = tpl.safety_stock;
      newProductData.value.max_stock = tpl.max_stock;
      newProductData.value.reorder_qty = tpl.reorder_qty;
      newProductData.value.lead_time_days = tpl.lead_time_days;
      newProductData.value.supplier_id = tpl.supplier_id;
    };

    const handleBatchNameInput = (prod, index) => {
      const q = (prod.name || '').trim().toLowerCase();
      const matched = combinedCatalogSuggestions.value.find(t => t.name.toLowerCase() === q);
      if (matched) {
        selectBatchTemplate(index, matched);
      }
    };

    const selectBatchTemplate = (index, tpl) => {
      if (batchProductList.value[index]) {
        batchProductList.value[index].name = tpl.name;
        batchProductList.value[index].sku = tpl.sku;
        batchProductList.value[index].category = tpl.category;
        batchProductList.value[index].price = tpl.price;
        batchProductList.value[index].current_stock = 50;
        batchProductList.value[index].safety_stock = tpl.safety_stock;
        batchProductList.value[index].max_stock = tpl.max_stock;
        batchProductList.value[index].reorder_qty = tpl.reorder_qty;
        batchProductList.value[index].lead_time_days = tpl.lead_time_days;
        batchProductList.value[index].supplier_id = tpl.supplier_id;
      }
    };

    const addBatchRow = () => {
      batchProductList.value.push(createBlankProduct());
    };

    const removeBatchRow = (index) => {
      if (batchProductList.value.length > 1) {
        batchProductList.value.splice(index, 1);
      }
    };

    const submitNewProductBatch = async () => {
      try {
        const res = await fetch(`/api/products?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify({ products: batchProductList.value })
        });
        const data = await res.json();
        if (data.status === 'success') {
          showAddProductModal.value = false;
          
          toastAlert.value = {
            show: true,
            title: "Products Created",
            msg: `Successfully added batch of ${batchProductList.value.length} products to the POS catalog.`,
            skuName: "BATCH LIST",
            channel: "Inventory Scope Manager"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);

          batchProductList.value = [createBlankProduct()];
          playPOSSound('success');
          await fetchLiveProducts();
        } else {
          let errMsg = data.message || "Unknown validation error.";
          if (data.errors) {
            const list = Object.values(data.errors).flat();
            if (list.length > 0) {
              errMsg += "\n\nDetails:\n- " + list.join("\n- ");
            }
          }
          alert("Failed to create batch: " + errMsg);
        }
      } catch (err) {
        console.error("Error creating batch products:", err);
        alert("Server communication error. Please check formatting.");
      }
    };

    const downloadCSVTemplate = () => {
      const headers = [
        'Product Name',
        'SKU Code',
        'Category',
        'Price (LKR)',
        'Stock',
        'Safety Stock',
        'Max Capacity',
        'Reorder Qty',
        'Lead Time (Days)',
        'Supplier ID (1=AgriBulk, 2=GrownOrganic, 3=Veggie)'
      ].join(',');

      const rows = [
        ['Organic Fuji Apples', 'APPL-FUJ-001', 'Fruits', '2.49', '150', '30', '400', '80', '5', '2'],
        ['Fresh Baby Spinach', 'SPIN-BAB-002', 'Vegetables', '3.99', '80', '15', '250', '50', '3', '3'],
        ['Whole Wheat Oats', 'OATS-WHL-003', 'Grains', '4.89', '200', '40', '500', '100', '7', '1']
      ].map(r => r.join(',')).join('\n');

      const csvContent = "data:text/csv;charset=utf-8," + encodeURIComponent(headers + '\n' + rows);
      const link = document.createElement('a');
      link.setAttribute('href', csvContent);
      link.setAttribute('download', 'stocksense_sku_import_template.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    const handleCSVUpload = (event) => {
      const file = event.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const content = e.target.result;
          const lines = content.split(/\r?\n/);
          if (lines.length <= 1) {
            alert("The uploaded CSV file is empty.");
            return;
          }

          const parsedList = [];
          for (let i = 1; i < lines.length; i++) {
            const line = lines[i].trim();
            if (!line) continue;

            const cols = line.split(',').map(c => c.replace(/^["']|["']$/g, '').trim());
            if (cols.length < 2) continue;

            parsedList.push({
              name: cols[0] || '',
              sku: cols[1] || '',
              category: cols[2] || '',
              price: parseFloat(cols[3]) || 9.99,
              current_stock: parseInt(cols[4]) || 50,
              safety_stock: parseInt(cols[5]) || 15,
              max_stock: parseInt(cols[6]) || 120,
              reorder_qty: cols[7] ? parseInt(cols[7]) : 40,
              lead_time_days: cols[8] ? parseInt(cols[8]) : 3,
              supplier_id: cols[9] || '1'
            });
          }

          if (parsedList.length > 0) {
            batchProductList.value = parsedList;
            
            toastAlert.value = {
              show: true,
              title: "CSV File Parsed",
              msg: `Successfully imported ${parsedList.length} products into the batch table list. Review and submit!`,
              skuName: file.name,
              channel: "CSV Parser Engine"
            };
            setTimeout(() => { toastAlert.value.show = false; }, 5000);
          } else {
            alert("No valid product rows were found in the uploaded file.");
          }
        } catch (err) {
          console.error("Error parsing CSV upload:", err);
          alert("Error parsing CSV. Please check formatting.");
        }
      };

      reader.readAsText(file);
      event.target.value = '';
    };

    // Tenants management actions
    const fetchTenants = async () => {
      try {
        const res = await fetch('/api/tenants');
        const data = await res.json();
        if (data.status === 'success') {
          tenantsList.value = data.data;
          if (tenantsList.value.length > 0 && !selectedProvisionTenantId.value) {
            selectedProvisionTenantId.value = tenantsList.value[0].id;
          }
          await fetchTenantUsers();
        }
      } catch (err) {
        console.error("Error fetching tenants:", err);
      }
    };

    const registerNewTenant = async () => {
      if (!newTenant.value.id || !newTenant.value.name) {
        alert("Please fill in both ID and Name.");
        return;
      }
      try {
        const res = await fetch('/api/tenants', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(newTenant.value)
        });
        const data = await res.json();
        if (data.status === 'success') {
          toastAlert.value = {
            show: true,
            title: "Tenant Registered",
            msg: `Successfully provisioned tenant: ${newTenant.value.name}.`,
            skuName: newTenant.value.id,
            channel: "SaaS Admin Console"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          newTenant.value = { id: '', name: '', company_name: '', subscription_tier: 'free' };
          await fetchTenants();
        } else {
          alert("Registration failed: " + data.message);
        }
      } catch (err) {
        console.error("Error creating tenant:", err);
      }
    };

    const selectedProvisionTenantId = ref('tenant_001');
    const tenantUsersList = ref([]);
    const newTenantUser = ref({ username: '', name: '', password: '', role: 'cashier' });

    const fetchTenantUsers = async () => {
      if (!selectedProvisionTenantId.value) return;
      try {
        const res = await fetch(`/api/tenants/${selectedProvisionTenantId.value}/users`);
        const data = await res.json();
        if (data.status === 'success') {
          tenantUsersList.value = data.data;
        }
      } catch (err) {
        console.error("Error fetching tenant users:", err);
      }
    };

    const createTenantUser = async () => {
      if (!selectedProvisionTenantId.value) return;
      const payload = newTenantUser.value;
      if (!payload.username || !payload.name || !payload.password) {
        alert("Please fill in all user profile details.");
        return;
      }
      try {
        const res = await fetch(`/api/tenants/${selectedProvisionTenantId.value}/users`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
          toastAlert.value = {
            show: true,
            title: "Operator Provisioned",
            msg: `Successfully created user ${payload.name} (${payload.role}) for ${selectedProvisionTenantId.value}.`,
            channel: "SaaS Admin Console"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          newTenantUser.value = { username: '', name: '', password: '', role: 'cashier' };
          await fetchTenantUsers();
        } else {
          alert("Failed to create user: " + data.message);
        }
      } catch (err) {
        console.error("Error creating tenant user:", err);
      }
    };

    const deleteTenantUser = async (userId) => {
      if (!confirm("Are you sure you want to revoke access and delete this operator account?")) {
        return;
      }
      try {
        const res = await fetch(`/api/tenant-users/${userId}`, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json'
          }
        });
        const data = await res.json();
        if (data.status === 'success') {
          toastAlert.value = {
            show: true,
            title: "Access Revoked",
            msg: data.message,
            channel: "SaaS Admin Console"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          await fetchTenantUsers();
        } else {
          alert("Failed to delete user: " + data.message);
        }
      } catch (err) {
        console.error("Error deleting tenant user:", err);
      }
    };

    const toggleTenantStatus = async (tenant) => {
      const targetStatus = tenant.status === 'active' ? 'suspended' : 'active';
      try {
        const res = await fetch(`/api/tenants/${tenant.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ status: targetStatus })
        });
        const data = await res.json();
        if (data.status === 'success') {
          localStorage.setItem('tenant_settings_updated', Date.now().toString());
          await fetchTenants();
        }
      } catch (err) {
        console.error("Error updating tenant status:", err);
      }
    };

    const changeTenantTier = async (tenant, tier) => {
      try {
        const res = await fetch(`/api/tenants/${tenant.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ subscription_tier: tier })
        });
        const data = await res.json();
        if (data.status === 'success') {
          localStorage.setItem('tenant_settings_updated', Date.now().toString());
          await fetchTenants();
        }
      } catch (err) {
        console.error("Error updating subscription tier:", err);
      }
    };

    const fetchActiveTenantSettings = async () => {
      try {
        const res = await fetch(`/api/tenants/${activeTenantId.value}`);
        const data = await res.json();
        if (data.status === 'success') {
          const parsedRate = parseFloat(data.data.tax_rate);
          activeTenantTaxRate.value = isNaN(parsedRate) ? 8.0 : parsedRate;
        }
      } catch (err) {
        console.error("Error fetching active tenant settings:", err);
      }
    };

    const fetchSuppliers = async () => {
      try {
        const res = await fetch(`/api/suppliers?tenant_id=${activeTenantId.value}`);
        const data = await res.json();
        if (data.status === 'success') {
          suppliersList.value = data.data;
        }
      } catch (err) {
        console.error("Error fetching suppliers:", err);
      }
    };

    const submitNewSupplier = async () => {
      if (!newSupplier.value.name) {
        alert("Please enter a supplier name.");
        return;
      }
      try {
        const res = await fetch(`/api/suppliers?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify(newSupplier.value)
        });
        const data = await res.json();
        if (data.status === 'success') {
          toastAlert.value = {
            show: true,
            title: "Supplier Registered",
            msg: `Successfully registered supplier: ${newSupplier.value.name}.`,
            skuName: data.data.id,
            channel: "Supplier Registry"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          
          const newId = data.data.id;
          newSupplier.value = { name: '', email: '', phone: '', alert_channel: 'Email' };
          showAddSupplierModal.value = false;
          
          await fetchSuppliers();
          newProductData.value.supplier_id = newId;
        } else {
          alert("Failed to save supplier: " + (data.message || JSON.stringify(data.errors)));
        }
      } catch (err) {
        console.error("Error registering supplier:", err);
        alert("Server communication error.");
      }
    };

    const changeTenantTaxRate = async (tenant, newTaxRate) => {
      try {
        const res = await fetch(`/api/tenants/${tenant.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ tax_rate: parseFloat(newTaxRate) })
        });
        const data = await res.json();
        if (data.status === 'success') {
          localStorage.setItem('tenant_settings_updated', Date.now().toString());
          toastAlert.value = {
            show: true,
            title: "Tax Settings Updated",
            msg: `Successfully adjusted tax/VAT rate for ${tenant.name} to ${newTaxRate}%.`,
            skuName: tenant.id,
            channel: "SaaS Admin Console"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          await fetchTenants();
        }
      } catch (err) {
        console.error("Error changing tenant tax rate:", err);
      }
    };

    const switchTenantContext = async (tenantId) => {
      activeTenantId.value = tenantId;
      localStorage.setItem('active_tenant_id', tenantId);
      localStorage.setItem('tenant_settings_updated', Date.now().toString());
      const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + `?tenant_id=${tenantId}`;
      window.history.pushState({ path: newurl }, '', newurl);
      
      skus.value = [];
      selectedSkuId.value = '';
      
      toastAlert.value = {
        show: true,
        title: "Context Switched",
        msg: `SaaS session switched to scope: ${tenantId}. Reloading inventory partition.`,
        skuName: tenantId,
        channel: "System Scope Manager"
      };
      setTimeout(() => { toastAlert.value.show = false; }, 4000);
      
      await fetchActiveTenantSettings();
      await fetchSuppliers();
      await fetchLiveProducts();
      await fetchTransactionHistory();
      await fetchActiveShift();
    };

    const activeShift = ref(null);
    const showShiftGate = ref(false);
    const openingFloat = ref(0);

    const showCloseShiftModal = ref(false);
    const actualCashCounted = ref(0);
    const closeShiftReport = ref(null);

    const showPaymentModal = ref(false);
    const checkoutPaymentMethod = ref('cash');
    const cashTendered = ref(0);

    const fetchActiveShift = async () => {
      try {
        const res = await fetch(`/api/shifts/active?tenant_id=${activeTenantId.value}`);
        const data = await res.json();
        if (data.status === 'success') {
          activeShift.value = data.data;
          showShiftGate.value = false;
        } else {
          activeShift.value = null;
          showShiftGate.value = true;
        }
      } catch (err) {
        console.error("Error fetching active shift:", err);
      }
    };

    const openShift = async () => {
      if (parseFloat(openingFloat.value || 0) < 0) {
        alert("Please enter a valid float amount.");
        return;
      }
      try {
        const res = await fetch(`/api/shifts/open?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify({ opening_float: parseFloat(openingFloat.value || 0) })
        });
        const data = await res.json();
        if (data.status === 'success') {
          playPOSSound('success');
          showShiftGate.value = false;
          await fetchActiveShift();
        } else {
          alert("Error opening shift: " + data.message);
        }
      } catch (err) {
        console.error("Error opening shift:", err);
      }
    };

    const closeShift = async () => {
      if (parseFloat(actualCashCounted.value || 0) < 0) {
        alert("Please enter a valid counted cash amount.");
        return;
      }
      try {
        const res = await fetch(`/api/shifts/close?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify({ actual_cash: parseFloat(actualCashCounted.value || 0) })
        });
        const data = await res.json();
        if (data.status === 'success') {
          playPOSSound('success');
          closeShiftReport.value = data.data;
          showCloseShiftModal.value = false;
          activeShift.value = null;
          showShiftGate.value = true;
        } else {
          alert("Error closing shift: " + data.message);
        }
      } catch (err) {
        console.error("Error closing shift:", err);
      }
    };

    const startCheckoutFlow = () => {
      if (cart.value.length === 0) return;
      cashTendered.value = cartTotal.value;
      checkoutPaymentMethod.value = 'cash';
      showPaymentModal.value = true;
    };

    const showBarcodeModal = ref(false);
    const barcodeModalProduct = ref(null);
    const barcodePrintQty = ref('single');

    const openBarcodeModal = (product) => {
      barcodeModalProduct.value = product;
      barcodePrintQty.value = 'single';
      showBarcodeModal.value = true;
    };

    const getBarcodeSVG = (sku) => {
      return getCode39SVG(sku);
    };

    const transactionHistory = ref([]);
    const showHistoryPanel = ref(false);

    const fetchTransactionHistory = async () => {
      try {
        const res = await fetch(`/api/transactions?tenant_id=${activeTenantId.value}`);
        const data = await res.json();
        if (data.status === 'success') {
          transactionHistory.value = data.data;
        }
      } catch (err) {
        console.error("Error fetching transactions:", err);
      }
    };

    // Product Performance Analytics Computeds
    const flatTransactions = computed(() => {
      return transactionHistory.value.flatMap(receipt => {
        return receipt.items.map(item => ({
          ...item,
          receiptId: receipt.id,
          time: receipt.time,
          discount: receipt.discount,
          tax: receipt.tax,
          paymentMethod: receipt.payment_method
        }));
      });
    });

    const skuSales = computed(() => {
      const sales = {};
      skus.value.forEach(p => {
        sales[p.sku] = {
          id: p.id,
          sku: p.sku,
          name: p.name,
          price: p.price,
          costPrice: p.costPrice || (p.price * 0.7),
          quantity: 0,
          revenue: 0,
          profit: 0
        };
      });

      flatTransactions.value.forEach(tx => {
        if (sales[tx.sku]) {
          sales[tx.sku].quantity += tx.quantity;
          sales[tx.sku].revenue += tx.subtotal;
          const cost = sales[tx.sku].costPrice;
          sales[tx.sku].profit += tx.quantity * (tx.price - cost);
        }
      });

      return Object.values(sales);
    });

    const topSellingProducts = computed(() => {
      return [...skuSales.value]
        .filter(s => s.quantity > 0)
        .sort((a, b) => b.revenue - a.revenue)
        .slice(0, 5);
    });

    const slowMovingItems = computed(() => {
      return skuSales.value
        .filter(s => s.quantity < 5)
        .map(s => {
          const prod = skus.value.find(p => p.sku === s.sku);
          return {
            id: s.id,
            sku: s.sku,
            name: s.name,
            price: s.price,
            quantitySold: s.quantity,
            currentStock: prod ? prod.currentStock : 0,
          };
        });
    });

    const analyticsKPIs = computed(() => {
      let totalRevenue = 0;
      let totalProfit = 0;
      let totalCOGS = 0;

      flatTransactions.value.forEach(tx => {
        totalRevenue += tx.subtotal;
        const prod = skus.value.find(p => p.sku === tx.sku);
        const costPrice = prod ? (prod.costPrice || (prod.price * 0.7)) : (tx.price * 0.7);
        const cost = costPrice * tx.quantity;
        totalCOGS += cost;
        totalProfit += (tx.subtotal - cost);
      });

      let totalInventoryCost = 0;
      skus.value.forEach(p => {
        const cost = p.costPrice || (p.price * 0.7);
        totalInventoryCost += p.currentStock * cost;
      });

      const turnoverRatio = totalInventoryCost > 0 ? (totalCOGS / totalInventoryCost) : 0;
      const averageMargin = totalRevenue > 0 ? (totalProfit / totalRevenue) * 100 : 0;

      return {
        totalRevenue,
        totalProfit,
        averageMargin,
        turnoverRatio,
        totalInventoryValue: totalInventoryCost
      };
    });

    const applyMarkdown = async (productId, percent) => {
      const prod = skus.value.find(p => p.id === productId.toString());
      if (!prod) return;
      const originalPrice = prod.price;
      const discountedPrice = Math.round(originalPrice * (1 - percent / 100));

      try {
        const res = await fetch(`/api/products/${productId}/price?tenant_id=${activeTenantId.value}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify({ price: discountedPrice })
        });
        const data = await res.json();
        if (data.status === 'success') {
          playPOSSound('success');
          toastAlert.value = {
            show: true,
            title: "Promotion Markdown Applied",
            msg: `Price of ${prod.name} successfully discounted by ${percent}% (from LKR ${originalPrice.toFixed(2)} to LKR ${discountedPrice.toFixed(2)}).`,
            channel: "System Engine"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 6000);
          await fetchLiveProducts();
        } else {
          alert("Error applying markdown: " + data.message);
        }
      } catch (err) {
        console.error("Error applying markdown price:", err);
      }
    };

    const purchaseOrders = ref([]);
    const showPOModal = ref(false);
    const activePOProduct = ref(null);
    const poQuantity = ref(0);
    const poMessageBody = ref('');

    const fetchPurchaseOrders = async () => {
      try {
        const res = await fetch(`/api/purchase-orders?tenant_id=${activeTenantId.value}`, {
          headers: {
            'X-Tenant-ID': activeTenantId.value
          }
        });
        const data = await res.json();
        if (data.status === 'success') {
          purchaseOrders.value = data.data;
        }
      } catch (err) {
        console.error("Error fetching POs:", err);
      }
    };

    const openPOModal = (product) => {
      activePOProduct.value = product;
      poQuantity.value = product.reorderQty || 50;
      
      poMessageBody.value = `Subject: Urgent Restock Order - StockSense Store

Dear Supplier Partners,

This is an automated purchase order request from StockSense Store (Tenant ID: ${activeTenantId.value.toUpperCase()}).
Our stock levels for the following item have dropped below safety thresholds:

Item: ${product.name}
SKU: ${product.sku}
Recommended Reorder Qty: ${poQuantity.value} units

Please verify availability and dispatch with estimated lead time.

Regards,
Inventory Management Team`;

      showPOModal.value = true;
    };

    watch(poQuantity, (newVal) => {
      if (activePOProduct.value) {
        poMessageBody.value = poMessageBody.value.replace(/Recommended Reorder Qty: \d+ units/, `Recommended Reorder Qty: ${newVal} units`);
      }
    });

    const dispatchPurchaseOrder = async () => {
      if (!activePOProduct.value) return;
      try {
        const res = await fetch(`/api/purchase-orders?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify({
            product_id: activePOProduct.value.id,
            supplier_id: activePOProduct.value.supplierId || 1,
            quantity: poQuantity.value,
            message_body: poMessageBody.value
          })
        });
        const data = await res.json();
        if (data.status === 'success') {
          playPOSSound('success');
          showPOModal.value = false;
          
          toastAlert.value = {
            show: true,
            title: "PO Dispatched Successfully",
            msg: data.message,
            channel: activePOProduct.value.alertChannel || "Email"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 6000);
          
          await fetchPurchaseOrders();
          await fetchLiveProducts();
        } else {
          alert("Error dispatching PO: " + data.message);
        }
      } catch (err) {
        console.error("Error dispatching purchase order:", err);
      }
    };

    const receivePurchaseOrder = async (poId) => {
      try {
        const res = await fetch(`/api/purchase-orders/${poId}/receive?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          }
        });
        const data = await res.json();
        if (data.status === 'success') {
          playPOSSound('success');
          toastAlert.value = {
            show: true,
            title: "Stock Replenished",
            msg: data.message,
            channel: "System Inventory Engine"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 5000);
          
          await fetchPurchaseOrders();
          await fetchLiveProducts();
        } else {
          alert("Error receiving PO: " + data.message);
        }
      } catch (err) {
        console.error("Error receiving stock:", err);
      }
    };

    const calculatorServiceLevel = ref('95');
    const calculatorLeadTime = ref(3);
    const calculatorOverrideStdDev = ref(null);

    watch(selectedSku, (newSku) => {
      if (newSku) {
        calculatorLeadTime.value = newSku.leadTime || 3;
        calculatorOverrideStdDev.value = null;
      }
    }, { immediate: true });

    const getZScore = (level) => {
      const maps = { '90': 1.28, '95': 1.65, '98': 2.05, '99': 2.33 };
      return maps[level] || 1.65;
    };

    const calculatedStdDev = computed(() => {
      if (!selectedSku.value) return 0;
      
      const skuTxs = flatTransactions.value.filter(tx => tx.sku === selectedSku.value.sku);
      if (skuTxs.length === 0) return 3.5;
      
      const dailySales = {};
      skuTxs.forEach(tx => {
        const dateStr = new Date(tx.time).toDateString();
        dailySales[dateStr] = (dailySales[dateStr] || 0) + tx.quantity;
      });
      
      const vols = Object.values(dailySales);
      if (vols.length === 0) return 3.5;
      
      const n = vols.length;
      const mean = vols.reduce((a, b) => a + b, 0) / n;
      const variance = vols.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / n;
      return parseFloat(Math.max(0.5, Math.sqrt(variance)).toFixed(2));
    });

    const finalSafetyStock = computed(() => {
      if (!selectedSku.value) return 0;
      const z = getZScore(calculatorServiceLevel.value);
      const stdDev = calculatorOverrideStdDev.value !== null ? parseFloat(calculatorOverrideStdDev.value) : calculatedStdDev.value;
      const lt = calculatorLeadTime.value || selectedSku.value.leadTime || 3;
      
      return Math.round(z * stdDev * Math.sqrt(lt));
    });

    const applySafetyStock = async () => {
      if (!selectedSku.value) return;
      try {
        const res = await fetch(`/api/products/${selectedSku.value.id}/safety-stock?tenant_id=${activeTenantId.value}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify({ safety_stock: finalSafetyStock.value })
        });
        const data = await res.json();
        if (data.status === 'success') {
          playPOSSound('success');
          
          toastAlert.value = {
            show: true,
            title: "Safety Stock Updated",
            msg: `Safety stock threshold for ${selectedSku.value.name} updated to ${finalSafetyStock.value} units in database.`,
            channel: "Inventory Engine"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 6000);
          
          await fetchLiveProducts();
        } else {
          alert("Error updating safety stock: " + data.message);
        }
      } catch (err) {
        console.error("Error updating safety stock:", err);
      }
    };

    const voidTransaction = async (receiptId) => {
      if (!confirm(`Are you sure you want to void transaction ${receiptId}? This will delete the sale records and refund all items back into store inventory.`)) {
        return;
      }
      
      try {
        const res = await fetch(`/api/transactions/${receiptId}?tenant_id=${activeTenantId.value}`, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          }
        });
        const data = await res.json();
        if (data.status === 'success') {
          toastAlert.value = {
            show: true,
            title: "Transaction Voided",
            msg: `Receipt ${receiptId} was voided. Items returned to inventory.`,
            skuName: receiptId,
            channel: "POS Auditing"
          };
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          
          playPOSSound('trash');
          await fetchTransactionHistory();
          await fetchLiveProducts();
        } else {
          alert("Failed to void transaction: " + data.message);
        }
      } catch (err) {
        console.error("Error voiding transaction:", err);
        alert("Server communication error.");
      }
    };

    const reprintReceipt = (receipt) => {
      receiptData.value = receipt;
      showReceiptModal.value = true;
    };

    const printReceipt = () => {
      window.print();
    };

    const syncOfflineTransactions = async () => {
      const cached = JSON.parse(localStorage.getItem('offline_transactions') || '[]');
      if (cached.length === 0) return;
      
      systemStatus.value = `Syncing ${cached.length} offline orders...`;
      systemStatusClass.value = "warning";
      
      let successCount = 0;
      
      // Ingest all cached batches
      for (const payload of cached) {
        try {
          const res = await fetch(`/api/products/ingest?tenant_id=${activeTenantId.value}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-Tenant-ID': activeTenantId.value
            },
            body: JSON.stringify(payload)
          });
          const data = await res.json();
          if (data.status === 'success') {
            successCount += data.ingested_count;
          }
        } catch (err) {
          console.error("Offline sync item failed:", err);
        }
      }
      
      // Clear cache
      localStorage.removeItem('offline_transactions');
      
      // Show toast
      toastAlert.value = {
        show: true,
        title: "Data Synced",
        msg: `Synchronized ${successCount} offline transaction(s) to Neon cloud.`,
        skuName: "Auto-Sync",
        channel: "Database Gateway"
      };
      setTimeout(() => { toastAlert.value.show = false; }, 5000);
      
      // Refresh products list
      await fetchLiveProducts();
    };

    watch(activeTab, (newTab) => {
      if (userRole.value === 'cashier' && newTab !== 'pos') {
        activeTab.value = 'pos';
      } else if (userRole.value === 'owner') {
        if (subscriptionTier.value === 'free' && !['overview', 'pos'].includes(newTab)) {
          activeTab.value = 'overview';
        } else if (subscriptionTier.value === 'basic' && ['forecast', 'mlops'].includes(newTab)) {
          activeTab.value = 'overview';
        }
      }
    });

    onMounted(async () => {
      // Gating validations on load
      if (userRole.value === 'cashier') {
        activeTab.value = 'pos';
      } else if (userRole.value === 'owner') {
        if (subscriptionTier.value === 'free') {
          if (!['overview', 'pos'].includes(activeTab.value)) {
            activeTab.value = 'overview';
          }
        } else if (subscriptionTier.value === 'basic') {
          if (['forecast', 'mlops'].includes(activeTab.value)) {
            activeTab.value = 'overview';
          }
        }
      }

      await fetchActiveTenantSettings();
      await fetchSuppliers();
      await fetchLiveProducts();
      await fetchTransactionHistory();
      await fetchActiveShift();
      await fetchPurchaseOrders();

      // Listen to storage sync events across open tabs/panels
      window.addEventListener('storage', async (event) => {
        if (event.key === 'active_tenant_id' && event.newValue) {
          if (event.newValue !== activeTenantId.value) {
            activeTenantId.value = event.newValue;
            const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + `?tenant_id=${event.newValue}`;
            window.history.pushState({ path: newurl }, '', newurl);
          }
        }
        if (event.key === 'active_tenant_id' || event.key === 'tenant_settings_updated') {
          await fetchActiveTenantSettings();
          await fetchSuppliers();
          await fetchLiveProducts();
          await fetchTransactionHistory();
          await fetchActiveShift();
          await fetchPurchaseOrders();
        }
      });

      // Monitor network online/offline transitions
      window.addEventListener('online', async () => {
        isOnline.value = true;
        systemStatus.value = "Back online. Syncing database...";
        systemStatusClass.value = "warning";
        await syncOfflineTransactions();
      });

      window.addEventListener('offline', () => {
        isOnline.value = false;
        systemStatus.value = "Offline Mode Active";
        systemStatusClass.value = "warning";
      });

      // Try syncing any cached orders on boot if online
      if (isOnline.value) {
        await syncOfflineTransactions();
      }

      // Monitor hardware barcode scanner keyboard emulation
      let barcodeBuffer = '';
      let lastKeyTime = Date.now();

      window.addEventListener('keydown', (e) => {
        // Only run if active tab is POS Terminal
        if (activeTab.value !== 'pos') return;

        // Skip global buffer if user is explicitly typing in a form input (except our own barcode input)
        const activeEl = document.activeElement;
        if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'SELECT' || activeEl.tagName === 'TEXTAREA')) {
          if (!activeEl.classList.contains('barcode-input')) {
            return;
          }
        }

        const currentTime = Date.now();
        const timeDiff = currentTime - lastKeyTime;
        lastKeyTime = currentTime;

        // Scanners type extremely rapidly (typical delay < 35ms per character)
        if (timeDiff < 40) {
          if (e.key === 'Enter') {
            if (barcodeBuffer.length > 2) {
              addSkuByBarcode(barcodeBuffer.trim());
            }
            barcodeBuffer = '';
            e.preventDefault();
          } else if (e.key.length === 1) {
            barcodeBuffer += e.key;
          }
        } else {
          // Normal typing speed, reset buffer
          barcodeBuffer = e.key.length === 1 ? e.key : '';
        }
      });

      // Handle click outside suggestions dropdown to close it
      window.addEventListener('click', (e) => {
        const inputContainer = document.querySelector('.barcode-scanner-container');
        if (inputContainer && !inputContainer.contains(e.target)) {
          showBarcodeSuggestions.value = false;
        }
      });

      // Fluctuate model drift score periodically and check limit triggers
      setInterval(() => {
        if (isRetraining.value) return;
        const delta = (Math.random() * 0.45 - 0.15); // skew slightly upwards
        modelDriftScore.value = parseFloat(Math.max(0.5, Math.min(10.0, modelDriftScore.value + delta)).toFixed(2));
        checkDriftAutorecovery();
      }, 15000);
    });

    return {
      activeTab,
      systemStatus,
      systemStatusClass,
      savedCapital,
      stockoutRisks,
      wmapeAvg,
      activeSkusCount,
      skus,
      selectedSkuId,
      selectedSku,
      priceAdjustment,
      simulatedPrice,
      demandShiftPercent,
      adjustedForecast,
      simulatedUploadStatus,
      simulatedUploadProgress,
      simulatedUploadFileName,
      supplierAlerts,
      modelDriftScore,
      isRetraining,
      mlopsLogs,
      toastAlert,
      triggerPOSUpload,
      sendRestockAlert,
      triggerRetraining,
      cart,
      addToCart,
      removeFromCart,
      deleteFromCart,
      clearCart,
      cartSubtotal,
      cartTax,
      cartTotal,
      submitCheckout,
      tenantsList,
      newTenant,
      activeTenantId,
      fetchTenants,
      registerNewTenant,
      toggleTenantStatus,
      changeTenantTier,
      switchTenantContext,
      isOnline,
      showReceiptModal,
      receiptData,
      printReceipt,
      syncOfflineTransactions,
      barcodeQuery,
      barcodeScanClass,
      addSkuByBarcode,
      barcodeSuggestions,
      showBarcodeSuggestions,
      activeSuggestionIndex,
      moveSuggestionActive,
      selectActiveSuggestion,
      activeModel,
      driftLimit,
      showAddProductModal,
      newProductData,
      submitNewProduct,
      addProductTab,
      batchProductList,
      addBatchRow,
      removeBatchRow,
      submitNewProductBatch,
      discountType,
      discountValue,
      discountAmount,
      activeTenantTaxRate,
      changeTenantTaxRate,
      downloadCSVTemplate,
      handleCSVUpload,
      suppliersList,
      newSupplier,
      showAddSupplierModal,
      fetchSuppliers,
      submitNewSupplier,
      productTemplates,
      handleSingleNameInput,
      handleBatchNameInput,
      combinedCatalogSuggestions,
      transactionHistory,
      showHistoryPanel,
      fetchTransactionHistory,
      voidTransaction,
      reprintReceipt,
      activeShift,
      showShiftGate,
      openingFloat,
      showCloseShiftModal,
      actualCashCounted,
      closeShiftReport,
      showPaymentModal,
      checkoutPaymentMethod,
      cashTendered,
      openShift,
      closeShift,
      startCheckoutFlow,
      showBarcodeModal,
      barcodeModalProduct,
      barcodePrintQty,
      openBarcodeModal,
      getBarcodeSVG,
      topSellingProducts,
      slowMovingItems,
      skuSales,
      analyticsKPIs,
      applyMarkdown,
      purchaseOrders,
      showPOModal,
      activePOProduct,
      poQuantity,
      poMessageBody,
      openPOModal,
      dispatchPurchaseOrder,
      receivePurchaseOrder,
      calculatorServiceLevel,
      calculatorLeadTime,
      calculatorOverrideStdDev,
      calculatedStdDev,
      finalSafetyStock,
      applySafetyStock,
      userRole,
      userName,
      cleanedUserName,
      tenantName,
      selectedProvisionTenantId,
      tenantUsersList,
      newTenantUser,
      fetchTenantUsers,
      createTenantUser,
      deleteTenantUser,
      subscriptionTier
    };
  }
}).mount('#app');
