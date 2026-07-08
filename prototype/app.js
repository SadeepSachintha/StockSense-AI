const { createApp, ref, computed, onMounted, watch } = Vue;

createApp({
  setup() {
    // Nav & System status
    const activeTab = ref('overview');
    const systemStatus = ref('ETL Pipeline: Success');
    const systemStatusClass = ref('emerald');
    
    // Core KPIs
    const savedCapital = ref(12450);
    const stockoutRisks = ref(1);
    const wmapeAvg = ref(4.6);
    const activeSkusCount = ref(256);
    
    // SKU Database
    const skus = ref([
      {
        id: 'basmati_rice',
        name: 'Premium Basmati Rice - 5kg',
        category: 'Grains & Staples',
        sku: 'SKU-BMR-005',
        currentStock: 95,
        safetyStock: 80,
        maxStock: 300,
        wmape: 4.2,
        price: 14.99,
        elasticity: -1.4,
        reorderQty: 150,
        leadTime: 3,
        supplier: 'Indus Grains Co.',
        supplierContact: '+1 (555) 321-4567',
        restockStatus: 'Normal', // Normal, Low Stock, Dispatched, Confirmed
        alertChannel: 'WhatsApp',
        salesHistory: [70, 75, 82, 68, 90, 85, 78, 92, 88, 74, 80, 85, 96, 110, 105, 98, 85, 80, 72, 88, 95, 102, 108, 97, 86, 92, 100, 105, 98, 94],
        forecast: [95, 98, 102, 105, 110, 108, 115, 120, 118, 122, 125, 128, 130, 132]
      },
      {
        id: 'organic_fertilizer',
        name: 'Organic NPK Fertilizer - 25L',
        category: 'Agrochemicals',
        sku: 'SKU-ONP-025',
        currentStock: 32,
        safetyStock: 50,
        maxStock: 150,
        wmape: 5.8,
        price: 29.99,
        elasticity: -0.8,
        reorderQty: 80,
        leadTime: 5,
        supplier: 'BioAg Solutions',
        supplierContact: 'orders@bioagsolutions.com',
        restockStatus: 'Low Stock',
        alertChannel: 'Email',
        salesHistory: [25, 28, 30, 22, 18, 20, 24, 32, 35, 40, 38, 42, 45, 48, 52, 50, 47, 43, 38, 35, 30, 28, 26, 31, 35, 38, 42, 40, 36, 34],
        forecast: [35, 37, 39, 42, 45, 48, 50, 52, 55, 58, 60, 62, 65, 68]
      },
      {
        id: 'alphonso_mangoes',
        name: 'Alphonso Mango Export Box',
        category: 'Fresh Produce',
        sku: 'SKU-AMB-012',
        currentStock: 180,
        safetyStock: 60,
        maxStock: 250,
        wmape: 3.9,
        price: 24.50,
        elasticity: -2.1,
        reorderQty: 100,
        leadTime: 2,
        supplier: 'Deccan Orchards',
        supplierContact: '+1 (555) 987-6543',
        restockStatus: 'Normal',
        alertChannel: 'WhatsApp',
        salesHistory: [120, 130, 145, 150, 135, 140, 155, 160, 175, 190, 210, 205, 195, 180, 165, 150, 140, 130, 125, 135, 142, 150, 162, 170, 175, 185, 190, 195, 188, 182],
        forecast: [180, 175, 170, 165, 160, 155, 150, 145, 140, 135, 130, 125, 120, 115]
      }
    ]);

    const selectedSkuId = ref('basmati_rice');
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
      if (!selectedSku.value) return [];
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
        skuName: 'Organic NPK Fertilizer - 25L',
        supplier: 'BioAg Solutions',
        channel: 'Email',
        status: 'Sent',
        desc: 'Safety stock violated (Current: 32 / Safety: 50). Purchase Order #PO-2607-09 dispatch triggered.'
      }
    ]);

    // MLOps Retraining logs & Drift State
    const modelDriftScore = ref(5.8); // trigger point is 5.0%
    const isRetraining = ref(false);
    const mlopsLogs = ref([
      { time: '12:00:05', text: 'System standing by. MLflow registered model lgbm_v4.2.1 active.', type: 'info' },
      { time: '12:00:06', text: 'Drift monitor triggered warning: Alphonso Mangoes WMAPE degraded from 3.9% to 5.8% over last 7 days.', type: 'warning' },
      { time: '12:00:07', text: 'Suggested actions: Ingest latest POS data and trigger nightly retraining loop.', type: 'info' }
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
      if (!sku) return;

      const labels = generateChartLabels();
      
      // Sales History padded, predictions padded
      const historyData = [...sku.salesHistory];
      const emptyPadding = Array(14).fill(null);
      const salesHistoryDataset = [...historyData, ...emptyPadding];

      // Predicted baseline & adjusted
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

      // Confidence intervals (upper & lower bands)
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

      // Safety stock threshold line
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
            fill: '+1', // Fill to lower bound
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
                  font: {
                    family: 'Inter',
                    size: 11
                  },
                  filter: function(item) {
                    // Hide CI boundary item legends
                    return !item.text.includes('Bound CI') && !item.text.includes('Baseline');
                  }
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
                grid: {
                  color: 'rgba(255, 255, 255, 0.03)',
                  borderColor: 'rgba(255, 255, 255, 0.05)'
                },
                ticks: {
                  color: '#6b7280',
                  font: { size: 10 },
                  maxTicksLimit: 12
                }
              },
              y: {
                grid: {
                  color: 'rgba(255, 255, 255, 0.03)',
                  borderColor: 'rgba(255, 255, 255, 0.05)'
                },
                ticks: {
                  color: '#6b7280',
                  font: { size: 10 }
                }
              }
            }
          }
        });
      }
    };

    // Watch tab change to render chart
    watch(activeTab, (newTab) => {
      if (newTab === 'forecast' || newTab === 'overview') {
        setTimeout(renderChart, 50);
      }
    });

    // Watch SKU selection and Price Slider adjustments to update chart
    watch([selectedSkuId, priceAdjustment], () => {
      if (activeTab.value === 'forecast' || activeTab.value === 'overview') {
        renderChart();
      }
    });

    // Ingest POS CSV simulation
    const triggerPOSUpload = () => {
      if (simulatedUploadStatus.value === 'uploading') return;
      
      simulatedUploadFileName.value = 'pos_transactions_' + new Date().toISOString().slice(0,10) + '.csv';
      simulatedUploadStatus.value = 'uploading';
      simulatedUploadProgress.value = 10;
      
      const interval = setInterval(() => {
        simulatedUploadProgress.value += 30;
        if (simulatedUploadProgress.value >= 100) {
          clearInterval(interval);
          simulatedUploadStatus.value = 'success';
          
          // Trigger data changes to show visual updates
          setTimeout(() => {
            // Basmati Rice drops in stock simulates new sales records
            const rice = skus.value.find(s => s.id === 'basmati_rice');
            if (rice) {
              rice.currentStock = 45; // Below safety stock of 80
              rice.restockStatus = 'Low Stock';
            }
            
            // Increment stockout risks
            stockoutRisks.value = 2;
            systemStatus.value = 'Data Ingested. Stockout Warning Triggered.';
            systemStatusClass.value = 'danger';
            
            // Add timeline alert
            supplierAlerts.value.unshift({
              id: Date.now(),
              time: 'Just Now',
              skuName: 'Premium Basmati Rice - 5kg',
              supplier: 'Indus Grains Co.',
              channel: 'WhatsApp',
              status: 'Awaiting action',
              desc: 'POS Ingestion complete. Stock fell to 45 (Safety Limit: 80). Restocking recommended.'
            });

            // Log entry in MLOps
            const now = new Date().toLocaleTimeString();
            mlopsLogs.value.push({
              time: now,
              text: 'Ingested POS CSV successfully: 48,290 sales records processed. Data drift score increased to 6.2%.',
              type: 'warning'
            });
            modelDriftScore.value = 6.2;
            
            // Reset modal state
            setTimeout(() => {
              simulatedUploadStatus.value = 'idle';
              simulatedUploadProgress.value = 0;
            }, 3000);
          }, 500);
        }
      }, 400);
    };

    // Auto-restocking Supplier WhatsApp/Email simulation
    const sendRestockAlert = (sku) => {
      if (sku.restockStatus === 'Dispatched' || sku.restockStatus === 'Confirmed') return;
      
      sku.restockStatus = 'Dispatched';
      
      // Create new alert timeline log
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

      // Show Dispatch Toast
      toastAlert.value = {
        show: true,
        title: `${sku.alertChannel} PO Broadcasted`,
        msg: `Sent restocking order of ${sku.reorderQty} units for ${sku.name} directly to ${sku.supplier}.`,
        skuName: sku.name,
        channel: sku.alertChannel
      };

      // Auto dismiss toast in 4 seconds
      setTimeout(() => {
        if (toastAlert.value.skuName === sku.name) {
          toastAlert.value.show = false;
        }
      }, 4000);

      // Simulate Supplier Confirmation after 3.5 seconds
      setTimeout(() => {
        sku.restockStatus = 'Confirmed';
        
        // Update timeline status
        const alertIdx = supplierAlerts.value.findIndex(a => a.skuName === sku.name && a.status === 'Dispatched');
        if (alertIdx !== -1) {
          supplierAlerts.value[alertIdx].status = 'Confirmed';
          supplierAlerts.value[alertIdx].time = '3s ago';
          supplierAlerts.value[alertIdx].desc += ` supplier response received: Delivery scheduled in ${sku.leadTime} days.`;
        }

        // Show Confirmation Toast
        toastAlert.value = {
          show: true,
          title: `Supplier Confirmed PO`,
          msg: `${sku.supplier} accepted order for ${sku.name}. ETA: ${sku.leadTime} days. (Simulating direct ERP sync)`,
          skuName: sku.name,
          channel: sku.alertChannel + ' Confirmation'
        };

        // Increment saved capital/efficiency
        savedCapital.value += 120; // Simulated cost savings by preventing stockout
        if (stockoutRisks.value > 0) stockoutRisks.value -= 1;

        // Reset system status to normal if no risks
        if (stockoutRisks.value === 0) {
          systemStatus.value = 'ETL Pipeline: Success';
          systemStatusClass.value = 'emerald';
        }

        // Auto dismiss confirmation toast
        setTimeout(() => {
          if (toastAlert.value.skuName === sku.name) {
            toastAlert.value.show = false;
          }
        }, 5000);

      }, 3500);
    };

    // Airflow Retraining Simulation
    const triggerRetraining = () => {
      if (isRetraining.value) return;
      isRetraining.value = true;
      
      const appendLog = (text, type = 'info', delay = 0) => {
        return new Promise(resolve => {
          setTimeout(() => {
            const time = new Date().toLocaleTimeString();
            mlopsLogs.value.push({ time, text, type });
            // Scroll console to bottom
            const consoleBox = document.getElementById('consoleBox');
            if (consoleBox) {
              consoleBox.scrollTop = consoleBox.scrollHeight;
            }
            resolve();
          }, delay);
        });
      };

      (async () => {
        await appendLog('Initializing Airflow Orchestration DAG: nightly_retrain_lgbm...', 'info', 300);
        await appendLog('Executing Task [extract_raw_pos] - Downloading tenant sales CSVs from MinIO Data Lake...', 'info', 600);
        await appendLog('MinIO S3 Client: Connected to bucket /artifacts/stock-sense/public/data/tenant_1/raw_sales/', 'success', 400);
        await appendLog('Fetched new batch file: pos_transactions_today.csv (14,290 lines)', 'info', 300);
        await appendLog('Executing Task [validate_schema] - Running schema suite validations (Great Expectations)...', 'info', 500);
        await appendLog('Schema validation: PASSED. 0 anomalies detected.', 'success', 200);
        await appendLog('Executing Task [convert_parquet] - Compacting CSV into Apache Parquet format...', 'info', 400);
        await appendLog('Silver partition written: tenant_1/sales/year=2026/month=07/data.parquet (Size: 220KB)', 'success', 300);
        await appendLog('Executing Task [feature_engineering] - Rebuilding lag arrays (7d, 14d, 28d) & price index interactions...', 'info', 600);
        await appendLog('Generated 18 static features. Price elasticity index updated for SKU-BMR-005: -1.41', 'success', 200);
        await appendLog('Executing Task [train_lgbm] - Training LightGBM inference model per SKU with Optuna tuning...', 'info', 800);
        await appendLog('Optuna: Finished 50 trials. Best Params: learning_rate=0.08, num_leaves=31', 'success', 400);
        await appendLog('LightGBM Fitting Complete. Loss (WMAPE) on validation split: 3.5%', 'success', 200);
        await appendLog('Executing Task [register_model] - Registering run in MLflow Model Registry...', 'info', 500);
        await appendLog('MLflow registry: Run logged with ID run_89a01x. Model tag: "production-ready".', 'success', 300);
        await appendLog('Executing Task [verify_drift] - Comparing predicted distribution vs actual distribution...', 'info', 500);
        await appendLog('Drift verification: Drift reduced to 2.4%. Drift check PASSED.', 'success', 300);
        await appendLog('Airflow DAG completed successfully. Model deployed to FastAPI predictor microservice.', 'success', 400);
        
        // Update states after training success
        modelDriftScore.value = 2.4;
        wmapeAvg.value = 3.5;
        // Fix active status
        const basmati = skus.value.find(s => s.id === 'basmati_rice');
        if (basmati) {
          basmati.wmape = 3.5;
        }
        isRetraining.value = false;
        
        systemStatus.value = 'ETL Pipeline: Success';
        systemStatusClass.value = 'emerald';
      })();
    };

    onMounted(() => {
      // Delay initialization of charts to let the DOM paint
      setTimeout(renderChart, 100);
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
      triggerRetraining
    };
  }
}).mount('#app');
