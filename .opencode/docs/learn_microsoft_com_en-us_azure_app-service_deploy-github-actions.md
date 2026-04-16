# Deploy by Using GitHub Actions - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/deploy-github-actions
> Cached: 2026-04-16T20:57:42.173Z

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
					
				
			
		
	
					# Deploy to Azure App Service by using GitHub Actions

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					Use [GitHub Actions](https://docs.github.com/en/actions/learn-github-actions) to automate your workflow and deploy to [Azure App Service](overview) from GitHub.

## Prerequisites

- An Azure account with an active subscription. [Create an account for free](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn).

- A GitHub account. If you don't have one, sign up for [free](https://github.com/join).

## Set up GitHub Actions deployment when creating an app

GitHub Actions deployment is integrated into the default [Create Web App process](https://portal.azure.com/#create/Microsoft.WebSite). Set **Continuous deployment** to **Enable** in the **Deployment** tab, and configure your chosen organization, repository, and branch.

When you enable continuous deployment, the **Create Web App** process automatically picks the authentication method based on the basic authentication selection and configures your app and your GitHub repository accordingly:

Basic authentication selection
Authentication method

Disable
[User-assigned identity (OpenID Connect)](deploy-continuous-deployment#what-does-the-user-assigned-identity-option-do-for-github-actions) (recommended)

Enable
[Basic authentication](configure-basic-auth-disable)

Note

When you create an app, you might receive an error that states that your Azure account doesn't have certain permissions. Your account might need [the required permissions to create and configure the user-assigned identity](deploy-continuous-deployment#why-do-i-see-the-error-you-do-not-have-sufficient-permissions-on-this-app-to-assign-role-based-access-to-a-managed-identity-and-configure-federated-credentials). For an alternative, see the following section.

##  Set up GitHub Actions deployment from Deployment Center

For an existing app, you can quickly get started with GitHub Actions by using **Deployment Center** in App Service. This turnkey method generates a GitHub Actions workflow file based on your application stack and commits it to your GitHub repository.

By using **Deployment Center**, you can also easily configure the more secure OpenID Connect authentication with a *user-assigned identity*. For more information, see [the user-assigned identity option](deploy-continuous-deployment#what-does-the-user-assigned-identity-option-do-for-github-actions).

If your Azure account has the [needed permissions](deploy-continuous-deployment#why-do-i-see-the-error-you-do-not-have-sufficient-permissions-on-this-app-to-assign-role-based-access-to-a-managed-identity-and-configure-federated-credentials), you can create a user-assigned identity. Otherwise, you can select an existing user-assigned managed identity in the **Identity** dropdown menu. You can work with your Azure administrator to create a user-assigned managed identity with the [Website Contributor role](deploy-continuous-deployment#why-do-i-see-the-error-this-identity-does-not-have-write-permissions-on-this-app-please-select-a-different-identity-or-work-with-your-admin-to-grant-the-website-contributor-role-to-your-identity-on-this-app).

For more information, see [Continuous deployment to Azure App Service](deploy-continuous-deployment?tabs=github).

## Manually set up a GitHub Actions workflow

You can deploy a workflow without using **Deployment Center**. Perform these three steps:

- [Generate deployment credentials](#generate-deployment-credentials).

- [Configure the GitHub secret](#configure-the-github-secret).

- [Add the workflow file to your GitHub repository](#add-the-workflow-file-to-your-github-repository).

### Generate deployment credentials

We recommend that you use OpenID Connect to authenticate with Azure App Service for GitHub Actions. This authentication method uses short-lived tokens. Setting up [OpenID Connect with GitHub Actions](/en-us/azure/developer/github/connect-from-azure) is more complex but offers hardened security.

You can also authenticate with a user-assigned managed identity, a service principal, or a publish profile.

[OpenID Connect](#tabpanel_1_openid)

[Publish profile](#tabpanel_1_applevel)

[Service principal](#tabpanel_1_userlevel)

The following procedure describes the steps for creating a Microsoft Entra application, service principal, and federated credentials using Azure CLI statements. To learn how to create a Microsoft Entra application, service principal, and federated credentials in the Azure portal, see [Connect GitHub and Azure](/en-us/azure/developer/github/connect-from-azure#use-the-azure-login-action-with-openid-connect).

If you don't have an existing application, register a [new Microsoft Entra application and service principal that can access resources](../active-directory/develop/howto-create-service-principal-portal). Create the Microsoft Entra application.

```
az ad app create --display-name myApp

```

This command returns a JSON output with an `appId` that is your `client-id`. Save the value to use as the `AZURE_CLIENT_ID` GitHub secret later.

You use the `objectId` value when you create federated credentials with Graph API and reference it as the `APPLICATION-OBJECT-ID`.

Create a service principal. Replace the `$appID` with the `appId` from your JSON output.

This command generates a JSON output with a different `objectId` to use in the next step. The new  `objectId` is the `assignee-object-id`.

Copy the `appOwnerTenantId` to later use as a GitHub secret for `AZURE_TENANT_ID`.

```
az ad sp create --id $appId

```

Create a new role assignment by subscription and object. By default, the role assignment is tied to your default subscription. Replace `$subscriptionId` with your subscription ID, `$resourceGroupName` with your resource group name, `$webappName` with your web app name, and `$assigneeObjectId` with the generated `id`. Learn [how to manage Azure subscriptions with the Azure CLI](/en-us/cli/azure/manage-azure-subscriptions-azure-cli).

```
az role assignment create --role "Website Contributor" --subscription $subscriptionId --assignee-object-id  $assigneeObjectId --scope /subscriptions/$subscriptionId/resourceGroups/$resourceGroupName/providers/Microsoft.Web/sites/$webappName --assignee-principal-type ServicePrincipal

```

Run the following command to [create a new federated identity credential](/en-us/graph/api/application-post-federatedidentitycredentials?view=graph-rest-beta&preserve-view=true) for your Microsoft Entra app.

Replace `APPLICATION-OBJECT-ID` with the `appId` that you generated during app creation for your Active Directory application.

Set a value for `CREDENTIAL-NAME` to reference later.

Set the `subject`. GitHub defines its value depending on your workflow:

- For jobs in your GitHub Actions environment, use: `repo:< Organization/Repository >:environment:< Name >`

- For jobs not tied to an environment, include the ref path for branch/tag based on the ref path used for triggering the workflow: `repo:< Organization/Repository >:ref:< ref path>`. For example, `repo:n-username/ node_express:ref:refs/heads/my-branch` or `repo:n-username/ node_express:ref:refs/tags/my-tag`.

- For workflows triggered by a pull request event, use: `repo:< Organization/Repository >:pull_request`.

```
az ad app federated-credential create --id <APPLICATION-OBJECT-ID> --parameters credential.json
("credential.json" contains the following content)
{
    "name": "<CREDENTIAL-NAME>",
    "issuer": "https://token.actions.githubusercontent.com",
    "subject": "repo:organization/repository:ref:refs/heads/main",
    "description": "Testing",
    "audiences": [
        "api://AzureADTokenExchange"
    ]
}     

```

Note

To use publish profile, you must enable [basic authentication](configure-basic-auth-disable).

A publish profile is an app-level credential. Set up your publish profile as a GitHub secret.

Go to App Service in the Azure portal.

On the **Overview** page, select **Download publish profile**.

Save the downloaded file. Use the contents of the file to create a GitHub secret.

Note

As of October 2020, Linux web apps need the app setting `WEBSITE_WEBDEPLOY_USE_SCM` set to `true` *before downloading the publish profile*.

You can create a [service principal](../active-directory/develop/app-objects-and-service-principals#service-principal-object) with the [`az ad sp create-for-rbac`](/en-us/cli/azure/ad/sp#az-ad-sp-create-for-rbac) command in the [Azure CLI](/en-us/cli/azure/). Run this command by using [Azure Cloud Shell](https://shell.azure.com/) in the Azure portal or by selecting **Open Cloud Shell**.

```
az ad sp create-for-rbac --name "myApp" --role "Website Contributor" \
                            --scopes /subscriptions/<subscription-id>/resourceGroups/<group-name>/providers/Microsoft.Web/sites/<app-name> \
                            --json-auth

```

In the previous example, replace the placeholders with your subscription ID, resource group name, and app name. The output is a JSON object with the role assignment credentials that provide access to your App Service app. The output should look similar to the following JSON snippet. Copy this JSON object for later.

```
  {
    "clientId": "<GUID>",
    "clientSecret": "<GUID>",
    "subscriptionId": "<GUID>",
    "tenantId": "<GUID>",
    (...)
  }

```

Important

We recommend that you grant minimum access. The scope in the previous example is limited to the specific App Service app and not the entire resource group.

### Configure the GitHub secret

[OpenID Connect](#tabpanel_2_openid)

[Publish profile](#tabpanel_2_applevel)

[Service principal](#tabpanel_2_userlevel)

You need to provide your application's **Client ID**, **Tenant ID**, and **Subscription ID** to the [`Azure/login`](https://github.com/marketplace/actions/azure-login) action. These values can either be provided directly in the workflow or can be stored in GitHub secrets and referenced in your workflow. Saving the values as GitHub secrets is the more secure option.

Open your GitHub repository and go to **Settings** > **Security** > **Secrets and variables** > **Actions** > **New repository secret**.

Create secrets for `AZURE_CLIENT_ID`, `AZURE_TENANT_ID`, and `AZURE_SUBSCRIPTION_ID`. Use these values from your Active Directory application for your GitHub secrets:

GitHub secret
Active Directory application

`AZURE_CLIENT_ID`
Application (client) ID

`AZURE_TENANT_ID`
Directory (tenant) ID

`AZURE_SUBSCRIPTION_ID`
Subscription ID

Select **Add secret** to save each secret.

In [GitHub](https://github.com/), browse to your repository. Select **Settings** > **Security** > **Secrets and variables** > **Actions** > **New repository secret**.

To use the app-level credentials that you created in the previous section, paste the contents of the downloaded publish profile file into the secret's value field. Name the secret `AZURE_WEBAPP_PUBLISH_PROFILE`.

When you configure the GitHub workflow file later, use the `AZURE_WEBAPP_PUBLISH_PROFILE` in the **Deploy Azure Web App** action. For example:

```
- uses: azure/webapps-deploy@v2
  with:
    publish-profile: ${{ secrets.AZURE_WEBAPP_PUBLISH_PROFILE }}

```

In [GitHub](https://github.com/), browse to your repository. Select **Settings** > **Security** > **Secrets and variables** > **Actions** > **New repository secret**.

To use the user-level credentials that you created in the previous section, paste the entire JSON output from the Azure CLI command into the secret's value field. Name the secret `AZURE_CREDENTIALS`.

When you configure the GitHub workflow file later, use the secret for the input `creds` of [`Azure/login`](https://github.com/marketplace/actions/azure-login). For example:

```
- uses: azure/login@v2
  with:
    creds: ${{ secrets.AZURE_CREDENTIALS }}

```

### Add the workflow file to your GitHub repository

A YAML (.yml) file in the `/.github/workflows/` path in your GitHub repository defines a workflow. This definition contains the various steps and parameters that make up the workflow.

At a minimum, the workflow file has the following distinct steps:

- Authenticate with App Service by using the GitHub secret you created.

- Build the web app.

- Deploy the web app.

To deploy your code to an App Service app, use the [`azure/webapps-deploy@v3`](https://github.com/Azure/webapps-deploy/tree/releases/v3) action. The action requires the name of your web app in `app-name` and, depending on your language stack, the path of a `*.zip`, `*.war`, `*.jar`, or folder to deploy in `package`. For a complete list of possible inputs for the `azure/webapps-deploy@v3` action, see [action.yml](https://github.com/Azure/webapps-deploy/blob/releases/v3/action.yml).

The following examples show the part of the workflow that builds the web app, in different supported languages.

[OpenID Connect](#tabpanel_3_openid)

[Publish profile](#tabpanel_3_applevel)

[Service principal](#tabpanel_3_userlevel)

To deploy with OpenID Connect by using the managed identity you configured, use the `azure/login@v2` action with the `client-id`, `tenant-id`, and `subscription-id` keys. Reference the GitHub secrets that you created earlier.

[ASP.NET Core](#tabpanel_1_aspnetcore)

[ASP.NET](#tabpanel_1_aspnet)

[Java SE](#tabpanel_1_java)

[Tomcat](#tabpanel_1_tomcat)

[Node.js](#tabpanel_1_nodejs)

[Python](#tabpanel_1_python)

```
name: .NET Core

on: [push]

permissions:
      id-token: write
      contents: read

env:
  AZURE_WEBAPP_NAME: my-app    # Set this to your application's name
  AZURE_WEBAPP_PACKAGE_PATH: '.'      # Set this to the path to your web app project, defaults to the repository root
  

... [Content truncated]