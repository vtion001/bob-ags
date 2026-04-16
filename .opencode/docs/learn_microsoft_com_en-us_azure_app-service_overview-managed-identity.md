# Managed Identities - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/overview-managed-identity
> Cached: 2026-04-16T20:57:47.481Z

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
					
				
			
		
	
					# Use managed identities for App Service and Azure Functions

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					This article shows you how to create a managed identity for Azure App Service and Azure Functions applications, and how to use it to access other resources.

A managed identity from Microsoft Entra ID allows your app to easily access other Microsoft Entra-protected resources, such as Azure Key Vault. The Azure platform manages the identity, so you don't need to provision or rotate any secrets. For more information about managed identities in Microsoft Entra ID, see [Managed identities for Azure resources](/en-us/azure/active-directory/managed-identities-azure-resources/overview).

You can grant two types of identities to your application:

- A *system-assigned identity* is tied to the app and is deleted if the app is deleted. An app can have only one system-assigned identity.

- A *user-assigned identity* is a standalone Azure resource that can be assigned to your app. An app can have multiple user-assigned identities. One user-assigned identity can be assigned to multiple Azure resources, such as two App Service apps.

The managed identity configuration is specific to the slot. To configure a managed identity for a deployment slot in the portal, go to the slot first. To find the managed identity for your web app or deployment slot in your Microsoft Entra tenant from the Azure portal, search for it directly from the **Overview** page of your tenant.

Note

Managed identities aren't available for [apps deployed in Azure Arc](overview-arc-integration).

