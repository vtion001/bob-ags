# Azure Key Vault Integration for Laravel Applications

**Date:** 2026-04-17  
**Source:** Microsoft Learn (Azure App Service, Azure Key Vault)  
**Confidence:** HIGH (Official Microsoft Documentation)

---

## Executive Summary

For Laravel production deployments on Azure App Service, the **recommended approach** is **Option A: Azure App Settings with Key Vault References** combined with **System-Assigned Managed Identity**. This approach requires zero code changes, works with Laravel's native `.env` pattern via `APP_KEY`, and provides automatic secret rotation.

---

## 1. Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Azure App Service                         │
│  ┌─────────────────────────────────────────────────────┐    │
│  │         Laravel Application                          │    │
│  │                                                      │    │
│  │   Config reads from App Settings (env vars)           │    │
│  │         ↓         ↓         ↓                        │    │
│  │   DB_CONN  APP_KEY  OPENAI_API_KEY                  │    │
│  └─────────────────────────────────────────────────────┘    │
│                          ↑                                  │
│              System-Assigned Managed Identity                │
└──────────────────────────│──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    Azure Key Vault                           │
│                                                              │
│   Secrets: OPENAI_API_KEY, DB_PASSWORD, APP_KEY, etc.      │
│                                                              │
│   Access: Key Vault Secrets User (RBAC)                     │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Azure Key Vault Setup

### 2.1 Create Key Vault

```bash
# Login to Azure
az login

# Create resource group (if needed)
az group create --name "bob-ags-rg" --location "EastUS"

# Create Key Vault with RBAC authorization
az keyvault create \
    --name "bob-ags-kv" \
    --resource-group "bob-ags-rg" \
    --location "EastUS" \
    --enable-rbac-authorization true

# Note: The vault name must be globally unique
```

### 2.2 Add Secrets to Key Vault

```bash
# Add OpenAI API Key
az keyvault secret set \
    --vault-name "bob-ags-kv" \
    --name "OPENAI_API_KEY" \
    --value "sk-..."

# Add Database Password
az keyvault secret set \
    --vault-name "bob-ags-kv" \
    --name "DB_PASSWORD" \
    --value "your-secure-db-password"

# Add APP_KEY (generate with: php artisan key:generate --show)
az keyvault secret set \
    --vault-name "bob-ags-kv" \
    --name "APP_KEY" \
    --value "base64:..."

# Add Azure Blob Storage connection string
az keyvault secret set \
    --vault-name "bob-ags-kv" \
    --name "AZURE_STORAGE_CONNECTION_STRING" \
    --value "DefaultEndpointsProtocol=https;..."

# Add Anthropic API Key (if used)
az keyvault secret set \
    --vault-name "bob-ags-kv" \
    --name "ANTHROPIC_API_KEY" \
    --value "sk-ant-..."
```

### 2.3 Access Policies vs RBAC

| Aspect | Access Policies (Legacy) | RBAC (Recommended) |
|--------|------------------------|---------------------|
| Model | Per-vault permission assignment | Azure-wide role assignments |
| Secret Officer Role | Key Vault Secrets Officer | Key Vault Secrets Officer |
| Secret User Role | Key Vault Secrets User | Key Vault Secrets User |
| New Vaults | Must opt-in | Default since API 2026-02-01 |
| Best For | Simple, single-vault setups | Enterprise, multi-vault management |

**Recommendation:** Use RBAC (`--enable-rbac-authorization true`)

---

## 3. Managed Identity Setup

### 3.1 Enable System-Assigned Managed Identity on App Service

**Azure CLI:**
```bash
# Get App Service name
az webapp list --resource-group "bob-ags-rg" --query "[].name"

# Enable system-assigned managed identity
az webapp identity assign \
    --resource-group "bob-ags-rg" \
    --name "bob-ags"
```

