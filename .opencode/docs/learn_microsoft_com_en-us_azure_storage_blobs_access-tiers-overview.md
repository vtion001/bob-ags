# Access tiers for blob data - Azure Storage | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/storage/blobs/access-tiers-overview
> Cached: 2026-04-16T20:58:02.962Z

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
					
				
			
		
	
					# Access tiers for blob data

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					Data stored in the cloud grows at an exponential pace. To manage costs for your expanding storage needs, it can be helpful to organize your data based on how frequently it will be accessed and how long it will be retained. Azure storage offers different access tiers so that you can store your blob data in the most cost-effective manner based on how it's being used. Azure Storage access tiers include:

- **Hot tier** - An online tier optimized for storing data that is accessed or modified frequently. The hot tier has the highest storage costs, but the lowest access costs.

- **Cool tier** - An online tier optimized for storing data that is infrequently accessed or modified. Data in the cool tier should be stored for a minimum of **30** days. The cool tier has lower storage costs and higher access costs compared to the hot tier.

- **Cold tier** - An online tier optimized for storing data that is rarely accessed or modified, but still requires fast retrieval. Data in the cold tier should be stored for a minimum of **90** days. The cold tier has lower storage costs and higher access costs compared to the cool tier.

- **Archive tier** - An offline tier optimized for storing data that is rarely accessed, and that has flexible latency requirements, on the order of hours. Data in the archive tier should be stored for a minimum of **180** days.

- **Smart tier** - Smart tier automatically moves your data between the hot, cool, and cold access tiers based on usage patterns, optimizing your costs for these access tiers automatically. To learn more, see [Optimize costs with smart tier](access-tiers-smart).

Azure storage capacity limits are set at the account level, rather than according to access tier. You can choose to maximize your capacity usage in one tier, or to distribute capacity across two or more tiers.

Note

Setting the access tier is only allowed on Block Blobs. They are not supported for Append and Page Blobs.

## Online access tiers

When your data is stored in an online access tier (either hot, cool or cold), users can access it immediately. The hot tier is the best choice for data that is in active use. The cool or cold tier is ideal for data that is accessed less frequently, but that still must be available for reading and writing.

Example usage scenarios for the hot tier include:

- Data that's in active use or data that you expect will require frequent reads and writes.

- Data that's staged for processing and eventual migration to the cool access tier.

Usage scenarios for the cool and cold access tiers include:

- Short-term data backup and disaster recovery.

- Older data sets that aren't used frequently, but are expected to be available for immediate access.

- Large data sets that need to be stored in a cost-effective way while other data is being gathered for processing.

