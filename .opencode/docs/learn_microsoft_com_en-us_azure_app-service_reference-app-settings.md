# Environment Variables and App Settings Reference - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/reference-app-settings
> Cached: 2026-04-16T20:57:17.309Z

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
					
				
			
		
	
					# Environment variables and app settings in Azure App Service

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					In [Azure App Service](overview), certain settings are available to the deployment or runtime environment as environment variables. You can customize some of these settings when you set them manually as [app settings](configure-common#configure-app-settings). This reference shows the variables that you can use or customize.

## App environment

The following environment variables are related to the app environment in general.

Setting name
Description

`WEBSITE_SITE_NAME`
Read-only. App name.

`WEBSITE_RESOURCE_GROUP`
Read-only. Azure resource group name that contains the app resource.

`WEBSITE_OWNER_NAME`
Read-only. Contains the Azure subscription ID that owns the app, the resource group, and the webspace.

`REGION_NAME`
Read-only. Region name of the app.

`WEBSITE_PLATFORM_VERSION`
Read-only. App Service platform version.

`HOME`
Read-only. Path to the home directory (for example, `D:\home` for Windows).

`SERVER_PORT`
Read-only. Port that the app should listen to.

`WEBSITE_WARMUP_PATH`
Relative path to ping to warm up the app, beginning with a slash. The default is `/robots933456.txt`.

Whenever the platform starts up a container, the orchestrator makes repeated requests against this endpoint. The platform considers any response from this endpoint as an indication that the container is ready. When the platform considers the container to be ready, it starts forwarding organic traffic to the newly started container. Unless `WEBSITE_WARMUP_STATUSES` is configured, the platform considers any response from the container at this endpoint (even error codes such as 404 or 502) as an indication that the container is ready.

This app setting doesn't change the path that Always On uses.

`WEBSITE_WARMUP_STATUSES`
Comma-delimited list of HTTP status codes that are considered successful when the platform makes warm-up pings against a newly started container. Used with `WEBSITE_WARMUP_PATH`.

By default, any status code is considered an indication that the container is ready for organic traffic. You can use this app to require a specific response before organic traffic is routed to the container.

An example is `200,202`. If pings against the app's configured warm-up path receive a response with a 200 or 202 status code, organic traffic is routed to the container. If a status code that isn't in the list is received (such as 502), the platform continues to make pings until a 200 or 202 is received, or until the container startup timeout limit is reached. (See `WEBSITES_CONTAINER_START_TIME_LIMIT` later in this table.)

If the container doesn't respond with an HTTP status code that's in the list, the platform eventually fails the startup attempt and retries, which results in 503 errors.

`WEBSITE_COMPUTE_MODE`
Read-only. Specifies whether the app runs on dedicated (`Dedicated`) or shared (`Shared`) virtual machines (VMs).

`WEBSITE_SKU`
Read-only. Pricing tier of the app. Possible values are `Free`, `Shared`, `Basic`, and `Standard`.

`SITE_BITNESS`
Read-only. Shows whether the app is 32 bit (`x86`) or 64 bit (`AMD64`).

`WEBSITE_HOSTNAME`
Read-only. Primary host name for the app. This setting doesn't account for custom host names.

`WEBSITE_DEFAULT_HOSTNAME`
Read-only. The default host name for the app. This could be either in the original format `<sitename>.azurewebsites.net` or the unique hostname `<sitename>-<randomhash>.<region>.azurewebsites.net`. This setting is sticky and not swappable.

`WEBSITE_VOLUME_TYPE`
Read-only. Shows the storage volume type currently in use.

`WEBSITE_NPM_DEFAULT_VERSION`
Default npm version that the app is using.

`WEBSOCKET_CONCURRENT_REQUEST_LIMIT`
Read-only. Limit for concurrent WebSocket requests. For the `Standard` tier and higher, the value is `-1`, but there's still a per-VM limit based on your VM size. See [Cross VM Numerical Limits](https://github.com/projectkudu/kudu/wiki/Azure-Web-App-sandbox#cross-vm-numerical-limits).

`WEBSITE_PRIVATE_EXTENSIONS`
Set to `0` to disable the use of private site extensions.

`WEBSITE_TIME_ZONE`
By default, the time zone for the app is always UTC. You can change it to any of the valid values that are listed in [Default time zones](/en-us/windows-hardware/manufacture/desktop/default-time-zones). If the specified value isn't recognized, the app uses UTC. 

Example: `Atlantic Standard Time`

`WEBSITE_ADD_SITENAME_BINDINGS_IN_APPHOST_CONFIG`
After slot swaps, the app might experience unexpected restarts. The reason is that after a swap, the host-name binding configuration goes out of sync, which by itself doesn't cause restarts. However, certain underlying storage events (such as storage volume failovers) might detect these discrepancies and force all worker processes to restart.

To minimize these types of restarts, set the app setting value to `1` on all slots. (The default is `0`.) But don't set this value if you're running a Windows Communication Foundation application. For more information, see [Troubleshoot swaps](deploy-staging-slots#troubleshoot-swaps).

`WEBSITE_PROACTIVE_AUTOHEAL_ENABLED`
By default, a VM instance is proactively corrected when it uses more than 90% of allocated memory for more than 30 seconds, or when 80% of the total requests in the last two minutes take longer than 200 seconds. If a VM instance triggers one of these rules, the recovery process is an overlapping restart of the instance.

Set to `false` to disable this recovery behavior. The default is `true`.

For more information, see the [Introducing Proactive Auto Heal](https://azure.github.io/AppService/2017/08/17/Introducing-Proactive-Auto-Heal.html) blog post.

`WEBSITE_PROACTIVE_CRASHMONITORING_ENABLED`
Whenever the w3wp.exe process on a VM instance of your app crashes due to an unhandled exception for more than three times in 24 hours, a debugger process is attached to the main worker process on that instance. The debugger process collects a memory dump when the worker process crashes again. This memory dump is then analyzed, and the call stack of the thread that caused the crash is logged in your App Service logs.

Set to `false` to disable this automatic monitoring behavior. The default is `true`.

For more information, see the [Proactive Crash Monitoring in Azure App Service](https://azure.github.io/AppService/2021/03/01/Proactive-Crash-Monitoring-in-Azure-App-Service.html) blog post.

`WEBSITE_DAAS_STORAGE_SASURI`
During crash monitoring (proactive or manual), the memory dumps are deleted by default. To save the memory dumps to a storage blob container, specify the shared access signature (SAS) URI.

`WEBSITE_CRASHMONITORING_ENABLED`
Set to `true` to enable [crash monitoring](https://azure.github.io/AppService/2020/08/11/Crash-Monitoring-Feature-in-Azure-App-Service.html) manually. You must also set `WEBSITE_DAAS_STORAGE_SASURI` and `WEBSITE_CRASHMONITORING_SETTINGS`. The default is `false`.

This setting has no effect if remote debugging is enabled. Also, if this setting is set to `true`, [proactive crash monitoring](https://azure.github.io/AppService/2021/03/01/Proactive-Crash-Monitoring-in-Azure-App-Service.html) is disabled.

`WEBSITE_CRASHMONITORING_SETTINGS`
JSON with the following format:`{"StartTimeUtc": "2020-02-10T08:21","MaxHours": "<elapsed-hours-from-StartTimeUtc>","MaxDumpCount": "<max-number-of-crash-dumps>"}`. Required to configure [crash monitoring](https://azure.github.io/AppService/2020/08/11/Crash-Monitoring-Feature-in-Azure-App-Service.html) if `WEBSITE_CRASHMONITORING_ENABLED` is specified. To log the call stack without saving the crash dump in the storage account, add `,"UseStorageAccount":"false"` in the JSON.

`REMOTEDEBUGGINGVERSION`
Remote debugging version.

`WEBSITE_CONTENTAZUREFILECONNECTIONSTRING`
By default, App Service creates a shared storage for you at app creation. To use a custom storage account instead, set to the connection string of your storage account. For functions, see [App settings reference for Azure Functions](../azure-functions/functions-app-settings#website_contentazurefileconnectionstring).

Example: `DefaultEndpointsProtocol=https;AccountName=<name>;AccountKey=<key>`

`WEBSITE_CONTENTSHARE`
When you use specify a custom storage account with `WEBSITE_CONTENTAZUREFILECONNECTIONSTRING`, App Service creates a file share in that storage account for your app. To use a custom name, set this variable to the name that you want. If a file share with the specified name doesn't exist, App Service creates it for you.

Example: `myapp123`

`WEBSITE_BYOS_BLOB_DIRECT_IO`
Set to `false` by default. If enabled, all transactions will query the remote storage directly and caching will be bypassed. This setting is applied at the application level and therefore affects all blob shares mounted by the application.

 Only relevant when using custom-mounted Azure Blob Storage. Applicable to Linux containers only (not applicable to Windows).

`WEBSITE_SCM_ALWAYS_ON_ENABLED`
Read-only. Shows whether Always On is enabled (`1`) or not (`0`).

`WEBSITE_SCM_SEPARATE_STATUS`
Read-only. Shows whether the Kudu app is running in a separate process (`1`) or not (`0`).

`WEBSITE_DNS_ATTEMPTS`
Number of times to try name resolution.

`WEBSITE_DNS_TIMEOUT`
Number of seconds to wait for name resolution.

`WEBSITES_CONTAINER_START_TIME_LIMIT`
Amount of time (in seconds) that the platform waits for a container to become ready on startup. This setting applies to both code-based and container-based apps on App Service for Linux. The default value is `230`. For Linux, the startup time limit must be between a minimum of `10` seconds, and a maximum of `1800` seconds. 

When a container starts up, repeated pings are made against the container to gauge its readiness to serve organic traffic. (See `WEBSITE_WARMUP_PATH` and `WEBSITE_WARMUP_STATUSES`.) These pings are continuously made until either a successful response is received or the start time limit is reached. If the container isn't deemed ready within the configured timeout, the platform fails the startup attempt and retries, which results in 503 errors.

For App Service for Windows containers, the default start time limit is `10 mins`. You can change the start time limit by specifying a time span. For example, `00:05:00` indicates 5 minutes. The time span for Windows Containers must be between a minimum of `00:01:00` - 1 minute, and maximum of `00:15:00` - 15 minutes.

## Variable prefixes

The following table shows environment variable prefixes that App Service uses for various purposes.

Setting name
Description

`APPSETTING_`
Signifies that the customer sets a variable as an app setting in the app configuration. It's injected into a .NET app as an app setting.

`MAINSITE_`
Signifies that a variable is specific to the app itself.

`SCMSITE_`
Signifies that a variable is specific to the Kudu app.

`SQLCONNSTR_`
SQL Server connection string in the app configuration. It's injected into a .NET app as a connection string.

`SQLAZURECONNSTR_`
Azure SQL Database connection string in the app configuration. It's injected into a .NET app as a connection string.

`POSTGRESQLCONNSTR_`
PostgreSQL connection string in the app configuration. It's injected into a .NET app as a connection string.

`CUSTOMCONNSTR_`
Custom connection string in the app configuration. It's injected into a .NET app as a connection string.

`MYSQLCONNSTR_`
MySQL database connection string in the app configuration. It's injected into a .NET app as a connection string.

`AZUREFILESSTORAGE_`
Connection string to a custom share for a custom container in Azure Files.

`AZUREBLOBSTORAGE_`
Connection string to a custom storage account for a custom container in Azure Blob Storage.

`NOTIFICATIONHUBCONNSTR_`
Connection string to a notification hub in Azure Notification Hubs.

`SERVICEBUSCONNSTR_`
Connection string to an instance of Azure Service Bus.

`EVENTHUBCONNSTR_`
Connection string to an event hub in Azure Event Hubs.

`DOCDBCONNSTR_`
Connection string to a database in Azure Cosmos DB.

`REDISCACHECONNSTR_`
Connection string to a cache in Azure Cache for Redis.

`FILESHARESTORAGE_`
Connection string to a custom file share.

## Deployment

The following environment variables are related to app deployment. For variables related to App Service build automation, see [Build automation](#build-automation) later in this article.

Setting name
Description

`DEPLOYMENT_BRANCH`
For [local Git](deploy-local-git) or [cloud Git](deploy-continuous-deployment) deployment (such as GitHub), set to the branch in Azure that you want to deploy to. By default, it's `master`.

`WEBSITE_RUN_FROM_PACKAGE`
Set to `1` to run the app from a local ZIP package, or set to an external URL to run the app from a remote ZIP package. For more information, see [Run your app in Azure App Service directly from a ZIP package](deploy-run-package).

`WEBSITE_USE_ZIP`
Deprecated. Use `WEBSITE_RUN_FROM_PACKAGE`.

`WEBSITE_RUN_FROM_ZIP`
Deprecated. Use `WEBSITE_RUN_FROM_PACKAGE`.

`SCM_MAX_ZIP_PACKAGE_COUNT`
Your app keeps five of the most recent ZIP files deployed via [ZIP deploy](deploy-zip). You can keep more or fewer by changing the app setting to a different number.

`WEBSITE_WEBDEPLOY_USE_SCM`
Set to `false` for Web Deploy to stop using the Kudu deployment engine. The default is `true`. To deploy to Linux apps by using Visual Studio (Web Deploy/MSDeploy), set it to `false`.

`MSDEPLOY_RENAME_LOCKED_FILES`
Set to `1` to attempt to rename DLLs if they can't be copied during a Web Deploy deployment. This setting isn't applicable if `WEBSITE_WEBDEPLOY_USE_SCM` is set to `false`.

`WEBSITE_DISABLE_SC

... [Content truncated]