**Azure Portal:**
1. Navigate to App Service → Settings → Identity
2. Select **System assigned** tab
3. Set **Status** to **On**
4. Click **Save**

### 3.2 Grant Key Vault Access via RBAC

```bash
# Get the App Service principal ID (from the identity assign output)
# Or retrieve it:
az webapp show \
    --resource-group "bob-ags-rg" \
    --name "bob-ags" \
    --query "identity.principalId"

# Assign Key Vault Secrets User role to the App Service
# Scope to the specific vault:
az role assignment create \
    --role "Key Vault Secrets User" \
    --assignee "<app-service-principal-id>" \
    --scope "/subscriptions/<subscription-id>/resourceGroups/bob-ags-rg/providers/Microsoft.KeyVault/vaults/bob-ags-kv"

# Alternative: Scope to subscription level
az role assignment create \
    --role "Key Vault Secrets User" \
    --assignee "<app-service-principal-id>" \
    --scope "/subscriptions/<subscription-id>"
```

**Built-in Roles for Key Vault Data Plane:**

| Role | Purpose |
|------|---------|
| Key Vault Secrets User | Read secret contents |
| Key Vault Secrets Officer | Create/update/delete secrets |
| Key Vault Administrator | Full data plane access (no role management) |
| Key Vault Reader | Read metadata only (no secret values) |

---

## 4. Configuration Approaches Comparison

### Option A: App Settings with Key Vault References (RECOMMENDED)

**Syntax:**
```
@Microsoft.KeyVault(SecretUri=https://myvault.vault.azure.net/secrets/mysecret)
@Microsoft.KeyVault(VaultName=myvault;SecretName=mysecret)
```

**Benefits:**
- ✅ Zero code changes required
- ✅ App settings encrypted at rest
- ✅ Automatic secret rotation (within 24 hours)
- ✅ Native Laravel `.env` integration
- ✅ Works with deployment slots
- ✅ No additional packages needed

**Azure CLI Configuration:**
```bash
# Set APP_KEY reference
az webapp config appsettings set \
    --resource-group "bob-ags-rg" \
    --name "bob-ags" \
    --settings APP_KEY="@Microsoft.KeyVault(VaultName=bob-ags-kv;SecretName=APP_KEY)"

# Set OPENAI_API_KEY reference
az webapp config appsettings set \
    --resource-group "bob-ags-rg" \
    --name "bob-ags" \
    --settings OPENAI_API_KEY="@Microsoft.KeyVault(VaultName=bob-ags-kv;SecretName=OPENAI_API_KEY)"

# Set DB_PASSWORD (for production PostgreSQL)
az webapp config appsettings set \
    --resource-group "bob-ags-rg" \
    --name "bob-ags" \
    --settings DB_PASSWORD="@Microsoft.KeyVault(VaultName=bob-ags-kv;SecretName=DB_PASSWORD)"

# Set ANTHROPIC_API_KEY
az webapp config appsettings set \
    --resource-group "bob-ags-rg" \
    --name "bob-ags" \
    --settings ANTHROPIC_API_KEY="@Microsoft.KeyVault(VaultName=bob-ags-kv;SecretName=ANTHROPIC_API_KEY)"
```

**For Laravel .env:**
In Azure App Settings, override the `APP_KEY` value with the Key Vault reference. Laravel will automatically pick it up since it reads from `getenv()`.

### Option B: Azure SDK Runtime Fetching

**Use Cases:**
- Secrets need to be fetched conditionally
- Need for secret listing/enumeration
- Custom caching strategies

**Install Azure Identity SDK:**
```bash
composer require microsoft/azure-identity microsoft/azure-sdk-for-php
```

