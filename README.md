# StockSense AI - Predictive Inventory & Demand Forecasting SaaS

StockSense AI is a predictive inventory software designed for local retailers, regional distributors, and agricultural commodity traders to optimize working capital and minimize stockouts using machine learning forecasting.

---

## 1. High-Level Product Architecture

The system features a hybrid multi-tenant structure split into three primary layers:
- **SaaS Core & Tenant Layer:** Centralized Laravel 12 application handling user billing, vendor access control, notifications, and telemetry views.
- **Inference & Orchestration Layer:** Python FastAPI predictor service running LightGBM models inside Docker containers, triggered by Apache Airflow DAGs.
- **Data & Storage Lake:** PostgreSQL + TimescaleDB for transactional/relational records, Redis for task queue worker systems, and MinIO for tenant-isolated sales file uploads.

---

## 2. Directory Structure

```bash
├── prototype/              # Interactive Client Dashboard prototype (HTML/CSS/Vue)
├── app/                    # Laravel application backend models and logic
├── config/                 # Laravel system configuration files
├── database/               # Database seeders and migrations
│   └── migrations/         # Relational schemas & TimescaleDB hypertables
├── docker-compose.yml      # Local service orchestration configuration
└── README.md               # Main project documentation
```

---

## 3. Development Timeline & Phases (1 &rarr; 2 &rarr; 3 &rarr; 4 &rarr; 5)

```mermaid
graph TD
    Phase1[Phase 1: Interactive Prototype] --> Phase2[Phase 2: SaaS Core & Schemas]
    Phase2 --> Phase3[Phase 3: Multi-Tenancy & FastAPI]
    Phase3 --> Phase4[Phase 4: Laravel-FastAPI HTTP Integration]
    Phase4 --> Phase5[Phase 5: Airflow ETL Ingestion]
    
    style Phase1 fill:#8b5cf6,stroke:#a78bfa,stroke-width:2px,color:#fff
    style Phase2 fill:#10b981,stroke:#34d399,stroke-width:2px,color:#fff
    style Phase3 fill:#3b82f6,stroke:#60a5fa,stroke-width:2px,color:#fff
    style Phase4 fill:#ec4899,stroke:#f472b6,stroke-width:2px,color:#fff
    style Phase5 fill:#14b8a6,stroke:#2dd4bf,stroke-width:2px,color:#fff
```

