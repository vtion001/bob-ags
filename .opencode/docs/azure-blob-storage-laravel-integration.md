# Azure Blob Storage Integration with Laravel

**Date:** 2026-04-17  
**Source:** Official Microsoft Learn, Azure-OSS GitHub, Packagist  
**Confidence:** HIGH  
**Status:** Verified and Current

---

## Table of Contents
1. [Overview](#overview)
2. [Azure Blob Storage Setup](#1-azure-blob-storage-setup)
3. [Laravel Configuration](#2-laravel-configuration)
4. [Usage in Laravel](#3-usage-in-laravel)
5. [CDN Integration](#4-cdn-integration)
6. [Environment Variables](#5-environment-variables)
7. [Security Best Practices](#6-security-best-practices)

---

## Overview

### Critical Finding: Package Change Required

> **⚠️ IMPORTANT:** The `league/flysystem-azure-blob-storage` package is **ABANDONED**.
> 
> Microsoft archived the official Azure SDK for PHP in November 2023. The community has taken over with the **azure-oss** organization packages.
> 
> **Use these packages instead:**
> - `azure-oss/storage-blob-laravel` — Laravel filesystem driver (supports Laravel 10, 11, 12, 13)
> - `azure-oss/storage-blob-flysystem` — Flysystem adapter (requires PHP 8.1+)
> - `azure-oss/storage` — Core SDK

**Source:** [Packagist - azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel)

---

## 1. Azure Blob Storage Setup

### 1.1 Creating a Storage Account

**Via Azure Portal:**
1. Go to Azure Portal → Storage accounts → Create
2. Select **Standard** performance with **General-purpose v2** account kind
3. Choose your region and redundancy option

**Redundancy Options:**

| Option | Description | Use Case |
|--------|-------------|----------|
| **LRS** | Locally redundant (3 copies, single datacenter) | Low cost, development |
| **GRS** | Geo-redundant (3 copies + 3 in paired region) | Production, disaster recovery |
| **RA-GRS** | Read-access geo-redundant | Production with read failover |
| **ZRS** | Zone-redundant (3 copies across availability zones) | High availability |
| **GZRS** | Geo-zone-redundant | Maximum availability + geo-protection |

**Source:** [Create Azure Storage Account - Microsoft Learn](https://learn.microsoft.com/en-us/azure/storage/common/storage-account-create)

### 1.2 Creating Blob Containers

Container naming rules:
- 3-63 characters
- Lowercase letters, numbers, and dashes only
- Must start with letter or number
- No consecutive dashes

**Recommended container structure:**
```
profiles/           → Public: profile images, avatars
uploads/            → Public: general uploads
documents/          → Private: secure documents
recordings/         → Private: call recordings
backups/            → Private: system backups
```

**Container Access Levels:**

| Access Level | Description | Security |
|--------------|-------------|----------|
| **Private** | No anonymous access | ✅ Recommended for sensitive data |
| **Blob** | Anonymous read access for blobs only | Use for public files |
| **Container** | Anonymous read access for container + blobs | Listing allowed |

**Source:** [Blob Storage Introduction - Microsoft Learn](https://learn.microsoft.com/en-us/azure/storage/blobs/storage-blobs-introduction)

### 1.3 Access Tiers

| Tier | Min Retention | Storage Cost | Access Cost | Use Case |
|------|---------------|-------------|------------|----------|
| **Hot** | None | Highest | Lowest | Frequently accessed data |
| **Cool** | 30 days | Lower | Higher | Infrequently accessed |
| **Cold** | 90 days | Lower | Higher | Rarely accessed |
| **Archive** | 180 days | Lowest | Highest (rehydration needed) | Long-term archival |

> **Note:** Early deletion fees apply if data is removed before minimum retention period.

**Source:** [Access Tiers Overview - Microsoft Learn](https://learn.microsoft.com/en-us/azure/storage/blobs/access-tiers-overview)

### 1.4 Authentication Methods

#### Method 1: Connection String (Shared Key)
- Simple setup using account name + key
- Stored in connection string format
- Full access to storage account

#### Method 2: SAS (Shared Access Signature)
- Time-limited access tokens
- Granular permissions control
- Can be scoped to specific containers/blobs
- **Recommended for production**

#### Method 3: Entra ID (Microsoft Entra ID)
- Token-based authentication
- No account keys in code
- Uses Azure RBAC roles
- **Most secure option**

#### Method 4: Managed Identity
- Azure resource authentication
- No credentials needed
- Ideal for Azure-hosted apps

**Source:** [SAS Overview - Microsoft Learn](https://learn.microsoft.com/en-us/azure/storage/common/storage-sas-overview)

---

## 2. Laravel Configuration

### 2.1 Install Required Package

```bash
composer require azure-oss/storage-blob-laravel
```

**Requirements:**
- PHP 8.1+
- Laravel 10, 11, 12, or 13
- `illuminate/filesystem` ^10|^11|^12|^13

**Source:** [Packagist - azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel)

### 2.2 Configure Disks

Edit `config/filesystems.php`:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    */
    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */
    'disks' => [

        // ... existing disks ...

        /*
        |--------------------------------------------------------------------------
        | Azure Blob Storage - Public Files
        |--------------------------------------------------------------------------
        | For files that need to be publicly accessible
        | Examples: profile images, avatars, public uploads
        */
        'azure-public' => [
            'driver' => 'azure-storage-blob',
            'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
            'container' => env('AZURE_PUBLIC_CONTAINER', 'public'),
            'public' => true,  // Containers with blob-level public access
        ],

        /*
        |--------------------------------------------------------------------------
        | Azure Blob Storage - Private Files
        |--------------------------------------------------------------------------
        | For files that require authentication
        | Examples: documents, recordings, backups
        | Requires signed URLs for access
        */
        'azure-private' => [
            'driver' => 'azure-storage-blob',
            'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
            'container' => env('AZURE_PRIVATE_CONTAINER', 'private'),
            'public' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Azure Blob Storage - Entra ID Authentication
        |--------------------------------------------------------------------------
        | For production use with Entra ID (recommended)
        | Requires: composer require azure-oss/identity
        */
        'azure-entra' => [
            'driver' => 'azure-storage-blob',
            'account_name' => env('AZURE_STORAGE_ACCOUNT_NAME'),
            'tenant_id' => env('AZURE_TENANT_ID'),
            'client_id' => env('AZURE_CLIENT_ID'),
            'client_secret' => env('AZURE_CLIENT_SECRET'),
            'container' => env('AZURE_PRIVATE_CONTAINER', 'private'),
            'public' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Azure Blob Storage - Managed Identity
        |--------------------------------------------------------------------------
        | For Azure-hosted applications
        | No credentials needed when running on Azure
        */
        'azure-managed' => [
            'driver' => 'azure-storage-blob',
            'account_name' => env('AZURE_STORAGE_ACCOUNT_NAME'),
            'use_managed_identity' => true,
            'container' => env('AZURE_PRIVATE_CONTAINER', 'private'),
            'public' => false,
        ],

    ],

];
```

### 2.3 Alternative: Connection String via Individual Keys

```php
'azure' => [
    'driver' => 'azure-storage-blob',
    'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
    'container' => env('AZURE_STORAGE_CONTAINER'),
],
```

Or using individual keys:

```php
'azure' => [
    'driver' => 'azure-storage-blob',
    'account_name' => env('AZURE_STORAGE_ACCOUNT_NAME'),
    'account_key' => env('AZURE_STORAGE_ACCOUNT_KEY'),
    'container' => env('AZURE_STORAGE_CONTAINER'),
],
```

**Source:** [azure-oss/storage-blob-laravel README](https://github.com/Azure-OSS/azure-storage-php-adapter-laravel)

---

## 3. Usage in Laravel

### 3.1 Basic File Operations

```php
<?php

use Illuminate\Support\Facades\Storage;

// Upload a file
Storage::disk('azure-public')->put('avatars/user-123.jpg', $fileContents);

// Upload with automatic streaming (recommended for large files)
$stream = fopen($localFilePath, 'r');
Storage::disk('azure-public')->writeStream('uploads/large-file.zip', $stream);
fclose($stream);

// Check if file exists
$exists = Storage::disk('azure-public')->exists('avatars/user-123.jpg');

// Get file contents
$contents = Storage::disk('azure-public')->get('avatars/user-123.jpg');

// Get file URL (for public containers)
$url = Storage::disk('azure-public')->url('avatars/user-123.jpg');

// Delete a file
Storage::disk('azure-public')->delete('avatars/user-123.jpg');

// List files in directory
$files = Storage::disk('azure-public')->files('avatars');
$allFiles = Storage::disk('azure-public')->allFiles('avatars');

// List directories
$directories = Storage::disk('azure-public')->directories('profiles');
```

### 3.2 Upload from Request

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Delete old avatar if exists
        if ($user->avatar_path) {
            Storage::disk('azure-public')->delete($user->avatar_path);
        }

        // Store new avatar
        $path = $request->file('avatar')->store(
            'avatars',
            'azure-public'
        );

        // Update user record
        $user->update(['avatar_path' => $path]);

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => Storage::disk('azure-public')->url($path),
        ]);
    }
}
```

### 3.3 Private Files with Temporary URLs

```php
<?php

use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Generate a temporary signed URL for private files
     * URL expires after specified duration
     */
    public function download(Request $request, string $filename)
    {
        $disk = Storage::disk('azure-private');
        
        if (!$disk->exists($filename)) {
            abort(404);
        }

        // Generate temporary URL (valid for 60 minutes)
        $signedUrl = $disk->temporaryUrl(
            $filename,
            now()->addMinutes(60)
        );

        return redirect($signedUrl);
    }

    /**
     * Download with custom expiration
     */
    public function preview(string $filename)
    {
        $signedUrl = Storage::disk('azure-private')->temporaryUrl(
            $filename,
            now()->addMinutes(15)  // Short-lived for preview
        );

        return response()->json([
            'url' => $signedUrl,
            'expires_at' => now()->addMinutes(15),
        ]);
    }
}
```

### 3.4 Handling Large File Uploads

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecordingController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'recording' => 'required|file|max:512000', // 500MB max
        ]);

        $file = $request->file('recording');
        $filename = sprintf(
            'recordings/%s/%s_%s.%s',
            date('Y/m/d'),
            auth()->id(),
            time(),
            $file->getClientOriginalExtension()
        );

        // Stream upload for large files (memory efficient)
        $stream = fopen($file->getRealPath(), 'r');
        Storage::disk('azure-private')->writeStream($filename, $stream);
        fclose($stream);

        return response()->json([
            'success' => true,
            'path' => $filename,
        ]);
    }
}
```

### 3.5 File Metadata

```php
<?php

use Illuminate\Support\Facades\Storage;

// Get file size
$size = Storage::disk('azure-public')->size('avatars/user-123.jpg');

// Get last modified timestamp
$lastModified = Storage::disk('azure-public')->lastModified('avatars/user-123.jpg');

// Get MIME type (if available)
$mimeType = Storage::disk('azure-public')->mimeType('avatars/user-123.jpg');
```

---

## 4. CDN Integration

### 4.1 Azure CDN Overview

Azure CDN delivers cached content from edge servers globally, reducing latency and origin load.

**Important Updates (2025-2026):**
- Azure CDN from Microsoft (classic) being deprecated
- Migrate to **Azure Front Door Standard/Premium** for new deployments
- Retirement: September 30, 2027

**Source:** [Azure CDN Overview - Microsoft Learn](https://learn.microsoft.com/en-us/azure/cdn/cdn-overview)

### 4.2 Configure CDN Endpoint

1. **Create CDN Profile in Azure Portal:**
   - Go to CDN profiles → Create
   - Choose **Microsoft (Standard/Premium)** or **Azure Front Door**

2. **Add Endpoint:**
   - Origin type: Blob Storage
   - Origin hostname: `<storage-account>.blob.core.windows.net`
   - Origin path: `/<container-name>`

3. **Configure Caching Rules:**
   - Set TTL for static assets (images, CSS, JS)
   - Disable caching for dynamic content

### 4.3 Using CDN with Laravel

```php
<?php

use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    /**
     * Get CDN URL for public assets
     */
    public function getAssetUrl(string $path): string
    {
        $disk = Storage::disk('azure-public');
        
        if (!$disk->exists($path)) {
            abort(404);
        }

        // Return CDN endpoint instead of direct blob URL
        $cdnEndpoint = config('services.azure.cdn_endpoint');
        
        return rtrim($cdnEndpoint, '/') . '/' . ltrim($path, '/');
    }
}
```

### 4.4 Configuration

```php
// config/services.php
return [
    // ... existing services ...
    
    'azure' => [
        'cdn_endpoint' => env('AZURE_CDN_ENDPOINT'),
    ],
];
```

```env
# .env
AZURE_CDN_ENDPOINT=https://your-cdn.azureedge.net
```

---

## 5. Environment Variables

### 5.1 Development (Connection String)

```env
# .env - Development/Local

# Storage Account Connection String
# Found in Azure Portal → Storage Account → Access Keys → Connection String
AZURE_STORAGE_CONNECTION_STRING=DefaultEndpointsProtocol=https;AccountName=yourstorageaccount;AccountKey=your-account-key;EndpointSuffix=core.windows.net

# Container Names
AZURE_PUBLIC_CONTAINER=public
AZURE_PRIVATE_CONTAINER=private

# CDN (optional)
AZURE_CDN_ENDPOINT=https://yourstorageaccount.azureedge.net
```

### 5.2 Production (Entra ID)

```env
# .env - Production with Entra ID

# Storage Account Name (without .blob.core.windows.net)
AZURE_STORAGE_ACCOUNT_NAME=yourstorageaccount

# Entra ID Application Credentials
AZURE_TENANT_ID=your-tenant-id
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret

# Container Names
AZURE_PUBLIC_CONTAINER=public
AZURE_PRIVATE_CONTAINER=private
AZURE_RECORDINGS_CONTAINER=recordings
```

### 5.3 Production (Managed Identity)

```env
# .env - Azure App Service with Managed Identity

# Storage Account Name Only
AZURE_STORAGE_ACCOUNT_NAME=yourstorageaccount

# Enable Managed Identity (no secrets needed)
AZURE_USE_MANAGED_IDENTITY=true

# Container Names
AZURE_PUBLIC_CONTAINER=public
AZURE_PRIVATE_CONTAINER=private
```

### 5.4 Complete .env Example

```env
# ===========================================
# Azure Storage Configuration
# ===========================================

# Method 1: Connection String (Development)
AZURE_STORAGE_CONNECTION_STRING=DefaultEndpointsProtocol=https;AccountName=yourstorageaccount;AccountKey=your-account-key;EndpointSuffix=core.windows.net

# OR Method 2: Individual Keys (Alternative)
AZURE_STORAGE_ACCOUNT_NAME=yourstorageaccount
AZURE_STORAGE_ACCOUNT_KEY=your-account-key

# OR Method 3: Entra ID (Production - Recommended)
# AZURE_TENANT_ID=your-tenant-id
# AZURE_CLIENT_ID=your-client-id
# AZURE_CLIENT_SECRET=your-client-secret

# OR Method 4: Managed Identity (Azure App Service)
# Uncomment when deploying to Azure App Service
# AZURE_USE_MANAGED_IDENTITY=true

# Container Names
AZURE_PUBLIC_CONTAINER=profiles
AZURE_PRIVATE_CONTAINER=documents
AZURE_RECORDINGS_CONTAINER=recordings

# CDN Configuration
AZURE_CDN_ENDPOINT=https://your-cdn.azureedge.net
```

---

## 6. Security Best Practices

### 6.1 Access Keys vs SAS

| Method | Pros | Cons | Use Case |
|--------|------|------|----------|
| **Access Keys** | Simple setup | Full account access if leaked | Development only |
| **SAS Tokens** | Granular, time-limited | More complex | Production APIs |
| **Entra ID** | No secrets, RBAC | Azure setup required | Production (Recommended) |

### 6.2 SAS Best Practices

From Microsoft recommendations:

1. **Always use HTTPS** for SAS distribution
2. **Use User Delegation SAS** when possible (Entra ID-based)
3. **Set short expiration times** for ad hoc SAS (< 1 hour recommended)
4. **Use stored access policies** for service SAS (revocable)
5. **Grant least privileges** — read-only when possible
6. **Have revocation plan** in case of compromise

**Source:** [SAS Best Practices - Microsoft Learn](https://learn.microsoft.com/en-us/azure/storage/common/storage-sas-overview)

### 6.3 Container Security Checklist

- [ ] Use **Private** access level for sensitive containers
- [ ] Enable **soft delete** (7-30 days retention)
- [ ] Enable **blob versioning** for audit trail
- [ ] Use **HTTPS only** (disable HTTP in storage account)
- [ ] Implement **network restrictions** (IP allowlist if needed)
- [ ] Enable **Azure Defender** for threat detection
- [ ] Use **Entra ID** authentication in production
- [ ] Rotate access keys regularly
- [ ] Monitor access with **Azure Monitor** logs

### 6.4 Laravel Security Implementation

```php
<?php

// routes/web.php
use Illuminate\Support\Facades\Storage;

// Signed route for secure downloads
Route::get('/download/{path}', function ($path) {
    // Verify the path is within allowed directory
    $path = urldecode($path);
    
    if (!Storage::disk('azure-private')->exists($path)) {
        abort(404);
    }
    
    // Generate short-lived signed URL (5 minutes)
    $url = Storage::disk('azure-private')->temporaryUrl(
        $path,
        now()->addMinutes(5)
    );
    
    return redirect($url);
})->middleware('auth')->name('secure-download');
```

---

## 7. Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| `No such host` error | Check connection string or network access settings |
| `403 Forbidden` | Verify container access level or SAS permissions |
| `Container not found` | Ensure container name matches exactly (lowercase) |
| Temporary URL not working | Ensure container allows public access for URL generation |

### Debug Mode

```php
// In development, dump storage info
Storage::disk('azure-public')->getDriver()->getAdapter()->getContainerClient();
```

---

## 8. Quick Reference

### Installation Command
```bash
composer require azure-oss/storage-blob-laravel
```

### Basic Usage Pattern
```php
// Upload
Storage::disk('azure-public')->put('path/file.jpg', $contents);

// Download
$contents = Storage::disk('azure-public')->get('path/file.jpg');

// Public URL
$url = Storage::disk('azure-public')->url('path/file.jpg');

// Private URL (signed)
$url = Storage::disk('azure-private')->temporaryUrl('path/file.pdf', now()->addMinutes(60));

// Delete
Storage::disk('azure-public')->delete('path/file.jpg');
```

---

## References

1. [Azure Blob Storage Introduction](https://learn.microsoft.com/en-us/azure/storage/blobs/storage-blobs-introduction)
2. [Create Storage Account](https://learn.microsoft.com/en-us/azure/storage/common/storage-account-create)
3. [Access Tiers Overview](https://learn.microsoft.com/en-us/azure/storage/blobs/access-tiers-overview)
4. [SAS Overview](https://learn.microsoft.com/en-us/azure/storage/common/storage-sas-overview)
5. [Azure CDN Overview](https://learn.microsoft.com/en-us/azure/cdn/cdn-overview)
6. [azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel)
7. [azure-oss/storage-blob-flysystem](https://packagist.org/packages/azure-oss/storage-blob-flysystem)
8. [Laravel 11.x Filesystem Docs](https://laravel.com/docs/11.x/filesystem)
