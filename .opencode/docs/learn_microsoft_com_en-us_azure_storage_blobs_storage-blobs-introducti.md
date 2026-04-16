# Introduction to Blob (object) Storage - Azure Storage | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/storage/blobs/storage-blobs-introduction
> Cached: 2026-04-16T20:57:43.892Z

---

Table of contents 
			
			
				
				Exit editor mode
			
		
	
			
				
					
		
			
				
		
			
				
					
				
			
			
		

		
	 
		
			
		
			
				
			
		
		
			
				
			
			Ask Learn
		
		
			
				
			
			Ask Learn
		
	 
		
			
				
			
			Focus mode
		
	 

			
				
					
						
					
				
				
					
		
			
			Table of contents
		
	 
		
			
				
			
			Read in English
		
	 
		
			
				
			
			Add
		
	
					
		
			
				
			
			Add to plan
		
	  
		
			
				
			
			Edit
		
	
					
		
		#### Share via

		
					
						
							
						
						Facebook
					

					
						
							
						
						x.com
					

					
						
							
						
						LinkedIn
					
					
						
							
						
						Email
					
			  
	 
		
		
				
					
						
						
						
					
					Copy Markdown
				
		   
				
					
						
					
					Print
				
		  
	
				
			
		
	
			
		
	  
		
		
			
			
				
					
						
						Note
					
					
						Access to this page requires authorization. You can try [signing in](#) or changing directories.
					
					
						Access to this page requires authorization. You can try changing directories.
					
				
			
		
	
					# Introduction to Azure Blob Storage

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					Azure Blob Storage is Microsoft's object storage solution for the cloud. Blob Storage is optimized for storing massive amounts of unstructured data. Unstructured data is data that doesn't adhere to a particular data model or definition, such as text or binary data.

## About Blob Storage

Blob Storage is designed for:

- Serving images or documents directly to a browser.

- Storing files for distributed access.

- Streaming video and audio.

- Writing to log files.

- Storing data for backup and restore, disaster recovery, and archiving.

- Storing data for analysis by an on-premises or Azure-hosted service.

Users or client applications can access objects in Blob Storage via HTTP/HTTPS, from anywhere in the world. Objects in Blob Storage are accessible via the [Azure Storage REST API](/en-us/rest/api/storageservices/blob-service-rest-api), [Azure PowerShell](/en-us/powershell/module/az.storage), [Azure CLI](/en-us/cli/azure/storage), or an Azure Storage client library. Client libraries are available for different languages, including:

- [.NET](/en-us/dotnet/api/overview/azure/storage)

- [Java](/en-us/java/api/overview/azure/storage)

- [Node.js](https://github.com/Azure/azure-sdk-for-js/tree/master/sdk/storage)

- [Python](storage-quickstart-blobs-python)

- [Go](https://github.com/Azure/azure-sdk-for-go/tree/main/sdk/storage/azblob)

Clients can also securely connect to Blob Storage by using SSH File Transfer Protocol (SFTP) and mount Blob Storage containers by using the Network File System (NFS) 3.0 protocol.

## About Azure Data Lake Storage Gen2

Blob Storage supports Azure Data Lake Storage Gen2, Microsoft's enterprise big data analytics solution for the cloud. Azure Data Lake Storage Gen2 offers a hierarchical file system as well as the advantages of Blob Storage, including:

- Low-cost, tiered storage

- High availability

- Strong consistency

- Disaster recovery capabilities

For more information about Data Lake Storage Gen2, see [Introduction to Azure Data Lake Storage Gen2](data-lake-storage-introduction).

## Blob Storage resources

Blob Storage offers three types of resources:

- The **storage account**

- A **container** in the storage account

- A **blob** in a container

The following diagram shows the relationship between these resources.

### Storage accounts

A storage account provides a unique namespace in Azure for your data. Every object that you store in Azure Storage has an address that includes your unique account name. The combination of the account name and the Blob Storage endpoint forms the base address for the objects in your storage account.

For example, if your storage account is named *mystorageaccount*, then the default endpoint for Blob Storage is:

```
http://mystorageaccount.blob.core.windows.net

```

The following table describes the different types of storage accounts that are supported for Blob Storage:

Type of storage account
Performance tier
Usage

General-purpose v2
Standard
Standard storage account type for blobs, file shares, queues, and tables. Recommended for most scenarios using Blob Storage or one of the other Azure Storage services.

Block blob
Premium
Premium storage account type for block blobs and append blobs. Recommended for scenarios with high transaction rates or that use smaller objects or require consistently low storage latency. [Learn more about workloads for premium block blob accounts...](storage-blob-block-blob-premium)

Page blob
Premium
Premium storage account type for page blobs only. [Learn more about workloads for premium page blob accounts...](storage-blob-pageblob-overview)

To learn more about types of storage accounts, see [Azure storage account overview](../common/storage-account-overview?toc=/azure/storage/blobs/toc.json). For information about legacy storage account types, see [Legacy storage account types](../common/storage-account-overview#legacy-storage-account-types).

To learn how to create a storage account, see [Create a storage account](../common/storage-account-create).

### Containers

A container organizes a set of blobs, similar to a directory in a file system. A storage account can include an unlimited number of containers, and a container can store an unlimited number of blobs.

A container name must be a valid DNS name, as it forms part of the unique URI (Uniform resource identifier) used to address the container or its blobs. Follow these rules when naming a container:

- Container names can be between 3 and 63 characters long.

- Container names must start with a letter or number, and can contain only lowercase letters, numbers, and the dash (-) character.

- Two or more consecutive dash characters aren't permitted in container names.

The URI for a container is similar to:

`https://myaccount.blob.core.windows.net/mycontainer`

For more information about naming containers, see [Naming and Referencing Containers, Blobs, and Metadata](/en-us/rest/api/storageservices/Naming-and-Referencing-Containers--Blobs--and-Metadata).

### Blobs

Azure Storage supports three types of blobs:

- **Block blobs** store text and binary data. Block blobs are made up of blocks of data that can be managed individually. Block blobs can store up to about 190.7 TiB.

- **Append blobs** are made up of blocks like block blobs, but are optimized for append operations. Append blobs are ideal for scenarios such as logging data from virtual machines.

- **Page blobs** store random access files up to 8 TiB in size. Page blobs store virtual hard drive (VHD) files and serve as disks for Azure virtual machines. For more information about page blobs, see [Overview of Azure page blobs](storage-blob-pageblob-overview)

For more information about the different types of blobs, see [Understanding Block Blobs, Append Blobs, and Page Blobs](/en-us/rest/api/storageservices/understanding-block-blobs--append-blobs--and-page-blobs).

The URI for a blob is similar to:

`https://myaccount.blob.core.windows.net/mycontainer/myblob`

or

`https://myaccount.blob.core.windows.net/mycontainer/myvirtualdirectory/myblob`

Follow these rules when naming a blob:

- A blob name can contain any combination of characters.

- A blob name must be at least one character long and cannot be more than 1,024 characters long, for blobs in Azure Storage.

- Blob names are case-sensitive.

- Reserved URL characters must be properly escaped.

There are limitations on the number of path segments comprising a blob name. A path segment is the string between consecutive delimiter characters (for example, a forward slash `/`) that corresponds to the directory or virtual directory. The following path segment limitations apply to blob names:

- If the storage account *does not* have hierarchical namespace enabled, the number of path segments comprising the blob name cannot exceed 254.

- If the storage account has hierarchical namespace enabled, the number of path segments comprising the blob name cannot exceed 63 (including path segments for container name and account host name).

Note

Avoid blob names that end with a dot (.), a forward slash (/), or a sequence or combination of the two. No path segments should end with a dot (.).

For more information about naming blobs, see [Naming and Referencing Containers, Blobs, and Metadata](/en-us/rest/api/storageservices/Naming-and-Referencing-Containers--Blobs--and-Metadata).

## Move data to Blob Storage

A number of solutions exist for migrating existing data to Blob Storage:

- **AzCopy** is an easy-to-use command-line tool for Windows and Linux that copies data to and from Blob Storage, across containers, or across storage accounts. For more information about AzCopy, see [Transfer data with the AzCopy v10](../common/storage-use-azcopy-v10).

- The **Azure Storage Data Movement library** is a .NET library for moving data between Azure Storage services. The AzCopy utility is built with the Data Movement library. For more information, see the [reference documentation](/en-us/dotnet/api/microsoft.azure.storage.datamovement) for the Data Movement library.

- **Azure Data Factory** supports copying data to and from Blob Storage by using the account key, a shared access signature, a service principal, or managed identities for Azure resources. For more information, see [Copy data to or from Azure Blob Storage by using Azure Data Factory](../../data-factory/connector-azure-blob-storage?toc=/azure/storage/blobs/toc.json).

- **Blobfuse** is a virtual file system driver for Azure Blob Storage. You can use BlobFuse to access your existing block blob data in your Storage account through the Linux file system. For more information, see [What is BlobFuse? - BlobFuse2 (preview)](blobfuse2-what-is).

- **Azure Data Box** service is available to transfer on-premises data to Blob Storage when large datasets or network constraints make uploading data over the wire unrealistic. Depending on your data size, you can request [Azure Data Box Disk](../../databox/data-box-disk-overview), [Azure Data Box](../../databox/data-box-overview), or [Azure Data Box Heavy](../../databox/data-box-heavy-overview) devices from Microsoft. You can then copy your data to those devices and ship them back to Microsoft to be uploaded into Blob Storage.

- The **Azure Import/Export service** provides a way to import or export large amounts of data to and from your storage account using hard drives that you provide. For more information, see [What is Azure Import/Export service?](../../import-export/storage-import-export-service)

## Next steps

- [Create a storage account](../common/storage-account-create?toc=/azure/storage/blobs/toc.json)

- [Scalability and performance targets for Blob Storage](scalability-targets)

					
		
	 
		
		
	
					
		
		
			
			## Feedback

			
				
					Was this page helpful?
				
				
					
						
							
						
						Yes
					
					
						
							
						
						No
					
					
						
							
								
							
							No
						
						
							
								Need help with this topic?
							
							
								Want to try using Ask Learn to clarify or guide you through this topic?
							
							
		
			
		
			
				
			
		
		
			
				
			
			Ask Learn
		
		
			
				
			
			Ask Learn
		
	
			
				
					
				
				 Suggest a fix? 
			
		
	
						
					
				
			
		
		
	
				
				
		
			
			
				Additional resources
			
			
		
	 
		
	 
		
	
		
	 
		
			
			

				
			
				Last updated on 
		2023-10-10