### Phase 1: Client Dashboard Prototype [Completed]
A high-fidelity frontend representation to showcase predictive charts, alerts, and model observabilities.
* **Location:** [prototype/index.html](file:///d:/xampp/htdocs/StockSense%20AI/prototype/index.html)
* **Features:** Price Elasticity scenario slider, simulated POS csv uploader, WhatsApp alert trigger, and mock Airflow retraining log console.

### Phase 2: SaaS Core & Database Provisioning [Completed]
Setting up the relational database structures and container environments for local dev.
* **Codebase:** Laravel 12 project setup.
* **Local Services:** Configured in [docker-compose.yml](file:///d:/xampp/htdocs/StockSense%20AI/docker-compose.yml) (PostgreSQL + TimescaleDB, Redis, and MinIO S3).
* **Migrations:** 
  - `tenants` & `suppliers` tables.
  - `products` (holding safety stocks, prices, lead times, and ML coefficients).
  - `pos_transactions` (converted into a partitioned **TimescaleDB hypertable** using pgsql driver hooks).

### Phase 3: Laravel Multi-Tenancy & FastAPI Predictor [Completed]
Initializing tenant database scopes and machine learning prediction engines.
* **Laravel Scoping:** Intercepts tenant headers (`X-Tenant-ID`) via [ResolveTenantContext](file:///d:/xampp/htdocs/StockSense%20AI/app/Http/Middleware/ResolveTenantContext.php) middleware and binds the active tenant context. Applies automatic global query filtering (`where tenant_id = X`) via the [BelongsToTenant](file:///d:/xampp/htdocs/StockSense%20AI/app/Models/Traits/BelongsToTenant.php) trait on model queries.
* **Python microservice:** Initialized virtual environment in [ml-service/](file:///d:/xampp/htdocs/StockSense%20AI/ml-service/). Coded [main.py](file:///d:/xampp/htdocs/StockSense%20AI/ml-service/main.py) with schemas and a predictive endpoint `/predict` POST that trains a `lightgbm.LGBMRegressor` on-the-fly and generates recursive forecasts and safety stocks.
* **Unit Tests:** `MultiTenantScopeTest` passing successfully.

### Phase 4: Laravel-FastAPI HTTP Integration [Completed]
Connecting the SaaS backend controllers directly with the ML forecasting microservice.
* **Controllers:** Implemented [ProductForecastController](file:///d:/xampp/htdocs/StockSense%20AI/app/Http/Controllers/ProductForecastController.php) (aggregates sales history and query connectors) and [POSIngestController](file:///d:/xampp/htdocs/StockSense%20AI/app/Http/Controllers/POSIngestController.php) (records transaction streams and evaluates safety limits).
* **API Endpoints:** Set up under [api.php](file:///d:/xampp/htdocs/StockSense%20AI/routes/api.php) (`GET /api/products`, `GET /api/products/{id}/forecast`, `POST /api/products/ingest`).
* **Integration Tests:** `ForecastIntegrationTest` mock HTTP responses validated successfully.

### Phase 5: Data Lake Ingestion & Airflow ETL [Completed]
Nightly data engineering pipelines and model retrain loops.
* **Orchestration:** Implemented [retrain_models_dag.py](file:///d:/xampp/htdocs/StockSense%20AI/airflow/dags/retrain_models_dag.py) using modern task decorator APIs. Coordinates POS extraction from S3, data quality validation checks via Great Expectations, Snappy-compressed Parquet compactions in MinIO, feature engineering (lags & elasticity), LightGBM model fits (Optuna), MLflow registries, and drift check loops.
* **Validation:** Verified via python compiler module with 0 compile errors.

---

## 4. Local Setup Instructions

### Step 1: Open the Frontend Prototype
Launch the prototype dashboard in any browser. Double-click the file or navigate to:
```text
file:///d:/xampp/htdocs/StockSense AI/prototype/index.html
```

### Step 2: Spin Up Infrastructure Services (Docker)
Start Docker Desktop on your system, open your terminal, and run:
```bash
docker compose up -d
```
This runs PostgreSQL on port `5432`, Redis on `6379`, and the MinIO object console on `9001` (user/pass: `minioadmin`).

### Step 3: Run Database Migrations & Tests
Verify Composer dependencies, run migrations, and execute the feature tests:
```bash
# Setup backend dependencies
composer install

# Copy environment template & generate keys (if needed)
copy .env.example .env
php artisan key:generate

# Build database schemas
php artisan migrate

# Run multi-tenancy tests
php artisan test --filter=MultiTenantScopeTest
```

### Step 4: Run the FastAPI Prediction Engine
Start the Python uvicorn server:
```bash
# Navigate to the microservice and start the predictor engine
cd ml-service
.venv\Scripts\python.exe main.py
```
The forecaster will start running at `http://127.0.0.1:8000`. You can query the POST endpoint `/predict` with SKU sales histories to generate live LightGBM models.

---

## 5. Decoupled Cloud Deployment (Render + Neon.tech)

If you are hosting the production or staging instances on Render and Neon:

### Step A: Setup Neon PostgreSQL Database
1. Create a free PostgreSQL database cluster on [Neon.tech](https://neon.tech).
2. Copy the Connection String (URI format).
3. Select your Laravel app environment variables and update:
   ```text
   DB_CONNECTION=pgsql
   DB_HOST=ep-example-1234.us-east-2.aws.neon.tech
   DB_PORT=5432
   DB_DATABASE=neondb
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   # Ensure SSL mode is enabled (required by Neon)
   DB_SSLMODE=require
   ```
*(Note: Since free Neon database clusters do not have the TimescaleDB extension enabled, the migrations will automatically log a warning and fall back to standard PostgreSQL relational tables without crashing).*

### Step B: Deploy FastAPI Predictor to Render
1. Create a new **Web Service** on [Render](https://render.com) connected to your GitHub repository.
2. Set the **Root Directory** to `ml-service`.
3. Set the **Runtime** to `Python 3`.
4. Set the **Build Command** to:
   ```bash
   pip install -r requirements.txt
   ```
5. Set the **Start Command** to:
   ```bash
   uvicorn main:app --host 0.0.0.0 --port $PORT
   ```
6. Render will assign a public URL, e.g., `https://stocksense-ml.onrender.com`.

### Step C: Deploy Laravel SaaS to Render
1. Create another **Web Service on Render** for the root folder of the project.
2. Select the **PHP** or **Docker** runtime.
3. Configure the environment variables to point to your Neon Database host.
4. Set the `FORECAST_SERVICE_URL` variable to point to your Render FastAPI URL:
   ```text
   FORECAST_SERVICE_URL=https://stocksense-ml.onrender.com
   ```
5. Run remote console migrations:
   ```bash
   php artisan migrate --force
   ```