Because [managed identities don't support cross-directory scenarios](../active-directory/managed-identities-azure-resources/managed-identities-faq#can-i-use-a-managed-identity-to-access-a-resource-in-a-different-directorytenant), they don't behave as expected if your app is migrated across subscriptions or tenants. To re-create the managed identities after such a move, see [Will managed identities be re-created automatically if I move a subscription to another directory?](../active-directory/managed-identities-azure-resources/managed-identities-faq#will-managed-identities-be-recreated-automatically-if-i-move-a-subscription-to-another-directory) Downstream resources also need to have access policies updated to use the new identity.

## Prerequisites

To perform the steps in this article, you must have a minimum set of permissions over your Azure resources. The specific permissions that you need vary based on your scenario. The following table summarizes the most common scenarios:

Scenario
Required permission
Example built-in roles

[Create a system-assigned identity](#add-a-system-assigned-identity)
`Microsoft.Web/sites/write` over the app, or `Microsoft.Web/sites/slots/write` over the slot
[Website Contributor](../role-based-access-control/built-in-roles/web-and-mobile#website-contributor)

[Create a user-assigned identity](/en-us/entra/identity/managed-identities-azure-resources/how-manage-user-assigned-managed-identities#create-a-user-assigned-managed-identity)
`Microsoft.ManagedIdentity/userAssignedIdentities/write` over the resource group in which to create the identity
[Managed Identity Contributor](../role-based-access-control/built-in-roles/identity#managed-identity-contributor)

[Assign a user-assigned identity to your app](#add-a-user-assigned-identity)
`Microsoft.Web/sites/write` over the app, `Microsoft.Web/sites/slots/write` over the slot, or 
`Microsoft.ManagedIdentity/userAssignedIdentities/*/assign/action` over the identity
[Website Contributor](../role-based-access-control/built-in-roles/web-and-mobile#website-contributor) and [Managed Identity Operator](../role-based-access-control/built-in-roles/identity#managed-identity-operator)

[Create Azure role assignments](../role-based-access-control/role-assignments-steps)
`Microsoft.Authorization/roleAssignments/write` over the target resource scope
[Role Based Access Control Administrator](../role-based-access-control/built-in-roles/privileged#role-based-access-control-administrator) or [User Access Administrator](../role-based-access-control/built-in-roles/privileged#user-access-administrator)

## Add a system-assigned identity

To enable a system-assigned managed identity, use the following instructions.

[Azure portal](#tabpanel_1_portal)

[Azure CLI](#tabpanel_1_cli)

[Azure PowerShell](#tabpanel_1_ps)

[ARM template](#tabpanel_1_arm)

In the [Azure portal](https://portal.azure.com), go to your app's page.

On the left menu, select **Settings** > **Identity**.

On the **System assigned** tab, switch **Status** to **On**. Then select **Save**.

Run the `az webapp identity assign` command:

```
az webapp identity assign --resource-group <group-name> --name <app-name> 

```

#### For App Service

Run the `Set-AzWebApp -AssignIdentity` command:

```
Set-AzWebApp -AssignIdentity $true -ResourceGroupName <group-name>  -Name <app-name>

```

#### For Functions

Run the `Update-AzFunctionApp -IdentityType` command:

```
Update-AzFunctionApp -ResourceGroupName <group-name> -Name <function-app-name>  -IdentityType SystemAssigned

```

You can use an Azure Resource Manager template to automate deployment of your Azure resources. To learn more, see [Automate resource deployment in App Service](deploy-complex-application-predictably) and [Automate resource deployment in Azure Functions](../azure-functions/functions-infrastructure-as-code).

You can create any resource of type `Microsoft.Web/sites` with an identity by including the following property in the resource definition:

```
"identity": {
    "type": "SystemAssigned"
}

```

Adding the system-assigned type tells Azure to create and manage the identity for your application.

For example, a web app's template might look like the following JSON:

```
{
    "apiVersion": "2022-03-01",
    "type": "Microsoft.Web/sites",
    "name": "[variables('appName')]",
    "location": "[resourceGroup().location]",
    "identity": {
        "type": "SystemAssigned"
    },
    "properties": {
        "name": "[variables('appName')]",
        "serverFarmId": "[resourceId('Microsoft.Web/serverfarms', variables('hostingPlanName'))]",
        "hostingEnvironment": "",
        "clientAffinityEnabled": false,
        "alwaysOn": true
    },
    "dependsOn": [
        "[resourceId('Microsoft.Web/serverfarms', variables('hostingPlanName'))]"
    ]
}

```

When the site is created, it includes the following properties:

```
"identity": {
    "type": "SystemAssigned",
    "tenantId": "<tenant-id>",
    "principalId": "<principal-id>"
}

```

The `tenantId` property identifies what Microsoft Entra tenant the identity belongs to. The `principalId` property is a unique identifier for the application's new identity. In Microsoft Entra ID, the service principal has the same name that you gave to your App Service or Azure Functions instance.

If you need to refer to these properties in a later stage in the template, use the [`reference()` template function](../azure-resource-manager/templates/template-functions-resource#reference) with the `'Full'` option, as in this example:

```
{
    "tenantId": "[reference(resourceId('Microsoft.Web/sites', variables('appName')), '2018-02-01', 'Full').identity.tenantId]",
    "objectId": "[reference(resourceId('Microsoft.Web/sites', variables('appName')), '2018-02-01', 'Full').identity.principalId]",
}

```

## Add a user-assigned identity

To create an app with a user-assigned identity, create the identity and then add its resource identifier to your app configuration.

[Azure portal](#tabpanel_2_portal)

[Azure CLI](#tabpanel_2_cli)

[Azure PowerShell](#tabpanel_2_ps)

[ARM template](#tabpanel_2_arm)

Create a user-assigned managed identity resource according to [these instructions](/en-us/entra/identity/managed-identities-azure-resources/how-manage-user-assigned-managed-identities#create-a-user-assigned-managed-identity).

On the left menu for your app's page, select **Settings** > **Identity**.

Select **User assigned**, then select **Add**.

Search for the identity that you created earlier, select it, and then select **Add**.

After you finish these steps, the app restarts.

Create a user-assigned identity:

```
az identity create --resource-group <group-name> --name <identity-name>

```

Run the `az webapp identity assign` command to assign the identity to the app:

```
az webapp identity assign --resource-group <group-name> --name <app-name> --identities <identity-id>

```

#### For App Service

Adding a user-assigned identity in App Service by using Azure PowerShell is currently not supported.

#### For Functions

Create a user-assigned identity:

```
Install-Module -Name Az.ManagedServiceIdentity -AllowPrerelease
$userAssignedIdentity = New-AzUserAssignedIdentity -Name <identity-name> -ResourceGroupName <group-name> -Location <region>

```

Run the `Update-AzFunctionApp -IdentityType UserAssigned -IdentityId` command to assign the identity in Functions:

```
Update-AzFunctionApp -Name <app-name> -ResourceGroupName <group-name> -IdentityType UserAssigned -IdentityId $userAssignedIdentity.Id

```

You can use an Azure Resource Manager template to automate deployment of your Azure resources. To learn more, see [Automate resource deployment in App Service](deploy-complex-application-predictably) and [Automate resource deployment in Azure Functions](../azure-functions/functions-infrastructure-as-code).

You can create any resource of type `Microsoft.Web/sites` with an identity by including the following block in the resource definition. Replace `<resource-id>` with the resource ID of the desired identity.

```
"identity": {
    "type": "UserAssigned",
    "userAssignedIdentities": {
        "<resource-id>": {}
    }
}

```

Note

An application can have both system-assigned and user-assigned identities at the same time. In that case, the `type` property is `SystemAssigned,UserAssigned`.

Adding the user-assigned type tells Azure to use the user-assigned identity that you specified for your application.

For example, a web app's template might look like the following JSON:

```
{
    "apiVersion": "2022-03-01",
    "type": "Microsoft.Web/sites",
    "name": "[variables('appName')]",
    "location": "[resourceGroup().location]",
    "identity": {
        "type": "UserAssigned",
        "userAssignedIdentities": {
            "[resourceId('Microsoft.ManagedIdentity/userAssignedIdentities', variables('identityName'))]": {}
        }
    },
    "properties": {
        "name": "[variables('appName')]",
        "serverFarmId": "[resourceId('Microsoft.Web/serverfarms', variables('hostingPlanName'))]",
        "hostingEnvironment": "",
        "clientAffinityEnabled": false,
        "alwaysOn": true
    },
    "dependsOn": [
        "[resourceId('Microsoft.Web/serverfarms', variables('hostingPlanName'))]",
        "[resourceId('Microsoft.ManagedIdentity/userAssignedIdentities', variables('identityName'))]"
    ]
}

```

When the site is created, it includes the following properties:

```
"identity": {
    "type": "UserAssigned",
    "userAssignedIdentities": {
        "<resource-id>": {
            "principalId": "<principal-id>",
            "clientId": "<client-id>"
        }
    }
}

```

The `principalId` property is a unique identifier for the identity that's used for Microsoft Entra administration. The `clientId` property is a unique identifier for the application's new identity. You use it to specify which identity to use during runtime calls.

##  Configure the target resource

You need to configure the target resource to allow access from your app. For most Azure services, you configure the target resource by [creating a role assignment](../role-based-access-control/role-assignments-steps).

Some services use mechanisms other than Azure role-based access control. To understand how to configure access by using an identity, refer to the documentation for each target resource. To learn more about which resources support Microsoft Entra tokens, see [Azure services that support Microsoft Entra authentication](../active-directory/managed-identities-azure-resources/services-support-managed-identities#azure-services-that-support-azure-ad-authentication).

For example, if you [request a token](#connect-to-azure-services-in-app-code) to access a secret in Azure Key Vault, you must also create a role assignment that allows the managed identity to work with secrets in the target vault. Otherwise, Key Vault rejects your calls even if you use a valid token. The same is true for Azure SQL Database and other services.

Important

The back-end services for managed identities maintain a cache per resource URI for around 24 hours and can take up to that amount of time for changes to a managed identity's group or role membership to take effect. It's currently not possible to force a managed identity's token to be refreshed before its expiration. If you change a managed identity's group or role membership to add or remove permissions, you might need to wait up to around 24 hours for the Azure resource that's using the identity to have the correct access.

For alternatives to groups or role memberships, see [Limitation of using managed identities for authorization](/en-us/entra/identity/managed-identities-azure-resources/managed-identity-best-practice-recommendations#limitation-of-using-managed-identities-for-authorization).

## Connect to Azure services in app code

With its managed identity, an app can get tokens for Azure resources that Microsoft Entra ID helps protect, such as Azure SQL Database, Azure Key Vault, and Azure Storage. These tokens represent the application that acces

... [Content truncated]