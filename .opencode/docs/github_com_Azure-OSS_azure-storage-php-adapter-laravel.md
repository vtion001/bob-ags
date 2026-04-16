# GitHub - Azure-OSS/azure-storage-php-adapter-laravel: [READ ONLY] Subtree split of the AzureOss\Storage\BlobLaravel component · GitHub

> Source: https://github.com/Azure-OSS/azure-storage-php-adapter-laravel
> Cached: 2026-04-16T20:58:13.607Z

---

# Azure Storage Blob filesystem driver for Laravel

[](#azure-storage-blob-filesystem-driver-for-laravel)
[](https://packagist.org/packages/azure-oss/storage-blob-laravel)
[](https://packagist.org/packages/azure-oss/storage-blob-laravel)
Community-driven PHP SDKs for Azure, because Microsoft won't.

In November 2023, Microsoft officially archived their [Azure SDK for PHP](https://github.com/Azure/azure-sdk-for-php) and stopped maintaining PHP integrations for most Azure services. No migration path, no replacement — just a repository marked read-only.

We picked up where they left off.

[](https://camo.githubusercontent.com/4231ff59e3078111b9723a49fe9b1efdea82c0f3cb6242f7a3485c99a933f9cc/68747470733a2f2f617a7572652d6f73732e6769746875622e696f2f696d672f6c6f676f2e737667)

Our other packages:

**[azure-oss/storage](https://packagist.org/packages/azure-oss/storage)** – Azure Blob Storage SDK

[](https://camo.githubusercontent.com/7912c891169f6b6e901a06b4b34bdc2fafbf3c569543f9d68134613488fc7fd9/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f72616765)

**[azure-oss/storage-blob-flysystem](https://packagist.org/packages/azure-oss/storage-blob-flysystem)** – Flysystem adapter

[](https://camo.githubusercontent.com/6b4f681dda3cabb175b00ef63a78e52a86900ee8a0c72fcb9e02581a23e5316c/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d626c6f622d666c7973797374656d)

**[azure-oss/storage-queue](https://packagist.org/packages/azure-oss/storage-queue)** – Azure Storage Queue SDK

[](https://camo.githubusercontent.com/603cf9d2f39843e8cdbc4a079c8b99b82bde0006bc723043b6a322a582f60733/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d7175657565)

**[azure-oss/storage-queue-laravel](https://packagist.org/packages/azure-oss/storage-queue-laravel)** – Laravel Queue connector

[](https://camo.githubusercontent.com/fef3afef7bac10d323dc9f4d1c421318d8e94570acc0c20e734f05ff06e7a561/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d71756575652d6c61726176656c)

## Install

[](#install)
composer require azure-oss/storage-blob-laravel
## Documentation

[](#documentation)
You can read the documentation [here](https://azure-oss.github.io/category/storage-blob-laravel).

## Quickstart

[](#quickstart)
# config/filesystems.php

'azure' => [
    'driver' => 'azure-storage-blob',
    'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
    'container' => env('AZURE_STORAGE_CONTAINER'),
],
Besides shared key via connection string, this driver supports additional authentication methods (like Entra ID / token-based credentials, managed identity, workload identity, and shared key via account key). See the docs for configuration examples: [https://azure-oss.github.io/category/storage-blob-laravel/installation](https://azure-oss.github.io/category/storage-blob-laravel/installation)

## License

[](#license)
This project is released under the MIT License. See [LICENSE](https://github.com/Azure-OSS/azure-storage-php-monorepo/blob/02759360186be8d2d04bd1e9b2aba3839b6d39dc/LICENSE) for details.