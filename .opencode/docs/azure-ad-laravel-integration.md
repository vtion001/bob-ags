# Research: Azure AD / Microsoft Entra ID Authentication Integration with Laravel 12

**Date:** 2026-04-17
**Confidence:** HIGH (Official Microsoft + Laravel documentation)
**Project:** bob-ags (Laravel 12 + Breeze + Tailwind)

---

## Project Context (Detected)

| Aspect | Value |
|--------|-------|
| Framework | Laravel 12.x |
| PHP | ^8.2 |
| Auth Package | Laravel Breeze |
| Auth Guard | Session-based (`web`) |
| User Model | `App\Models\User` (Eloquent) |
| Session Driver | Database (`sessions` table) |
| User Fields | `name`, `email`, `password`, `role`, `ctm_agent_id` |
| Roles | admin, qa, viewer, supervisor |
| Existing Migrations | `0001_01_01_000000_create_users_table.php`, `2024_01_01_000001_add_role_and_ctm_to_users_table.php` |

---

## Option 1: SocialiteProviders / Microsoft Azure (Manual OAuth)

### Overview
Full control via `socialiteproviders/microsoft-azure` package. App handles the entire OAuth flow directly with Microsoft Entra ID.

**Best for:** Maximum control, non-Azure deployments, multi-cloud, on-prem

### Step 1: Install Package

```bash
composer require laravel/socialite
composer require socialiteproviders/microsoft-azure
```

### Step 2: Azure App Registration (Microsoft Entra ID)

