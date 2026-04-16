# Quickstart: Create a PHP Web App - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/quickstart-php
> Cached: 2026-04-16T20:57:22.979Z

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
					
				
			
		
	
					# Create a PHP web app in Azure App Service

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					

Warning

PHP on Windows reached the [end of support](https://github.com/Azure/app-service-linux-docs/blob/master/Runtime_Support/php_support.md#end-of-life-for-php-74) in November 2022. PHP is supported only for App Service on Linux. This article is for reference only.

[Azure App Service](overview) provides a highly scalable, self-patching web hosting service.  This quickstart tutorial shows how to deploy a PHP app to Azure App Service on Windows.

You create the web app using the [Azure CLI](/en-us/cli/azure/get-started-with-azure-cli) in Cloud Shell, and you use Git to deploy sample PHP code to the web app.

You can follow the steps here using a Mac, Windows, or Linux machine. Once the prerequisites are installed, it takes about five minutes to complete the steps.

If you don't have an Azure account, create a [free account](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn) before you begin.

Note

[After November 28, 2022, PHP will only be supported on App Service on Linux.](https://github.com/Azure/app-service-linux-docs/blob/master/Runtime_Support/php_support.md#end-of-life-for-php-74)

## Prerequisites

To complete this quickstart:

- [Install Git](https://git-scm.com/)

- [Install PHP](https://php.net/manual/install.php)

## Download the sample locally

In a terminal window, run the following commands. It will clone the sample application to your local machine, and navigate to the directory containing the sample code.

```
git clone https://github.com/Azure-Samples/php-docs-hello-world
cd php-docs-hello-world

```

Make sure the default branch is `main`.

```
git branch -m main

```

Tip

The branch name change isn't required by App Service. However, since many repositories are changing their default branch to `main`, this quickstart also shows you how to deploy a repository from `main`.

## Run the app locally

Run the application locally so that you see how it should look when you deploy it to Azure. Open a terminal window and use the `php` command to launch the built-in PHP web server.

```
php -S localhost:8080

```

Open a web browser, and navigate to the sample app at `http://localhost:8080`.

You see the **Hello World!** message from the sample app displayed in the page.

In your terminal window, press **Ctrl+C** to exit the web server.

## Azure Cloud Shell

Azure hosts Azure Cloud Shell, an interactive shell environment that you can use through your browser. You can use either Bash or PowerShell with Cloud Shell to work with Azure services. You can use the Cloud Shell preinstalled commands to run the code in this article, without having to install anything on your local environment.

To start Azure Cloud Shell:

Option
Example/Link

Select **Try It** in the upper-right corner of a code or command block. Selecting **Try It** doesn't automatically copy the code or command to Cloud Shell.

Go to [https://shell.azure.com](https://shell.azure.com), or select the **Launch Cloud Shell** button to open Cloud Shell in your browser.
[](https://shell.azure.com)

Select the **Cloud Shell** button on the menu bar at the upper right in the [Azure portal](https://portal.azure.com).

To use Azure Cloud Shell:

Start Cloud Shell.

Select the **Copy** button on a code block (or command block) to copy the code or command.

Paste the code or command into the Cloud Shell session by selecting **Ctrl**+**Shift**+**V** on Windows and Linux, or by selecting **Cmd**+**Shift**+**V** on macOS.

Select **Enter** to run the code or command.

## Configure a deployment user

FTP and local Git can deploy to an Azure web app by using a *deployment user*. Once you configure your deployment user, you can use it for all your Azure deployments. Your account-level deployment username and password are different from your Azure subscription credentials.

To configure the deployment user, run the [az webapp deployment user set](/en-us/cli/azure/webapp/deployment/user#az-webapp-deployment-user-set) command in Azure Cloud Shell. Replace <username> and <password> with a deployment user username and password.

- The username must be unique within Azure, and for local Git pushes, must not contain the ‘@’ symbol.

- The password must be at least eight characters long, with two of the following three elements: letters, numbers, and symbols.

```
az webapp deployment user set --user-name <username> --password <password>

```

The JSON output shows the password as `null`. If you get a `'Conflict'. Details: 409` error, change the username. If you get a `'Bad Request'. Details: 400` error, use a stronger password.

Record your username and password to use to deploy your web apps.

## Create a resource group

A [resource group](/en-us/azure/azure-resource-manager/management/overview#terminology) is a logical container into which Azure resources, such as web apps, databases, and storage accounts, are deployed and managed. For example, you can choose to delete the entire resource group in one simple step later.

In the Cloud Shell, create a resource group with the [`az group create`](/en-us/cli/azure/group) command. The following example creates a resource group named *myResourceGroup* in the *West Europe* location. To see all supported locations for App Service in **Free** tier, run the [`az appservice list-locations --sku FREE`](/en-us/cli/azure/appservice) command.

```
az group create --name myResourceGroup --location "West Europe"

```

You generally create your resource group and the resources in a region near you.

When the command finishes, a JSON output shows you the resource group properties.

## Create an Azure App Service plan

In the Cloud Shell, create an App Service plan with the [`az appservice plan create`](/en-us/cli/azure/appservice/plan) command.

The following example creates an App Service plan named `myAppServicePlan` in the **Free** pricing tier:

```
az appservice plan create --name myAppServicePlan --resource-group myResourceGroup --sku FREE --is-linux

```

When the App Service plan has been created, the Azure CLI shows information similar to the following example:

{ 
  "freeOfferExpirationTime": null,
  "geoRegion": "West Europe",
  "hostingEnvironmentProfile": null,
  "id": "/subscriptions/0000-0000/resourceGroups/myResourceGroup/providers/Microsoft.Web/serverfarms/myAppServicePlan",
  "kind": "linux",
  "location": "West Europe",
  "maximumNumberOfWorkers": 1,
  "name": "myAppServicePlan",
  < JSON data removed for brevity. >
  "targetWorkerSizeId": 0,
  "type": "Microsoft.Web/serverfarms",
  "workerTierName": null
} 

## Create a web app

In the Cloud Shell, create a web app in the `myAppServicePlan` App Service plan with the [`az webapp create`](/en-us/cli/azure/webapp#az_webapp_create) command.

In the following example, replace `<app-name>` with a globally unique app name (valid characters are `a-z`, `0-9`, and `-`). The runtime is set to `PHP|7.4`. To see all supported runtimes, run [`az webapp list-runtimes`](/en-us/cli/azure/webapp#az_webapp_list_runtimes).

```
az webapp create --resource-group myResourceGroup --plan myAppServicePlan --name <app-name> --runtime 'PHP|8.1' --deployment-local-git

```

When the web app has been created, the Azure CLI shows output similar to the following example:

  Local git is configured with url of <URL>
 {
   "availabilityState": "Normal",
   "clientAffinityEnabled": true,
   "clientCertEnabled": false,
   "cloningInfo": null,
   "containerSize": 0,
   "dailyMemoryTimeQuota": 0,
   "defaultHostName": "<app-name>.azurewebsites.net",
   "enabled": true,
   < JSON data removed for brevity. >
 }
 
You've created an empty new web app, with git deployment enabled.

Note

The URL of the Git remote is shown in the `deploymentLocalGitUrl` property. Save this URL as you need it later.

Browse to your newly created web app.

Here's what your new web app should look like:

## Push to Azure from Git

Because you're deploying the `main` branch, you need to set the default deployment branch for your App Service app to `main`. (See [Change deployment branch](deploy-local-git#change-deployment-branch).) In the Cloud Shell, set the `DEPLOYMENT_BRANCH` app setting by using the [`az webapp config appsettings set`](/en-us/cli/azure/webapp/config/appsettings#az-webapp-config-appsettings-set) command.

```
az webapp config appsettings set --name <app-name> --resource-group myResourceGroup --settings DEPLOYMENT_BRANCH='main'

```

Back in the local terminal window, add an Azure remote to your local Git repository. Replace *<deploymentLocalGitUrl-from-create-step>* with the URL of the Git remote that you saved from [Create a web app](#create-a-web-app).

```
git remote add azure <deploymentLocalGitUrl-from-create-step>

```

Push to the Azure remote to deploy your app with the following command. When Git Credential Manager prompts you for credentials, make sure you enter the credentials you created in **Configure local git deployment**, not the credentials you use to sign in to the Azure portal.

```
git push azure main

```

This command might take a few minutes to run. While running, it displays information similar to the following example:

    Counting objects: 2, done.
  Delta compression using up to 4 threads.
  Compressing objects: 100% (2/2), done.
  Writing objects: 100% (2/2), 352 bytes | 0 bytes/s, done.
  Total 2 (delta 1), reused 0 (delta 0)
  remote: Updating branch 'main'.
  remote: Updating submodules.
  remote: Preparing deployment for commit id '25f18051e9'.
  remote: Generating deployment script.
  remote: Running deployment command...
  remote: Handling Basic Web Site deployment.
  remote: Kudu sync from: '/home/site/repository' to: '/home/site/wwwroot'
  remote: Copying file: '.gitignore'
  remote: Copying file: 'LICENSE'
  remote: Copying file: 'README.md'
  remote: Copying file: 'index.php'
  remote: Ignoring: .git
  remote: Finished successfully.
  remote: Running post deployment command(s)...
  remote: Deployment successful.
  To <URL>
      cc39b1e..25f1805  main -> main
  
## Browse to the app

Browse to the deployed application using your web browser.

The PHP sample code is running in an Azure App Service web app.

**Congratulations!** You've deployed your first PHP app to App Service.

## Update locally and redeploy the code

Using a local text editor, open the `index.php` file within the PHP app, and make a small change to the text within the string next to `echo`:

```
echo "Hello Azure!";

```

In the local terminal window, commit your changes in Git, and then push the code changes to Azure.

```
git commit -am "updated output"
git push azure main

```

Once deployment has completed, return to the browser window that opened during the **Browse to the app** step, and refresh the page.

## Manage your new Azure app

Go to the [Azure portal](https://portal.azure.com) to manage the web app you created. Search for and select **App Services**.

Select the name of your Azure app.

Your web app's **Overview** page will be displayed. Here, you can perform basic management tasks like **Browse**, **Stop**, **Restart**, and **Delete**.

The web app menu provides different options for configuring your app.

## Clean up resources

In the preceding steps, you created Azure resources in a resource group. If you don't expect to need these resources in the future, delete the resource group by running the following command in the Cloud Shell:

```
az group delete --name myResourceGroup

```

This command might take a minute to run.

[Azure App Service](overview) provides a highly scalable, self-patching service for web hosting. This quickstart shows how to deploy a PHP app to Azure App Service on Linux.

You can follow the steps here using a Mac, Windows, or Linux machine. Once the prerequisites are installed, it takes about ten minutes to complete the steps.

## Prerequisites

- An Azure account with an active subscription. [Create an account for free](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn).

- [Git](https://git-scm.com/)

- [PHP](https://php.net/downloads.php)

- [Azure CLI](/en-us/cli/azure/install-azure-cli) to run commands in any shell to create and configure Azure resources.

## Download the sample repository

[Azure CLI](#tabpanel_1_cli)

[Portal](#tabpanel_1_portal)

In the following steps, you create the web app by using the [Azure CLI](/en-us/cli/azure/get-started-with-azure-cli), and then you deploy sample PHP code to the web app.

You can use the [Azure Cloud Shell](https://shell.azure.com).

In a terminal window, run the following commands to clone the sample application to your local machine and navigate to the project root.

```
git clone https://github.com/Azure-Samples/php-docs-hello-world
cd php-docs-hello-world

```

To run the application locally, use the `php` command to launch the built-in PHP web server.

```
php -S localhost:8080

```

Browse to the sample application at `http://localhost:8080` in a web browser.

In your terminal window, press **Ctrl+C** to exit the web server.

In your browser, navigate to the repository containing [the sample code](https://github.com/Azure-Samples/php-docs-hello-world).

In the upper right corner, select **Fork**.

On the **Create a new fork** screen, confirm the **Owner** and **Repository name** fields. Select **Create fork**.

Note

This should take you to the new fork. Your fork URL looks something like this: `https://github.com/YOUR_GITHUB_ACCOUNT_NAME/php-docs-hello-world`

## Deploy your application code to Azure

[Azure CLI](#tabpanel_2_cli)

[Portal](#tabpanel_2_portal)

Azure

... [Content truncated]