To learn how to move a blob to the hot, cool, or cold tier, see [Set a blob's access tier](access-tiers-online-manage).

Data in the cool and cold tiers have slightly lower availability, but offer the same high durability, retrieval latency, and throughput characteristics as the hot tier. For data in the cool or cold tiers, slightly lower availability and higher access costs may be acceptable trade-offs for lower overall storage costs, as compared to the hot tier. For more information, see [SLA for storage](https://azure.microsoft.com/support/legal/sla/storage/v1_5/).

Blobs are subject to an early deletion penalty if they are deleted, overwritten or moved to a different tier before the minimum number of days required by the tier have transpired. For example, a blob in the cool tier in a general-purpose v2 account is subject to an early deletion penalty if it's deleted or moved to a different tier before 30 days has elapsed. For a blob in the cold tier, the deletion penalty applies if it's deleted or moved to a different tier before 90 days has elapsed. This charge is prorated. For example, if a blob is moved to the cool tier and then deleted after 21 days, you'll be charged an early deletion fee equivalent to 9 (30 minus 21) days of storing that blob in the cool tier.
Early deletion charges also occur if the entire object is rewritten through any operation (i.e. Put Blob, Put Block List, or Copy Blob) within the specified time window. This charge is prorated based on the data storage price of the corresponding tier, i.e. deleting an archived blob after 120 days will lead to this object being charged for 180 days.

Note

In an account that has soft delete enabled, a blob is considered deleted after it is deleted and retention period expires. Until that period expires, the blob is only  *soft-deleted* and is not subject to the early deletion penalty.

The hot, cool, and cold tiers support all redundancy configurations. For more information about data redundancy options in Azure Storage, see [Azure Storage redundancy](../common/storage-redundancy).

## Archive access tier

The archive tier is an offline tier for storing data that is rarely accessed. The archive access tier has the lowest storage cost. However, this tier has higher data retrieval costs with a higher latency as compared to the hot, cool, and cold tiers. Example usage scenarios for the archive access tier include:

- Long-term backup, secondary backup, and archival datasets

- Original (raw) data that must be preserved, even after it has been processed into final usable form

- Compliance and archival data that needs to be stored for a long time and is hardly ever accessed

To learn how to move a blob to the archive tier, see [Archive a blob](archive-blob).

Data must remain in the archive tier for at least 180 days or be subject to an early deletion charge. For example, if a blob is moved to the archive tier and then deleted or moved to the hot tier after 45 days, you'll be charged an early deletion fee equivalent to 135 (180 minus 45) days of storing that blob in the archive tier.

Note

In an account that has soft delete enabled, a blob is considered deleted after it is deleted and retention period expires. Until that period expires, the blob is only  *soft-deleted* and is not subject to the early deletion penalty.

While a blob is in the archive tier, it can't be read or modified. To read or download a blob in the archive tier, you must first rehydrate it to an online tier, either hot, cool, or cold. Data in the archive tier can take up to 15 hours to rehydrate, depending on the priority you specify for the rehydration operation. For more information about blob rehydration, see [Overview of blob rehydration from the archive tier](archive-rehydrate-overview).

An archived blob's metadata remains available for read access, so that you can list the blob and its properties, metadata, and index tags. Metadata for a blob in the archive tier is read-only, while blob index tags can be read or written. Storage costs for metadata of archived blobs will be charged on cool tier rates.
Snapshots aren't supported for archived blobs.
The following operations are supported for blobs in the archive tier:

- [Copy Blob](/en-us/rest/api/storageservices/copy-blob)

- [Delete Blob](/en-us/rest/api/storageservices/delete-blob)

- [Undelete Blob](/en-us/rest/api/storageservices/undelete-blob)

- [Find Blobs by Tags](/en-us/rest/api/storageservices/find-blobs-by-tags)

- [Get Blob Metadata](/en-us/rest/api/storageservices/get-blob-metadata)

- [Get Blob Properties](/en-us/rest/api/storageservices/get-blob-properties)

- [Get Blob Tags](/en-us/rest/api/storageservices/get-blob-tags)

- [List Blobs](/en-us/rest/api/storageservices/list-blobs)

- [Set Blob Tags](/en-us/rest/api/storageservices/set-blob-tags)

- [Set Blob Tier](/en-us/rest/api/storageservices/set-blob-tier)

Only storage accounts that are configured for LRS, GRS, or RA-GRS support moving blobs to the archive tier. The archive tier isn't supported for ZRS, GZRS, or RA-GZRS accounts. For more information about redundancy configurations for Azure Storage, see [Azure Storage redundancy](../common/storage-redundancy).

To change the redundancy configuration for a storage account that contains blobs in the archive tier, you must first rehydrate all archived blobs to the hot, cool, or cold tier. Because rehydration operations can be costly and time-consuming, Microsoft recommends that you avoid changing the redundancy configuration of a storage account that contains archived blobs.

Migrating a storage account from LRS to GRS is supported as long as no blobs were moved to the archive tier while the account was configured for LRS.

## Minimum billable object size on cooler tiers

For storage accounts that use Azure Blob Storage or Azure Data Lake Storage, a minimum billable object size of **128 KiB** applies to objects stored in the **cool**, **cold**, and **archive** access tiers. Objects in these tiers that are smaller than 128 KiB are billed as 128 KiB objects at the rate for the corresponding tier. Billing uses the existing capacity billing meters (data stored), and there is no change to transaction billing.

This billing behavior will be introduced in two stages:

- **July 1, 2026**: The billing behavior applies to all new storage accounts created on or after this date. There is no change for existing storage accounts.

- **July 1, 2027**: The billing behavior applies to all storage accounts.

The creation time of a storage account, which is part of the account-level metadata, determines which stage applies.

The **hot** access tier continues to have no minimum billable object size. To reduce potential cost impact, consider [packaging small objects into larger objects](access-tiers-best-practices#pack-small-files-before-moving-data-to-cooler-tiers) before moving data to cooler tiers, or using [smart tier](access-tiers-smart) to automatically keep small objects on the hot access tier.

To support this change, the **Blob Capacity** metrics in the Azure portal will introduce new blob types: **BlockBlobSmall** and Azure **Azure Data Lake Storage Small**.

Note

Customers with existing dashboards, alerts, cost reports, or automation that explicitly depend on the BlockBlob blob type should review and update those workflows accordingly.

Workflows that assume all block blobs are reported under the BlockBlob datatype may return incomplete or unexpected results once these new datatypes appear in capacity metrics.

## Default account access tier setting

Storage accounts have a default access tier setting that indicates the online tier in which a new blob is created. The default access tier setting can be set to either hot, cool or cold. Users can override the default setting for an individual blob when uploading the blob or changing its tier.

The default access tier for a new general-purpose v2 storage account is set to the hot tier by default. You can change the default access tier setting when you create a storage account or after it's created. If you don't change this setting on the storage account or explicitly set the tier when uploading a blob, then a new blob is uploaded to the hot tier by default.

A blob that doesn't have an explicitly assigned tier infers its tier from the default account access tier setting. If a blob's access tier is inferred from the default account access tier setting, then the Azure portal displays the access tier as **Hot (inferred)**, **Cool (inferred)**, or **Cold (inferred)**.

Changing the default access tier setting for a storage account applies to all blobs in the account for which an access tier hasn't been explicitly set. If you toggle the default access tier setting to a cooler tier in a general-purpose v2 account, then you're charged for write operations (per 10,000) for all blobs for which the access tier is inferred. You're charged for both read operations (per 10,000) and data retrieval (per GB) if you toggle to a warmer tier in a general-purpose v2 account.

When you create a legacy Blob Storage account, you must specify the default access tier setting as hot or cool at create time. There's no charge for changing the default account access tier setting to a cooler tier in a legacy Blob Storage account. You're charged for both read operations (per 10,000) and data retrieval (per GB) if you toggle to a warmer tier in a Blob Storage account. Microsoft recommends using general-purpose v2 storage accounts rather than Blob Storage accounts when possible.

Note

The archive tier is not supported as the default access tier for a storage account.

## Setting or changing a blob's tier

To explicitly set a blob's tier when you create it, specify the tier when you upload the blob.

After a blob is created, you can change its tier in either of the following ways:

By calling the [Set Blob Tier](/en-us/rest/api/storageservices/set-blob-tier) operation, either directly or via a [lifecycle management](#blob-lifecycle-management) policy. Calling [Set Blob Tier](/en-us/rest/api/storageservices/set-blob-tier) is typically the best option when you're changing a blob's tier from a warmer tier to a cooler one.

Note

You can't rehydrate an archived blob to an online tier by using lifecycle management policies.

By calling the [Copy Blob](/en-us/rest/api/storageservices/copy-blob) operation to copy a blob from one tier to another. Calling [Copy Blob](/en-us/rest/api/storageservices/copy-blob) is recommended for most scenarios where you're rehydrating a blob from the archive tier to an online tier, or moving a blob from cool or cold to hot. By copying a blob, you can avoid the early deletion penalty, if the required storage interval for the source blob hasn

... [Content truncated]