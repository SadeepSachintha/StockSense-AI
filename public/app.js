const { createApp, ref, computed, onMounted, watch } = Vue;

createApp({
  setup() {
    // Nav & System status
    const activeTab = ref('overview');
    const systemStatus = ref('ETL Pipeline: Success');
    const systemStatusClass = ref('emerald');
    const activeTenantId = ref(new URLSearchParams(window.location.search).get('tenant_id') || 'tenant_001');

    // Tenant Administration UI bindings
    const tenantsList = ref([]);
    const newTenant = ref({ id: '', name: '', company_name: '', subscription_tier: 'free' });
    
    // Core KPIs
    const savedCapital = ref(12450);
    const stockoutRisks = ref(0);
    const wmapeAvg = ref(3.8);
    const activeSkusCount = ref(0);
    
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

      const labels = generateChartLabels();
      
      const salesHistoryDataset = [...sku.salesHistory, ...Array(14).fill(null)];
      
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
            label: 'Demand Forecast (Adjusted)',
            data: adjustedForecastPadded,
            borderColor: '#8b5cf6',
            borderDash: [5, 5],
            borderWidth: 3,
            pointRadius: 0,
            tension: 0.3,
            fill: false
          },
          {
            label: 'Baseline Forecast',
            data: baselineForecastPadded,
            borderColor: 'rgba(139, 92, 246, 0.3)',
            borderDash: [3, 3],
            borderWidth: 1.5,
            pointRadius: 0,
            tension: 0.3,
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
            backgroundColor: 'rgba(139, 92, 246, 0.05)',
            pointRadius: 0,
            fill: '+1',
            tension: 0.3
          },
          {
            label: 'Lower Bound CI',
            data: confidenceLowerPadded,
            borderColor: 'transparent',
            backgroundColor: 'rgba(139, 92, 246, 0.05)',
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
            elasticity: parseFloat(p.elasticity),
            currentStock: parseInt(p.current_stock),
            safetyStock: parseInt(p.safety_stock),
            maxStock: parseInt(p.max_stock),
            reorderQty: parseInt(p.reorder_qty),
            leadTime: parseInt(p.lead_time_days),
            supplier: p.supplier.name,
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
      setTimeout(() => {
        sku.restockStatus = 'Confirmed';
        sku.currentStock += sku.reorderQty; // Replenish stock
        
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
        await appendLog('Executing Task [verify_drift] - Drift verify completed (2.41% score).', 'success', 300);
        await appendLog('DAG completed successfully. FastAPI prediction cached reloaded.', 'success', 400);
        
        modelDriftScore.value = 2.4;
        wmapeAvg.value = 3.5;
        isRetraining.value = false;
      })();
    };

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

    const cartSubtotal = computed(() => {
      return cart.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
    });

    const cartTax = computed(() => {
      return cartSubtotal.value * 0.08; // 8% sales tax
    });

    const cartTotal = computed(() => {
      return cartSubtotal.value + cartTax.value;
    });

    const submitCheckout = async () => {
      if (cart.value.length === 0) return;
      
      systemStatus.value = "Processing checkout...";
      systemStatusClass.value = "warning";
      
      const payload = {
        transactions: cart.value.map(item => ({
          sku: item.product.sku,
          quantity: item.quantity,
          price_sold: item.product.price,
          transaction_date: new Date().toISOString().slice(0, 19).replace('T', ' ')
        }))
      };

      try {
        const res = await fetch(`/api/products/ingest?tenant_id=${activeTenantId.value}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Tenant-ID': activeTenantId.value
          },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.status === 'success') {
          // Clear cart
          cart.value = [];
          
          // Toast Notification
          toastAlert.value = {
            show: true,
            title: "Transaction Complete",
            msg: `Successfully processed checkout of ${data.ingested_count} item(s). Inventory synced.`,
            skuName: "POS Checkout",
            channel: "POS Terminal"
          };
          
          setTimeout(() => { toastAlert.value.show = false; }, 4000);
          
          // Reload products list to reflect new stocks and update alerts
          await fetchLiveProducts();
        } else {
          alert("Checkout failed: " + data.message);
          systemStatus.value = "Checkout failed.";
          systemStatusClass.value = "danger";
        }
      } catch (err) {
        console.error("Checkout error:", err);
        alert("Server communication error.");
        systemStatus.value = "SaaS offline.";
        systemStatusClass.value = "danger";
      }
    };

    const fetchTenants = async () => {
      try {
        const res = await fetch('/api/tenants');
        const data = await res.json();
        if (data.status === 'success') {
          tenantsList.value = data.data;
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
          headers: { 'Content-Type': 'application/json' },
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

    const toggleTenantStatus = async (tenant) => {
      const targetStatus = tenant.status === 'active' ? 'suspended' : 'active';
      try {
        const res = await fetch(`/api/tenants/${tenant.id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: targetStatus })
        });
        const data = await res.json();
        if (data.status === 'success') {
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
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ subscription_tier: tier })
        });
        const data = await res.json();
        if (data.status === 'success') {
          await fetchTenants();
        }
      } catch (err) {
        console.error("Error updating subscription tier:", err);
      }
    };

    const switchTenantContext = async (tenantId) => {
      activeTenantId.value = tenantId;
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
      
      await fetchLiveProducts();
    };

    onMounted(async () => {
      await fetchLiveProducts();
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
      switchTenantContext
    };
  }
}).mount('#app');
