# CI/CD Deployment Research: Laravel + Vue/Vite on Azure App Service

**Date:** 2026-04-17
**Source:** Microsoft Learn (Azure App Service docs, 2024-2025)
**Confidence:** HIGH
**Project:** bob-ags (Laravel 12 + Vue 3 + Vite)

---

## Project Stack (Detected)

| Component | Version |
|-----------|---------|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Vue | ^3.5 |
| Vite | ^7.0 |
| Node.js | detected via package.json |
| Database | SQLite (dev/test) / PostgreSQL (prod) |
| Queue | database driver |
| Session | database driver |
| Cache | database driver |

---

## 1. GitHub Actions + Azure App Service

### Authentication Methods (Recommended: OpenID Connect)

Source: [Deploy to Azure App Service by using GitHub Actions](https://learn.microsoft.com/en-us/azure/app-service/deploy-github-actions) (Microsoft Learn, 2024-2025)

| Method | Security | Complexity | Recommended |
|--------|----------|-----------|-------------|
| **OpenID Connect** (User-assigned identity) | ⭐⭐⭐⭐⭐ Best | Medium | ✅ **YES** |
| Publish Profile | ⭐⭐ | Low | Use only if OIDC unavailable |
| Service Principal | ⭐⭐⭐⭐ | Medium | ✅ Acceptable alternative |

### Recommended: OpenID Connect Setup (User-Assigned Managed Identity)

This is the most secure method — no secrets stored in GitHub.

#### Step 1: Azure CLI — Create Microsoft Entra App & Service Principal

```bash
# Create the Entra application
az ad app create --display-name "bob-ags-github-actions"

# Save the appId from output as AZURE_CLIENT_ID
# Save the appOwnerTenantId from output as AZURE_TENANT_ID

# Create service principal
az ad sp create --id <appId from above>

# Save the assignee-object-id for role assignment

# Create role assignment (Website Contributor on the specific web app)
az role assignment create \
  --role "Website Contributor" \
  --subscription <subscription-id> \
  --assignee-object-id <assignee-object-id> \
  --scope /subscriptions/<subscription-id>/resourceGroups/<group-name>/providers/Microsoft.Web/sites/<webapp-name> \
  --assignee-principal-type ServicePrincipal
```

#### Step 2: Azure CLI — Create Federated Credential

```bash
az ad app federated-credential create \
  --id <APPLICATION-OBJECT-ID> \
  --parameters credential.json
```

Where `credential.json`:
```json
{
    "name": "bob-ags-federated-credential",
    "issuer": "https://token.actions.githubusercontent.com",
    "subject": "repo:YOUR_ORG/bob-ags:ref:refs/heads/main",
    "description": "GitHub Actions OIDC for bob-ags",
    "audiences": ["api://AzureADTokenExchange"]
}
```

For pull request workflows, use:
```json
"subject": "repo:YOUR_ORG/bob-ags:pull_request"
```

#### Step 3: GitHub Secrets (Settings → Security → Secrets → Actions)

| Secret Name | Value Source |
|------------|-------------|
| `AZURE_CLIENT_ID` | Application (client) ID from Entra app |
| `AZURE_TENANT_ID` | Directory (tenant) ID from Entra app |
| `AZURE_SUBSCRIPTION_ID` | Azure subscription ID |

---

### Complete Working YAML — GitHub Actions (OpenID Connect)

```yaml
name: Deploy Laravel App to Azure App Service

on:
  push:
    branches:
      - main
  pull_request:
    types: [opened, synchronize]

permissions:
  id-token: write
  contents: read

env:
  AZURE_WEBAPP_NAME: bob-ags           # Your Azure Web App name
  AZURE_WEBAPP_SLOT_NAME: staging      # Optional: staging slot
  PHP_VERSION: '8.2'
  NODE_VERSION: '20'

jobs:
  build:
    name: Build & Test
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP ${{ env.PHP_VERSION }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite
          coverage: none

      - name: Setup Node.js ${{ env.NODE_VERSION }}
        uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}
          cache: 'npm'
          cache-dependency-path: package-lock.json

      - name: Install PHP dependencies (Composer)
        run: composer install --prefer-dist --no-interaction --no-progress

      - name: Install Node dependencies (npm)
        run: npm ci

      - name: Run Laravel Pint (code style)
        run: ./vendor/bin/pint --test

      - name: Run Laravel tests
        env:
          APP_ENV: testing
          DB_CONNECTION: sqlite
          DB_DATABASE: ':memory:'
          SESSION_DRIVER: array
          CACHE_DRIVER: array
          QUEUE_CONNECTION: sync
        run: php artisan test --without-tty

      - name: Build frontend assets (Vite)
        run: npm run build

      - name: Create deployment package
        run: |
          zip -r deployment.zip . \
            --exclude 'vendor/*/tests/*' \
            --exclude 'vendor/*/test/*' \
            --exclude 'node_modules/.cache/*' \
            --exclude '.git/*' \
            --exclude 'storage/*.key'

      - name: Upload build artifact
        uses: actions/upload-artifact@v4
        with:
          name: laravel-app
          path: deployment.zip
          retention-days: 7

  deploy:
    name: Deploy to Azure App Service
    needs: build
    runs-on: ubuntu-latest
    if: github.event_name == 'push' && github.ref == 'refs/heads/main'

    steps:
      - name: Download build artifact
        uses: actions/download-artifact@v4
        with:
          name: laravel-app
          path: ./

      - name: Login to Azure (OpenID Connect)
        uses: azure/login@v2
        with:
          client-id: ${{ secrets.AZURE_CLIENT_ID }}
          tenant-id: ${{ secrets.AZURE_TENANT_ID }}
          subscription-id: ${{ secrets.AZURE_SUBSCRIPTION_ID }}

      # --- OPTION A: Deploy to staging slot (Zero-downtime) ---
      - name: Deploy to Staging Slot
        uses: azure/webapps-deploy@v3
        with:
          app-name: ${{ env.AZURE_WEBAPP_NAME }}
          slot-name: ${{ env.AZURE_WEBAPP_SLOT_NAME }}
          package: ./deployment.zip
          deployment-secret: ${{ secrets.AZURE_WEBAPP_PUBLISH_PROFILE }}

      - name: Swap Staging to Production
        run: |
          az webapp deployment slot swap \
            --resource-group ${{ vars.AZURE_RESOURCE_GROUP }} \
            --name ${{ env.AZURE_WEBAPP_NAME }} \
            --slot ${{ env.AZURE_WEBAPP_SLOT_NAME }} \
            --target-slot production

      - name: Logout from Azure
        if: always()
        run: az logout
```

---

### Alternative YAML — Publish Profile (Simpler, Less Secure)

Use this if you can't set up OpenID Connect.

```yaml
name: Deploy Laravel App to Azure App Service (Publish Profile)

on:
  push:
    branches: [main]

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install dependencies
        run: |
          composer install --no-interaction --prefer-dist
          npm ci

      - name: Run tests
        env:
          APP_ENV: testing
          DB_CONNECTION: sqlite
          DB_DATABASE: ':memory:'
          SESSION_DRIVER: array
        run: php artisan test --without-tty

      - name: Build frontend
        run: npm run build

      - name: Deploy to Azure Web App
        uses: azure/webapps-deploy@v3
        with:
          app-name: ${{ secrets.AZURE_WEBAPP_NAME }}
          publish-profile: ${{ secrets.AZURE_WEBAPP_PUBLISH_PROFILE }}
          package: .
```

#### Publish Profile Setup

1. Go to Azure Portal → App Service → Overview → **Download publish profile**
2. GitHub → Settings → Secrets → Actions → **New repository secret**
3. Name: `AZURE_WEBAPP_PUBLISH_PROFILE`, Value: contents of the downloaded file

---

### Azure App Settings — Add via GitHub Actions or Azure Portal

Source: [Environment Variables and App Settings Reference](https://learn.microsoft.com/en-us/azure/app-service/reference-app-settings) (Microsoft Learn, 2024)

```bash
# Set app settings via Azure CLI
az webapp config appsettings set \
  --resource-group <rg-name> \
  --name <webapp-name> \
  --settings \
    APP_ENV=production \
    LOG_CHANNEL=azure \
    DB_CONNECTION=pgsql \
    SESSION_DRIVER=database \
    QUEUE_CONNECTION=database \
    CACHE_STORE=database \
    APP_KEY=<generated-key>
```

**IMPORTANT App Settings for Laravel:**

| Setting | Value | Notes |
|---------|-------|-------|
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | Set `true` only in staging |
| `APP_KEY` | `base64:<key>` | Generate: `php artisan key:generate --show` |
| `LOG_CHANNEL` | `azure` or `app` | Use Azure Monitor |
| `DB_CONNECTION` | `pgsql` | PostgreSQL for production |
| `SESSION_DRIVER` | `database` | |
| `QUEUE_CONNECTION` | `database` | |
| `CACHE_STORE` | `database` | Or `redis` if using Azure Cache for Redis |
| `WEBSITES_WEBDEPLOY_USE_SCM` | `true` | Required for ZIP deploy |
| `PHP_VERSION` | `8.2` | |
| `WEBSITE_HTTPLOGGING_RETENTION_DAYS` | `30` | |

---

## 2. Azure Pipelines

Source: [Build, test, and deploy PHP apps - Azure Pipelines](https://learn.microsoft.com/en-us/azure/devops/pipelines/ecosystems/php) (Microsoft Learn, 2024-2025)

### Complete YAML — Azure Pipelines (azure-pipelines.yml)

```yaml
trigger:
- main

variables:
  azureSubscription: 'service-connection-name'    # Azure DevOps service connection
  webAppName: 'bob-ags'
  vmImageName: 'ubuntu-22.04'
  environmentName: 'bob-ags-environment'
  phpVersion: '8.2'
  nodeVersion: '20'

stages:
- stage: Build
  displayName: 'Build & Test'
  jobs:
  - job: BuildJob
    pool:
      vmImage: $(vmImageName)
    steps:
    - script: |
        sudo update-alternatives --set php /usr/bin/php$(phpVersion)
        sudo update-alternatives --set phar /usr/bin/phar$(phpVersion)
        sudo update-alternatives --set phpdbg /usr/bin/phpdbg$(phpVersion)
        sudo update-alternatives --set php-cgi /usr/bin/php-cgi$(phpVersion)
        php -version
      displayName: 'Setup PHP $(phpVersion)'

    - task: NodeTool@0
      inputs:
        versionSpec: '$(nodeVersion)'
      displayName: 'Setup Node.js $(nodeVersion)'

    - script: composer install --no-interaction --prefer-dist
      displayName: 'Composer install'

    - script: npm ci
      displayName: 'NPM install'

    - script: ./vendor/bin/pint --test
      displayName: 'Laravel Pint (lint)'

    - script: |
        php artisan test --without-tty
      env:
        APP_ENV: testing
        DB_CONNECTION: sqlite
        DB_DATABASE: ':memory:'
        SESSION_DRIVER: array
        CACHE_DRIVER: array
        QUEUE_CONNECTION: sync
      displayName: 'Laravel Tests'

    - script: npm run build
      displayName: 'Build Vite assets'

    - task: ArchiveFiles@2
      inputs:
        rootFolderOrFile: '$(System.DefaultWorkingDirectory)'
        includeRootFolder: false
        archiveType: zip
        archiveFile: $(Build.ArtifactStagingDirectory)/$(Build.BuildId).zip
        replaceExistingArchive: true
      displayName: 'Create deployment ZIP'

    - publish: $(Build.ArtifactStagingDirectory)/$(Build.BuildId).zip
      artifact: drop
      displayName: 'Upload artifact'

- stage: Deploy_Staging
  displayName: 'Deploy to Staging'
  dependsOn: Build
  condition: succeeded()
  jobs:
  - deployment: DeployStaging
    pool:
      vmImage: $(vmImageName)
    environment: '$(environmentName)-staging'
    strategy:
      runOnce:
        deploy:
          steps:
          - task: AzureWebApp@1
            displayName: 'Deploy to Staging Slot'
            inputs:
              azureSubscription: $(azureSubscription)
              appName: $(webAppName)
              slotName: staging
              package: $(Pipeline.Workspace)/drop/$(Build.BuildId).zip

          - script: |
              az webapp config appsettings set \
                --resource-group $(resourceGroup) \
                --name $(webAppName) \
                --slot staging \
                --settings APP_ENV=staging
            displayName: 'Set staging app settings'

- stage: Deploy_Production
  displayName: 'Deploy to Production'
  dependsOn: Deploy_Staging
  condition: succeeded()
  jobs:
  - deployment: DeployProduction
    pool:
      vmImage: $(vmImageName)
    environment: '$(environmentName)'
    strategy:
      runOnce:
        deploy:
          steps:
          - task: AzureWebApp@1
            displayName: 'Swap Staging to Production'
            inputs:
              azureSubscription: $(azureSubscription)
              appName: $(webAppName)
              deployToSlotOrASE: true
              slotName: staging
              action: swap
```

### Azure DevOps Service Connection Setup

1. Azure DevOps → Project Settings → Service Connections → New
2. Select **Azure Resource Manager**
3. Choose **Service principal (automatic)** or **Manual**
4. Grant **Website Contributor** role on the App Service
5. Name the connection (e.g., `bob-ags-azure-connection`)

---

## 3. GitHub Actions vs Azure Pipelines — Comparison

Source: [Configure continuous deployment](https://learn.microsoft.com/en-us/azure/app-service/deploy-continuous-deployment) (Microsoft Learn, 2024-2025)

| Criteria | GitHub Actions | Azure Pipelines |
|----------|--------------|----------------|
| **Source** | GitHub repos only | Any Git repo |
| **Build agents** | GitHub-hosted or self-hosted | Azure-hosted or self-hosted |
| **YAML storage** | `.github/workflows/` in repo | Azure DevOps managed |
| **OpenID Connect** | ✅ Native | ✅ Via service principal |
| **Deployment slots** | ✅ via `azure/webapps-deploy@v3` | ✅ via `AzureWebApp@1` |
| **Native PHP support** | Via `shivammathur/setup-php` | Pre-installed on Ubuntu agents |
| **Parallel jobs** | ✅ Free tier has limits | ✅ Free tier with Azure DevOps |
| **Integrated portal logging** | ✅ Via Deployment Center | ✅ Via Azure Pipelines |
| **Secret management** | GitHub Secrets | Azure DevOps Variable Groups |
| **Recommended for** | Open-source / GitHub repos | Enterprise / Azure DevOps orgs |

### Recommendation

> **GitHub Actions is recommended** for this project (bob-ags) because:
> 1. Code is hosted on GitHub
> 2. OpenID Connect provides enterprise-grade security without storing secrets
> 3. Deployment Center in Azure Portal can auto-generate the workflow
> 4. Existing CI/CD in `.github/workflows/tests.yml` follows GitHub Actions pattern

---

## 4. Zero-Downtime Deployment — Deployment Slots

Source: [Set up staging environments in Azure App Service](https://learn.microsoft.com/en-us/azure/app-service/deploy-staging-slots) (Microsoft Learn, 2024-2025)

### Slot Tiers

| App Service Plan | Included Slots |
|-----------------|---------------|
| Free / Shared | 0 |
| Basic | 0 |
| Standard | Up to 5 |
| Premium | Up to 20 |
| Isolated | Up to 20 |

### Swap Behavior (What Happens)

Source: [Microsoft Learn — swap operation steps](https://learn.microsoft.com/en-us/azure/app-service/deploy-staging-slots)

When App Service swaps slots:
1. **Applies target slot settings** to source slot instances → triggers restart
2. **Waits for all instances** to restart and warm up
3. **Triggers HTTP warmup requests** to source slot root (`/`)
4. **Swaps routing rules** — target gets warmed-up app, zero downtime
5. **Applies settings** to former production instances (now in staging)

### Settings That Swap vs Stay

**Swapped between slots:**
- App settings (unless marked `slotStick: true`)
- Connection strings (unless marked `slotStick: true`)
- Language framework version, 32/64-bit, WebSockets, HTTP version

**NOT swapped (slot-specific):**
- Custom domain names
- TLS/SSL certificates
- Scale settings
- IP restrictions
- Always On
- Managed identities
- Virtual network integration

### Auto-Swap Configuration

```yaml
# In the workflow after deploying to staging slot:
- name: Configure Auto-Swap
  run: |
    az webapp config appsettings set \
      --resource-group ${{ vars.AZURE_RESOURCE_GROUP }} \
      --name ${{ env.AZURE_WEBAPP_NAME }} \
      --slot staging \
      --settings WEBSITE_ENABLE_SYNC_UPDATE_SITE=true
```

Or via Azure Portal: App Service → Deployment Slots → Staging → **Configuration** → **General settings** → **Auto Swap: On**

### Swap with Preview (Multi-Phase)

For critical apps, use swap-with-preview to validate before completing:

```bash
# Phase 1: Apply production settings to staging, pause
az webapp deployment slot swap \
  --resource-group <rg> \
  --name <app> \
  --slot staging \
  --target-slot production \
  --action preview

# After validation, complete the swap
az webapp deployment slot swap \
  --resource-group <rg> \
  --name <app> \
  --slot staging \
  --target-slot production \
  --action completeSwap
```

### Warmup Configuration (applicationInitialization)

Source: [Microsoft Learn — swap operation steps](https://learn.microsoft.com/en-us/azure/app-service/deploy-staging-slots)

App Service sends HTTP requests to `/` by default. For Laravel, add a health endpoint:

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'app' => 'bob-ags']);
});
```

Configure custom warmup path in `web.config`:

```xml
<system.webServer>
  <applicationInitialization>
    <add path="/health" hostHeader="localhost" />
  </applicationInitialization>
</system.webServer>
```

---

## 5. Rollback Strategies

### Strategy 1: Slot Swap Back (Fastest — Recommended)

```bash
# Immediate rollback: swap staging (with old code) back to production
az webapp deployment slot swap \
  --resource-group <rg> \
  --name <app> \
  --slot staging \
  --target-slot production
```

**How it works:** After every deployment, keep the **previous production version** in the staging slot. If the new deployment fails, swap staging back. If deployment succeeds, update staging with the new version.

```yaml
# Workflow: Keep previous version in staging for rollback
- name: Deploy to Staging Slot
  uses: azure/webapps-deploy@v3
  with:
    app-name: ${{ env.AZURE_WEBAPP_NAME }}
    slot-name: staging
    package: ./deployment.zip

# After swap to production, the old production is now in staging (as rollback target)
- name: Swap to Production
  run: |
    az webapp deployment slot swap \
      --resource-group ${{ vars.AZURE_RESOURCE_GROUP }} \
      --name ${{ env.AZURE_WEBAPP_NAME }} \
      --slot staging \
      --target-slot production
```

### Strategy 2: Git History Rollback

```bash
# Revert to a previous commit
git revert <commit-sha>

# Or reset and force push
git reset --hard <good-commit-sha>
git push origin main --force
# ⚠️ This triggers a new deployment — not instant
```

**Caution:** Force pushing to `main` is dangerous in team environments. Use Strategy 1 for production emergencies.

### Strategy 3: Re-Deploy Previous Git Tag/Commit

```yaml
# In GitHub: Workflow_dispatch allows selecting a specific ref
on:
  workflow_dispatch:
    inputs:
      git_ref:
        description: 'Git ref (tag or commit SHA)'
        required: true
        type: string

# In the job:
- name: Checkout specific ref
  uses: actions/checkout@v4
  with:
    ref: ${{ inputs.git_ref }}
```

### Health Check + Auto-Heal

Source: [Microsoft Learn — swap with warmup](https://learn.microsoft.com/en-us/azure/app-service/deploy-staging-slots)

```bash
# Enable health check
az webapp config set \
  --resource-group <rg> \
  --name <app> \
  --health-check-path /health \
  --health-check-interval 120 \
  --number-of-differences 1

# Auto-heal triggers when health check fails
# Configure in Azure Portal: App Service → Configuration → Health check
```

---

## 6. Deployment Best Practices for Laravel on Azure

### Pre-Deployment Checklist

Source: [Configure a PHP App](https://learn.microsoft.com/en-us/azure/app-service/configure-language-php) (Microsoft Learn, 2024)

```bash
# 1. Generate APP_KEY (store in Azure App Settings, NOT in code)
php artisan key:generate --show

# 2. Clear and optimize config (in build step)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. For production, set:
#    APP_ENV=production
#    APP_DEBUG=false
#    LOG_CHANNEL=azure  (or "app" for Laravel log channel)

# 4. Storage link (run once after deploy)
php artisan storage:link
```

### Azure App Service Startup Command

If using custom startup:

```bash
# In Azure Portal: Configuration → General Settings → Startup Command
# Or via CLI:
az webapp config set \
  --resource-group <rg> \
  --name <app> \
  --startup-file "php artisan serve --host=0.0.0.0 --port=8000"
```

**Note:** Azure App Service for PHP uses IIS by default on Windows, or Apache/Nginx on Linux. For PHP on Linux, the default startup uses the built-in PHP-FPM server.

### Recommended Azure App Service Settings

```bash
# Production app settings
az webapp config appsettings set \
  --resource-group <rg> \
  --name <app> \
  --settings \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY="base64:<generated-key>" \
    LOG_CHANNEL=azure \
    DB_CONNECTION=pgsql \
    POSTGRESQL_HOST="<server>.postgres.database.azure.com" \
    POSTGRESQL_DATABASE=bob_ags \
    POSTGRESQL_USERNAME=<user> \
    POSTGRESQL_PASSWORD="<password>" \
    SESSION_DRIVER=database \
    QUEUE_CONNECTION=database \
    CACHE_STORE=database \
    WEBSITES_WEBDEPLOY_USE_SCM=true \
    PHP_VERSION=8.2
```

### Deployment Slot Configuration

```bash
# Create staging slot
az webapp deployment slot create \
  --name <app> \
  --resource-group <rg> \
  --slot staging

# Clone settings from production to staging (optional)
az webapp deployment slot create \
  --name <app> \
  --resource-group <rg> \
  --slot staging \
  --configuration-source <production-slot>

# Staging-specific settings (NOT swapped):
az webapp config appsettings set \
  --name <app> \
  --resource-group <rg> \
  --slot staging \
  --settings \
    APP_DEBUG=true \
    APP_ENV=staging

# Staging uses different DB (optional)
az webapp config appsettings set \
  --name <app> \
  --resource-group <rg> \
  --slot staging \
  --settings \
    DB_CONNECTION=pgsql_staging \
    POSTGRESQL_HOST="<staging-server>.postgres.database.azure.com"
```

---

## 7. Summary — Recommended Approach for bob-ags

Given this project:
- **Laravel 12** with Vue 3 + Vite frontend
- **PHP 8.2**, Node 20
- **Existing GitHub Actions** tests at `.github/workflows/tests.yml`
- **PostgreSQL** for production, SQLite for tests
- **Queue, Session, Cache** all use database driver

### Recommended Setup

1. **CI/CD:** GitHub Actions (extend existing `tests.yml`)
2. **Auth:** OpenID Connect (federated credentials — no secrets)
3. **Deploy target:** Staging slot → Swap to Production
4. **Build:** `composer install` + `npm ci` + `npm run build`
5. **Test:** Extend existing `php artisan test` with production SQLite memory
6. **Rollback:** Swap staging back to production (keep previous version in staging)

### GitHub Secrets Required

| Secret | Value |
|--------|-------|
| `AZURE_CLIENT_ID` | Entra app client ID |
| `AZURE_TENANT_ID` | Entra tenant ID |
| `AZURE_SUBSCRIPTION_ID` | Azure subscription ID |
| `AZURE_WEBAPP_PUBLISH_PROFILE` | Downloaded publish profile (for deployment secret) |

### GitHub Variables Required

| Variable | Value |
|----------|-------|
| `AZURE_RESOURCE_GROUP` | Azure resource group name |
| `AZURE_WEBAPP_NAME` | Azure Web App name |
| `AZURE_WEBAPP_SLOT_NAME` | Staging slot name |
