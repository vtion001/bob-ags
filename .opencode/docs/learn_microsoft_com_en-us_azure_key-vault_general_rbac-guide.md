# Grant permission to applications to access an Azure key vault using Azure RBAC | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/key-vault/general/rbac-guide
> Cached: 2026-04-16T20:57:54.275Z

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
					
				
			
		
	
					# Provide access to Key Vault keys, certificates, and secrets with Azure role-based access control

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					
Note

Key Vault resource provider supports two resource types: **vaults** and **managed HSMs**. Access control described in this article only applies to **vaults**. To learn more about access control for managed HSM, see [Managed HSM access control](../managed-hsm/access-control).

Azure role-based access control (Azure RBAC) is an authorization system built on [Azure Resource Manager](/en-us/azure/azure-resource-manager/management/overview) that provides centralized access management of Azure resources. Starting with API version 2026-02-01, Azure RBAC is the default access control model for newly created key vaults. For details on this change and how to prepare, see [Prepare for Key Vault API version 2026-02-01 and later](access-control-default).

Azure RBAC allows users to manage keys, secrets, and certificates permissions, and provides one place to manage all permissions across all key vaults.

The Azure RBAC model allows users to set permissions on different scope levels: management group, subscription, resource group, or individual resources. Azure RBAC for key vault also allows users to have separate permissions on individual keys, secrets, and certificates.

For more information, see [Azure role-based access control (Azure RBAC)](/en-us/azure/role-based-access-control/overview).

## Key Vault access model overview

Access to a key vault is controlled through two interfaces: the **control plane** and the **data plane**.

The **control plane** is where you manage Key Vault itself. Operations in this plane include creating and deleting key vaults, retrieving Key Vault properties, and updating access policies.

The **data plane** is where you work with the data stored in a key vault. You can add, delete, and modify keys, secrets, and certificates.

Both planes use [Microsoft Entra ID](/en-us/entra/fundamentals/whatis) for authentication. For authorization, the control plane uses [Azure role-based access control (Azure RBAC)](/en-us/azure/role-based-access-control/overview) and the data plane uses a [Key Vault access policy](assign-access-policy) (legacy) or [Azure RBAC for Key Vault data plane operations](rbac-guide).

To access a key vault in either plane, all callers (users or applications) must have proper authentication and authorization. Authentication establishes the identity of the caller. Authorization determines which operations the caller can execute.

Applications access the planes through endpoints. The access controls for the two planes work independently. To grant an application access to use keys in a key vault, you grant data plane access by using Azure RBAC or a Key Vault access policy. To grant a user read access to Key Vault properties and tags, but not access to data (keys, secrets, or certificates), you grant control plane access with Azure RBAC.

### Access plane endpoints

The following table shows the endpoints for the control and data planes.

Access plane
Access endpoints
Operations
Access control mechanism

Control plane
**Global:**
 management.azure.com:443

 **Microsoft Azure operated by 21Vianet:**
 management.chinacloudapi.cn:443

 **Azure US Government:**
 management.usgovcloudapi.net:443

Create, read, update, and delete key vaults

Set Key Vault access policies

Set Key Vault tags
Azure RBAC

Data plane
**Global:**
 <vault-name>.vault.azure.net:443

 **Microsoft Azure operated by 21Vianet:**
 <vault-name>.vault.azure.cn:443

 **Azure US Government:**
 <vault-name>.vault.usgovcloudapi.net:443

**Keys**: encrypt, decrypt, wrapKey, unwrapKey, sign, verify, get, list, create, update, import, delete, recover, backup, restore, purge, rotate, getrotationpolicy, setrotationpolicy, release

 **Certificates**: managecontacts, getissuers, listissuers, setissuers, deleteissuers, manageissuers, get, list, create, import, update, delete, recover, backup, restore, purge

 **Secrets**: get, list, set, delete, recover, backup, restore, purge
Key Vault access policy (legacy) or Azure RBAC

### Managing administrative access to Key Vault

When you create a key vault in a resource group, you manage access by using Microsoft Entra ID. You grant users or groups the ability to manage the key vaults in a resource group. You can grant access at a specific scope level by assigning the appropriate Azure roles. To grant access to a user to manage key vaults, you assign a predefined `Key Vault Contributor` role to the user at a specific scope. The following scopes levels can be assigned to an Azure role:

