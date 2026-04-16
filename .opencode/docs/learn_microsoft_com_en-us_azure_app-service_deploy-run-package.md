# Run Your App from a ZIP Package - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/deploy-run-package
> Cached: 2026-04-16T20:57:22.966Z

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
					
				
			
		
	
					# Run your app in Azure App Service directly from a ZIP package

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					
Note

Run from package is not supported for Python apps. When deploying a ZIP file of your Python code, you need to set a flag to enable Azure build automation. The build automation will create the Python virtual environment for your app and install any necessary requirements and package needed. See [build automation](quickstart-python?tabs=flask,mac-linux,azure-cli,zip-deploy,deploy-instructions-azportal,terminal-bash,deploy-instructions-zip-azcli#enable-build-automation) for more details.

Run from package is also not supported for Java apps on Azure App Service. Built‑in Java runtimes (Java SE, Tomcat, and JBoss EAP) require write access to the app directory at startup, while Run from package mounts the app content as a read‑only filesystem.

In [Azure App Service](overview), you can run your apps directly from a deployment ZIP package file. This article shows how to enable this functionality in your app.

All other deployment methods in App Service have something in common, which is your unzip files are deployed to *D:\home\site\wwwroot* in your app (or */home/site/wwwroot* for Linux apps). Since the same directory is used by your app at runtime, it's possible for deployment to fail because of file lock conflicts, and for the app to behave unpredictably because some of the files aren't yet updated. To enable this setting, you don't need to assign any value to the `WEBSITE_RUN_FROM_PACKAGE` variable, or you can remove it entirely.

In contrast, when you run directly from a ZIP package, the files in the package aren't copied to the *wwwroot* directory. Instead, the ZIP package itself gets mounted directly as the read-only *wwwroot* directory. To enable this setting, set `WEBSITE_RUN_FROM_PACKAGE`=1 or provide the URL of the ZIP file. There are several benefits to running directly from a package:

- Eliminates file lock conflicts between deployment and runtime.

- Ensures only full-deployed apps are running at any time.

- Can be deployed to a production app (with restart).

- Improves the performance of Azure Resource Manager deployments.

- May reduce cold-start times, particularly for JavaScript functions with large npm package trees.

Note

Currently, only ZIP package files are supported.

## Create a project ZIP package

Important

When you create the ZIP package for deployment, don't include the root directory. Include only the files and directories in the root directory. If you download a GitHub repository as a ZIP file, you can't deploy that file as-is to App Service. GitHub adds nested directories at the top level, which doesn't work with App Service.

In a local terminal window, navigate to the root directory of your app project.

This directory should contain the entry file to your web app, such as `index.html`, `index.php`, and `app.js`. It can also contain package management files like `project.json`, `composer.json`, `package.json`, `bower.json`, and `requirements.txt`.

If you don't want App Service to run deployment automation for you, run all the build tasks. For example: `npm`, `bower`, `gulp`, `composer`, and `pip`. Make sure that you have all the files you need to run the app. This step is required if you want to [run your package directly](deploy-run-package).

Create a ZIP archive of everything in your project. For `dotnet` projects, add everything in the output directory of the `dotnet publish` command, excluding the output directory itself. For example, enter the following command in your terminal to create a ZIP package that includes the contents of the current directory:

```
# Bash
zip -r <file-name>.zip .

# PowerShell
Compress-Archive -Path * -DestinationPath <file-name>.zip

```

## Enable running from ZIP package

The `WEBSITE_RUN_FROM_PACKAGE` app setting enables running from a ZIP package. To set it, run the following command with Azure CLI.

```
az webapp config appsettings set --resource-group <group-name> --name <app-name> --settings WEBSITE_RUN_FROM_PACKAGE="1"

```

`WEBSITE_RUN_FROM_PACKAGE="1"` lets you run your app from a ZIP package local to your app. You can also [run from a remote package](#run-from-external-url-instead).

## Run the ZIP package

The easiest way to run a ZIP package in your App Service is with the Azure CLI [az webapp deployment source config-zip](/en-us/cli/azure/webapp/deployment/source#az-webapp-deployment-source-config-zip) command. For example:

```
az webapp deploy --resource-group <group-name> --name <app-name> --src-path <filename>.zip

```

Because the `WEBSITE_RUN_FROM_PACKAGE` app setting is set, this command doesn't extract the ZIP package content to the *D:\home\site\wwwroot* directory of your app. Instead, it uploads the ZIP file as-is to *D:\home\data\SitePackages*, and it creates a *packagename.txt* in the same directory that contains the name of the ZIP package to load at runtime. If you upload your ZIP package in a different way (such as [FTP](deploy-ftp)), you need to create the *D:\home\data\SitePackages* directory and the *packagename.txt* file manually.

The command also restarts the app. Because `WEBSITE_RUN_FROM_PACKAGE` is set, App Service mounts the uploaded package as the read-only *wwwroot* directory and runs the app directly from that mounted directory.

## Run from external URL instead

You can also run a ZIP package from an external URL, such as Azure Blob Storage. You can use the [Azure Storage Explorer](/en-us/azure/storage/storage-explorer/vs-azure-tools-storage-manage-with-storage-explorer) to upload ZIP package files to your Blob storage account. You should use a private storage container with a [Shared Access Signature (SAS)](/en-us/azure/storage/storage-explorer/vs-azure-tools-storage-manage-with-storage-explorer#generate-a-sas-in-storage-explorer) or [use a managed identity](#access-a-package-in-azure-blob-storage-using-a-managed-identity) to enable the App Service runtime to access the ZIP package securely.

Note

Currently, an existing App Service resource that runs a local ZIP package can't be migrated to run from a remote ZIP package. You'll have to create a new App Service resource configured to run from an external URL.

Once you upload your file to Blob storage and have an SAS URL for the file, set the `WEBSITE_RUN_FROM_PACKAGE` app setting to the URL. Make sure the URL ends with `.zip`. The following example does it by using Azure CLI:

```
az webapp config appsettings set --name <app-name> --resource-group <resource-group-name> --settings WEBSITE_RUN_FROM_PACKAGE="https://myblobstorage.blob.core.windows.net/content/SampleCoreMVCApp.zip?st=2018-02-13T09%3A48%3A00Z&se=2044-06-14T09%3A48%3A00Z&sp=rl&sv=2017-04-17&sr=b&sig=bNrVrEFzRHQB17GFJ7boEanetyJ9DGwBSV8OM3Mdh%2FM%3D"

```

If you publish an updated package with the same name to Blob storage, you need to restart your app so that the updated package is loaded into App Service.

### Access a package in Azure Blob Storage using a managed identity

You can configure Azure Blob Storage to [authorize requests with Microsoft Entra ID](/en-us/azure/storage/blobs/authorize-access-azure-active-directory?toc=%2fazure%2fstorage%2fblobs%2ftoc.json). This configuration means that instead of generating a SAS key with an expiration, you can instead rely on the application's [managed identity](/en-us/azure/app-service/overview-managed-identity).

By default, the app's system-assigned identity is used. If you wish to specify a user-assigned identity, you can set the `WEBSITE_RUN_FROM_PACKAGE_BLOB_MI_RESOURCE_ID` app setting to the resource ID of that identity. The setting can also accept `SystemAssigned` as a value, which is equivalent to omitting the setting.

To enable the package to be fetched using the identity:

Ensure that the blob is [configured for private access](/en-us/azure/storage/blobs/anonymous-read-access-configure#set-the-anonymous-access-level-for-a-container).

Grant the identity the [Storage Blob Data Reader](/en-us/azure/role-based-access-control/built-in-roles#storage-blob-data-reader) role with scope over the package blob. See [Assign an Azure role for access to blob data](/en-us/azure/storage/blobs/assign-azure-role-data-access) for details on creating the role assignment.

Set the `WEBSITE_RUN_FROM_PACKAGE` application setting to the blob URL of the package. This URL is usually of the form `https://<storage-account-name>.blob.core.windows.net/<container-name>/<path-to-package>` or similar.

If you wish to specify a user-assigned identity, you can set the `WEBSITE_RUN_FROM_PACKAGE_BLOB_MI_RESOURCE_ID` app setting to the resource ID of that identity. The setting can also accept *SystemAssigned* as a value, although this is the same as omitting the setting altogether. A resource ID is a standard representation for a resource in Azure. For a user-assigned managed identity, that is going to be `/subscriptions/subid/resourcegroups/rg-name/providers/Microsoft.ManagedIdentity/userAssignedIdentities/identity-name`. The resource ID of a user-assigned managed identity can be obtained in the **Settings** > **Properties** > **ID for the user assigned managed identity**.

## Deploy WebJob files when running from package

There are two ways to deploy [WebJob](webjobs-create) files when you [enable running an app from package](#enable-running-from-zip-package):

- **Deploy in the same ZIP package as your app**: Include them as you normally would in `<project-root>\app_data\jobs\...` (which maps to the deployment path `\site\wwwroot\app_data\jobs\...` as specified in the [WebJobs quickstart](webjobs-create#webjob-types)).

- **Deploy separately from the ZIP package of your app**: Since the usual deployment path `\site\wwwroot\app_data\jobs\...` is now read-only, you can't deploy WebJob files there. Instead, deploy WebJob files to `\site\jobs\...`, which isn't read only. WebJobs deployed to `\site\wwwroot\app_data\jobs\...` and `\site\jobs\...` both run.

Note

When `\site\wwwroot` becomes read-only, operations like the creation of the *disable.job* will fail.

## Troubleshooting

- Running directly from a package makes `wwwroot` read-only. Your app will receive an error if it tries to write files to this directory.

- TAR and GZIP formats are not supported.

- The ZIP file can be at most 1 GB.

- This feature isn't compatible with [local cache](overview-local-cache).

- For improved cold-start performance, use the local Zip option (`WEBSITE_RUN_FROM_PACKAGE`=1).

## Related content

- [Continuous deployment for Azure App Service](deploy-continuous-deployment)

- [Deploy code with a ZIP or WAR file](deploy-zip)

					
		
	 
		
		
	
					
		
		
			
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
		2026-04-01