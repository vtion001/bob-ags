# Create an Azure storage account - Azure Storage | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/storage/common/storage-account-create
> Cached: 2026-04-16T20:57:44.002Z

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
					
				
			
		
	
					# Create an Azure storage account

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					An Azure storage account contains all of your Azure Storage data objects: blobs, files, queues, and tables. The storage account provides a unique namespace for your Azure Storage data that is accessible from anywhere in the world over HTTP or HTTPS. For more information about Azure storage accounts, see [Storage account overview](storage-account-overview). To create a storage account specifically for use with Azure Files, see [Create an SMB file share](../files/storage-how-to-create-file-share?tabs=azure-portal#create-a-storage-account).

In this how-to article, you learn to create a storage account using the [Azure portal](https://portal.azure.com/), [Azure PowerShell](/en-us/powershell/azure/), [Azure CLI](/en-us/cli/azure), or an [Azure Resource Manager template](../../azure-resource-manager/management/overview).

## Prerequisites

If you don't have an Azure subscription, create a [free account](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn) before you begin.

[Portal](#tabpanel_1_azure-portal)

[PowerShell](#tabpanel_1_azure-powershell)

[Azure CLI](#tabpanel_1_azure-cli)

[Bicep](#tabpanel_1_bicep)

[Template](#tabpanel_1_template)

[Azure Developer CLI](#tabpanel_1_azure-developer-cli)

[Terraform](#tabpanel_1_terraform)

None.

To create an Azure storage account with PowerShell, make sure you have installed the latest [Azure Az PowerShell module](https://www.powershellgallery.com/packages/Az). See [Install the Azure PowerShell module](/en-us/powershell/azure/install-azure-powershell).

You can sign in to Azure and run Azure CLI commands in one of two ways:

- You can run CLI commands from within the Azure portal, in Azure Cloud Shell.

- You can install the CLI and run CLI commands locally.

### Use Azure Cloud Shell

Azure Cloud Shell is a free Bash shell that you can run directly within the Azure portal. The Azure CLI is preinstalled and configured to use with your account. Select the **Cloud Shell** button on the menu in the upper-right section of the Azure portal:

[](https://portal.azure.com)

The button launches an interactive shell that you can use to run the steps outlined in this how-to article:

[](https://portal.azure.com)

### Install the Azure CLI locally

You can also install and use the Azure CLI locally. If you plan to use Azure CLI locally, make sure you have installed the latest version of the Azure CLI. See [Install the Azure CLI](/en-us/cli/azure/install-azure-cli).

None.

None.

[The Azure Developer CLI](/en-us/azure/developer/azure-developer-cli/overview) (`azd`) is an open-source, command-line tool that streamlines provisioning and deploying resources to Azure using a template system. `azd` is available for several development [environments](/en-us/azure/developer/azure-developer-cli/supported-languages-environments#supported-development-environments), including the following:

- Locally via CLI by [installing azd](/en-us/azure/developer/azure-developer-cli/overview).

- [GitHub Codespaces](https://github.com/features/codespaces) environments.

The Azure portal using [Cloud Shell](/en-us/azure/cloud-shell/overview)

Note

The `azd` template includes a `.devcontainer` that already has `azd` installed, therefore you can skip the installation step if you plan to use a `devcontainer` either locally or in an environment like Codespaces.

You need an Azure account with an active subscription. You can [create an account for free](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn).

[Install and configure Terraform](/en-us/azure/developer/terraform/quickstart-configure)

Next, sign in to Azure.

[Portal](#tabpanel_2_azure-portal)

[PowerShell](#tabpanel_2_azure-powershell)

[Azure CLI](#tabpanel_2_azure-cli)

[Bicep](#tabpanel_2_bicep)

[Template](#tabpanel_2_template)

[Azure Developer CLI](#tabpanel_2_azure-developer-cli)

[Terraform](#tabpanel_2_terraform)

Sign in to the [Azure portal](https://portal.azure.com).

Sign in to your Azure subscription with the `Connect-AzAccount` command and follow the on-screen directions to authenticate.

```
Connect-AzAccount

```

To launch Azure Cloud Shell, sign in to the [Azure portal](https://portal.azure.com).

To log into your local installation of the CLI, run the [az sign-in](/en-us/cli/azure/reference-index#az-login) command:

```
az login

```

N/A

N/A

If you plan to use `azd` via Cloud Shell:

Sign-in to the [Azure portal](https://portal.azure.com)

Launch Cloud Shell by clicking on the corresponding icon. `azd` is automatically available in Cloud Shell and will authenticate via the account you used to sign-in to the Azure portal.

To sign-in to a local installation of `azd` or Codespaces environment, run the [azd auth sign-in](/en-us/azure/developer/azure-developer-cli/reference#azd-auth-login) command:

```
    azd auth login

```

`azd` will launch a browser window that you can use to sign-in to Azure.

[Authenticate Terraform to Azure](/en-us/azure/developer/terraform/authenticate-to-azure)

## Create a storage account

A storage account is an Azure Resource Manager resource. Resource Manager is the deployment and management service for Azure. For more information, see [Azure Resource Manager overview](../../azure-resource-manager/management/overview).

Every Resource Manager resource, including an Azure storage account, must belong to an Azure resource group. A resource group is a logical container for grouping your Azure services. When you create a storage account, you have the option to either create a new resource group, or use an existing resource group. This how-to shows how to create a new resource group.

### Storage account type parameters

When you create a storage account using PowerShell, the Azure CLI, Bicep, Azure Templates, or the Azure Developer CLI, the storage account type is specified by the `kind` parameter (for example, `StorageV2`). The performance tier and redundancy configuration are specified together by the `sku` or `SkuName` parameter (for example, `Standard_GRS`). The following table shows which values to use for the `kind` parameter and the `sku` or `SkuName` parameter to create a particular type of storage account with the desired redundancy configuration.

Type of storage account
Supported redundancy configurations
Supported values for the kind parameter
Supported values for the sku or SkuName parameter
Supports hierarchical namespace

Standard general-purpose v2
LRS / GRS / RA-GRS / ZRS / GZRS / RA-GZRS
StorageV2
Standard_LRS / Standard_GRS / Standard_RAGRS/ Standard_ZRS / Standard_GZRS / Standard_RAGZRS
Yes

Premium block blobs
LRS / ZRS
BlockBlobStorage
Premium_LRS / Premium_ZRS
Yes

Premium file shares
LRS / ZRS
FileStorage
Premium_LRS / Premium_ZRS
No

Premium page blobs
LRS
StorageV2
Premium_LRS
No

Legacy standard general-purpose v1
LRS / GRS / RA-GRS
Storage
Standard_LRS / Standard_GRS / Standard_RAGRS
No

Legacy blob storage
LRS / GRS / RA-GRS
BlobStorage
Standard_LRS / Standard_GRS / Standard_RAGRS
No

[Portal](#tabpanel_3_azure-portal)

[PowerShell](#tabpanel_3_azure-powershell)

[Azure CLI](#tabpanel_3_azure-cli)

[Bicep](#tabpanel_3_bicep)

[Template](#tabpanel_3_template)

[Azure Developer CLI](#tabpanel_3_azure-developer-cli)

[Terraform](#tabpanel_3_terraform)

To create an Azure storage account with the Azure portal, follow these steps:

From the left portal menu, select **Storage accounts** to display a list of your storage accounts. If the portal menu isn't visible, select the menu button to toggle it on.

On the **Storage accounts** page, select **Create**.

Options for your new storage account are organized into tabs in the **Create a storage account** page. The following sections describe each of the tabs and their options.

### Basics tab

On the **Basics** tab, provide the essential information for your storage account. After you complete the **Basics** tab, you can choose to further customize your new storage account by setting options on the other tabs, or you can select **Review + create** to accept the default options and proceed to validate and create the account.

The following table describes the fields on the **Basics** tab.

Section
Field
Required or optional
Description

Project details
Subscription
Required
Select the subscription for the new storage account.

Project details
Resource group
Required
Create a new resource group for this storage account, or select an existing one. For more information, see [Resource groups](../../azure-resource-manager/management/overview#resource-groups).

Instance details
Storage account name
Required
Choose a unique name for your storage account. Storage account names must be between 3 and 24 characters in length and might contain numbers and lowercase letters only.

Instance details
Region
Required
Select the appropriate region for your storage account. For more information, see [Regions and Availability Zones in Azure](/en-us/azure/reliability/availability-zones-overview).

Not all regions are supported for all types of storage accounts or redundancy configurations. For more information, see [Azure Storage redundancy](storage-redundancy).

The choice of region can have a billing impact. For more information, see [Storage account billing](storage-account-overview#storage-account-billing).

Instance details
Preferred storage type
Required
Preferred storage type allows us to provide relevant guidance in the account creation experience based on your selected storage type. There are four storage types to choose from: Blob storage or Azure Data Lake Storage (ADLS) Gen 2, Azure Files, Tables, and Queues. Choosing a preferred storage type does not limit you from using any other services inside the storage account.

Instance details
Performance
Required
Select **Standard** performance for general-purpose v2 storage accounts (default). This type of account is recommended by Microsoft for most scenarios. For more information, see [Types of storage accounts](storage-account-overview#types-of-storage-accounts).

Select **Premium** for scenarios requiring low latency. After selecting **Premium**, select the type of premium storage account to create. The following types of premium storage accounts are available: 
- [Block blobs](storage-account-overview)
- [File shares](../files/storage-files-planning#management-concepts)
- [Page blobs](../blobs/storage-blob-pageblob-overview)

Instance details
Redundancy
Required
Select your desired redundancy configuration. Not all redundancy options are available for all types of storage accounts in all regions. For more information about redundancy configurations, see [Azure Storage redundancy](storage-redundancy).

If you select a geo-redundant configuration (GRS or GZRS), your data is replicated to a data center in a different region. For read access to data in the secondary region, select **Make read access to data available in the event of regional unavailability**.

The following image shows a standard configuration of the basic properties for a new storage account.

### Advanced tab

On the **Advanced** tab, you can configure additional options and modify default settings for your new storage account. Some of these options can also be configured after the storage account is created, while others must be configured at the time of creation.

The following table describes the fields on the **Advanced** tab.

Section
Field
Required or optional
Description

Security
Require secure transfer for REST API operations
Optional
Require secure transfer to ensure that incoming requests to this storage account are made only via HTTPS (default). Recommended for optimal security. If neither **Require Encryption in Transit for SMB** or **Require Encryption in Transit for NFS** are selected in the **Azure Files** section of the **Advanced** tab, this setting applies to SMB and NFS for Azure Files as well as REST/HTTPS traffic. If you have clients that need access to unencrypted SMB (such as SMB 2.1), uncheck this checkbox. For more information, see [Require secure transfer to ensure secure connections](storage-require-secure-transfer).

Security
Allow enabling anonymous access on individual containers
Optional
When enabled, this setting allows a user with the appropriate permissions to enable anonymous access to a container in the storage account (default). Disabling this setting prevents all anonymous access to the storage account. Microsoft recommends disabling this setting for optimal security.
 
 For more information, see [Prevent anonymous read access to containers and blobs](../blobs/anonymous-read-access-prevent).
 
 Enabling anonymous access does not make blob data available for anonymous access unless the user takes the additional step to explicitly configure the container's anonymous access setting.

Security
Enable storage account key access
Optional
When enabled, this setting allows clients to authorize requests to the storage account using either the account access keys or a Microsoft Entra account (default). Disabling this setting is more secure because it prevents authorization with the account access keys. For more information, see [Prevent Shared Key authorization for an Azure Storage account](shared-key-authorization-prevent).

Security
Default to Microsoft Entra authorization in the Azure portal
Optional
When enabled, the Azure portal authorizes data operations with the user's Microsoft Entra credentials by default. If the user does not have the appropriate permissions assigned via Azure role-based access control (Azure RBAC) to perform data operations, then the portal will use t

... [Content truncated]