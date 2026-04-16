# Use Key Vault References as App Settings - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/app-service-key-vault-references
> Cached: 2026-04-16T20:57:42.569Z

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
					
				
			
		
	
					# Use Key Vault references as app settings in Azure App Service, Azure Functions, and Azure Logic Apps (Standard)

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					This article shows how to use secrets from Azure Key Vault as values in [app settings](configure-common#configure-app-settings) or [connection strings](configure-common#configure-connection-strings) for apps created with Azure App Service, Azure Functions, or Azure Logic Apps (Standard).

[Key Vault](/en-us/azure/key-vault/general/overview) is a service that provides centralized secrets management, with full control over access policies and audit history. When an app setting or connection string is a Key Vault reference, your application code can use it like any other app setting or connection string. This way, you can maintain secrets apart from your app's configuration. App settings are securely encrypted at rest, but if you need capabilities for managing secrets, they should go into a key vault.

## Grant your app access to a key vault

To read secrets from a key vault, you first need to create a vault and give your app permission to access it:

Create a key vault by following the [Key Vault quickstart](/en-us/azure/key-vault/secrets/quick-create-cli).

Create a [managed identity](overview-managed-identity) for your application.

Key vault references use the app's system-assigned identity by default, but you can [specify a user-assigned identity](#access-vaults-with-a-user-assigned-identity).

Authorize [read access to secrets in your key vault](/en-us/azure/key-vault/general/security-features#privileged-access) for the managed identity that you created. How you do it depends on the permissions model of your key vault:

- **Azure role-based access control**: Assign the **Key Vault Secrets User** role to the managed identity. See [Provide access to Key Vault keys, certificates, and secrets with Azure role-based access control](/en-us/azure/key-vault/general/rbac-guide).

- **Vault access policy**: Assign the **Get** secrets permission to the managed identity. See [Assign a Key Vault access policy](/en-us/azure/key-vault/general/assign-access-policy).

### Access network-restricted vaults

If your vault is configured with [network restrictions](/en-us/azure/key-vault/general/overview-vnet-service-endpoints), ensure that the application has network access. Vaults shouldn't depend on the app's public outbound IP addresses because the origin IP address of the secret request could be different. Instead, the vault should be configured to accept traffic from a virtual network that the app uses.

Make sure that the application has outbound networking capabilities configured, as described in [App Service networking features](networking-features) and [Azure Functions networking options](../azure-functions/functions-networking-options).

With the exception of function apps running in the Flex Consumption plan, Linux applications that connect to private endpoints must be explicitly configured to route all traffic through the virtual network. When running in a Flex Consumption plan, this routing is done automatically, and additional configuration isn't required. Run the following command to configure virtual network routing by setting [vnetRouteAllEnabled](../azure-functions/functions-app-settings#vnetrouteallenabled) to `true`:

[Azure CLI](#tabpanel_1_azure-cli)

[Azure PowerShell](#tabpanel_1_azure-powershell)

```
az webapp config set --resource-group <group-name>  --subscription <subscription> --name <app-name> --generic-configurations '{"vnetRouteAllEnabled": true}'

```

```
Update-AzFunctionAppSetting -Name <app-name> -ResourceGroupName <group-name> -AppSetting @{vnetRouteAllEnabled = $true}

```

Make sure that the vault's configuration allows the network or subnet that your app uses to access it.

Note that even if you have correctly configured the vault to accept traffic from your virtual network the vault's audit logs may still show a failed (403 - Forbidden) SecretGet event from the app's public outbound IP. This will be followed by a successful SecretGet event from the app's private IP, and is by design.

### Access vaults with a user-assigned identity

Some apps need to refer to secrets at creation time, when a system-assigned identity isn't available yet. In these cases, create a user-assigned identity and give it access to the vault in advance.

After you grant permissions to the user-assigned identity, follow these steps:

[Assign the identity](overview-managed-identity#add-a-user-assigned-identity) to your application.

Configure the app to use this identity for Key Vault reference operations by setting the `keyVaultReferenceIdentity` property to the resource ID of the user-assigned identity:

[Azure CLI](#tabpanel_2_azure-cli)

[Azure PowerShell](#tabpanel_2_azure-powershell)

```
identityResourceId=$(az identity show --resource-group <group-name> --name <identity-name> --query id -o tsv)
az webapp update --resource-group <group-name> --name <app-name> --set keyVaultReferenceIdentity=${identityResourceId}

```

```
$identityResourceId = Get-AzUserAssignedIdentity -ResourceGroupName <group-name> -Name <identity-name> | Select-Object -ExpandProperty Id
$appResourceId = Get-AzFunctionApp -ResourceGroupName <group-name> -Name <app-name> | Select-Object -ExpandProperty Id

$Path = "{0}?api-version=2021-01-01" -f $appResourceId
Invoke-AzRestMethod -Method PATCH -Path $Path -Payload "{'properties':{'keyVaultReferenceIdentity':'$identityResourceId'}}"

```

This setting applies to all Key Vault references for the app.

Tip

If you want to revert your app to use the system-assigned identity, set the value to `SystemAssigned` instead of the Resource ID.

##  Understand rotation

If the secret version isn't specified in the reference, the app uses the latest version that exists in the key vault. When newer versions become available, such as with rotation, the app is automatically updated and begins using the latest version within 24 hours.

The delay is because App Service caches the values of the Key Vault references and refetches them every 24 hours. Any configuration change to the app causes an app restart and an immediate refetch of all referenced secrets.

To force resolution of your app's Key Vault references, make an authenticated POST request to the API endpoint `https://management.azure.com/[Resource ID]/config/configreferences/appsettings/refresh?api-version=2022-03-01`.

##  Understand source app settings from Key Vault

To use a Key Vault reference, set the reference as the value of the setting. Your app can reference the secret through its key as normal. No code changes are required.

Tip

Because you should have separate vaults for each environment, most app settings that use Key Vault references should be marked as slot settings.

A Key Vault reference is of the form `@Microsoft.KeyVault({referenceString})`, where `{referenceString}` is in one of the following formats:

Reference string
Description

`SecretUri=<secretUri>`
The `SecretUri` should be the full data-plane URI of a secret in the vault. For example, `https://myvault.vault.azure.net/secrets/mysecret`. Optionally, include a version, such as `https://myvault.vault.azure.net/secrets/mysecret/ec96f02080254f109c51a1f14cdb1931`.

`VaultName=<vaultName>;SecretName=<secretName>`;`SecretVersion=<secretVersion>`
The `VaultName` value is required and is the vault name. The `SecretName` value is required and is the secret name. The `SecretVersion` value is optional but, if present, indicates the version of the secret to use.

For example, a complete reference without a specific version would look like the following string:

`@Microsoft.KeyVault(SecretUri=https://myvault.vault.azure.net/secrets/mysecret)`

Alternatively:

`@Microsoft.KeyVault(VaultName=myvault;SecretName=mysecret)`

### Considerations for Azure Files mounting

Apps can use the `WEBSITE_CONTENTAZUREFILECONNECTIONSTRING` application setting to mount [Azure Files](../storage/files/storage-files-introduction) as the file system. This setting has validation checks to ensure that the app can be properly started.

The platform relies on having a content share within Azure Files. The platform assumes a default name unless one is specified by using the `WEBSITE_CONTENTSHARE` setting. For any requests that modify these settings, the platform validates that this content share exists. If the content share doesn't exist, the platform tries to create it. If the platform can't locate or create the content share, it blocks the request.

When you use Key Vault references in this setting, the validation check fails by default, because the secret can't be resolved during processing of the incoming request. To avoid this problem, you can skip the validation by setting `WEBSITE_SKIP_CONTENTSHARE_VALIDATION` to `1`. This setting tells App Service to bypass all checks, and it doesn't create the content share for you. You should ensure that the content share is created in advance.

Caution

If you skip validation and either the connection string or the content share is invalid, the app doesn't start properly and creates HTTP 500 errors.

As part of creating the app, attempted mounting of the content share could fail because managed identity permissions aren't being propagated or the virtual network integration isn't set up. You can defer setting up Azure Files until later in the deployment template to accommodate this behavior. For more information, see [Azure Resource Manager deployment](#azure-resource-manager-deployment) later in this article.

In this case, App Service uses a default file system until Azure Files is set up, and files aren't copied over. You must ensure that no deployment attempts occur during the interim period before Azure Files is mounted.

### Considerations for Application Insights instrumentation

Apps can use the `APPINSIGHTS_INSTRUMENTATIONKEY` or `APPLICATIONINSIGHTS_CONNECTION_STRING` application settings to integrate with [Application Insights](/en-us/azure/azure-monitor/app/app-insights-overview).

For App Service and Azure Functions, the Azure portal also uses these settings to surface telemetry data from the resource. If these values are referenced from Key Vault, this approach isn't available. Instead, you need to work directly with the Application Insights resource to view the telemetry. However, these values [aren't considered secrets](/en-us/azure/azure-monitor/app/sdk-connection-string#is-the-connection-string-a-secret), so you might consider configuring them directly instead of using Key Vault references.

### Azure Resource Manager deployment

When you automate resource deployments through Azure Resource Manager templates, you might need to sequence your dependencies in a particular order. Be sure to define your app settings as their own resource, rather than using a `siteConfig` property in the app definition. The app needs to be defined first so that the system-assigned identity is created with it and can be used in the access policy.

The following pseudo-template is an example of what a function app might look like:

```
{
    //...
    "resources": [
        {
            "type": "Microsoft.Storage/storageAccounts",
            "name": "[variables('storageAccountName')]",
            //...
        },
        {
            "type": "Microsoft.Insights/components",
            "name": "[variables('appInsightsName')]",
            //...
        },
        {
            "type": "Microsoft.Web/sites",
            "name": "[variables('functionAppName')]",
            "identity": {
                "type": "SystemAssigned"
            },
            //...
            "resources": [
                {
                    "type": "config",
                    "name": "appsettings",
                    //...
                    "dependsOn": [
                        "[resourceId('Microsoft.Web/sites', variables('functionAppName'))]",
                        "[resourceId('Microsoft.KeyVault/vaults/', variables('keyVaultName'))]",
                        "[resourceId('Microsoft.KeyVault/vaults/secrets', variables('keyVaultName'), variables('storageConnectionStringName'))]",
                        "[resourceId('Microsoft.KeyVault/vaults/secrets', variables('keyVaultName'), variables('appInsightsKeyName'))]"
                    ],
                    "properties": {
                        "AzureWebJobsStorage": "[concat('@Microsoft.KeyVault(SecretUri=', reference(variables('storageConnectionStringName')).secretUriWithVersion, ')')]",
                        "WEBSITE_CONTENTAZUREFILECONNECTIONSTRING": "[concat('@Microsoft.KeyVault(SecretUri=', reference(variables('storageConnectionStringName')).secretUriWithVersion, ')')]",
                        "APPINSIGHTS_INSTRUMENTATIONKEY": "[concat('@Microsoft.KeyVault(SecretUri=', reference(variables('appInsightsKeyName')).secretUriWithVersion, ')')]",
                        "WEBSITE_ENABLE_SYNC_UPDATE_SITE": "true"
                        //...
                    }
                },
                {
                    "type": "sourcecontrols",
                    "name": "web",
                    //...
                    "dependsOn": [
                        "[resourceId('Microsoft.Web/sites', variables('functionAppName'))]",
                        "[resourceId('Microsoft.Web/sites/config', variables('functionAppName'), 'appsettings')]"
                    ],
                }
            ]
        },
        {
            "type": "Microsoft.KeyVault/vaults",
            "name": "[variables('keyVaultName')]",
            //...
            "dependsOn": [
                "[resourceId('Microso

... [Content truncated]