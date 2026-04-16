# Azure SQL Database Setup for Laravel Applications

**Date:** 2026-04-17
**Confidence:** HIGH (official Microsoft Learn + Laravel docs)
**Sources:**
- https://learn.microsoft.com/en-us/azure/azure-sql/database/single-database-create-quickstart
- https://learn.microsoft.com/en-us/azure/azure-sql/database/serverless-tier-overview
- https://learn.microsoft.com/en-us/azure/azure-sql/database/purchasing-models
- https://learn.microsoft.com/en-us/azure/azure-sql/database/connect-query-php
- https://learn.microsoft.com/en-us/azure/azure-sql/database/firewall-configure
- https://learn.microsoft.com/en-us/azure/azure-sql/database/elastic-pool-overview
- https://learn.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac
- https://laravel.com/docs/11.x/database

---

## 1. Azure SQL Database Deployment Models

### Single Database vs Elastic Pool

| Model | Best For | Description |
|-------|----------|-------------|
| **Single Database** | 1–2 apps, dedicated resources | Isolated compute & storage per database. Pay for what you provision. |
| **Elastic Pool** | SaaS/multi-tenant, 3+ databases | Shared pool of resources across multiple databases. Cost-effective when databases have intermittent, unpredictable usage. Databases share eDTUs/vCores and billing is per pool hour. |

### Service Tiers (applies to both models)

| Tier | Use Case | Characteristics |
|------|----------|----------------|
| **General Purpose** | Most workloads, budget-friendly | Balanced compute/storage. Standard-series (Gen5) hardware. Good for dev/test. |
| **Business Critical** | High I/O, low-latency needs | Local SSD storage, ~2.7x the General Purpose price due to 3 HA replicas |
| **Hyperscale** | Very large DBs (100GB–4TB+), auto-scaling | Separate compute and storage; read replicas supported |

---

## 2. Purchasing Models

### vCore Model (Recommended)

- **Provisioned**: Fixed vCores always running. Lower per-vCore price than serverless.
- **Serverless**: Auto-scaling vCores within a configurable range. Auto-pauses (General Purpose only) when idle — only storage billed. Per-second billing. Best for intermittent/dev workloads. Minimum 1 vCore, max varies by tier.
  - **Auto-pause delay**: Configurable (default 1 hour). Pauses when `sessions = 0` AND `CPU = 0`.
  - **Note**: Auto-pause/resume only in General Purpose tier. Business Critical and Hyperscale cannot auto-pause.

### DTU Model (Legacy/Simple)

- Bundled compute + storage packages (Basic/Standard/Premium).
- Simpler but less flexible than vCore.
- Microsoft recommends vCore for new workloads.

### Recommendations by Environment

| Environment | Recommendation | Reason |
|-------------|---------------|--------|
| **Dev/Test** | General Purpose + Serverless (vCore) | Auto-pause saves cost; low compute is sufficient |
| **Production (small)** | General Purpose + Provisioned (vCore) | Predictable cost, consistent performance |
| **Production (high I/O)** | Business Critical + Provisioned (vCore) | Low latency, local SSD, HA replicas |
| **Production (large scale)** | Hyperscale (vCore) | Auto-scaling storage, up to 4TB+ |

---

## 3. Creating an Azure SQL Database (2024–2025)

### Azure Portal (Recommended for Quick Start)

