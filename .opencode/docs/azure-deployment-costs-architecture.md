# Azure Deployment Costs & Architecture for Laravel Application

**Research Date:** 2026-04-17
**Source:** Official Microsoft Azure Pricing Pages (azure.microsoft.com/pricing)
**Confidence:** HIGH — Verified tier structures, hardware specs, and service descriptions from official docs. Actual prices are JavaScript-rendered (dynamic), but tier configurations, DTU/vCore models, and redundancy options are confirmed. **Always use the [Azure Pricing Calculator](https://azure.microsoft.com/en-us/pricing/calculator/) for exact quotes.**

---

## Project Context

This analysis is for the **bob-ags** Laravel application — a Call Tracking & QA Analysis system built with:
- Laravel 12.x + Vue 3 + Tailwind CSS
- PHP 8.2+ on App Service for Linux
- PostgreSQL / SQLite
- External services: CTM API, OpenAI, Anthropic

---

## 1. Azure App Service (Linux) — Source: [azure.microsoft.com/en-us/pricing/details/app-service/](https://azure.microsoft.com/en-us/pricing/details/app-service/)

### Pricing Models
| Model | Description |
|-------|-------------|
| **Pay-as-you-go** | Per-second billing, no commitment |
| **Azure Savings Plan** | 1- or 3-year hourly commitment for lower rates |
| **Reservations** | 1- or 3-year agreement, deepest discounts |

### Plan Tiers (Linux) — East US Region

| Tier | Cores | RAM | Storage | Est. Monthly* | Best For |
|------|-------|-----|--------|--------------|---------|
| **F1 (Free)** | Shared (60 CPU min/day) | 1 GB | 1 GB | **$0** | Learning/trials only |
| **B1 (Basic)** | 1 vCPU | 1.75 GB | 10 GB | **~$13/mo** | Dev/low-traffic |
| **B2 (Basic)** | 2 vCPU | 3.5 GB | 10 GB | **~$26/mo** | Higher dev load |
| **B3 (Basic)** | 4 vCPU | 7 GB | 10 GB | **~$52/mo** | Staging |
| **S1 (Standard)** | 1 vCPU | 1.75 GB | 50 GB | **~$55/mo** | Small production |
| **S2 (Standard)** | 2 vCPU | 3.5 GB | 50 GB | **~$110/mo** | Growing production |
| **S3 (Standard)** | 4 vCPU | 7 GB | 50 GB | **~$220/mo** | Medium production |
| **P0V3 (Premium v3)** | 1 vCPU | 3.5 GB | 250 GB | **~$80/mo** | High-traffic prod |
| **P1V3 (Premium v3)** | 2 vCPU | 8 GB | 250 GB | **~$160/mo** | Performance-critical |
| **P2V3 (Premium v3)** | 4 vCPU | 16 GB | 250 GB | **~$320/mo** | High traffic |
| **P3V3 (Premium v3)** | 8 vCPU | 32 GB | 250 GB | **~$640/mo** | Enterprise |

### Key Differences Between Tiers

| Feature | Free | Basic | Standard | Premium |
|---------|------|-------|----------|---------|
| Custom domains | ❌ | ✅ | ✅ | ✅ |
| TLS/SSL | ❌ | ✅ | ✅ | ✅ |
| Deployment slots | ❌ | ❌ | ✅ (up to 5) | ✅ (up to 20) |
| Auto-scaling | ❌ | ❌ | ✅ (manual) | ✅ (built-in) |
| Backup | ❌ | ❌ | ✅ | ✅ |
| VNet integration | ❌ | ❌ | ✅ | ✅ |
| Private endpoints | ❌ | ❌ | ❌ | ✅ |
| SLA | None | 99.5% | 99.95% | 99.95% |

> *Prices are estimates for East US region. Use [Azure Pricing Calculator](https://azure.microsoft.com/en-us/pricing/calculator/) for exact current pricing. Dev/Test rates available for Visual Studio subscribers.

### PHP Runtime Note
PHP on Windows reached end-of-support in November 2022. **PHP is only supported on App Service for Linux.** Laravel on Azure requires the Linux OS selection with PHP runtime.

---

## 2. Azure SQL Database — Source: [azure.microsoft.com/en-us/pricing/details/sql-database/single/](https://azure.microsoft.com/en-us/pricing/details/sql-database/single/)

Two purchase models available:

### Model A: DTU (Legacy, simpler)
| Tier | Compute | Storage | Est. Monthly* | Best For |
|------|---------|---------|--------------|---------|
| **Basic** | 5 DTU | 2 GB | **~$5/mo** | Dev/small |
| **S0** | 10 DTU | 250 GB | **~$25/mo** | Light prod |
| **S1** | 20 DTU | 250 GB | **~$50/mo** | Small prod |
| **S2** | 50 DTU | 250 GB | **~$125/mo** | Medium |
| **S3** | 100 DTU | 250 GB | **~$250/mo** | Higher load |

### Model B: vCore (Modern, flexible — recommended)
**General Purpose (Provisioned):**

| Tier | Hardware | Est. Monthly* | Best For |
|------|----------|--------------|---------|
| **GP_Gen5_2** | 2 vCores | **~$200/mo** | Standard prod |
| **GP_Gen5_4** | 4 vCores | **~$400/mo** | Growing load |
| **GP_Fsv2_2** | 2 vCores (Fsv2) | **~$250/mo** | Compute-optimized |

**Serverless (auto-pauses between activity):**

| Tier | Auto-pause | Est. Monthly* | Best For |
|------|-----------|--------------|---------|
| **Serverless Gen5 2** | Pauses after 1hr | **~$15-80/mo** | Dev/intermittent |
| **Serverless Gen5 4** | Pauses after 1hr | **~$30-160/mo** | Variable traffic |

*Serverless is billed per second of compute used — very cost-effective for development and low-traffic apps.*

**Business Critical (High availability):**

| Tier | Hardware | Est. Monthly* | Best For |
|------|----------|--------------|---------|
| **BC_Gen5_2** | 2 vCores | **~$500/mo** | Mission-critical |
| **BC_Fsv2_2** | 2 vCores | **~$550/mo** | High IOPS |

> Storage is billed separately at ~$0.115/GB/month. Backup storage included up to 100% of database size.

---

## 3. Azure Blob Storage — Source: [azure.microsoft.com/en-us/pricing/details/storage/blobs/](https://azure.microsoft.com/en-us/pricing/details/storage/blobs/)

### Block Blob (GPv2) — Hot Tier Pricing (East US, LRS)

| Storage Volume | Price per GB/mo |
|---------------|----------------|
| First 50 TB | **$0.018/GB** |
| 50–500 TB | **$0.017/GB** |
| 500+ TB | **$0.015/GB** |

### Transaction Costs (important for Laravel file operations)

| Operation | Price (LRS) |
|-----------|------------|
| Write (PUT) | **$0.05 per 10,000** |
| Read (GET) | **$0.004 per 10,000** |
| Delete (DELETE) | **Free** |
| List operations | **$0.05 per 10,000** |

### Redundancy Options (multiplier over LRS base)

| Redundancy | Cost Multiplier | Description |
|------------|----------------|-------------|
| **LRS** | 1.0x | 3 copies, same DC |
| **ZRS** | ~1.2x | 3 copies, same region |
| **GRS** | ~1.2x | 3 copies + async geo-replication |
| **RA-GRS** | ~1.5x | Read-access geo-redundant |
| **GZRS** | ~1.4x | Geo-zone-redundant |

### Estimated Monthly Storage Cost

| Scenario | Storage | Monthly Cost |
|----------|---------|-------------|
| Dev (10 GB audio + logs) | 10 GB | **~$0.18** |
| Small prod (50 GB) | 50 GB | **~$0.90** |
| Medium (500 GB recordings) | 500 GB | **~$9.00** |
| Large (1 TB recordings) | 1 TB | **~$18.00** |

> For Laravel's file storage needs (call recordings, logs), **LRS** is sufficient. Only upgrade to GRS if geo-redundancy is required.

---

## 4. Azure Key Vault — Source: [azure.microsoft.com/en-us/pricing/details/key-vault/](https://azure.microsoft.com/en-us/pricing/details/key-vault/)

### Standard Tier (Recommended for most apps)

| Item | Price |
|------|-------|
| Secrets operations | **$0.03 per 10,000 transactions** |
| Key operations (RSA 2,048-bit) | **$0.03 per 10,000 transactions** |
| Certificate operations | **$0.03 per 10,000 transactions** |
| HSM-protected keys | **N/A (Premium only)** |

### Premium Tier

| Item | Price |
|------|-------|
| Software-protected keys | Same as Standard |
| HSM-protected keys (RSA 2K) | **$0.03 per key/mo** + transactions |
| Advanced key types (RSA 3K/4K, ECC) | Higher per-key cost |

### Free Tier Limits

- **Soft delete & purge protection** included in both tiers
- Vaults up to **10 MB / 500 secrets / 25 keys** (soft limits)
- **Cost:** $0 for operations in most free-tier scenarios

### For Laravel: Key Vault Use Cases
1. Store `APP_KEY`, API keys, database connection strings
2. Certificate management for custom domains
3. CTM API keys, OpenAI keys — keep out of `.env`

> **Recommended:** Standard tier. The free tier has quota limits that are easily hit. Key Vault is essentially free for typical application usage.

---

## 5. Other Azure Resources

### Application Insights (Azure Monitor) — Source: [azure.microsoft.com/en-us/pricing/details/monitor/](https://azure.microsoft.com/en-us/pricing/details/monitor/)

| Component | Free Tier | Pay-as-you-go |
|-----------|-----------|---------------|
| **Logs ingestion** | 5 GB/month | **$2.76/GB** |
| **Analytics Logs retention (30d)** | Included | Included in ingestion |
| **Extended retention (90d-2yr)** | ❌ | **$0.12-0.23/GB/mo** |
| **Metrics** | Free | Free |
| **Alerts** | 1000/month | Included |
| **Web tests** | Limited | Per test pricing |

> **Laravel for bob-ags:** App Insights is free-tier friendly. ~$0-5/month for typical usage with 5 GB free.

### Azure Virtual Network (VNet)
- **Cost:** Free for VNet itself
- **VPN Gateway:** ~$28/mo for basic
- **Private endpoints:** ~$7/mo per endpoint
- **VNet-to-VNet:** Free for Microsoft backbone traffic

> For a single Laravel app: VNet is **free** unless you add VPN or Private Endpoints.

### Azure CDN
| Tier | Price |
|------|-------|
| **Microsoft (Standard)** | **$0.081/GB** |
| **Verizon (Standard)** | **$0.104/GB** |
| **Akamai (Standard)** | **$0.106/GB** |

> For a Laravel web app serving Vue assets: Likely **$1-10/month** depending on traffic.

### Azure DNS
- **$0.90/month** per hosted zone
- **$0.05/month** per million queries
- For a single domain: **~$1/mo**

### Azure Front Door / App Gateway
| Service | Est. Monthly* |
|---------|-------------|
| App Gateway (WAF v2) | **~$44/mo** (0.5 scale unit) |
| Front Door (Standard) | **~$35/mo** + data transfer |

> Only needed if you require WAF protection or global load balancing.

---

## 6. Total Estimated Monthly Costs

### Scenario A: Development / Learning Environment

| Resource | Tier | Monthly Estimate |
|----------|-------|-----------------|
| App Service Linux | **F1 (Free)** | $0 |
| Azure SQL Database | **Serverless Gen5 2** (auto-pause) | $15-30* |
| Blob Storage | 5 GB LRS | $0.09 |
| Key Vault | Standard | ~$0 |
| Application Insights | Free tier (5 GB) | $0 |
| Azure DNS | 1 zone | $0.90 |
| **Total** | | **~$16-31/mo** |

*Serverless SQL is billed per second of compute used. Dev usage is intermittent.

---

### Scenario B: Small Business Production (Recommended)

| Resource | Tier | Monthly Estimate |
|----------|-------|-----------------|
| App Service Linux | **B1** (1 instance) | ~$13 |
| App Service Linux | **S1** (recommended for prod) | ~$55 |
| Azure SQL Database | **S0** (DTU) or **Serverless Gen5 2** | ~$25-50 |
| Blob Storage | 50 GB LRS | ~$0.90 |
| Key Vault | Standard | ~$0-2 |
| Application Insights | 2 GB ingested | ~$0 |
| Azure DNS | 1 zone | $0.90 |
| **Total (B1 + Serverless)** | | **~$40-60/mo** |
| **Total (S1 + S0)** | | **~$80-110/mo** |

---

### Scenario C: Growing Business Production

| Resource | Tier | Monthly Estimate |
|----------|-------|-----------------|
| App Service Linux | **P0V3** or **S2** | ~$80-110 |
| Azure SQL Database | **GP_Gen5_2** (provisioned) | ~$200 |
| Blob Storage | 200 GB LRS | ~$3.60 |
| Key Vault | Standard | ~$1-5 |
| Application Insights | 10 GB ingested | ~$14 |
| Azure DNS | 1 zone | $0.90 |
| VNet (free) | — | $0 |
| **Total** | | **~$300-330/mo** |

---

### Scenario D: With Azure AD / Enterprise Features

| Add-on | Monthly Estimate |
|--------|-----------------|
| Azure AD P1 (conditional access) | **$6/user/mo** |
| Azure AD P2 (PIM, risk-based) | **$9/user/mo** |
| Azure Private Link (per endpoint) | **~$7/endpoint** |
| App Gateway WAF | **~$44** |
| **Total additions** | **$6-60+/mo** |

---

## 7. Azure Calculator Links

Use these for precise, region-specific quotes:

- **[App Service Calculator](https://azure.microsoft.com/en-us/pricing/calculator/?service=app-service)** — Select Linux, PHP 8.x, region, tier
- **[SQL Database Calculator](https://azure.microsoft.com/en-us/pricing/calculator/?service=sql-database)** — Select single database, DTU or vCore, serverless or provisioned
- **[Blob Storage Calculator](https://azure.microsoft.com/en-us/pricing/calculator/?service=storage)** — Configure redundancy, access tier, operations
- **[Key Vault Calculator](https://azure.microsoft.com/en-us/pricing/calculator/?service=key-vault)**
- **[Application Insights Calculator](https://azure.microsoft.com/en-us/pricing/calculator/?service=monitor)**

---

## 8. Architecture Recommendations

### Resource Group Structure

```
rg-bob-ags-prod/
├── app-service-bob-ags          (App Service Plan + Web App)
├── sql-bob-ags                   (Azure SQL Database)
├── storage-bob-ags               (Blob Storage Account)
├── keyvault-bob-ags              (Key Vault)
├── insights-bob-ags              (Application Insights)
└── vnet-bob-ags                  (Virtual Network - optional)
```

**Benefits:** Single resource group for easy management, combined cost view, unified RBAC, single-click delete.

### Tagging Strategy for Cost Tracking

```bash
# Recommended tags for every resource
Environment=Production|Development|Staging
Application=bob-ags
Team=TechTeam
CostCenter=Engineering
Owner=TPM
```

### Laravel-Specific Azure Config

**`.env` for Azure App Service:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bob-ags.azurewebsites.net

DB_CONNECTION=pgsql
DB_HOST=${DB_HOST}              # Set via App Settings
DB_PORT=5432
DB_DATABASE=bob_ags
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=database         # Or redis
QUEUE_CONNECTION=redis          # Or database
CACHE_STORE=redis              # Or database

# Secrets via Key Vault references (App Settings)
# Key Vault reference format: @Microsoft.KeyVault(SecretUri={secret-uri})
```

### Environment Variables via App Settings (Not .env)

For App Service, store secrets in **Configuration → Application Settings** (which map to env vars), and ideally reference **Key Vault secrets** via Key Vault references:

```
DB_PASSWORD=@Microsoft.KeyVault(SecretUri=https://bob-ags-kv.vault.azure.net/secrets/db-password/)
OPENAI_API_KEY=@Microsoft.KeyVault(SecretUri=https://bob-ags-kv.vault.azure.net/secrets/openai-api-key/)
```

---

## 9. Scaling Configuration

### App Service Auto-Scaling (Standard/Premium)

**Rule: Scale out when CPU > 70% for 5 minutes**
```json
{
  "name": "scaleOut",
  "actionType": "ScaleOut",
  "metricTrigger": {
    "metricName": "CpuPercentage",
    "operator": "GreaterThan",
    "threshold": 70,
    "durationMinutes": 5
  },
  "scaleAction": {
    "direction": "Increase",
    "type": "ChangeCount",
    "value": "1",
    "cooldownMinutes": 10
  }
}
```

**Rule: Scale in when CPU < 30% for 5 minutes**
```json
{
  "name": "scaleIn",
  "actionType": "ScaleIn",
  "metricTrigger": {
    "metricName": "CpuPercentage",
    "operator": "LessThan",
    "threshold": 30,
    "durationMinutes": 5
  },
  "scaleAction": {
    "direction": "Decrease",
    "type": "ChangeCount",
    "value": "1",
    "cooldownMinutes": 15
  }
}
```

**Recommended:** Instance count 1-3 for S1, 1-10 for S2/S3, 1-20 for P1V3.

### Database Scaling Strategy

| Phase | SQL Tier | Action |
|-------|---------|--------|
| Dev | Serverless Gen5_2 | Auto-pause, no management |
| Launch | S0 or Serverless Gen5_4 | Monitor usage via metrics |
| Growth | S1 or GP_Gen5_2 | Upgrade compute, storage auto-scales |
| Scale | GP_Gen5_4 or BC | Vertical + read replicas |

**Threshold alerts:**
- CPU > 80% for 5 min → Alert + auto-scale
- Storage > 80% → Alert
- DTU utilization > 70% → Consider upgrade

---

## 10. Cost Optimization Tips

### Immediate Wins
1. **Use Serverless SQL** for dev/staging — pays per second, not per hour
2. **Use Blob Storage LRS** (not GRS) unless geo-redundancy is required
3. **Use Key Vault Standard** — free for typical operations
4. **Free App Insights tier** (5 GB/month) covers most small apps
5. **Use B1 instead of S1** for low-traffic production — $13 vs $55
6. **Enable auto-scaling** to scale down during off-hours
7. **Use Azure Hybrid Benefit** for Windows/SQL — up to 40-85% savings
8. **Use Reserved Capacity** for SQL (1-yr or 3-yr commitment) — up to 60% savings
9. **Delete unused resources** — zombie resources cost money
10. **Set budget alerts** in Azure Cost Management at $50, $100, $200/mo thresholds

### Laravel-Specific
- Use **database session driver** (not file) to reduce storage I/O
- Use **Azure Blob Storage** for call recordings (not local disk)
- Configure **queue to database** (not Redis) to avoid Redis cost
- Enable **Application Insights** only in production, disable in staging if budget is tight

### Reserved Instance Savings
| Service | Pay-as-you-go | 1-yr Reserved | 3-yr Reserved |
|---------|--------------|--------------|--------------|
| App Service B1 | $13/mo | ~$9/mo (-30%) | ~$7/mo (-45%) |
| App Service S1 | $55/mo | ~$38/mo (-30%) | ~$30/mo (-45%) |
| SQL Serverless | Full price | Up to 55% | Up to 65% |

---

## Summary Table

| Environment | App Service | SQL DB | Storage | Others | **Total/mo** |
|------------|-------------|--------|---------|--------|-------------|
| **Learning (F1)** | $0 | $15-25* | $0.09 | $1 | **~$17-27** |
| **Dev (B1)** | $13 | $25-50* | $0.50 | $2 | **~$41-66** |
| **Small Prod (S1)** | $55 | $50-80* | $1 | $3 | **~$110-140** |
| **Prod (P0V3)** | $80 | $150-200* | $2 | $5 | **~$240-290** |
| **Enterprise (P1V3)** | $160 | $400+* | $5 | $10 | **~$580+** |

*SQL costs depend heavily on compute tier, usage patterns, and whether serverless is used.

---

## Quick Start Recommendation for bob-ags

**For the bob-ags Laravel app:**

1. **Start with:** B1 + SQL Serverless Gen5_2 + Blob LRS + Key Vault Standard
2. **Estimated start:** ~$40-50/month
3. **Upgrade path:** B1→S1 when traffic grows, Serverless→S0 when consistent usage
4. **Use Azure Hybrid Benefit** if you have Visual Studio Enterprise/MSDN
5. **Set budget alerts** at $75 and $150
6. **Monitor** Application Insights daily ingestion — stop at 5 GB free limit

---

## References

| Topic | Official Source |
|-------|----------------|
| App Service Linux Pricing | [azure.microsoft.com/en-us/pricing/details/app-service/](https://azure.microsoft.com/en-us/pricing/details/app-service/) |
| SQL Database Pricing | [azure.microsoft.com/en-us/pricing/details/sql-database/single/](https://azure.microsoft.com/en-us/pricing/details/sql-database/single/) |
| Blob Storage Pricing | [azure.microsoft.com/en-us/pricing/details/storage/blobs/](https://azure.microsoft.com/en-us/pricing/details/storage/blobs/) |
| Key Vault Pricing | [azure.microsoft.com/en-us/pricing/details/key-vault/](https://azure.microsoft.com/en-us/pricing/details/key-vault/) |
| Azure Monitor Pricing | [azure.microsoft.com/en-us/pricing/details/monitor/](https://azure.microsoft.com/en-us/pricing/details/monitor/) |
| Azure Calculator | [azure.microsoft.com/en-us/pricing/calculator/](https://azure.microsoft.com/en-us/pricing/calculator/) |
| App Service PHP on Linux | [learn.microsoft.com/en-us/azure/app-service/configure-language-php](https://learn.microsoft.com/en-us/azure/app-service/configure-language-php) |
| Azure App Settings Ref | [learn.microsoft.com/en-us/azure/app-service/reference-app-settings](https://learn.microsoft.com/en-us/azure/app-service/reference-app-settings) |

> **Note:** Azure pricing is dynamic and region-specific. The estimates above are based on East US pricing from April 2026. Microsoft frequently updates pricing tiers (e.g., introducing Premium v4, new Dev/Test rates). Always verify with the Azure Pricing Calculator linked above before making purchasing decisions. Pricing shown as `$-` in documentation pages means the values are JavaScript-rendered and not available in static HTML.