**Example Service:**
```php
<?php
// app/Services/KeyVaultService.php

namespace App\Services;

use Azure.Identity\ClientSecretCredential;
use Azure KeyVault\KeyVaultClient;

class KeyVaultService
{
    protected ClientSecretCredential $credential;
    protected string $vaultUrl;

    public function __construct()
    {
        $tenantId = config('services.azure.tenant_id');
        $clientId = config('services.azure.client_id');
        $clientSecret = config('services.azure.client_secret');
        $this->vaultUrl = config('services.azure.key_vault_url');

        $this->credential = new ClientSecretCredential(
            $tenantId,
            $clientId,
            $clientSecret
        );
    }

    public function getSecret(string $secretName, ?string $version = null): string
    {
        $client = new KeyVaultClient($this->credential);
        
        if ($version) {
            $secret = $client->getSecret($this->vaultUrl, $secretName, $version);
        } else {
            $secret = $client->getSecret($this->vaultUrl, $secretName);
        }
        
        return $secret->value;
    }
}
```

**Note:** Option B requires storing client credentials in App Settings, which partially defeats the purpose of Key Vault. Option A is cleaner.

### Option C: Managed Identity with Azure SDK

```php
<?php
// Using Managed Identity (recommended for runtime fetching)

use Azure.Identity\ManagedIdentityCredential;

$credential = new ManagedIdentityCredential();
$token = $credential->getToken("https://vault.azure.net/.default");
```

**Downside:** Requires additional code changes and caching logic.

---

## 5. Secrets to Store in Key Vault

### Critical (Always Key Vault)
| Secret | Why |
|--------|-----|
| `OPENAI_API_KEY` | External API key - high security requirement |
| `ANTHROPIC_API_KEY` | External API key - high security requirement |
| `APP_KEY` | Laravel encryption key - session/data encryption |
| `DB_PASSWORD` | Database credentials |

### Recommended (Key Vault)
| Secret | Why |
|--------|-----|
| `AZURE_STORAGE_CONNECTION_STRING` | Blob storage access |
| `SESSION_ENCRYPTION_KEY` | If custom session encryption |
| `CTM_API_KEY` | Third-party call tracking API |

### Optional (Can remain as App Settings)
| Setting | Why |
|---------|-----|
| `APP_ENV` | Non-sensitive, changes frequently |
| `APP_DEBUG` | Security risk if public, but doesn't need rotation |
| `LOG_LEVEL` | Non-sensitive configuration |

---

## 6. Environment-Specific Setup

### Development Environment
```bash
# Keep local .env for development
# DO NOT commit production secrets to .env

# Use Azure CLI to authenticate
az login

# Set local development secrets manually or use
cp .env.example .env
php artisan key:generate
```

### Staging/Production
```bash
# Production Key Vault
az keyvault create \
    --name "bob-ags-kv-prod" \
    --resource-group "bob-ags-rg-prod" \
    --enable-rbac-authorization true

# Staging Key Vault (separate vault per environment)
az keyvault create \
    --name "bob-ags-kv-staging" \
    --resource-group "bob-ags-rg" \
    --enable-rbac-authorization true
```

**Best Practice:** Use separate Key Vaults per environment (Development, Staging, Production)

---

## 7. Deployment Slot Configuration

For deployment slots (staging/production swap):

```bash
# Get slot name
az webapp deployment slot list \
    --resource-group "bob-ags-rg" \
    --name "bob-ags"

# Configure staging slot with staging Key Vault reference
az webapp config appsettings set \
    --resource-group "bob-ags-rg" \
    --name "bob-ags" \
    --slot staging \
    --settings APP_KEY="@Microsoft.KeyVault(VaultName=bob-ags-kv-staging;SecretName=APP_KEY)"
```

**Note:** Most Key Vault references should be marked as slot-specific settings to use environment-specific vaults.

---

## 8. Secret Rotation

### Automatic Rotation
Key Vault references without version specification automatically pick up new versions within 24 hours.

```bash
# Update secret in Key Vault
az keyvault secret set \
    --vault-name "bob-ags-kv" \
    --name "OPENAI_API_KEY" \
    --value "sk-new-api-key"

# Force App Service to refresh (optional)
az webapp config appsettings refresh \
    --resource-group "bob-ags-rg" \
    --name "bob-ags"
```

