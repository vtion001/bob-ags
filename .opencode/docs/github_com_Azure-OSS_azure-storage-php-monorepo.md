# GitHub - Azure-OSS/azure-storage-php-monorepo: Azure Storage PHP SDK · GitHub

> Source: https://github.com/Azure-OSS/azure-storage-php-monorepo
> Cached: 2026-04-16T20:58:35.162Z

---

# Azure Storage PHP Monorepo

[](#azure-storage-php-monorepo)
Community-driven PHP SDKs for Azure, because Microsoft won't.

In November 2023, Microsoft officially archived their [Azure SDK for PHP](https://github.com/Azure/azure-sdk-for-php) and stopped maintaining PHP integrations for most Azure services. No migration path, no replacement — just a repository marked read-only.

We picked up where they left off.

[](https://camo.githubusercontent.com/4231ff59e3078111b9723a49fe9b1efdea82c0f3cb6242f7a3485c99a933f9cc/68747470733a2f2f617a7572652d6f73732e6769746875622e696f2f696d672f6c6f676f2e737667)

## Documentation

[](#documentation)
You can read the documentation [here](https://azure-oss.github.io).

## Packages

[](#packages)
This monorepo contains the following packages:

### [azure-oss/storage](https://packagist.org/packages/azure-oss/storage) [](https://camo.githubusercontent.com/6c13dfa21eb44423f04e526a56c75108f45ae1b31c28d222f863616c48d75242/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f762f617a7572652d6f73732f73746f72616765) [](https://camo.githubusercontent.com/7912c891169f6b6e901a06b4b34bdc2fafbf3c569543f9d68134613488fc7fd9/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f72616765)

[](#azure-ossstorage--)
The core Azure Blob Storage PHP SDK. This is the main package that provides the core functionality for interacting with Azure Blob Storage.

### [azure-oss/storage-queue](https://packagist.org/packages/azure-oss/storage-queue) [](https://camo.githubusercontent.com/9302eafe4b31cb2787152ca2d6d9cd1ed812f4fbe3faf96b7b78e7c11a0a83e3/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f762f617a7572652d6f73732f73746f726167652d7175657565) [](https://camo.githubusercontent.com/603cf9d2f39843e8cdbc4a079c8b99b82bde0006bc723043b6a322a582f60733/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d7175657565)

[](#azure-ossstorage-queue--)
Azure Storage Queue PHP SDK. Provides functionality for interacting with Azure Storage Queues, including queue and message operations.

### [azure-oss/storage-blob-flysystem](https://packagist.org/packages/azure-oss/storage-blob-flysystem) [](https://camo.githubusercontent.com/5868e7b1116640b043e48fe516261960050b1f41deafa79d4705769cf1783933/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f762f617a7572652d6f73732f73746f726167652d626c6f622d666c7973797374656d) [](https://camo.githubusercontent.com/6b4f681dda3cabb175b00ef63a78e52a86900ee8a0c72fcb9e02581a23e5316c/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d626c6f622d666c7973797374656d)

[](#azure-ossstorage-blob-flysystem--)
Flysystem adapter for Azure Storage PHP. Provides integration with the [Flysystem](https://flysystem.thephpleague.com/) filesystem abstraction library.

### [azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel) [](https://camo.githubusercontent.com/4e5d122e7a50cf4abc0d6ecbf09fb92150b2714178af226c65a724e89df170a2/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f762f617a7572652d6f73732f73746f726167652d626c6f622d6c61726176656c) [](https://camo.githubusercontent.com/775d6da94256b6777ac7730929f4a2cc7aa847b21435e0545e4b65b343de0619/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d626c6f622d6c61726176656c)

[](#azure-ossstorage-blob-laravel--)
Laravel filesystem driver for Azure Storage Blob. Provides seamless integration with Laravel's filesystem abstraction.

### [azure-oss/storage-queue-laravel](https://packagist.org/packages/azure-oss/storage-queue-laravel) [](https://camo.githubusercontent.com/73c0a78014e339610f1059dbf9ee4ac2998bd3444490b22ad2b9da847a27840b/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f762f617a7572652d6f73732f73746f726167652d71756575652d6c61726176656c) [](https://camo.githubusercontent.com/fef3afef7bac10d323dc9f4d1c421318d8e94570acc0c20e734f05ff06e7a561/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d71756575652d6c61726176656c)

[](#azure-ossstorage-queue-laravel--)
Laravel Queue connector for Azure Storage Queues. Provides integration with Laravel's queue system.

### [azure-oss/storage-common](https://packagist.org/packages/azure-oss/storage-common) [](https://camo.githubusercontent.com/cd385d69e08de52fa5891811803f1e83fd302a8841ed1b1f35fa9976cbf29056/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f762f617a7572652d6f73732f73746f726167652d636f6d6d6f6e) [](https://camo.githubusercontent.com/d05ab9c8f5a617835c3367fd80a4f0991a2a1fae812429ec9cf549607ce4221b/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d636f6d6d6f6e)

[](#azure-ossstorage-common--)
Common utilities and shared components for the Azure Storage PHP SDK. This package contains reusable functionality used across the Azure Storage Blob, Queue, and related integrations.

## Other packages by us

[](#other-packages-by-us)
### [azure-oss/identity](https://packagist.org/packages/azure-oss/identity) [](https://camo.githubusercontent.com/545cab83f3fc5dbd55f71e764705749b8df50326bdb83223e2fb11373d110591/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f762f617a7572652d6f73732f6964656e74697479) [](https://camo.githubusercontent.com/cfc16e558806eb6db1dff87cad3cf4df1fcede23cddcebf68432bbfa4792a453/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f6964656e74697479)

[](#azure-ossidentity--)
Azure Active Directory (Entra ID) token authentication.

## License

[](#license)
This project is released under the MIT License. See [LICENSE](/Azure-OSS/azure-storage-php-monorepo/blob/main/LICENSE) for details.