- **Subscription**: An Azure role assigned at the subscription level applies to all resource groups and resources within that subscription.

- **Resource group**: An Azure role assigned at the resource group level applies to all resources in that resource group.

- **Specific resource**: An Azure role assigned for a specific resource applies to that resource. In this case, the resource is a specific key vault.

There are several predefined roles. If a predefined role doesn't fit your needs, you can define your own role. For more information, see [Azure RBAC: Built-in roles](/en-us/azure/role-based-access-control/built-in-roles).

Important

If a user has `Contributor` permissions to a key vault control plane, the user can grant themselves access to the data plane by setting a Key Vault access policy. You should tightly control who has `Contributor` role access to your key vaults. Ensure that only authorized persons can access and manage your key vaults, keys, secrets, and certificates.

## Best Practices for individual keys, secrets, and certificates role assignments

Our recommendation is to use a vault per application per environment (Development, Pre-Production, and Production) with roles assigned at the key vault scope.

Assigning roles on individual keys, secrets and certificates is not recommended. Exceptions include scenarios where:

- Individual secrets require individual user access; for example, where users must read their SSH private key to authenticate to a virtual machine using [Azure Bastion](/en-us/azure/bastion/bastion-overview).

- Individual secrets must be shared between multiple applications; for example, where one application needs to access data from another application.

More about Azure Key Vault management guidelines, see:

- [Secure your Azure Key Vault](secure-key-vault)

- [Azure Key Vault service limits](service-limits)

## Azure built-in roles for Key Vault data plane operations

Note

The `Key Vault Contributor` role is for control plane operations only to manage key vaults. It does not allow access to keys, secrets and certificates.

Built-in role
Description
ID

Key Vault Administrator
Perform all data plane operations on a key vault and all objects in it, including certificates, keys, and secrets. Cannot manage key vault resources or manage role assignments. Only works for key vaults that use the 'Azure role-based access control' permission model.
00482a5a-887f-4fb3-b363-3b7fe8e74483

Key Vault Reader
Read metadata of key vaults and its certificates, keys, and secrets. Cannot read sensitive values such as secret contents or key material. Only works for key vaults that use the 'Azure role-based access control' permission model.
21090545-7ca7-4776-b22c-e363652d74d2

Key Vault Purge Operator
Allows permanent deletion of soft-deleted vaults.
a68e7c17-0ab2-4c09-9a58-125dae29748c

Key Vault Certificates Officer
Perform any action on the certificates of a key vault, except managing permissions. Only works for key vaults that use the 'Azure role-based access control' permission model.
a4417e6f-fecd-4de8-b567-7b0420556985

Key Vault Certificate User
Read entire certificate contents including secret and key portion. Only works for key vaults that use the 'Azure role-based access control' permission model.
db79e9a7-68ee-4b58-9aeb-b90e7c24fcba

Key Vault Crypto Officer
Perform any action on the keys of a key vault, except manage permissions. Only works for key vaults that use the 'Azure role-based access control' permission model.
14b46e9e-c2b7-41b4-b07b-48a6ebf60603

Key Vault Crypto Service Encryption User
Read metadata of keys and perform wrap/unwrap operations. Only works for key vaults that use the 'Azure role-based access control' permission model.
e147488a-f6f5-4113-8e2d-b22465e65bf6

Key Vault Crypto User
Perform cryptographic operations using keys. Only works for key vaults that use the 'Azure role-based access control' permission model.
12338af0-0e69-4776-bea7-57ae8d297424

Key Vault Crypto Service Release User
Release keys for [Azure Confidential Computing](/en-us/azure/confidential-computing/concept-skr-attestation) and equivalent environments. Only works for key vaults that use the 'Azure role-based access control' permission model.

Key Vault Secrets Officer
Perform any action on the secrets of a key vault, except manage permissions. Only works for key vaults that use the 'Azure role-based access control' permission model.
b86a8fe4-44ce-4948-aee5-eccb2c155cd7

Key Vault Secrets User
Read secret contents including secret portion of a certificate with private key. Only works for key vaults that use the 'Azure role-based access control' permission model.
4633458b-17de-408a-b874-0445c86b69e6

