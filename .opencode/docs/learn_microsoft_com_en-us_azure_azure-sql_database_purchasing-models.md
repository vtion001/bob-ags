# Purchasing Models - Azure SQL Database | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/azure-sql/database/purchasing-models
> Cached: 2026-04-16T20:57:34.833Z

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
					
				
			
		
	
					# Compare vCore and DTU-based purchasing models of Azure SQL Database

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					**Applies to:** 
 [Azure SQL Database](/en-us/sql/sql-server/sql-docs-navigation-guide#applies-to)
This article compares the two purchasing models for [Azure SQL Database](sql-database-paas-overview?view=azuresql):

- vCore-based purchasing model (recommended)

- DTU-based purchasing model

## Purchasing models

There are two purchasing models for Azure SQL Database:

The [vCore-based purchasing model](service-tiers-vcore?view=azuresql) provides a choice between the provisioned or serverless compute tiers:

- With the **provisioned** compute tier, you choose the exact amount of compute resources that are always provisioned for your workload.

- With the **serverless** compute tier, you specify the autoscaling of the compute resources over a configurable compute range. The serverless compute tier automatically pauses databases during inactive periods when only storage is billed and automatically resumes databases when activity returns. The vCore unit price per unit of time is lower in the provisioned compute tier than it is in the serverless compute tier.

- The [DTU-based purchasing model](service-tiers-dtu?view=azuresql) provides bundled compute and storage packages balanced for common workloads.

The following table and chart compares and contrasts the vCore-based and the DTU-based purchasing models:

**Purchasing model**
**Description**
**Best for**

DTU-based
This model is based on a bundled measure of compute, storage, and I/O resources. Compute sizes are expressed in DTUs for single databases and in elastic database transaction units (eDTUs) for elastic pools. For more information about DTUs and eDTUs, see [What are DTUs and eDTUs?](purchasing-models?view=azuresql#dtu-purchasing-model).
Customers who want simple, preconfigured resource options

vCore-based
This model allows you to independently choose compute and storage resources. The vCore-based purchasing model also allows you to use [Azure Hybrid Benefit](https://azure.microsoft.com/pricing/hybrid-benefit/) for SQL Server to save costs.
Customers who value flexibility, control, and transparency

## vCore purchasing model

A virtual core (vCore) represents a logical CPU and offers you the option to choose between generations of hardware and the physical characteristics of the hardware (for example, the number of cores, the memory, and the storage size). The vCore-based purchasing model gives you flexibility, control, transparency of individual resource consumption, and a straightforward way to translate on-premises workload requirements to the cloud. This model allows you to choose compute, memory, and storage resources based on your workload needs.

The vCore-based purchasing model has three service tiers: **General Purpose**, **Business Critical**, and **Hyperscale** service tiers. Review [service tiers](service-tiers-sql-database-vcore?view=azuresql#service-tiers) to learn more.

In the vCore-based purchasing model, your costs depend on the choice and usage of:

- Service tier

- Hardware configuration

- Compute resources (the number of vCores and the amount of memory)

- Reserved database storage

- Actual backup storage

## DTU purchasing model

The DTU-based purchasing model uses a database transaction unit (DTU) to calculate and bundle compute costs. A database transaction unit (DTU) represents a blended measure of CPU, memory, reads, and writes. The DTU-based purchasing model offers a set of preconfigured bundles of compute resources and included storage to drive different levels of application performance. If you prefer the simplicity of a preconfigured bundle and fixed payments each month, the DTU-based model might be more suitable for your needs.

In the DTU-based purchasing model, you can choose between the **Basic**, **Standard**, and **Premium** service tiers for Azure SQL Database. Review [DTU service tiers](service-tiers-dtu?view=azuresql#compare-service-tiers) to learn more.

To convert from the DTU-based purchasing model to the vCore-based purchasing model, see [Migrate Azure SQL Database from the DTU-based model to the vCore-based model](migrate-dtu-to-vcore?view=azuresql).

## Compute costs

Compute costs are calculated differently based on each purchasing model.

### DTU compute costs

In the DTU purchasing model, DTUs are offered in preconfigured bundles of compute resources and included storage to drive different levels of application performance. You're billed by the number of DTUs you allocate to your database for your application.

### vCore compute costs

In the vCore-based purchasing model, choose between the provisioned compute tier, or the [serverless compute tier](serverless-tier-overview?view=azuresql). In the provisioned compute tier, the compute cost reflects the total compute capacity that is provisioned for the application. In the serverless compute tier, compute resources are autoscaled based on workload capacity and billed for the amount of compute used, per second.

For single databases, compute resources, I/O, and data and log storage are charged per database. For elastic pools, these resources are charged per pool. However, backup storage is always charged per database.

Since three additional replicas are automatically allocated in the Business Critical service tier, the price is approximately 2.7 times higher than it is in the General Purpose service tier. Likewise, the higher storage price per GB in the Business Critical service tier reflects the higher I/O limits and lower latency of the local SSD storage.

## Storage costs

Storage costs are calculated differently based on each purchasing model.

### DTU storage costs

Storage is included in the price of the DTU. It's possible to add extra storage in the Standard and Premium tiers. See the [pricing options](https://azure.microsoft.com/pricing/details/sql-database/single/) for details on provisioning extra storage.

[Long-term retention](long-term-retention-overview?view=azuresql) isn't included, and is billed separately.

## vCore storage costs

Different types of storage are billed differently.

- For data storage, you're charged for the provisioned storage based upon the maximum database or pool size you select. The cost doesn't change unless you reduce or increase that maximum.

- Backup storage is associated with automated backups of your databases and is allocated dynamically. Increasing your backup retention period will increase the backup storage required by your databases.

- The cost of backup storage is the same for the Business Critical service tier and the General Purpose service tier because both tiers use standard storage for backups.

By default, seven days of automated backups of your databases are copied to a storage account. This storage is used by full backups, differential backups, and transaction log backups. The size of differential and transaction log backups depends on the rate of change of the database. A minimum storage amount equal to 100 percent of the maximum data size for the database is provided at no extra charge. Additional consumption of backup storage is charged in GB per month.

For more information about storage prices, see [Azure SQL Database pricing](https://azure.microsoft.com/pricing/details/sql-database/single/).

## Frequently asked questions (FAQs)

### Do I need to take my application offline to convert from a DTU-based service tier to a vCore-based service tier?

No. You don't need to take the application offline. The new service tiers offer a simple online-conversion method that's similar to the existing process of upgrading databases from the Standard to the Premium service tier and the other way around. You can start this conversion by using the Azure portal, PowerShell, the Azure CLI, T-SQL, or the REST API. For more information, see [migrate DTU to vCore](migrate-dtu-to-vcore?view=azuresql) and [scale elastic pools](elastic-pool-scale?view=azuresql).

### Can I convert a database from a service tier in the vCore-based purchasing model to a service tier in the DTU-based purchasing model?

Yes, you can easily convert your database to any supported performance objective by using the Azure portal, PowerShell, the Azure CLI, T-SQL, or the REST API. For more information, see [migrate DTU to vCore](migrate-dtu-to-vcore?view=azuresql) and [scale elastic pools](elastic-pool-scale?view=azuresql#change-compute-resources-vcores-or-dtus).

## Related content

- [vCore-based purchasing model](service-tiers-vcore?view=azuresql)

- [DTU-based purchasing model overview](service-tiers-dtu?view=azuresql)

					
		
	 
		
		
	
					
		
		
			
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
		2025-08-07