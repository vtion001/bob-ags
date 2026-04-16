# Serverless compute tier - Azure SQL Database | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/azure-sql/database/serverless-tier-overview
> Cached: 2026-04-16T20:57:33.425Z

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
					
				
			
		
	
					# Serverless compute tier for Azure SQL Database

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					**Applies to:** 
 [Azure SQL Database](/en-us/sql/sql-server/sql-docs-navigation-guide#applies-to)
Serverless is a [compute tier](service-tiers-sql-database-vcore?view=azuresql#compute) for single databases in Azure SQL Database that automatically scales compute based on workload demand and bills for the amount of compute used per second. The serverless compute tier also automatically pauses databases during inactive periods when only storage is billed and automatically resumes databases when activity returns. The serverless compute tier is available in the [General Purpose](service-tiers-sql-database-vcore?view=azuresql#general-purpose) service tier and the [Hyperscale](service-tier-hyperscale?view=azuresql) service tier.

Currently, auto-pause and auto-resume are currently only supported in the General Purpose service tier.

## Overview

A compute autoscaling range and an auto-pause delay are important parameters for the serverless compute tier. The configuration of these parameters shapes the database performance experience and compute cost.

### Performance configuration

- The **minimum vCores** and **maximum vCores** are configurable parameters that define the range of compute capacity available for the database. Memory and IO limits are proportional to the vCore range specified. 

- The **auto-pause delay** is a configurable parameter that defines the period of time the database must be inactive before it is automatically paused. The database is automatically resumed when the next login or other activity occurs. Alternatively, automatic pausing can be disabled.

### Cost

The cost for a serverless database is the summation of the compute cost and storage cost. The storage cost is determined in the same way as in the provisioned compute tier.

- When compute usage is between the minimum and maximum limits configured, the compute cost is based on vCore and memory used.

- When compute usage is below the minimum limits configured, the compute cost is based on the minimum vCores and minimum memory configured.

- When the database is paused, the compute cost is zero and only storage costs are incurred.

For more cost details, see [Billing](serverless-tier-overview?view=azuresql#billing).

## Scenarios

Serverless is price-performance optimized for single databases with intermittent, unpredictable usage patterns that can afford some delay in compute warm-up after idle usage periods. In contrast, the [provisioned compute tier](service-tiers-sql-database-vcore?view=azuresql#compute) is price-performance optimized for single databases or multiple databases in [elastic pools](elastic-pool-overview?view=azuresql) with higher average usage that cannot afford any delay in compute warm-up.

### Scenarios well suited for serverless compute

- Single databases with intermittent, unpredictable usage patterns interspersed with periods of inactivity, and lower average compute utilization over time.

- Single databases in the provisioned compute tier that are frequently rescaled and customers who prefer to delegate compute rescaling to the service.

- New single databases without usage history where compute sizing is difficult or not possible to estimate before deployment in an Azure SQL Database.

### Scenarios well suited for provisioned compute

- Single databases with more regular, predictable usage patterns and higher average compute utilization over time.

- Databases that cannot tolerate performance trade-offs resulting from more frequent memory trimming or delays in resuming from a paused state.

- Multiple databases with intermittent, unpredictable usage patterns that can be consolidated into elastic pools for better price-performance optimization.

### Compare compute tiers

The following table summarizes distinctions between the serverless compute tier and the provisioned compute tier:

**Serverless compute**
**Provisioned compute**

**Database usage pattern**
Intermittent, unpredictable usage with lower average compute utilization over time.
More regular usage patterns with higher average compute utilization over time, or multiple databases using elastic pools.

**Performance management effort**
Lower
Higher

**Compute scaling**
Automatic
Manual

**Compute responsiveness**
Lower after inactive periods
Immediate

**Billing granularity**
Per second
Per hour

## Purchasing model and service tier

The following table describes serverless support based on purchasing model, service tiers, and hardware:

**Category**
**Supported**
**Not supported**

**Purchasing model**
[vCore](service-tiers-vcore?view=azuresql)
[DTU](service-tiers-dtu?view=azuresql)

**Service tier**
[General Purpose](service-tiers-sql-database-vcore?view=azuresql#general-purpose) 
 [Hyperscale](service-tier-hyperscale?view=azuresql)
Business Critical

**Hardware**
Standard-series (Gen5)
All other hardware

## Autoscaling

### Scaling responsiveness

Serverless databases are run on a machine with sufficient capacity to satisfy resource demand without interruption for any amount of compute requested, within limits set by the maximum vCores value. Occasionally, load balancing automatically occurs if the machine is unable to satisfy resource demand within a few minutes. For example, if the resource demand is 4 vCores, but only 2 vCores are available, then it can take up to a few minutes to load balance before 4 vCores are provided. The database remains online during load balancing except for a brief period at the end of the operation when connections are dropped.

### Memory management

In both the General Purpose and Hyperscale service tiers, memory for serverless databases is reclaimed more frequently than for provisioned compute databases. This behavior is important to control costs in serverless and can impact performance.

#### Cache reclamation

Unlike provisioned compute databases, memory from the SQL cache is reclaimed from a serverless database when CPU or active cache utilization is low.

- Active cache utilization is considered low when the total size of the most recently used cache entries falls below a threshold, for a period of time.

- When cache reclamation is triggered, the target cache size is reduced incrementally to a fraction of its previous size and reclaiming only continues if usage remains low.

- When cache reclamation occurs, the policy for selecting cache entries to evict is the same selection policy as for provisioned compute databases when memory pressure is high.

- The cache size is never reduced below the minimum memory limit as defined by minimum vCores.

In both serverless and provisioned compute databases, cache entries can be evicted if all available memory is used.

When CPU utilization is low, active cache utilization can remain high depending on the usage pattern and prevent memory reclamation. Also, there can be other delays after user activity stops before memory reclamation occurs due to periodic background processes responding to prior user activity. For example, delete operations and Query Store cleanup tasks generate ghost records that are marked for deletion, but are not physically deleted until the ghost cleanup process runs. Ghost cleanup might involve reading data pages into cache.

#### Cache hydration

The SQL memory cache grows as data is fetched from disk in the same way and with the same speed as for provisioned databases. When the database is busy, the cache is allowed to grow unconstrained while there is available memory.

### Disk cache management

In the Hyperscale service tier for both serverless and provisioned compute tiers, each compute replica uses a Resilient Buffer Pool Extension (RBPEX) cache, which stores data pages on local SSD to improve IO performance. However, in the serverless compute tier for Hyperscale, the RBPEX cache for each compute replica automatically grows and shrinks in response to increasing and decreasing workload demand. The maximum size the RBPEX cache can grow to is three times the maximum memory configured for the database. For details on maximum memory and RBPEX auto-scaling limits in serverless, see [serverless Hyperscale resource limits](resource-limits-vcore-single-databases?view=azuresql#hyperscale---serverless-compute---standard-series-gen5).

## Auto-pause and auto-resume

Currently, serverless auto-pausing and auto-resuming are only supported in the General Purpose tier.

### Auto-pause

Auto-pausing is triggered if all of the following conditions are true during the auto-pause delay:

- Number of sessions = 0

- CPU = 0 for user workload running in the user resource pool

An option is provided to disable auto-pausing if desired.

The following features do not support auto-pausing, but do support auto-scaling. If any of the following features are used, then auto-pausing must be disabled and the database remains online regardless of the duration of database inactivity:

- Geo-replication ([active geo-replication](active-geo-replication-overview?view=azuresql) and [failover groups](failover-group-sql-db?view=azuresql)).

- [Long-term backup retention](long-term-retention-overview?view=azuresql) (LTR).

- The sync database used in [SQL Data Sync](sql-data-sync-data-sql-server-sql-database?view=azuresql). Unlike sync databases, hub and member databases support auto-pausing.

- [DNS alias](dns-alias-overview?view=azuresql) created for the logical server containing a serverless database.

- [Elastic Jobs](elastic-jobs-overview?view=azuresql), Auto-pause enabled serverless database is not supported as a *Job Database*. Serverless databases targeted by elastic jobs do support auto-pausing. Job connections resume a database.

Auto-pausing is temporarily prevented during the deployment of some service updates, which require the database be online. In such cases, auto-pausing becomes allowed again once the service update completes.

#### Auto-pause troubleshooting

If auto-pausing is enabled and features that block auto-pausing are not used, but a database does not auto-pause after the delay period, then application or user sessions might be preventing auto-pausing.

To see if there are any application or user sessions currently connected to the database, connect to the database using any client tool, and execute the following query:

```
SELECT session_id,
       host_name,
       program_name,
       client_interface_name,
       login_name,
       status,
       login_time,
       last_request_start_time,
       last_request_end_time
FROM sys.dm_exec_sessions AS s
INNER JOIN sys.dm_resource_governor_workload_groups AS wg
ON s.group_id = wg.group_id
WHERE s.session_id <> @@SPID
      AND
      (
          (
          wg.name like 'UserPrimaryGroup.DB%'
          AND
          TRY_CAST(RIGHT(wg.name, LEN(wg.name) - LEN('UserPrimaryGroup.DB') - 2) AS int) = DB_ID()
          )
      OR
      wg.name = 'DACGroup'
      );

```

Tip

After running the query, make sure to disconnect from the database. Otherwise, the open session used by the query prevents auto-pausing.

- If the result set is nonempty, it indicates that there are sessions currently preventing auto-pausing.

- If the result set is empty, it is still possible that sessions were open, possibly for a short time, at some point earlier during the auto-pause delay period. To check for activity during the delay period, you can use [Auditing for Azure SQL Database and Azure Synapse Analytics](auditing-overview?view=azuresql) and examine audit data for the relevant period.

Important

The presence of open sessions, with or without concurrent CPU utilization in the user resource pool, is the most common reason for a serverless database to not auto-pause as expected.

### Auto-resume

Auto-resuming is triggered if any of the following conditions are true at any time:

Feature
Auto-resume trigger

Authentication and authorization
Login attempt

Threat detection
Enabling/disabling threat detection settings at the database or server level.
Modifying threat detection settings at the database or server level.

Data discovery and classification
Adding, modifying, deleting, or viewing sensitivity labels

Auditing
Viewing auditing records.
Updating or viewing auditing policy.

Data masking
Adding, modifying, deleting, or viewing data masking rules

Transparent data encryption
Viewing state or status of transparent data encryption

Vulnerability assessment
Manually initiated scans and periodic scans if enabled

Query (performance) data store
Modifying or viewing Query Store settings

Performance recommendations
Viewing or applying performance recommendations

Auto-tuning
Application and verification of auto-tuning recommendations such as auto-indexing

Database copying
Create database as copy.
Export to a BACPAC file.

SQL data sync
Synchronization between hub and member databases that run on a configurable schedule or are performed manually

Modifying certain database metadata
Adding or modify Azure tags on the database.
Changing maximum vCores, minimum vCores, or auto-pause delay.

SQL Server Management Studio (SSMS)
When using SSMS versions earlier than 18.1 and opening a new query window for any database in the server, any auto-paused database in the same server is resumed. This behavior does not occur if using SSMS version 18.1 or later.

Monitoring, management, or other solutions performing any of these operations trigger auto-resuming. Auto-resuming is also triggered during the deployment of some service updates that require the database be online.

#### Auto-resume trigger identification

Auto-resume triggers are exposed in the [Azure Monitor acti

... [Content truncated]