1. Go to **[Microsoft Entra Admin Center](https://entra.microsoft.com)** → **App registrations** → **New registration**
2. **Name:** `bob-ags` (or your app name)
3. **Supported account types:**
   - **Accounts in this organizational directory only** — Single tenant (your org only)
   - **Accounts in any organizational directory** — Multitenant SaaS
   - **Accounts in any organizational directory and personal Microsoft accounts** — Widest reach
4. **Register** → Copy **Application (client) ID**
5. Go to **Certificates & secrets** → **Client secrets** → **New client secret**
   - Copy the **Value** (shown only once!)
6. Go to **Authentication** → **Add a platform** → **Web**
   - Add redirect URI: `https://yourdomain.com/auth/callback` (for production)
   - For local dev: `http://localhost:8000/auth/callback`
7. Copy **Directory (tenant) ID** from the Overview page

### Step 3: Environment Variables (.env)

```env
# Azure AD / Microsoft Entra ID
AZURE_CLIENT_ID=your-application-client-id
AZURE_CLIENT_SECRET=your-client-secret-value
AZURE_TENANT_ID=your-directory-tenant-id
AZURE_REDIRECT_URI=https://yourdomain.com/auth/callback
```

### Step 4: config/services.php

```php
'azure' => [
    'client_id' => env('AZURE_CLIENT_ID'),
    'client_secret' => env('AZURE_CLIENT_SECRET'),
    'redirect' => env('AZURE_REDIRECT_URI'),
    'tenant' => env('AZURE_TENANT_ID'),
],
```

### Step 5: Event Listener Registration (Laravel 12)

In `bootstrap/app.php` or `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;

Event::listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event) {
    $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
});
```

### Step 6: Routes (routes/auth.php)

```php
use Laravel\Socialite\Facades\Socialite;

// Add to existing auth routes (routes/auth.php)

Route::middleware('guest')->group(function () {
    // ... existing Breeze routes ...

    // Azure AD Login
    Route::get('auth/azure', function () {
        return Socialite::driver('azure')->redirect();
    })->name('auth.azure');

    Route::get('auth/azure/callback', function () {
        $azureUser = Socialite::driver('azure')->stateless()->user();

        // Find or create user
        $user = \App\Models\User::updateOrCreate(
            ['email' => $azureUser->getEmail()],
            [
                'name' => $azureUser->getName() ?? $azureUser->getNickName(),
                // Password is nullable for SSO-only users
                'password' => null,
            ]
        );

        Auth::login($user);
        return redirect()->intended(route('dashboard'));
    })->name('auth.azure.callback');
});
```

### Step 7: Azure Logout

```php
Route::post('logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Optional: Also log out of Azure
    $azureLogoutUrl = 'https://login.microsoftonline.com/' . env('AZURE_TENANT_ID') 
        . '/oauth2/v2.0/logout'
        . '?post_logout_redirect_uri=' . urlencode(route('login'));
    
    return redirect($azureLogoutUrl);
})->name('logout');
```

### Step 8: User Model Update

```php
// Make password nullable for SSO-only users
$table->string('password')->nullable();

// Optional: Add Azure-specific fields
$table->string('azure_id')->nullable()->unique();
```

### Step 9: Migration for Azure ID

```php
php artisan make:migration add_azure_id_to_users_table
```

```php
// In the migration
Schema::table('users', function (Blueprint $table) {
    $table->string('azure_id')->nullable()->unique()->after('ctm_agent_id');
});
```

### Tenant Values (Advanced)

| Scenario | `AZURE_TENANT_ID` |
|----------|-------------------|
| Single tenant (your org) | `your-tenant-id` |
| Multitenant (any org) | `organizations` |
| Multitenant + personal accounts | `common` |
| Personal accounts only | `consumers` |

---

## Option 2: Azure App Service EasyAuth (Built-in Authentication)

### Overview
Azure App Service's built-in authentication feature ("EasyAuth") handles OAuth at the platform level. The app receives pre-authenticated requests with identity in HTTP headers.

**Best for:** Azure App Service deployments, minimal code changes, quick setup

### Architecture

```
User → Azure App Service (EasyAuth) → X-MS-CLIENT-PRINCIPAL headers → Laravel app
```

### Step 1: Enable EasyAuth in Azure Portal

1. Go to your **Azure App Service** → **Settings** → **Authentication**
2. Select **Add identity provider** → **Microsoft**
3. Choose **Workforce configuration** (employees) or **External configuration** (customers)
4. **App registration:** Select **Create new app registration**
5. Configure:
   - **Supported account types:** As needed
   - **Client secret:** Auto-generated (saved as `MICROSOFT_PROVIDER_AUTHENTICATION_SECRET`)
6. **Unauthenticated requests:** Choose action (recommended: **Sign in** for protected routes)

### Step 2: Configure Redirect URIs in Entra ID

Go to **Microsoft Entra Admin Center** → **App registrations** → Select your app → **Authentication**
Add redirect URI: `https://yourapp.azurewebsites.net/.auth/login/aad/callback`

### Step 3: Laravel Reads EasyAuth Headers

EasyAuth injects these headers on every authenticated request:

| Header | Description |
|--------|-------------|
| `X-MS-CLIENT-PRINCIPAL` | Base64-encoded JSON with all claims |
| `X-MS-CLIENT-PRINCIPAL-ID` | User's unique identifier |
| `X-MS-CLIENT-PRINCIPAL-NAME` | Display name (email or UPN) |
| `X-MS-CLIENT-PRINCIPAL-IDP` | Identity provider name (`aad`) |
| `X-MS-TOKEN-AAD-ACCESS-TOKEN` | Azure AD access token |
| `X-MS-TOKEN-AAD-ID-TOKEN` | Azure AD ID token |

### Step 4: Create Middleware to Read EasyAuth Headers

```php
// app/Http/Middleware/HandleEasyAuth.php
namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleEasyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only process if EasyAuth headers are present and user not already authenticated
        if (!Auth::check() && $request->hasHeader('X-MS-CLIENT-PRINCIPAL')) {
            $principal = $this->decodePrincipal($request->header('X-MS-CLIENT-PRINCIPAL'));
            
            if ($principal) {
                $claims = collect($principal['claims'] ?? []);
                
                $oid = $claims->firstWhere('typ', 'oid')['val'] 
                    ?? $claims->firstWhere('typ', 'http://schemas.microsoft.com/identity/claims/objectidentifier')['val']
                    ?? null;
                
                $email = $claims->firstWhere('typ', 'email')['val']
                    ?? $claims->firstWhere('typ', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress')['val']
                    ?? $claims->firstWhere('typ', 'preferred_username')['val']
                    ?? null;
                
                $name = $claims->firstWhere('typ', 'name')['val']
                    ?? $claims->firstWhere('typ', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname')['val']
                    ?? $email
                    ?? 'Azure User';

                if ($email) {
                    $user = User::updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => $name,
                            'azure_id' => $oid,
                            'password' => null, // SSO-only user
                        ]
                    );

                    Auth::login($user);
                }
            }
        }

        return $next($request);
    }

    private function decodePrincipal(?string $header): ?array
    {
        if (!$header) {
            return null;
        }

        try {
            $decoded = base64_decode($header);
            return json_decode($decoded, true);
        } catch (\Exception $e) {
            return null;
        }
    }
}
```

### Step 5: Register Middleware (bootstrap/app.php)

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'easyauth' => \App\Http\Middleware\HandleEasyAuth::class,
        ]);
        
        // Apply EasyAuth middleware globally for authenticated routes
        $middleware->web(append: [
            \App\Http\Middleware\HandleEasyAuth::class,
        ]);
    })
    // ...
