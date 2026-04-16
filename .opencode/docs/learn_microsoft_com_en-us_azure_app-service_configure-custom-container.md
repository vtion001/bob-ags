# Configure a Custom Container - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/configure-custom-container
> Cached: 2026-04-16T20:57:42.202Z

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
					
				
			
		
	
					# Configure a custom container for Azure App Service

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					This article shows you how to configure a custom container to run on Azure App Service.

Learn about key concepts and get instructions for containerization of Windows apps in App Service.

## Prerequisites

New users should first follow the [custom container quickstart](quickstart-custom-container) and [tutorial](tutorial-custom-container).

Learn about key concepts and get instructions for containerization of Linux apps in App Service.

## Prerequisites

New users should first follow the [custom container quickstart](quickstart-custom-container) and [tutorial](tutorial-custom-container). For sidecar containers, see [Tutorial: Configure a sidecar container for a custom container app](tutorial-custom-container-sidecar).

Note

Using a service principal for Windows container image pull authentication is no longer supported. We recommend that you use managed identity for both Windows and Linux containers.

## Supported parent images

Select the right [parent image (base image)](https://docs.docker.com/develop/develop-images/baseimages/) for the framework you want for your custom Windows image:

- To deploy .NET Framework apps, use a parent image based on the Windows Server [Long-Term Servicing Channel](/en-us/windows-server/get-started/servicing-channels-comparison#long-term-servicing-channel-ltsc) release.

- To deploy .NET Core apps, use a parent image based on the Windows Server [Annual Channel](/en-us/windows-server/get-started/servicing-channels-comparison#annual-channel-ac) release.

It takes some time to download a parent image during app startup. You can reduce startup time by using one of the following parent images that are already cached in Azure App Service:

- [mcr.microsoft.com/windows/servercore:ltsc2025](https://mcr.microsoft.com/artifact/mar/windows/servercore/about)

- [mcr.microsoft.com/windows/servercore:ltsc2022](https://mcr.microsoft.com/artifact/mar/windows/servercore/about)

- [mcr.microsoft.com/dotnet/framework/aspnet:4.8.1-windowsservercore-ltsc2022](https://mcr.microsoft.com/artifact/mar/dotnet/framework/aspnet/tag/4.8.1-windowsservercore-ltsc2022)

- [mcr.microsoft.com/dotnet/framework/aspnet:4.8-windowsservercore-ltsc2019](https://mcr.microsoft.com/artifact/mar/dotnet/framework/aspnet/tag/4.8-windowsservercore-ltsc2019)

- [mcr.microsoft.com/dotnet/runtime:10.0-nanoserver-ltsc2025](https://mcr.microsoft.com/artifact/mar/dotnet/runtime/tag/10.0-nanoserver-ltsc2025)

- [mcr.microsoft.com/dotnet/runtime:8.0-nanoserver-ltsc2022](https://mcr.microsoft.com/artifact/mar/dotnet/runtime/tag/8.0-nanoserver-ltsc2022)

- [mcr.microsoft.com/dotnet/aspnet:10.0-nanoserver-ltsc2025](https://mcr.microsoft.com/artifact/mar/dotnet/aspnet/tag/10.0-nanoserver-ltsc2025)

- [mcr.microsoft.com/dotnet/aspnet:8.0-nanoserver-ltsc2022](https://mcr.microsoft.com/artifact/mar/dotnet/aspnet/tag/8.0-nanoserver-ltsc2022)

## Change the Docker image of a custom container

Use the following command to change the current Docker image to a new image in an existing custom container:

```
az webapp config container set --name <app-name> --resource-group <group-name> --container-image-name <docker-hub-repo>/<image>

```

Replace the *<placeholders>* with your own values.

## Use an image from a private registry

To use an image from a private registry, such as Azure Container Registry, run the following command:

```
az webapp config container set --name <app-name> --resource-group <group-name> --container-image-name <image-name> --docker-registry-server-url <private-repo-url> --docker-registry-server-user <username> --docker-registry-server-password <password>

```

Supply the sign-in credentials for your private registry account in the *<username>* and *<password>* fields.

## Use managed identity to pull an image from Azure Container Registry

Use the following steps to configure your web app to pull from Azure Container Registry by using managed identity. The steps use system-assigned managed identity, but you can also use user-assigned managed identity.

Enable the [system-assigned managed identity](overview-managed-identity) for the web app by using the [`az webapp identity assign`](/en-us/cli/azure/webapp/identity#az-webapp-identity-assign) command:

```
az webapp identity assign --resource-group <group-name> --name <app-name> --query principalId --output tsv

```

Replace *<app-name>* with the name of your app. The output of the command, filtered by the `--query` and `--output` arguments, is the service principal ID of the assigned identity.

Get the resource ID of your container registry:

```
az acr show --resource-group <group-name> --name <registry-name> --query id --output tsv

```

Replace *<registry-name>* with the name of your registry. The output of the command, filtered by the `--query` and `--output` arguments, is the resource ID of the container registry.

Grant the managed identity permission to access the container registry:

```
az role assignment create --assignee <principal-id> --scope <registry-resource-id> --role "AcrPull"

```

Replace the following values:

- *<principal-id>* with the service principal ID from the `az webapp identity assign` command.

- *<registry-resource-id>* with the ID of your container registry from the `az acr show` command.

For more information about these permissions, see [What is Azure role-based access control?](../role-based-access-control/overview)

Configure your app to use the managed identity to pull from Azure Container Registry.

```
az webapp config set --resource-group <group-name> --name <app-name> --generic-configurations '{"acrUseManagedIdentityCreds": true}'

```

Replace *<app-name>* with the name of your web app.

Tip

If you use PowerShell console to run the commands, escape the strings in the `--generic-configurations` argument in this step and the next step. For example: `--generic-configurations '{\"acrUseManagedIdentityCreds\": true'`.

(Optional) If your app uses a [user-assigned managed identity](overview-managed-identity#add-a-user-assigned-identity), make sure the identity is configured on the web app and then set the `acrUserManagedIdentityID` property to specify its client ID:

```
az identity show --resource-group <group-name> --name <identity-name> --query clientId --output tsv

```

Replace the *<identity-name>* of your user-assigned managed identity and use the output *<client-id>* to configure the user-assigned managed identity ID.

```
az  webapp config set --resource-group <group-name> --name <app-name> --generic-configurations '{"acrUserManagedIdentityID": "<client-id>"}'

```

The web app now uses managed identity to pull from Azure Container Registry.

## Use an image from a network-protected registry

To connect and pull from a registry inside a virtual network or on-premises, your app must integrate with a virtual network. You also need virtual network integration for Azure Container Registry with a private endpoint. After you configure your network and DNS resolution, enable the routing of the image pull through the virtual network. Configure the `vnetImagePullEnabled` site setting:

```
az resource update --resource-group <group-name> --name <app-name> --resource-type "Microsoft.Web/sites" --set properties.vnetImagePullEnabled [true|false]

```

### Troubleshoot what to do if you don't see the updated container

If you change your Docker container settings to point to a new container, it might take a few minutes before the app serves HTTP requests from the new container. While the new container is pulled and started, App Service continues to serve requests from the old container. App Service only sends requests to the new container after it starts and is ready to receive requests.

### Learn how container images are stored

The first time you run a custom Docker image in App Service, App Service performs the `docker pull` command and pulls all image layers. The layers are stored on disk, the same as when you use Docker on-premises. Each time the app restarts, App Service performs the `docker pull` command. It pulls only changed layers. If there are no changes, App Service uses existing layers on the local disk.

If the app changes compute instances for any reason (like changing pricing tiers), App Service must again pull all layers. The same is true if you scale out to add more instances. Also, in rare cases, the app instances might change without a scale operation.

## Configure port number

By default, App Service assumes your custom container listens on port 80. If your container listens to a different port, set the `WEBSITES_PORT` app setting in your App Service app. You can set it by using [Azure Cloud Shell](https://shell.azure.com). In Bash, use the following command:

```
az webapp config appsettings set --resource-group <group-name> --name <app-name> --settings WEBSITES_PORT=8000

```

In PowerShell, use the following command:

```
Set-AzWebApp -ResourceGroupName <group-name> -Name <app-name> -AppSettings @{"WEBSITES_PORT"="8000"}

```

App Service currently allows your container to expose only one port for HTTP requests.

## Configure environment variables

Your custom container might use environment variables that you need to supply externally. You can pass them in by using [Cloud Shell](https://shell.azure.com). In Bash, use the following command:

```
az webapp config appsettings set --resource-group <group-name> --name <app-name> --settings DB_HOST="myownserver.mysql.database.azure.com"

```

In PowerShell, use the following command:

```
Set-AzWebApp -ResourceGroupName <group-name> -Name <app-name> -AppSettings @{"DB_HOST"="myownserver.mysql.database.azure.com"}

```

When your app runs, the App Service app settings are automatically injected into the process as environment variables. You can verify container environment variables with the URL `https://<app-name>.scm.azurewebsites.net/Env`.

When you SSH into a container with custom Docker images, you might see only a few environment variables if you try to use commands like `env` or `printenv`. To see all environment variables within the container, like ones you pass in to your application for runtime usage, add this line to your entrypoint script:

```
eval $(printenv | sed -n "s/^\([^=]\+\)=\(.*\)$/export \1=\2/p" | sed 's/"/\\\"/g' | sed '/=/s//="/' | sed 's/$/"/' >> /etc/profile)

```

See a [full example](https://github.com/azureossd/docker-container-ssh-examples/blob/main/alpine-node/init_container.sh).

If your app uses images from a private registry or from Docker Hub, the credentials for accessing the repository are saved in environment variables: `DOCKER_REGISTRY_SERVER_URL`, `DOCKER_REGISTRY_SERVER_USERNAME`, and `DOCKER_REGISTRY_SERVER_PASSWORD`. Because of security risks, none of these reserved variable names are exposed to the application.

For Internet Information Services (IIS) or .NET Framework (4.0 or later) containers, credentials are automatically injected into `System.ConfigurationManager` as .NET app settings and connection strings by App Service. For all other languages or frameworks, they're provided as environment variables for the process, with one of the following prefixes:

- `APPSETTING_`

- `SQLCONTR_`

- `MYSQLCONTR_`

- `SQLAZURECOSTR_`

- `POSTGRESQLCONTR_`

- `CUSTOMCONNSTR_`

You can use this method for both single-container or multi-container apps, where the environment variables are specified in the *docker-compose.yml* file.

## Use persistent shared storage

You can use the `C:\home` directory in your custom container file system to persist files across restarts and share them across instances. When you use the `C:\home` directory, your custom container can access persistent storage.

- When persistent storage is *disabled*, writes to the `C:\home` directory aren't persisted across app restarts or across multiple instances.

- When persistent storage is *enabled*, all writes to the `C:\home` directory persist.

All instances of a scaled-out app can access them. When the container starts, if any files are present on the persistent storage, they overwrite any contents in the `C:\home` directory of the container.

The only exception is the `C:\home\LogFiles` directory. This directory stores the container and application logs. The folder always persists upon app restarts if [application logging is enabled](troubleshoot-diagnostic-logs?#enable-application-logging-windows) with the **File System** option, whether or not persistent storage is enabled. In other words, when you enable or disable persistent storage, it doesn't affect application logging behavior.

By default, persistent storage is *enabled* on Windows custom containers. To disable it, set the `WEBSITES_ENABLE_APP_SERVICE_STORAGE` app setting value to `false` by using [Cloud Shell](https://shell.azure.com). In Bash, use the following command:

```
az webapp config appsettings set --resource-group <group-name> --name <app-name> --settings WEBSITES_ENABLE_APP_SERVICE_STORAGE=false

```

In PowerShell, use the following command:

```
Set-AzWebApp -ResourceGroupName <group-name> -Name <app-name> -AppSettings @{"WEBSITES_ENABLE_APP_SERVICE_STORAGE"=false}

```

You can use the `/home` directory in your custom container file system to persist files across restarts and share them across instances. When you use the `/home` directory, your custom container can access persistent storage. Keep in mind that data that you save within `/home` contributes to the [storage space quota](../azure-resource-manager/management/azure-subscription-service-limits#azure-app-service-limits) included with your App Servic

... [Content truncated]