1. Go to [Azure SQL hub](https://aka.ms/azuresqlhub) → **SQL databases** → **+ Create**.
2. **Subscription** → **Resource group** → **Database name**.
3. **Server**: Create new → name (globally unique, e.g., `myapp-server`), location, auth method (SQL auth), admin login/password.
4. **Want to use SQL elastic pool**: No (single) or Yes (pool).
5. **Workload environment**: Development vs Production (sets defaults).
6. **Compute + storage**: Select service tier, compute tier (Provisioned/Serverless), hardware (Standard-series Gen5 default).
7. **Networking**: Public endpoint → Add current client IP = Yes → Allow Azure services = No (or Yes for App Service).
8. **Additional settings**: Sample data (AdventureWorksLT) optional.
9. **Review + create**.

### Azure CLI (Cross-Platform)

```bash
# Variables
subscription="<subscription-id>"
resourceGroup="rg-myapp"
location="eastus"
server="myapp-server-$RANDOM"   # globally unique
database="myappdb"
login="azureuser"
password="Pa$$w0rD-ChangeMe!"

az account set -s $subscription

# 1. Create resource group
az group create --name $resourceGroup --location "$location"

# 2. Create logical server
az sql server create \
    --name $server \
    --resource-group $resourceGroup \
    --location "$location" \
    --admin-user $login \
    --admin-password $password

# 3. Create server-level firewall (your IP)
az sql server firewall-rule create \
    --resource-group $resourceGroup \
    --server $server \
    -n AllowMyIp \
    --start-ip-address <YOUR_IP> \
    --end-ip-address <YOUR_IP>

# 4. Allow Azure services (for App Service, VMs in Azure)
az sql server firewall-rule create \
    --resource-group $resourceGroup \
    --server $server \
    -n AllowAzureServices \
    --start-ip-address 0.0.0.0 \
    --end-ip-address 0.0.0.0

# 5. Create single database (Serverless, General Purpose, 2 vCores)
az sql db create \
    --resource-group $resourceGroup \
    --server $server \
    --name $database \
    --edition GeneralPurpose \
    --compute-model Serverless \
    --family Gen5 \
    --capacity 2 \
    --max-size 50GB
```

### Azure CLI — Elastic Pool

```bash
# After creating server and firewall rules above:
# 1. Create elastic pool
az sql elastic-pool create \
    --resource-group $resourceGroup \
    --server $server \
    --name my-elastic-pool \
    --edition GeneralPurpose \
    --dtu 100 \
    --database-max-size 50GB

# 2. Create databases inside the pool
az sql db create \
    --resource-group $resourceGroup \
    --server $server \
    --name appdb1 \
    --elastic-pool my-elastic-pool

az sql db create \
    --resource-group $resourceGroup \
    --server $server \
    --name appdb2 \
    --elastic-pool my-elastic-pool
```

---

## 4. Firewall Rules

Azure SQL Database blocks ALL external connectivity by default. Two types:

### Server-Level IP Firewall Rules (Recommended for most cases)

- Stored in `master` database.
- Max 256 rules per server.
- Created via: Azure Portal, Azure CLI, PowerShell, or T-SQL.

**Via Azure CLI:**
```bash
# Single IP
az sql server firewall-rule create \
    --resource-group $resourceGroup --server $server \
    -n AllowMyIP --start-ip-address 203.0.113.50 --end-ip-address 203.0.113.50

# Allow all Azure services (for Azure-hosted apps)
az sql server firewall-rule create \
    --resource-group $resourceGroup --server $server \
    -n AllowAzureServices --start-ip-address 0.0.0.0 --end-ip-address 0.0.0.0
```

**Via T-SQL:**
```sql
-- Create
EXECUTE sp_set_firewall_rule @name = N'AllowMyIP',
    @start_ip_address = '203.0.113.50', @end_ip_address = '203.0.113.50';

-- View
SELECT * FROM sys.firewall_rules;

-- Delete
EXECUTE sp_delete_firewall_rule @name = N'AllowMyIP';
```

**Via Azure Portal:**
Server Overview → **Networking** → **Firewall rules** → Add your IP.

### Important Notes

- Port **1433** must be open outbound on your local network for TCP connections.
- `AllowAllWindowsAzureIps` (0.0.0.0–0.0.0.0) enables ALL Azure services to connect — use only for App Service, Azure VMs, etc.
- For production, prefer **private endpoints** or **virtual network service endpoints** over public endpoint + firewall.
- IP rules are cached at the database level. Refresh with `DBCC FLUSHAUTHCACHE`.

---

## 5. Connection String Format

Azure SQL Database uses the standard SQL Server connection string format over **TCP** on port **1433**, with **TLS/SSL enforced by default**.

### Standard Connection String (ADO.NET / SQL Server Authentication)

```
Server=tcp:myapp-server12345.database.windows.net,1433;
Database=myappdb;
User Id=azureuser@myapp-server12345;
Password=Pa$$w0rD-ChangeMe!;
Encrypt=true;
TrustServerCertificate=false;
Connection Timeout=30;
```

### Alternative Formats

**With `sqlcmd`:**
```
sqlcmd -S myapp-server12345.database.windows.net -d myappdb -U azureuser -P Pa$$w0rD-ChangeMe! -E -C
```

**PHP (sqlsrv driver):**
```php
<?php
$serverName = "myapp-server12345.database.windows.net";
$connectionOptions = array(
    "Database" => "myappdb",
    "Uid" => "azureuser@myapp-server12345",  // format: username@servername
    "PWD" => "Pa$$w0rD-ChangeMe!",
    "Encrypt" => true,
    "TrustServerCertificate" => false,
    "ConnectionTimeout" => 30,
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
?>
```

**Laravel `.env`:**
```
DB_CONNECTION=sqlsrv
DB_HOST=myapp-server12345.database.windows.net
DB_PORT=1433
DB_DATABASE=myappdb
DB_USERNAME=azureuser@myapp-server12345
DB_PASSWORD=Pa$$w0rD-ChangeMe!
```

### Connection String Options Explained

| Parameter | Value | Purpose |
|-----------|-------|---------|
| `Encrypt` | `true` | Required. Encrypts channel (Azure SQL enforces TLS 1.2+) |
| `TrustServerCertificate` | `false` | Validate Azure's certificate (recommended for production) |
| `Connection Timeout` | `30` | Seconds to wait before timeout |
| `User Id` format | `user@servername` | Azure SQL logical server requires `@servername` suffix |
| `Authentication` | SQL Auth (default) or Microsoft Entra ID | Prefer Entra ID for production |

---

## 6. Laravel Configuration

### Required PHP Extensions

Laravel 11.x requires **SQL Server 2017+** support. The PDO SQLSRV driver must be installed:

```bash
# Linux (Ubuntu/Debian)
sudo apt-get install unixodbc-dev
sudo pecl install sqlsrv pdo_sqlsrv
sudo phpenmod sqlsrv pdo_sqlsrv

# macOS
brew install unixodbc
sudo pecl install sqlsrv pdo_sqlsrv

# Verify
php -m | grep -E "pdo_sqlsrv|sqlsrv"
```

### composer.json Dependencies

```bash
# Required for SQL Server migrations with schema manipulation
composer require doctrine/dbal

# The sqlsrv PDO driver (Windows typically ships this with PHP)
# On Linux/macOS, install via PECL above
```

### config/database.php

```php
'sqlsrv' => [
    'driver' => 'sqlsrv',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', '1433'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',          // Azure SQL supports utf8mb4
    'collation' => 'SQL_Latin1_General_CP1_CI_AS',  // Default SQL Server collation
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'dbo',          // Default schema
    'trust_server_certificate' => false,
],
```

### .env Configuration

```env
# Azure SQL Database
DB_CONNECTION=sqlsrv
DB_HOST=myapp-server12345.database.windows.net
DB_PORT=1433
DB_DATABASE=myappdb
DB_USERNAME=azureuser@myapp-server12345
DB_PASSWORD=Pa$$w0rD-ChangeMe!

# Alternative: DATABASE_URL format
DATABASE_URL="sqlsrv://azureuser@myapp-server12345:Pa$$w0rD-ChangeMe!@myapp-server12345.database.windows.net:1433/myappdb"

# DO NOT set DB_CONNECTION=sqlite or DB_CONNECTION=pgsql
```

### Trust Server Certificate (Development Only)

If you get TLS errors in local dev with self-signed certs:
```php
'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', false),
```
```env
DB_TRUST_SERVER_CERTIFICATE=true  # DEV ONLY — NEVER in production
```

---

## 7. Migration from SQLite/PostgreSQL to Azure SQL

### Export Data

**From SQLite:**
```bash
# Option 1: Use sqlite3 CLI + manual conversion
sqlite3 database.sqlite ".dump" > dump.sql

# Option 2: Use Laravel's built-in export + Azure Data Studio migration wizard
# Azure Data Studio (free) has built-in migration from SQLite/PostgreSQL to Azure SQL
```

**From PostgreSQL:**
```bash
# Use Azure Data Studio or pg_dump + transformation
pg_dump -h localhost -U postgres -d mydb -f dump.sql

# Then use Azure Data Studio Migration extension to move to Azure SQL
```

### SQL Server–Specific Migration Differences

#### 1. Auto-Increment → IDENTITY

SQL Server uses `IDENTITY` instead of `auto_increment`:

```php
// Laravel migration — this works on all DBs including SQL Server
$table->id();              // Creates IDENTITY(1,1) on SQL Server
$table->bigId();           // Creates BIGINT IDENTITY

// Explicit IDENTITY (Laravel handles this automatically)
$table->bigIncrements('id');  // BIGINT IDENTITY
```

#### 2. String Lengths

SQL Server `nvarchar(max)` is used for `longText` / unlimited strings. Laravel's `string('column', 255)` creates `nvarchar(255)`.

```php
// This works the same across all databases:
$table->string('name', 255);     // nvarchar(255)
$table->text('description');      // nvarchar(max)
$table->json('metadata');         // nvarchar(max) in SQL Server
```

#### 3. Foreign Key Constraints

SQL Server has **trusted** and **untrusted** foreign key states. Azure SQL defaults to untrusted for new constraints. Laravel handles this:

```php
// Standard Laravel foreign key — works on Azure SQL
$table->foreignId('user_id')->constrained('users')->onDelete('cascade');
```

> **Important**: If you see FK constraint errors during migration, check that the FK constraint state is trusted:
> ```sql
> -- Check constraint state
> SELECT name, is_disabled, is_not_trusted
> FROM sys.foreign_keys;
>
> -- If untrusted, enable with check:
> ALTER TABLE child_table WITH CHECK CHECK CONSTRAINT fk_name;
> ```

#### 4. Schema Builder Peculiarities

`doctrine/dbal` is required for column modification migrations on SQL Server:
```bash
composer require doctrine/dbal
```

SQL Server does **not** support `enum`. Use `string` or a lookup table:
```php
// Instead of $table->enum('status', ['active','inactive']);
$table->string('status', 20);  // Use CHECK constraint if needed
```

#### 5. Index Naming

SQL Server has a 128-character identifier limit. Laravel 10+ automatically truncates index names. If on older Laravel:
```php
// Use shorter names to stay within SQL Server's 128-char limit
$table->index('email', 'idx_email');  // Instead of 'users_email_index'
```

#### 6. Collation

Azure SQL default: `SQL_Latin1_General_CP1_CI_AS`. For case-insensitive string comparisons:
```php
// In config/database.php
'collation' => 'SQL_Latin1_General_CP1_CI_AS',
```

#### 7. Running Migrations

```bash
# Standard
php artisan migrate

# Fresh migration (CAREFUL — drops all tables)
php artisan migrate:fresh

# Seed
php artisan db:seed

# Rollback one step
php artisan migrate:rollback
```

**Known issue**: If migrations fail with `There is no SQL Server specified`, ensure `DB_CONNECTION=sqlsrv` is set in `.env`.

#### 8. BACPAC Import (Alternative Migration Method)

For larger datasets, export as BACPAC from source, then import to Azure SQL:

```bash
# Export from PostgreSQL/SQLite using Azure Data Studio or SSMS
# Then import to Azure:
az sql db import \
    --resource-group $resourceGroup \
    --server $server \
    --name $database \
    --admin-user $login \
    --admin-password $password \
    --storage-uri "https://mystorageaccount.blob.core.windows.net/bacpacs/mydb.bacpac" \
    --storage-key "your-storage-key"
```

---

## 8. Azure SQL TLS/SSL Requirements

Azure SQL Database **enforces TLS 1.2+** for all connections. This is non-configurable at the server level for new servers (2023+).

### Requirements for PHP/sqlsrv

- The `sqlsrv` or `pdo_sqlsrv` driver automatically negotiates TLS.
- Set `Encrypt=true` and `TrustServerCertificate=false` in connection options (production).
- For local dev with self-signed certs: `TrustServerCertificate=true` only in `.env`.

### Check TLS Version

```sql
-- In Azure SQL
SELECT session_id, client_net_address, connect_time, protocol_version
FROM sys.dm_exec_connections
WHERE session_id = @@SPID;
```

### For Azure App Service (PHP 8.2+)

Azure App Service has sqlsrv enabled by default. No extra config needed beyond `.env`.

---

## 9. Performance: DTU vs vCore Recommendations

### Quick Decision Guide

| Scenario | Model | Tier | Why |
|----------|-------|------|-----|
| Small app, dev/test | vCore | Serverless, General Purpose | Auto-pause saves money |
| Production, <100GB, steady load | vCore | Provisioned, General Purpose | Predictable cost |
| Production, high I/O, <100GB | vCore | Provisioned, Business Critical | SSD, low latency |
| Multi-tenant SaaS | vCore | Provisioned, Elastic Pool | Share resources, scale |
| Very large DB (100GB+) | vCore | Hyperscale | Auto-scaling storage |

### vCore Sizing (General Purpose Provisioned)

| Database Size | Min vCores | Max vCores | Storage |
|--------------|-----------|-----------|---------|
| <1 GB | 1 | 2 | 5 GB |
| 1–10 GB | 2 | 4 | 50 GB |
| 10–50 GB | 4 | 8 | 200 GB |
| 50–100 GB | 8 | 16 | 500 GB |

### DTU Tiers (Legacy)

| Tier | DTUs | Best For |
|------|------|----------|
| Basic | 5 | Tiny dev/test, <2 GB |
| Standard (S0–S12) | 10–100 | Web/business apps, 2 GB–250 GB |
| Premium (P1–P80) | 125–4000 | Mission-critical, high I/O |

---

## 10. Complete .env.example for Azure SQL

```env
APP_NAME=BOB-AGS
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug

# ====== AZURE SQL DATABASE ======
DB_CONNECTION=sqlsrv
DB_HOST=your-server.database.windows.net
DB_PORT=1433
DB_DATABASE=your_database
DB_USERNAME=your_username@your_server
DB_PASSWORD=your_password

# DO NOT use these for production Azure SQL:
# DB_CONNECTION=sqlite
# DB_CONNECTION=pgsql

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

# TLS — set to true ONLY for local dev with self-signed certs
# DB_TRUST_SERVER_CERTIFICATE=false
```

---

## 11. Quick Reference: Azure SQL vs Other Databases

| Feature | SQLite | PostgreSQL | Azure SQL |
|---------|--------|-----------|-----------|
| Auto-increment | `AUTOINCREMENT` | `SERIAL` / `GENERATED` | `IDENTITY(1,1)` |
| Laravel `id()` type | `INTEGER` PK | `BIGSERIAL` | `BIGINT` IDENTITY |
| Max string | Unlimited (file) | Unlimited | `NVARCHAR(max)` = 2GB |
| Enum type | Yes | Yes | No — use lookup table |
| Array type | No | Yes | No |
| JSONB | No | Yes (binary) | Yes (text, indexed with functions) |
| FK constraints | Off by default | On by default | Trusted/Untrusted states |
| Collation | UTF-8 default | UTF-8 default | `SQL_Latin1_General_CP1_CI_AS` default |
| Index name limit | None | None | 128 characters |
| Connection | File | TCP 5432 | TCP 1433 |
| Encryption | None (file-level) | SSL optional | TLS 1.2+ enforced |

---

## Summary Checklist

- [ ] Create Azure subscription + resource group
- [ ] Create logical SQL Server (globally unique name)
- [ ] Create firewall rule for your IP
- [ ] Create firewall rule `0.0.0.0–0.0.0.0` if using Azure App Service
- [ ] Create single database or elastic pool
- [ ] Install `sqlsrv` / `pdo_sqlsrv` PHP extension
- [ ] `composer require doctrine/dbal`
- [ ] Set `DB_CONNECTION=sqlsrv` in `.env`
- [ ] Format `DB_USERNAME` as `user@servername`
- [ ] Set `Encrypt=true` in connection options
- [ ] Run `php artisan migrate`
- [ ] Update CI/CD `.env` with Azure SQL credentials
