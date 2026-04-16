# Research: Azure App Service Deployment for Laravel 12

**Date:** 2026-04-17
**Source:** [Microsoft Learn - Configure PHP App](https://learn.microsoft.com/en-us/azure/app-service/configure-language-php), [App Settings Reference](https://learn.microsoft.com/en-us/azure/app-service/reference-app-settings), [Run from Package](https://learn.microsoft.com/en-us/azure/app-service/deploy-run-package), [Tutorial: PHP MySQL Redis](https://learn.microsoft.com/en-us/azure/app-service/tutorial-php-mysql-app)
**Confidence:** HIGH
**Version:** Laravel 12, PHP 8.2+, Azure App Service Linux

---

## PHP Version Support

### Azure App Service Linux PHP Support
- **PHP on Windows reached end of support November 2022** — PHP is only supported on App Service Linux
- Azure CLI to check supported versions:
```bash
az webapp list-runtimes --os linux | grep PHP
```
- **Recommended: PHP 8.2+** (Laravel 12 requires PHP 8.2 minimum)
- To set PHP version:
```bash
az webapp config set --resource-group <rg> --name <app> --linux-fx-version "PHP|8.2"
```

---

## Laravel Configuration

### Document Root
Laravel uses `public/` as site root. Set via Azure CLI:
```bash
az resource update --name web --resource-group <rg> --namespace Microsoft.Web --resource-type config --parent sites/<app> --set properties.virtualApplications[0].physicalPath="site\\wwwroot\\public" --api-version 2015-06-01
```

### Nginx Configuration for Laravel
The default PHP image uses NGINX. Sample config with Laravel's `public/` root:
```nginx
server {
    listen 8080;
    listen [::]:8080;
    root /home/site/wwwroot/public;
    location / {            
        index index.php index.html index.htm hostingstart.html;
        try_files $uri $uri/ /index.php?$args;
    }
    location ~ \\.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Custom Startup Command
```bash
az webapp config set --resource-group <rg> --name <app> --startup-file "cp /home/site/wwwroot/default /etc/nginx/sites-available/default && service nginx reload"
```

---

## Deployment Methods

### 1. Git (Local/GitHub Actions) — RECOMMENDED
```bash
# Local Git
az webapp deploy --resource-group <rg> --name <app> --src-path <filename>.zip

# GitHub Actions (via Azure Portal Deployment Center)
# App Service auto-generates workflow file in .github/workflows/
```

### 2. ZIP Deploy with WEBSITE_RUN_FROM_PACKAGE — RECOMMENDED for Laravel
```bash
az webapp config appsettings set --resource-group <rg> --name <app> --settings WEBSITE_RUN_FROM_PACKAGE="1"

# Then deploy:
az webapp deploy --resource-group <rg> --name <app> --src-path app.zip
```

**Benefits:**
- Eliminates file lock conflicts
- Ensures only fully-deployed apps run
- Improves cold-start performance
- Zero-downtime deployments

### 3. Docker Container
For custom containers with specific requirements.

---

## Required App Settings

```bash
# Core Laravel
az webapp config appsettings set --name <app> --resource-group <rg> --settings \
  APP_KEY="base64:<your-generated-key>" \
  APP_ENV="production" \
  APP_DEBUG="false" \
  APP_URL="https://<app>.azurewebsites.net" \
  LOG_CHANNEL="stderr" \
  LOG_LEVEL="error"

# Database (if using Azure Database for MySQL)
az webapp config appsettings set --name <app> --resource-group <rg> --settings \
  DB_CONNECTION="mysql" \
  DB_HOST="<mysql-server>.mysql.database.azure.com" \
  DB_PORT="3306" \
  DB_DATABASE="<database>" \
  DB_USERNAME="<username>" \
  DB_PASSWORD="<password>"

# Cache/Queue
az webapp config appsettings set --name <app> --resource-group <rg> --settings \
  CACHE_DRIVER="redis" \
  QUEUE_CONNECTION="database" \
  SESSION_DRIVER="database"

# Deployment
az webapp config appsettings set --name <app> --resource-group <rg> --settings \
  WEBSITE_RUN_FROM_PACKAGE="1" \
  SCM_DO_BUILD_DURING_DEPLOYMENT="true"
```

---

## Storage & Paths

- **Azure uses `/home` for persistent storage** — Configure Laravel storage:
```php
// config/filesystems.php
'public' => [
    'driver' => 'local',
    'root' => env('APP_STORAGE_PATH', '/home/site/wwwroot/storage/app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

- **Environment variables:**
  - `HOME` = `/home/site/wwwroot`
  - `SERVER_PORT` = `8080`

---

## Performance Settings

```bash
# PHP worker processes via startup command
az webapp config set --resource-group <rg> --name <app> --startup-file "pm2 start /home/site/wwwroot/artisan --max-memory-restart=512M"

# Or via custom startup script with:
# - PHP_FPM children settings
# - Memory limits based on App Service plan
```

---

## GitHub Actions Example

Azure generates this workflow automatically. Key sections:
```yaml
- uses: azure/webapps-deploy@v3
  with:
    publish-profile: ${{ secrets.AZUREAPPSERVICE_PUBLISHPROFILE_xxx }}
    package: .
```

---

## Key References

1. [Configure PHP App Service Linux](https://learn.microsoft.com/en-us/azure/app-service/configure-language-php)
2. [App Settings Reference](https://learn.microsoft.com/en-us/azure/app-service/reference-app-settings)
3. [Run from ZIP Package](https://learn.microsoft.com/en-us/azure/app-service/deploy-run-package)
4. [PHP MySQL Redis Tutorial](https://learn.microsoft.com/en-us/azure/app-service/tutorial-php-mysql-app)
5. [Continuous Deployment](https://learn.microsoft.com/en-us/azure/app-service/deploy-continuous-deployment)
