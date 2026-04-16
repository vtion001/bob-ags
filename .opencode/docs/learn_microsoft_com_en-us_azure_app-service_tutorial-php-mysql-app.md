# Tutorial: PHP app with MySQL and Redis - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/tutorial-php-mysql-app
> Cached: 2026-04-16T20:57:28.728Z

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
					
				
			
		
	
					# Tutorial: Deploy a PHP, MySQL, and Redis app to Azure App Service

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					This tutorial shows how to create a secure PHP app in Azure App Service connects to a MySQL database using Azure Database for MySQL Flexible Server. You also deploy an Azure Cache for Redis to enable the caching code in your application. Azure App Service is a highly scalable, self-patching, web-hosting service that can easily deploy apps on Windows or Linux. When you're finished, you have a Laravel app running on Azure App Service on Linux.

## Prerequisites

- An Azure account with an active subscription. If you don't have an Azure account, you [can create one for free](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn).

- A GitHub account. You can also [get one for free](https://github.com/join).

- Knowledge of [PHP with Laravel development](https://laravel.com/).

- **(Optional)** To try GitHub Copilot, a [GitHub Copilot account](https://docs.github.com/copilot/using-github-copilot/using-github-copilot-code-suggestions-in-your-editor). A 30-day free trial is available.

## Run the sample

Set up a sample data-driven app as a starting point. The [sample repository](https://github.com/Azure-Samples/laravel-tasks) includes a [dev container](https://docs.github.com/codespaces/setting-up-your-project-for-codespaces/adding-a-dev-container-configuration/introduction-to-dev-containers) configuration. The dev container has everything you need to develop an application, including the database, cache, and all environment variables needed by the sample application. The dev container can run in a [GitHub codespace](https://docs.github.com/en/codespaces/overview), which means you can run the sample on any computer with a web browser.

**Step 1:** In a new browser window:

- Sign in to your GitHub account.

- Navigate to [https://github.com/Azure-Samples/laravel-tasks/fork](https://github.com/Azure-Samples/laravel-tasks/fork).

- Select **Create fork**.

**Step 2:** In the GitHub fork:

Select **Code** > **Create codespace on main**.
The codespace takes a few minutes to set up. Also, the provided *.env* file already contains a dummy `APP_KEY` [variable that Laravel needs to run locally](https://laravel.com/docs/11.x/encryption#configuration).

**Step 3:** In the codespace terminal:

- Run `composer install`.

- Run database migrations with `php artisan migrate`.

- Run the app with `php artisan serve`.

- When you see the notification `Your application running on port 80 is available.`, select **Open in Browser**. You should see the sample application in a new browser tab. To stop the application, type **Ctrl** + **C**.

 [!TIP]
> You can ask [GitHub Copilot](https://docs.github.com/copilot/using-github-copilot/using-github-copilot-code-suggestions-in-your-editor) about this repository. For example:
>
> * *@workspace What does this project do?*
> * *@workspace What does the .devcontainer folder do?* -->
Having issues? Check the [Troubleshooting section](#troubleshooting).

## Create App Service, database, and cache

In this step, you create the Azure resources. The steps used in this tutorial create a set of secure-by-default resources that include App Service, Azure Database for MySQL, and Azure Cache for Redis. For the creation process, you specify:

- The **Name** for the web app. It's used as part of the DNS name for your app.

- The **Region** to run the app physically in the world. It's also part of the DNS name for your app.

- The **Runtime stack** for the app. It's where you select the version of PHP to use for your app.

- The **Hosting plan** for the app. It's the pricing tier that includes the set of features and scaling capacity for your app.

- The **Resource Group** for the app. A resource group lets you group all the Azure resources needed for the application in a logical container.

Sign in to the [Azure portal](https://portal.azure.com/). Follow these steps to create your Azure App Service resources.

**Step 1:** In the Azure portal:

- In the top search bar, type *app service*.

- Select the item labeled **App Service** under the **Services** heading.

Select **Create** > **Web App**.
You can also navigate to [Create Web App](https://portal.azure.com/#create/Microsoft.WebSite) directly.

**Step 2:** In the **Create Web App** page, fill out the form as follows.

- *Name*: **msdocs-laravel-mysql**. The Azure portal creates a resource group named **msdocs-laravel-mysql_group**.

- *Runtime stack*: **PHP 8.4**.

- *Operating system*: **Linux**.

- *Region*: Any Azure region near you.

- *Linux Plan*: **Create new** and use the name **msdocs-laravel-mysql**.

- *Pricing plan*: **Basic**. When you're ready, you can [scale up](manage-scale-up) to a different pricing tier.

**Step 3:**

- Select **Next** to proceed to the **Database** tab.

- Select **Create a Database**.

- In **Engine**, select **MySQL - Flexible Server**.

- Select **Create an Azure Cache for Redis**.

- In **Name** (under Cache), enter a name for the cache.

- In **SKU**, select **Basic**.

**Step 4:**

- Select **Next** to proceed to the **Deployment** tab.

- Enable **Continuous deployment**.

- In **Organization**, select your GitHub alias.

- In **Repository**, select **laravel-tasks**.

- In **Branch**, select **main**.

- Make sure **Basic authentication** is disabled.

- Select **Review + create**.

- After validation completes, select **Create**.

**Step 5:** The deployment takes a few minutes to complete. To see the web app, select **Go to resource**. Deployment creates the following resources:

- **Resource group**: The container for all the created resources.

- **App Service plan**: Defines the compute resources for App Service. A Linux plan in the *Basic* tier is created.

- **App Service**: Represents your app and runs in the App Service plan.

- **Virtual network**: Integrated with the App Service app and isolates back-end network traffic.

- **Private endpoints**: Access endpoints for the database server and the Redis cache in the virtual network.

- **Network interfaces**: Represents private IP addresses, one for each of the private endpoints.

- **Azure Database for MySQL Flexible Server**: Accessible only from behind its private endpoint. A database and a user are created for you on the server.

- **Azure Cache for Redis**: Accessible only from behind its private endpoint.

- **Private DNS zones**: Enable DNS resolution of the database server and the Redis cache in the virtual network.

## Secure connection secrets

Deployment generated the connectivity variables for you already as [app settings](configure-common#configure-app-settings). The security best practice is to keep secrets out of App Service completely. Move your secrets to a key vault and change your app setting to [Key Vault references](app-service-key-vault-references) with the help of Service Connectors.

**Step 1:** Retrieve the existing connection string.

- In the left menu of the App Service page, select **Settings** > **Environment variables**.

- Select **Connection strings**.

- Select **AZURE_MYSQL_CONNECTIONSTRING**.

In **Add/Edit application setting**, in the **Value** field, copy the username and password for use later.
The connection string lets you connect to the MySQL database secured behind private endpoints. The secrets are saved directly in the App Service app, which isn't the best. You'll change this configuration.

**Step 2:**  Create a key vault for secure management of secrets.

- In the top search bar, type "*key vault*", then select **Marketplace** > **Key Vault**.

- In **Resource Group**, select **msdocs-laravel-mysql_group**.

- In **Key vault name**, enter a name that consists of only letters and numbers.

- In **Region**, select the same location as the resource group.

**Step 3:** Secure the key vault with a Private Endpoint.

- Select the **Networking** tab.

- Unselect **Enable public access**.

- Select **Create a private endpoint**.

- In **Resource Group**, select **msdocs-laravel-mysql_group**.

- In the dialog, in **Location**, select the same location as your App Service app.

- In **Name**, enter *msdocs-laravel-mysqlVaultEndpoint*.

- In **Virtual network**, select the virtual network in the **msdocs-laravel-mysql_group** group.

- In **Subnet**, select the available compatible subnet.

- Select **OK**.

- Select **Review + create**, then select **Create**. Wait for the key vault deployment to finish. You should see **Your deployment is complete**.

**Step 4:** Create the MySQL connector.

- In the top search bar, enter *msdocs-laravel-mysql*, then select the App Service resource called **msdocs-laravel-mysql**.

- In the App Service page, in the left menu, select **Settings** > **Service Connector**.

- Select **Create**.

- For **Service type**, select **DB for MySQL flexible server**.

- For **MySQL flexible server**, select your server, for example, **msdocs-laravel-mysql-server**.

- For **MySQL database**, select your database, for example, **msdocs-laravel-mysql-database**.

**Step 5:** Configure authentication for the MySQL connector.

- Select the **Authentication** tab.

- Select **Connection string**.

- In **Password**, paste the password you copied earlier.

- Select **Store Secret in Key Vault**.

Under **Key Vault Connection**, select **Create new**.
A **Create connection** dialog is opened on top of the edit dialog.

**Step 6:** Establish the Key Vault connection.

- In the **Create connection** dialog for the Key Vault connection, in **Key Vault**, select the key vault you created earlier.

- Select **Review + Create**.

- When validation completes, select **Create**.

**Step 7:** Finalize the MySQL connector settings.

- You're back in the MySQL connector dialog. In the **Authentication** tab, wait for the key vault connector to be created. When it's finished, **Key Vault Connection** automatically selects it.

- Select **Review + Create**.

- Select **Create**. Wait until the **Update succeeded** notification appears.

**Step 8:** Configure the Redis connector to use Key Vault secrets.

- In the Service Connectors page, select the checkbox next to the Cache for Redis connector, then select **Edit**.

- Select the **Authentication** tab.

- Select **Store Secret in Key Vault**.

- Under **Key Vault Connection**, select the key vault you created.

- Select **Next: Networking**.

- Select **Configure firewall rules to enable access to target service**. The app creation wizard already secured the SQL database with a private endpoint.

- Select **Save**. Wait until the **Update succeeded** notification appears.

**Step 9:** Verify the Key Vault integration.

- From the left menu, select **Settings** > **Environment variables** again.

- Next to **AZURE_MYSQL_PASSWORD**, select **Show value**. The value should be `@Microsoft.KeyVault(...)`, which means that it's a [key vault reference](app-service-key-vault-references) because the secret is now managed in the key vault.

- To verify the Redis connection string, select **Show value** next to **AZURE_REDIS_CONNECTIONSTRING**.

To summarize, the process for securing your connection secrets involved:

- Retrieving the connection secrets from the App Service app's environment variables.

- Creating a key vault.

- Creating a Key Vault connection with the system-assigned managed identity.

- Updating the service connectors to store the secrets in the key vault.

Having issues? Check the [Troubleshooting section](#troubleshooting).

## Configure Laravel variables

**Step 1:** Create `CACHE_DRIVER` as an app setting.

- In your web app, select **Settings** > **Environment variables**.

- In the **App settings** tab, select **Add**.

- For **Name**, enter *CACHE_DRIVER*.

- For **Value**, enter *redis*.

- Select **Apply**, then **Apply** again, then **Confirm**.

**Step 2:** Using the same steps in **Step 1**, create the following app settings. After you finish, select **Apply** to update your **App settings**.

- **MYSQL_ATTR_SSL_CA**: Use */home/site/wwwroot/ssl/DigiCertGlobalRootCA.crt.pem* as the value. This app setting points to the path of the [TLS/SSL certificate you need to access the MySQL server](/en-us/azure/mysql/flexible-server/how-to-connect-tls-ssl#download-the-public-ssl-certificate). It's included in the sample repository.

- **LOG_CHANNEL**: Use *stderr* as the value. This setting tells Laravel to pipe logs to stderr, which makes it available to the App Service logs.

- **APP_DEBUG**: Use *true* as the value. It's a [Laravel debugging variable](https://laravel.com/docs/10.x/errors#configuration) that enables debug mode pages.

- **APP_KEY**: Use *base64:Dsz40HWwbCqnq0oxMsjq7fItmKIeBfCBGORfspaI1Kw=* as the value. It's a [Laravel encryption variable](https://laravel.com/docs/10.x/encryption#configuration).

Important

The `APP_KEY` value is used here for convenience. For production scenarios, it should be generated specifically for your deployment using `php artisan key:generate --show` in the command line.

Ideally, the `APP_KEY` app setting should be configured as a key vault reference too, which is a multi-step process. For more information, see [How do I change the APP_KEY app setting to a Key Vault reference?](#how-do-i-change-the-app_key-app-setting-to-a-key-vault-reference)

## Deploy sample code

In this step, you configure GitHub deployment using GitHub Actions. It's just one of many ways to deploy to App Service, but also a great way to have continuous integration in your deployment process. By default, every `git push` to your GitHub repository kicks off the build and deploy action.

**Step 1:** Back in the GitHu

... [Content truncated]