### Versioned References (if needed)
```bash
# Specify version explicitly
@Microsoft.KeyVault(SecretUri=https://bob-ags-kv.vault.azure.net/secrets/OPENAI_API_KEY/a1b2c3d4e5f6...)
```

### APP_KEY Rotation
```bash
# Generate new key
php artisan key:generate

# Update Key Vault
az keyvault secret set \
    --vault-name "bob-ags-kv" \
    --name "APP_KEY" \
    --value "base64:newly-generated-key"

# Force refresh
az webapp config appsettings refresh \
    --resource-group "bob-ags-rg" \
    --name "bob-ags"
```

**Warning:** APP_KEY rotation will invalidate all existing encrypted sessions and data. Plan accordingly.

---

## 9. Troubleshooting

### Common Issues

**1. Key Vault reference not resolving:**
```bash
# Check app settings
az webapp config appsettings show \
    --resource-group "bob-ags-rg" \
    --name "bob-ags"

# Verify managed identity is enabled
az webapp show \
    --resource-group "bob-ags-rg" \
    --name "bob-ags" \
    --query "identity"

# Verify role assignment
az role assignment list \
    --assignee "<app-service-principal-id>" \
    --scope "/subscriptions/<subscription-id>/resourceGroups/bob-ags-rg/providers/Microsoft.KeyVault/vaults/bob-ags-kv"
```

**2. Access denied (403):**
- Ensure Key Vault Secrets User role is assigned
- Check Key Vault firewall settings (if enabled)
- Verify managed identity is enabled and propagate (~24 hours)

**3. Network-restricted Key Vault:**
```bash
# Allow App Service outbound traffic through VNet integration
# Or add App Service to Key Vault firewall exceptions
```

---

## 10. Complete Setup Checklist

- [ ] Create Key Vault(s) with RBAC enabled
- [ ] Add all secrets to Key Vault
- [ ] Enable System-Assigned Managed Identity on App Service
- [ ] Assign Key Vault Secrets User role to App Service
- [ ] Configure App Settings with Key Vault references
- [ ] Mark slot-specific settings appropriately
- [ ] Test secret resolution
- [ ] Verify deployment slot swap behavior
- [ ] Document secret rotation procedures

---

## 11. Recommended Approach for bob-ags

Based on the project's architecture (Laravel 12 + Vue 3 + Azure App Service):

```
Step 1: Create Key Vault
├── Production vault: bob-ags-kv-prod
└── Staging vault: bob-ags-kv-staging

Step 2: Add Secrets
├── OPENAI_API_KEY
├── ANTHROPIC_API_KEY
├── APP_KEY
└── DB_PASSWORD (for PostgreSQL)

Step 3: Enable Managed Identity
└── System-assigned on bob-ags App Service

Step 4: Grant Access
└── Key Vault Secrets User role to App Service

Step 5: Configure App Settings
└── Azure Portal or CLI with @Microsoft.KeyVault() syntax

Step 6: Verify
└── Check app starts correctly with Key Vault-sourced secrets
```

**No code changes required** - Laravel naturally reads from `getenv()` which App Settings populate.

---

## References

1. [Use Key Vault references as App Settings](https://learn.microsoft.com/en-us/azure/app-service/app-service-key-vault-references)
2. [Managed identities for App Service](https://learn.microsoft.com/en-us/azure/app-service/overview-managed-identity)
3. [Quickstart: Set and retrieve a secret](https://learn.microsoft.com/en-us/azure/key-vault/secrets/quick-create-cli)
4. [Azure RBAC for Key Vault](https://learn.microsoft.com/en-us/azure/key-vault/general/rbac-guide)
5. [Azure App Service Reference - App Settings](https://learn.microsoft.com/en-us/azure/app-service/reference-app-settings)