```

### Step 6: User Model Updates

```php
// database/migrations/xxxx_add_azure_fields_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->string('azure_id')->nullable()->unique()->after('ctm_agent_id');
    $table->string('password')->nullable()->change();
});
```

```php
// app/Models/User.php
protected $fillable = [
    'name',
    'email',
    'password',        // nullable for SSO-only users
    'role',
    'ctm_agent_id',
    'azure_id',       // Microsoft Entra Object ID
];
```

### Step 7: EasyAuth File-Based Config (Optional)

Place `auth.json` at project root (deployed to `D:\home\site\wwwroot\`):

```json
{
    "platform": {
        "enabled": true
    },
    "globalValidation": {
        "unauthenticatedClientAction": "RedirectToLoginPage",
        "redirectToProvider": "aad",
        "excludedPaths": [
            "/",
            "/.auth/login",
            "/webhooks/*"
        ]
    },
    "httpSettings": {
        "requireHttps": true
    },
    "login": {
        "tokenStore": {
            "enabled": true
        }
    },
    "identityProviders": {
        "azureActiveDirectory": {
            "enabled": true,
            "registration": {
                "openIdIssuer": "https://login.microsoftonline.com/{tenant-id}/v2.0",
                "clientId": "${AZURE_CLIENT_ID}",
                "clientSecretSettingName": "MICROSOFT_PROVIDER_AUTHENTICATION_SECRET"
            },
            "validation": {
                "allowedAudiences": [
                    "api://{client-id}"
                ]
            }
        }
    }
}
```

---

## Comparison: Socialite vs EasyAuth

| Criteria | Socialite (Manual OAuth) | EasyAuth (Built-in) |
|----------|--------------------------|---------------------|
| **Complexity** | More setup (package + routes) | Minimal setup |
| **Control** | Full (you handle tokens, flows) | Limited (Azure handles) |
| **Deployment** | Any server / cloud | Azure App Service only |
| **Token Access** | Full access to tokens | Limited (via headers) |
| **Logout** | Manual (must build logout flow) | Built-in `/.auth/logout` |
| **User Migration** | Manual via callback | Via middleware |
| **Testing** | Easier to mock/test | Harder (depends on Azure) |
| **Multi-provider** | Easy to add multiple | Limited (built-in only) |
| **Custom Claims** | Full access | Limited to forwarded headers |
| **Session Management** | Your session driver | App Service cookie |
| **Existing Auth** | Must replace/modify Breeze | Coexists (use headers) |

---

## Recommended Approach for bob-ags

**Recommended: Option 1 (SocialiteProviders / Manual OAuth)** for maximum flexibility, because:

1. **Deployment flexibility** — Works on any hosting (Azure, AWS, on-prem)
2. **Full token control** — Access to Azure AD tokens for Graph API calls
3. **Custom UX** — Full control over login/logout flows
4. **Testability** — Can mock Socialite in tests
5. **No EasyAuth lock-in** — Not tied to Azure platform

**Use Option 2 (EasyAuth) if:**
- Deploying **exclusively** on Azure App Service
- Want **minimum code changes**
- Don't need Azure AD tokens in your app
- Accept platform lock-in

---

## Full Implementation Checklist (Option 1)

### Composer Install
```bash
composer require laravel/socialite socialiteproviders/microsoft-azure
```

### .env Additions
```env
AZURE_CLIENT_ID=
AZURE_CLIENT_SECRET=
AZURE_TENANT_ID=
AZURE_REDIRECT_URI=http://localhost:8000/auth/azure/callback
```

### config/services.php Additions
```php
'azure' => [
    'client_id' => env('AZURE_CLIENT_ID'),
    'client_secret' => env('AZURE_CLIENT_SECRET'),
    'redirect' => env('AZURE_REDIRECT_URI'),
    'tenant' => env('AZURE_TENANT_ID'),
],
```

### bootstrap/app.php — Event Listener
```php
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteWasCalled;