For more information about Azure built-in roles definitions, see [Azure built-in roles](/en-us/azure/role-based-access-control/built-in-roles).

### Managing built-in Key Vault data plane role assignments

Built-in role
Description
ID

Key Vault Data Access Administrator
Manage access to Azure Key Vault by adding or removing role assignments for the Key Vault Administrator, Key Vault Certificates Officer, Key Vault Crypto Officer, Key Vault Crypto Service Encryption User, Key Vault Crypto User, Key Vault Reader, Key Vault Secrets Officer, or Key Vault Secrets User roles. Includes an ABAC condition to constrain role assignments.
8b54135c-b56d-4d72-a534-26097cfdc8d8

## Using Azure RBAC secret, key, and certificate permissions with Key Vault

The new Azure RBAC permission model for key vault provides alternative to the vault access policy permissions model.

### Prerequisites

You must have an Azure subscription. If you don't, you can create a [free account](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn) before you begin.

To manage role assignments, you must have `Microsoft.Authorization/roleAssignments/write` and `Microsoft.Authorization/roleAssignments/delete` permissions, such as [Key Vault Data Access Administrator](/en-us/azure/role-based-access-control/built-in-roles#key-vault-data-access-administrator) (with restricted permissions to only assign/remove specific Key Vault roles), [User Access Administrator](/en-us/azure/role-based-access-control/built-in-roles#user-access-administrator), or [Owner](/en-us/azure/role-based-access-control/built-in-roles#owner).

### Enable Azure RBAC permissions on Key Vault

Note

Changing the permission model requires unrestricted 'Microsoft.Authorization/roleAssignments/write' permission, which is part of the [Owner](/en-us/azure/role-based-access-control/built-in-roles#owner) and [User Access Administrator](/en-us/azure/role-based-access-control/built-in-roles#user-access-administrator) roles. Classic subscription administrator roles like 'Service Administrator' and 'Co-Administrator', or restricted 'Key Vault Data Access Administrator' cannot be used to change permission model.

Enable Azure RBAC permissions on new key vault:

Enable Azure RBAC permissions on existing key vault:

Important

Setting Azure RBAC permission model invalidates all access policies permissions. It can cause outages when equivalent Azure roles aren't assigned.

### Assign role

Note

It's recommended to use the unique role ID instead of the role name in scripts. Therefore, if a role is renamed, your scripts would continue to work. In this document role name is used for readability.

[Azure CLI](#tabpanel_1_azure-cli)

[Azure PowerShell](#tabpanel_1_azurepowershell)

[Azure portal](#tabpanel_1_azure-portal)

To create a role assignment using the Azure CLI, use the [az role assignment](/en-us/cli/azure/role/assignment) command:

```
az role assignment create --role <role-name> --assignee <user-principal-name>> --scope <scope>

```

For full details, see [Assign Azure roles using Azure CLI](/en-us/azure/role-based-access-control/role-assignments-cli).

To create a role assignment using Azure PowerShell, use the [New-AzRoleAssignment](/en-us/powershell/module/az.resources/new-azroleassignment) cmdlet:

```
#Assign by User Principal Name
New-AzRoleAssignment -RoleDefinitionName "<role-name>" -SignInName <user-principal-name> -Scope "<scope>"

#Assign by Service Principal ApplicationId
New-AzRoleAssignment -RoleDefinitionName "Reader" -ApplicationId <application-id> -Scope "<scope>"

```

For full details, see [Assign Azure roles using Azure PowerShell](/en-us/azure/role-based-access-control/role-assignments-powershell).

To assign roles using the Azure portal, see [Assign Azure roles using the Azure portal](/en-us/azure/role-based-access-control/role-assignments-portal). In the Azure portal, the Azure role assignments screen is available for all resources on the Access control (IAM) tab.

### Resource group scope role assignment

[Azure portal](#tabpanel_2_azure-portal)

[Azure CLI](#tabpanel_2_azure-cli)

[Azure PowerShell](#tabpanel_2_azurepowershell)

Go to the Resource Group that contains your key vault.

Select **Access control (IAM)**.

Select **Add** > **Add role assignment** to open the Add role assignment

... [Content truncated]