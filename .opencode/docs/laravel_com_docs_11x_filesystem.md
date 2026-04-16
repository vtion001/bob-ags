# File Storage | Laravel 11.x - The clean stack for Artisans and agents

> Source: https://laravel.com/docs/11.x/filesystem
> Cached: 2026-04-16T20:57:36.014Z

---

- [Home](/)

⌘KVersion MasterVersion 13.xVersion 12.xVersion 11.xVersion 10.xVersion 9.xVersion 8.xVersion 7.xVersion 6.xVersion 5.8Version 5.7Version 5.6Version 5.5Version 5.4Version 5.3Version 5.2Version 5.1Version 5.0Version 4.2vMasterv13.xv12.xv11.xv10.xv9.xv8.xv7.xv6.xv5.8v5.7v5.6v5.5v5.4v5.3v5.2v5.1v5.0v4.2Switch to light mode[Skip to content](#main-content)
## Prologue

- [Release Notes](/docs/11.x/releases)
- [Upgrade Guide](/docs/11.x/upgrade)
- [Contribution Guide](/docs/11.x/contributions)

## Getting Started

- [Installation](/docs/11.x/installation)
- [Configuration](/docs/11.x/configuration)
- [Directory Structure](/docs/11.x/structure)
- [Frontend](/docs/11.x/frontend)
- [Starter Kits](/docs/11.x/starter-kits)
- [Deployment](/docs/11.x/deployment)

## Architecture Concepts

- [Request Lifecycle](/docs/11.x/lifecycle)
- [Service Container](/docs/11.x/container)
- [Service Providers](/docs/11.x/providers)
- [Facades](/docs/11.x/facades)

## The Basics

- [Routing](/docs/11.x/routing)
- [Middleware](/docs/11.x/middleware)
- [CSRF Protection](/docs/11.x/csrf)
- [Controllers](/docs/11.x/controllers)
- [Requests](/docs/11.x/requests)
- [Responses](/docs/11.x/responses)
- [Views](/docs/11.x/views)
- [Blade Templates](/docs/11.x/blade)
- [Asset Bundling](/docs/11.x/vite)
- [URL Generation](/docs/11.x/urls)
- [Session](/docs/11.x/session)
- [Validation](/docs/11.x/validation)
- [Error Handling](/docs/11.x/errors)
- [Logging](/docs/11.x/logging)

## Digging Deeper

- [Artisan Console](/docs/11.x/artisan)
- [Broadcasting](/docs/11.x/broadcasting)
- [Cache](/docs/11.x/cache)
- [Collections](/docs/11.x/collections)
- [Concurrency](/docs/11.x/concurrency)
- [Context](/docs/11.x/context)
- [Contracts](/docs/11.x/contracts)
- [Events](/docs/11.x/events)
- [File Storage](/docs/11.x/filesystem)
- [Helpers](/docs/11.x/helpers)
- [HTTP Client](/docs/11.x/http-client)
- [Localization](/docs/11.x/localization)
- [Mail](/docs/11.x/mail)
- [Notifications](/docs/11.x/notifications)
- [Package Development](/docs/11.x/packages)
- [Processes](/docs/11.x/processes)
- [Queues](/docs/11.x/queues)
- [Rate Limiting](/docs/11.x/rate-limiting)
- [Strings](/docs/11.x/strings)
- [Task Scheduling](/docs/11.x/scheduling)

## Security

- [Authentication](/docs/11.x/authentication)
- [Authorization](/docs/11.x/authorization)
- [Email Verification](/docs/11.x/verification)
- [Encryption](/docs/11.x/encryption)
- [Hashing](/docs/11.x/hashing)
- [Password Reset](/docs/11.x/passwords)

## Database

- [Getting Started](/docs/11.x/database)
- [Query Builder](/docs/11.x/queries)
- [Pagination](/docs/11.x/pagination)
- [Migrations](/docs/11.x/migrations)
- [Seeding](/docs/11.x/seeding)
- [Redis](/docs/11.x/redis)
- [MongoDB](/docs/11.x/mongodb)

## Eloquent ORM

- [Getting Started](/docs/11.x/eloquent)
- [Relationships](/docs/11.x/eloquent-relationships)
- [Collections](/docs/11.x/eloquent-collections)
- [Mutators / Casts](/docs/11.x/eloquent-mutators)
- [API Resources](/docs/11.x/eloquent-resources)
- [Serialization](/docs/11.x/eloquent-serialization)
- [Factories](/docs/11.x/eloquent-factories)

## Testing

- [Getting Started](/docs/11.x/testing)
- [HTTP Tests](/docs/11.x/http-tests)
- [Console Tests](/docs/11.x/console-tests)
- [Browser Tests](/docs/11.x/dusk)
- [Database](/docs/11.x/database-testing)
- [Mocking](/docs/11.x/mocking)

## Packages

- [Breeze](/docs/11.x/starter-kits#laravel-breeze)
- [Cashier (Stripe)](/docs/11.x/billing)
- [Cashier (Paddle)](/docs/11.x/cashier-paddle)
- [Dusk](/docs/11.x/dusk)
- [Envoy](/docs/11.x/envoy)
- [Fortify](/docs/11.x/fortify)
- [Folio](/docs/11.x/folio)
- [Homestead](/docs/11.x/homestead)
- [Horizon](/docs/11.x/horizon)
- [Jetstream](https://jetstream.laravel.com/)
- [MCP](/docs/11.x/mcp)
- [Mix](/docs/11.x/mix)
- [Octane](/docs/11.x/octane)
- [Passport](/docs/11.x/passport)
- [Pennant](/docs/11.x/pennant)
- [Pint](/docs/11.x/pint)
- [Precognition](/docs/11.x/precognition)
- [Prompts](/docs/11.x/prompts)
- [Pulse](/docs/11.x/pulse)
- [Reverb](/docs/11.x/reverb)
- [Sail](/docs/11.x/sail)
- [Sanctum](/docs/11.x/sanctum)
- [Scout](/docs/11.x/scout)
- [Socialite](/docs/11.x/socialite)
- [Telescope](/docs/11.x/telescope)
- [Valet](/docs/11.x/valet)

## [API Documentation](https://api.laravel.com/docs/11.x/index.html)

## [Changelog](/docs/changelog)

> **WARNING** You&#x27;re browsing the documentation for an old version of Laravel. Consider upgrading your project to [Laravel 13.x](/docs/13.x/filesystem).

# File Storage

- [Introduction](#introduction)

[Configuration](#configuration)

- [The Local Driver](#the-local-driver)

- [The Public Disk](#the-public-disk)

- [Driver Prerequisites](#driver-prerequisites)

- [Scoped and Read-Only Filesystems](#scoped-and-read-only-filesystems)

- [Amazon S3 Compatible Filesystems](#amazon-s3-compatible-filesystems)

[Obtaining Disk Instances](#obtaining-disk-instances)

- [On-Demand Disks](#on-demand-disks)

[Retrieving Files](#retrieving-files)

- [Downloading Files](#downloading-files)

- [File URLs](#file-urls)

- [Temporary URLs](#temporary-urls)

- [File Metadata](#file-metadata)

[Storing Files](#storing-files)

- [Prepending and Appending To Files](#prepending-appending-to-files)

- [Copying and Moving Files](#copying-moving-files)

- [Automatic Streaming](#automatic-streaming)

- [File Uploads](#file-uploads)

- [File Visibility](#file-visibility)

- [Deleting Files](#deleting-files)

- [Directories](#directories)

- [Testing](#testing)

- [Custom Filesystems](#custom-filesystems)

## [Introduction](#introduction)

Laravel provides a powerful filesystem abstraction thanks to the wonderful [Flysystem](https://github.com/thephpleague/flysystem) PHP package by Frank de Jonge. The Laravel Flysystem integration provides simple drivers for working with local filesystems, SFTP, and Amazon S3. Even better, it's amazingly simple to switch between these storage options between your local development machine and production server as the API remains the same for each system.

## [Configuration](#configuration)

Laravel's filesystem configuration file is located at `config/filesystems.php`. Within this file, you may configure all of your filesystem "disks". Each disk represents a particular storage driver and storage location. Example configurations for each supported driver are included in the configuration file so you can modify the configuration to reflect your storage preferences and credentials.

The `local` driver interacts with files stored locally on the server running the Laravel application while the `s3` driver is used to write to Amazon's S3 cloud storage service.

    
        

    
    
You may configure as many disks as you like and may even have multiple disks that use the same driver.

### [The Local Driver](#the-local-driver)

When using the `local` driver, all file operations are relative to the `root` directory defined in your `filesystems` configuration file. By default, this value is set to the `storage/app/private` directory. Therefore, the following method would write to `storage/app/private/example.txt`:

```
1use Illuminate\Support\Facades\Storage;2 3Storage::disk('local')->put('example.txt', 'Contents');
use Illuminate\Support\Facades\Storage;

Storage::disk('local')->put('example.txt', 'Contents');
```

### [The Public Disk](#the-public-disk)

The `public` disk included in your application's `filesystems` configuration file is intended for files that are going to be publicly accessible. By default, the `public` disk uses the `local` driver and stores its files in `storage/app/public`.

If your `public` disk uses the `local` driver and you want to make these files accessible from the web, you should create a symbolic link from source directory `storage/app/public` to target directory `public/storage`:

To create the symbolic link, you may use the `storage:link` Artisan command:

```
1php artisan storage:link
php artisan storage:link
```

Once a file has been stored and the symbolic link has been created, you can create a URL to the files using the `asset` helper:

```
1echo asset('storage/file.txt');
echo asset('storage/file.txt');
```

You may configure additional symbolic links in your `filesystems` configuration file. Each of the configured links will be created when you run the `storage:link` command:

```
1'links' => [2    public_path('storage') => storage_path('app/public'),3    public_path('images') => storage_path('app/images'),4],
'links' => [
    public_path('storage') => storage_path('app/public'),
    public_path('images') => storage_path('app/images'),
],
```

The `storage:unlink` command may be used to destroy your configured symbolic links:

```
1php artisan storage:unlink
php artisan storage:unlink
```

### [Driver Prerequisites](#driver-prerequisites)

#### [S3 Driver Configuration](#s3-driver-configuration)

Before using the S3 driver, you will need to install the Flysystem S3 package via the Composer package manager:

```
1composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
```

An S3 disk configuration array is located in your `config/filesystems.php` configuration file. Typically, you should configure your S3 information and credentials using the following environment variables which are referenced by the `config/filesystems.php` configuration file:

```
1AWS_ACCESS_KEY_ID=<your-key-id>2AWS_SECRET_ACCESS_KEY=<your-secret-access-key>3AWS_DEFAULT_REGION=us-east-14AWS_BUCKET=<your-bucket-name>5AWS_USE_PATH_STYLE_ENDPOINT=false
AWS_ACCESS_KEY_ID=<your-key-id>
AWS_SECRET_ACCESS_KEY=<your-secret-access-key>
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=<your-bucket-name>
AWS_USE_PATH_STYLE_ENDPOINT=false
```

For convenience, these environment variables match the naming convention used by the AWS CLI.

#### [FTP Driver Configuration](#ftp-driver-configuration)

Before using the FTP driver, you will need to install the Flysystem FTP package via the Composer package manager:

```
1composer require league/flysystem-ftp "^3.0"
composer require league/flysystem-ftp "^3.0"
```

Laravel's Flysystem integrations work great with FTP; however, a sample configuration is not included with the framework's default `config/filesystems.php` configuration file. If you need to configure an FTP filesystem, you may use the configuration example below:

```
 1'ftp' => [ 2    'driver' => 'ftp', 3    'host' => env('FTP_HOST'), 4    'username' => env('FTP_USERNAME'), 5    'password' => env('FTP_PASSWORD'), 6  7    // Optional FTP Settings... 8    // 'port' => env('FTP_PORT', 21), 9    // 'root' => env('FTP_ROOT'),10    // 'passive' => true,11    // 'ssl' => true,12    // 'timeout' => 30,13],
'ftp' => [
    'driver' => 'ftp',
    'host' => env('FTP_HOST'),
    'username' => env('FTP_USERNAME'),
    'password' => env('FTP_PASSWORD'),

    // Optional FTP Settings...
    // 'port' => env('FTP_PORT', 21),
    // 'root' => env('FTP_ROOT'),
    // 'passive' => true,
    // 'ssl' => true,
    // 'timeout' => 30,
],
```

#### [SFTP Driver Configuration](#sftp-driver-configuration)

Before using the SFTP driver, you will need to install the Flysystem SFTP package via the Composer package manager:

```
1composer require league/flysystem-sftp-v3 "^3.0"
composer require league/flysystem-sftp-v3 "^3.0"
```

Laravel's Flysystem integrations work great with SFTP; however, a sample configuration is not included with the framework's default `config/filesystems.php` configuration file. If you need to configure an SFTP filesystem, you may use the configuration example below:

```
 1'sftp' => [ 2    'driver' => 'sftp', 3    'host' => env('SFTP_HOST'), 4  5    // Settings for basic authentication... 6    'username' => env('SFTP_USERNAME'), 7    'password' => env('SFTP_PASSWORD'), 8  9    // Settings for SSH key based authentication with encryption password...10    'privateKey' => env('SFTP_PRIVATE_KEY'),11    'passphrase' => env('SFTP_PASSPHRASE'),12 13    // Settings for file / directory permissions...14    'visibility' => 'private', // `private` = 0600, `public` = 064415    'directory_visibility' => 'private', // `private` = 0700, `public` = 075516 17    // Optional SFTP Settings...18    // 'hostFingerprint' => env('SFTP_HOST_FINGERPRINT'),19    // 'maxTries' => 4,20    // 'passphrase' => env('SFTP_PASSPHRASE'),21    // 'port' => env('SFTP_PORT', 22),22    // 'root' => env('SFTP_ROOT', ''),23    // 'timeout' => 30,24    // 'useAgent' => true,25],
'sftp' => [
    'driver' => 'sftp',
    'host' => env('SFTP_HOST'),

    // Settings for basic authentication...
    'username' => env('SFTP_USERNAME'),
    'password' => env('SFTP_PASSWORD'),

    // Settings for SSH key based authentication with encryption password...
    'privateKey' => env('SFTP_PRIVATE_KEY'),
    'passphrase' => env('SFTP_PASSPHRASE'),

    // Settings for file / directory permissions...
    'visibility' => 'private', // `private` = 0600, `public` = 0644
    'directory_visibility' => 'private', // `private` = 0700, `public` = 0755

    // Optional SFTP Settings...
    // 'hostFingerprint' => env('SFTP_HOST_FINGERPRINT'),
    // 'maxTries' => 4,
    // 'passphrase' => env('SFTP_PASSPHRASE'),
    // 'port' => env('SFTP_PORT', 22),
    // 'root' => env('SFTP_ROOT', ''),
    // 'timeout' => 30,
    // 'useAgent' => true,
],
```

### [Scoped and Read-Only Filesystems](#scoped-and-read-only-filesystems)

Scoped disks allow you to define a filesystem where all paths are automatically prefixed with a given path prefix. Before creating a scoped filesystem disk, you will need to install an additional Flysystem package via the Composer package manager:

```
1composer require league/flysystem-path-prefixing "^3.0"
composer require league/flysystem-path-prefixing "^3.0"
```

You may create a path scoped instance of any existing filesystem disk by defining a disk that utilizes the `scoped` driver. For example, you may create a disk which scopes your existing `s3` disk to a specific path prefix, and then every file operation using your scoped disk will utilize the specified prefix:

```
1's3-videos' => [2    'driver' => 'scoped',3    'disk' => 's3',4    'prefix' => 'path/to/videos',5],
's3-videos' => [
    'driver' => 'scoped',
    'disk' => 's3',
    'prefix' => 'path/to/videos',
],
```

"Read-only" disks allow you to create filesystem disks that do not allow write operations. Before using the `read-only` configuration option, you will need to install an additional Flysystem package via the Composer package manager:

```
1composer require league/flysystem-read-only "^3.0"
composer require league/flysystem-read-only "^3.0"
```

Next, you may include the `read-only` configuration option in one or more of your disk's configuration arrays:

```
1's3-videos' => [2    'driver' => 's3',3    // ...4    'read-only' => true,5],
's3-videos' => [
    'driver' => 's3',
    // ...
    'read-only' => true,
],
```

### [Amazon S3 Compatible Fil

... [Content truncated]