Event::listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event) {
    $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
});
```

### Migration
```bash
php artisan make:migration add_azure_id_to_users_table
```

### routes/auth.php — Add Azure Routes
```php
Route::get('auth/azure', fn() => \Laravel\Socialite\Facades\Socialite::driver('azure')->redirect())
    ->name('auth.azure');

Route::get('auth/azure/callback', function () {
    $azureUser = \Laravel\Socialite\Facades\Socialite::driver('azure')->stateless()->user();
    
    $user = \App\Models\User::updateOrCreate(
        ['email' => $azureUser->getEmail()],
        [
            'name' => $azureUser->getName() ?? $azureUser->getNickName(),
            'password' => null,
        ]
    );

    \Illuminate\Support\Facades\Auth::login($user);
    return redirect()->intended(route('dashboard'));
})->name('auth.azure.callback');
```

### app/Models/User.php Updates
```php
protected $fillable = [
    'name', 'email', 'password', 'role', 'ctm_agent_id',
];

// Add azure_id field via migration
$table->string('azure_id')->nullable()->unique()->after('ctm_agent_id');

// Make password nullable
$table->string('password')->nullable()->change();
```

### Login View Update (resources/views/auth/login.blade.php)
Add Azure SSO button alongside existing email/password form.

---

## Sources

1. [Microsoft Entra ID - Register an Application](https://learn.microsoft.com/en-us/entra/identity-platform/quickstart-register-app) — Official MS docs (2025-04-08)
2. [Microsoft - Add Redirect URIs](https://learn.microsoft.com/en-us/entra/identity-platform/how-to-add-redirect-uri) — Official MS docs (2025-05-29)
3. [Microsoft - Add Credentials](https://learn.microsoft.com/en-us/entra/identity-platform/how-to-add-credentials) — Official MS docs (2025-04-08)
4. [Azure App Service - Authentication Overview (EasyAuth)](https://learn.microsoft.com/en-us/azure/app-service/overview-authentication-authorization) — Official MS docs
5. [Azure App Service - Configure Microsoft Entra Authentication](https://learn.microsoft.com/en-us/azure/app-service/configure-authentication-provider-aad) — Official MS docs
6. [Azure App Service - File-Based Configuration](https://learn.microsoft.com/en-us/azure/app-service/configure-authentication-file-based) — Official MS docs (2026-03-10)
7. [Azure App Service - User Identities & X-MS-CLIENT-PRINCIPAL](https://learn.microsoft.com/en-us/azure/app-service/configure-authentication-user-identities) — Official MS docs (2025-07-03)
8. [SocialiteProviders - Microsoft Azure](https://socialiteproviders.com/Microsoft-Azure/) — Official package docs
9. [Laravel Socialite](https://laravel.com/docs/12.x/socialite) — Official Laravel docs (Laravel 12.x)
