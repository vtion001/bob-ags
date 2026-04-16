# Quickstart - Set and retrieve a secret from Azure Key Vault | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/key-vault/secrets/quick-create-cli
> Cached: 2026-04-16T20:57:54.618Z

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
					
				
			
		
	
					# Quickstart: Set and retrieve a secret from Azure Key Vault using Azure CLI

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					In this quickstart, you create a key vault in Azure Key Vault with Azure CLI. Azure Key Vault is a cloud service that works as a secure secrets store. You can securely store keys, passwords, certificates, and other secrets. For more information on Key Vault you may review the [Overview](../general/overview). Azure CLI is used to create and manage Azure resources using commands or scripts. Once you've completed that, you will store a secret.

If you don't have an Azure account, create a [free account](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn) before you begin.

## Prerequisites

Use the Bash environment in [Azure Cloud Shell](/en-us/azure/cloud-shell/overview). For more information, see [Get started with Azure Cloud Shell](/en-us/azure/cloud-shell/quickstart).

If you prefer to run CLI reference commands locally, [install](/en-us/cli/azure/install-azure-cli) the Azure CLI. If you're running on Windows or macOS, consider running Azure CLI in a Docker container. For more information, see [How to run the Azure CLI in a Docker container](/en-us/cli/azure/run-azure-cli-docker).

If you're using a local installation, sign in to the Azure CLI by using the [az login](/en-us/cli/azure/reference-index#az-login) command. To finish the authentication process, follow the steps displayed in your terminal. For other sign-in options, see [Authenticate to Azure using Azure CLI](/en-us/cli/azure/authenticate-azure-cli).

When you're prompted, install the Azure CLI extension on first use. For more information about extensions, see [Use and manage extensions with the Azure CLI](/en-us/cli/azure/azure-cli-extensions-overview).

Run [az version](/en-us/cli/azure/reference-index?#az-version) to find the version and dependent libraries that are installed. To upgrade to the latest version, run [az upgrade](/en-us/cli/azure/reference-index?#az-upgrade).

This quickstart requires version 2.0.4 or later of the Azure CLI. If using Azure Cloud Shell, the latest version is already installed.

## Create a resource group

A resource group is a logical container into which Azure resources are deployed and managed. Use the [az group create](/en-us/cli/azure/group#az-group-create) command to create a resource group named *myResourceGroup* in the *eastus* location.

```
az group create --name "myResourceGroup" --location "EastUS"

```

## Create a key vault

Use the Azure CLI [az keyvault create](/en-us/cli/azure/keyvault#az-keyvault-create) command to create a Key Vault in the resource group from the previous step. You will need to provide some information:

Key vault name: A string of 3 to 24 characters that can contain only numbers (0-9), letters (a-z, A-Z), and hyphens (-)

Important

Each key vault must have a unique name. Replace `<vault-name>` with the name of your key vault in the following examples.

Resource group name: **myResourceGroup**

The location: **EastUS**

```
az keyvault create --name "<vault-name>" --resource-group "myResourceGroup" --enable-rbac-authorization true

```

The output of this command shows properties of the newly created key vault. Take note of these two properties:

- **Vault Name**: The name you provided to the `--name` parameter.

- **Vault URI**: In this example, the vault URI is `https://<vault-name>.vault.azure.net/`. Applications that use your vault through its REST API must use this URI.

## Give your user account permissions to manage secrets in Key Vault

To gain permissions to your key vault through [Role-Based Access Control (RBAC)](/en-us/azure/key-vault/general/rbac-guide), assign a role to your "User Principal Name" (UPN) using the Azure CLI command [az role assignment create](/en-us/cli/azure/role/assignment#az-role-assignment-create).

```
az role assignment create --role "Key Vault Secrets Officer" --assignee "<upn>" --scope "/subscriptions/<subscription-id>/resourceGroups/myResourceGroup/providers/Microsoft.KeyVault/vaults/<vault-name>"

```

Replace `<upn>`, `<subscription-id>`, and `<vault-name>` with your actual values. If you used a different resource group name, replace "myResourceGroup" as well. Your UPN will typically be in the format of an email address (e.g., username@domain.com).

## Add a secret to Key Vault

To add a secret to the vault, you just need to take a couple of additional steps. This password could be used by an application. The password will be called **ExamplePassword** and will store the value of **hVFkk965BuUv** in it.

Use the Azure CLI [az keyvault secret set](/en-us/cli/azure/keyvault/secret#az-keyvault-secret-set) command below to create a secret in Key Vault called **ExamplePassword** that will store the value **hVFkk965BuUv** :

```
az keyvault secret set --vault-name "<vault-name>" --name "ExamplePassword" --value "hVFkk965BuUv"

```

## Retrieve a secret from Key Vault

You can now reference this password that you added to Azure Key Vault by using its URI. Use **`https://<vault-name>.vault.azure.net/secrets/ExamplePassword`** to get the current version.

To view the value contained in the secret as plain text, use the Azure CLI [az keyvault secret show](/en-us/cli/azure/keyvault/secret#az-keyvault-secret-show) command:

```
az keyvault secret show --name "ExamplePassword" --vault-name "<vault-name>" --query "value"

```

Now, you have created a Key Vault, stored a secret, and retrieved it.

## Clean up resources

Other quickstarts and tutorials in this collection build upon this quickstart. If you plan to continue on to work with subsequent quickstarts and tutorials, you may wish to leave these resources in place.

When no longer needed, you can use the Azure CLI [az group delete](/en-us/cli/azure/group) command to remove the resource group and all related resources:

```
az group delete --name "myResourceGroup"

```

## Next steps

In this quickstart you created a Key Vault and stored a secret in it. To learn more about Key Vault and how to integrate it with your applications, continue on to the articles below.

- Read an [Overview of Azure Key Vault](../general/overview)

- Learn how to [store multiline secrets in Key Vault](multiline-secrets)

- See the reference for the [Azure CLI az keyvault commands](/en-us/cli/azure/keyvault)

- Review the [Key Vault security overview](../general/secure-key-vault)

- Review [secrets-specific security best practices](secure-secrets)

					
		
	 
		
		
	
					
		
		
			
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
		2026-03-27