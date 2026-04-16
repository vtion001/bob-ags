# azure-oss/storage-blob-flysystem - Packagist.org

> Source: https://packagist.org/packages/azure-oss/storage-blob-flysystem
> Cached: 2026-04-16T20:57:43.454Z

---

Toggle navigation
                            
                            
                            
                        
                        # [Packagist](/) *The PHP Package Repository*

                    

                    
                        

                            
                                [Browse](/explore/)
                            
                            
                                [Submit](/packages/submit)
                                                        
                                [Create account](/register/)
                            
                            
                                
                                    [Sign in](/login/)

                                    
                                        
                                            
                                                
                                                
                                            
                                            
                                                
                                                
                                            

                                            
                                                
                                                     Remember me
                                                
                                            
                                            
                                                [Use Github](/login/github)
                                                Log in
                                            
                                        

                                        
                                            [No account yet? Create one now!](/register/)
                                        
                                    
                                
                                                    

                    
                
            
        

        
    
        
            
                
                                                            
                
            
            

                
        
        
            
                                    
                        
                            

                            
                                
                                    
                                        Active filters
                                        
                                    
                                    
                                
                                
                                
                            
                        

                        
                            
                                
                                    Search by [](https://www.algolia.com/)
                                
                                
                                    
                                
                            
                        
                    
                
                    
        
            

                
                    
                        
                                                        [azure-oss /](/packages/azure-oss/) storage-blob-flysystem
                        
                    
                
            

            
                
                    ** 

                    
                    
                                        
                    
                    Flysystem adapter for Azure Storage PHP

                                                        

                
                    
                        
                            ##### Maintainers

                            
                                [](/users/pimjansen/)
                                [](/users/Brecht/)
                                                            

                                                    
                    

                                                            
                    
                    
                    
                        
                            ##### Package info

                            
                                [github.com/Azure-OSS/azure-storage-php-adapter-flysystem](https://github.com/Azure-OSS/azure-storage-php-adapter-flysystem)
                            

                                                                                                                                                                                                                                                                                                                    pkg:composer/azure-oss/storage-blob-flysystem

                        
                    

                    
                    
                        
                            ##### Statistics

                            
                                
                                    [Installs](/packages/azure-oss/storage-blob-flysystem/stats):
                                
                                1&#8201;022&#8201;007                            
                                                            
                                    
                                        [Dependents](/packages/azure-oss/storage-blob-flysystem/dependents?order_by=downloads):
                                    
                                    8
                                
                                                                                        
                                    
                                        [Suggesters](/packages/azure-oss/storage-blob-flysystem/suggesters):
                                    
                                    1
                                
                                                                                        
                                    
                                        [Stars](https://github.com/Azure-OSS/azure-storage-php-adapter-flysystem/stargazers):
                                    
                                    28
                                
                                                                                
                    

                    
                        
                            ##### Security

                                                            
                                    
                                        [Advisories](/packages/azure-oss/storage-blob-flysystem/advisories):
                                    
                                    0
                                
                                                                                    
                                
                                    Aikido package health analysis
                                
                            
                        
                    
                
            

                            
                    
                                                    
    1.6.0

    2026-03-05 20:52 UTC

            
                                                            
                    Requires

                                            
- php: ^8.1
- [azure-oss/storage](/packages/azure-oss/storage): ^1.4
- [league/flysystem](/packages/league/flysystem): ^3.28

                                    
                                                            
                    Requires (Dev)

                                            None

                                    
                                                            
                    Suggests

                                            None

                                    
                                                            
                    Provides

                                            None

                                    
                                                            
                    Conflicts

                                            None

                                    
                                                            
                    Replaces

                                            None

                                    
                    
    

    ** MIT ** 2d6438b98a3779886ba90d20703e81a14a256837

            **
        

                            - Brecht Vermeersch                        <brechtvermeersch.woop@outlook.be>

                            - Pim Jansen                        <pimjansen.woop@gmail.com>

                    

    
    

                                            
                    
                        
    

                                
                [dev-main](#dev-main)

                
                
                            
                                
                [1.6.0](#1.6.0)

                
                
                            
                                
                [1.5.2](#1.5.2)

                
                
                            
                                
                [1.5.1](#1.5.1)

                
                
                            
                                
                [1.5.0](#1.5.0)

                
                
                            
                                
                [1.4.1](#1.4.1)

                
                
                            
                                
                [1.4.0](#1.4.0)

                
                
                            
                                
                [1.3.0](#1.3.0)

                
                
                            
                                
                [1.2.1](#1.2.1)

                
                
                            
                                
                [1.2.0](#1.2.0)

                
                
                            
                                
                [1.1.1](#1.1.1)

                
                
                            
                                
                [1.1.0](#1.1.0)

                
                
                            
                                
                [1.0.0](#1.0.0)

                
                
                            
                                
                [dev-use-actual-public-urls](#dev-use-actual-public-urls)

                
                
                            
                                
                [dev-docs](#dev-docs)

                
                
                            
            

    
        **
    

    
                    This package is auto-updated.

                Last update: 2026-03-27 17:51:20 UTC 

            
                    
                
            
                            
                
                    # README

                    [](#user-content-azure-storage-blob-flysystem-adapter)
[](https://packagist.org/packages/azure-oss/storage-blob-flysystem)
[](https://packagist.org/packages/azure-oss/storage-blob-flysystem)
Community-driven PHP SDKs for Azure, because Microsoft won&#039;t.

In November 2023, Microsoft officially archived their [Azure SDK for PHP](https://github.com/Azure/azure-sdk-for-php) and stopped maintaining PHP integrations for most Azure services. No migration path, no replacement — just a repository marked read-only.

We picked up where they left off.

[](https://camo.githubusercontent.com/4231ff59e3078111b9723a49fe9b1efdea82c0f3cb6242f7a3485c99a933f9cc/68747470733a2f2f617a7572652d6f73732e6769746875622e696f2f696d672f6c6f676f2e737667)

Our other packages:

**[azure-oss/storage](https://packagist.org/packages/azure-oss/storage)** – Azure Blob Storage SDK

[](https://camo.githubusercontent.com/7912c891169f6b6e901a06b4b34bdc2fafbf3c569543f9d68134613488fc7fd9/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f72616765)

**[azure-oss/storage-blob-laravel](https://packagist.org/packages/azure-oss/storage-blob-laravel)** – Laravel filesystem driver

[](https://camo.githubusercontent.com/775d6da94256b6777ac7730929f4a2cc7aa847b21435e0545e4b65b343de0619/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d626c6f622d6c61726176656c)

**[azure-oss/storage-queue](https://packagist.org/packages/azure-oss/storage-queue)** – Azure Storage Queue SDK

[](https://camo.githubusercontent.com/603cf9d2f39843e8cdbc4a079c8b99b82bde0006bc723043b6a322a582f60733/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d7175657565)

**[azure-oss/storage-queue-laravel](https://packagist.org/packages/azure-oss/storage-queue-laravel)** – Laravel Queue connector

[](https://camo.githubusercontent.com/fef3afef7bac10d323dc9f4d1c421318d8e94570acc0c20e734f05ff06e7a561/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f64742f617a7572652d6f73732f73746f726167652d71756575652d6c61726176656c)

## Install

[](#user-content-install)
composer require azure-oss/storage-blob-flysystem
## Documentation

[](#user-content-documentation)
You can read the documentation [here](https://azure-oss.github.io/category/storage-blob-flysystem).

## Quickstart

[](#user-content-quickstart)
<?php

use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;

$service &#61; BlobServiceClient::fromConnectionString(
    getenv(&#039;AZURE_STORAGE_CONNECTION_STRING&#039;)
);

$container &#61; $service->getContainerClient(
    getenv(&#039;AZURE_STORAGE_CONTAINER&#039;)
);

$adapter &#61; new AzureBlobStorageAdapter($container);
$filesystem &#61; new Filesystem($adapter);

// Write
$filesystem->write(&#039;docs/hello.txt&#039;, &#039;Hello Azure Blob &#43; Flysystem&#039;);

// Read
$contents &#61; $filesystem->read(&#039;docs/hello.txt&#039;);

// Stream upload
$stream &#61; fopen(&#039;/path/to/big-file.zip&#039;, &#039;r&#039;);
$filesystem->writeStream(&#039;archives/big-file.zip&#039;, $stream);
fclose($stream);

// List recursively
foreach ($filesystem->listContents(&#039;docs&#039;, true) as $item) {
    echo $item->path().PHP_EOL;
}

// Delete
$filesystem->delete(&#039;docs/hello.txt&#039;);
## License

[](#user-content-license)
This project is released under the MIT License. See [LICENSE](https://github.com/Azure-OSS/azure-storage-php-monorepo/blob/02759360186be8d2d04bd1e9b2aba3839b6d39dc/LICENSE) for details.

                
                    
    
            
        


... [Content truncated]