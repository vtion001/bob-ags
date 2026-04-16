# Create a Single Database - Azure SQL Database | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/azure-sql/database/single-database-create-quickstart
> Cached: 2026-04-16T20:57:27.352Z

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
					
				
			
		
	
					# Quickstart: Create a single database - Azure SQL Database

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					In this quickstart, you create a [single database](single-database-overview?view=azuresql) in Azure SQL Database using either the Azure portal, a PowerShell script, or an Azure CLI script. You then query the database using **Query editor** in the Azure portal.

Watch this video in the [Azure SQL Database essentials series](/en-us/shows/azure-sql-database-essentials/) for an overview of the deployment process:

## Prerequisites

- An active Azure subscription. If you don't have one, [create a free account](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn).

- Much of this article can be accomplished with the Azure portal alone. Optionally, use the latest version of [Azure PowerShell](/en-us/powershell/azure/install-az-ps) or [Azure CLI](/en-us/cli/azure/install-azure-cli-windows).

### Permissions

**To create databases via Transact-SQL**: `CREATE DATABASE` permissions are necessary. To create a database a login must be either the server admin login (created when the Azure SQL Database logical server was provisioned), the Microsoft Entra admin of the server, a member of the dbmanager database role in `master`. For more information, see [CREATE DATABASE](/en-us/sql/t-sql/statements/create-database-transact-sql?view=azuresqldb-current&preserve-view=true).

**To create databases via the Azure portal, PowerShell, Azure CLI, or REST API**: Azure RBAC permissions are needed, specifically the Contributor, SQL DB Contributor, or SQL Server Contributor Azure RBAC role. For more information, see [Azure RBAC built-in roles](/en-us/azure/role-based-access-control/built-in-roles).

## Create a single database

This quickstart creates a single database in the [serverless compute tier](serverless-tier-overview?view=azuresql).

Note

[Try Azure SQL Database free of charge](free-offer?view=azuresql) and get 100,000 vCore seconds of serverless compute and 32 GB of storage every month.

[Portal](#tabpanel_1_azure-portal)

[Azure CLI](#tabpanel_1_azure-cli)

[PowerShell](#tabpanel_1_azure-powershell)

To create a single database in the Azure portal:

Go to [Azure SQL hub at aka.ms/azuresqlhub](https://aka.ms/azuresqlhub).

In the resource menu, expand **Azure SQL Database** and select **SQL databases**.

Select the **+ Create** dropdown button and select **SQL database**.

On the **Basics** tab of the **Create SQL Database** form, under **Project details**, select the desired Azure **Subscription**.

For **Resource group**, select **Create new**, enter *myResourceGroup*, and select **OK**.

For **Database name**, enter *mySampleDatabase*.

For **Server**, select **Create new**, and fill out the **New server** form with the following values:

- **Server name**: Enter *mysqlserver*, and add some characters for uniqueness. We can't provide an exact server name to use because server names must be globally unique for all servers in Azure, not just unique within a subscription. The Azure portal lets you know if the name you type is available or not.

- **Location**: Select a location from the dropdown list.

- **Authentication method**: Select **Use SQL authentication**.

- **Server admin login**: Enter *azureuser*.

- **Password**: Enter a password that meets requirements, and enter it again in the **Confirm password** field.

Important

Do not include any personal, sensitive, or confidential information in the server admin login name field. Data entered in this field is not considered *customer data*.

Select **OK**.

Leave **Want to use SQL elastic pool** set to **No**.

For **Workload environment**, specify **Development** for this exercise.

The Azure portal provides a **Workload environment** option that helps to preset some configuration settings. These settings can be overridden. This option applies to the **Create SQL Database** portal page only. Otherwise, the **Workload environment** option has no impact on licensing or other database configuration settings.

- Choosing the **development** workload environment sets a few options, including:

- **Backup storage redundancy** option is locally redundant storage. Locally redundant storage incurs less cost and is appropriate for pre-production environments that do not require the redundance of zone- or geo-replicated storage.

- **Compute + storage** is General Purpose, Serverless with a single vCore. By default, there is a [one-hour auto-pause delay](serverless-tier-overview?view=azuresql&preserve-view=true&tabs=general-purpose#performance-configuration).

- Choosing the **Production** workload environment sets:

- **Backup storage redundancy** is geo-redundant storage, the default.

- **Compute + storage** is General Purpose, Provisioned with 2 vCores and 32 GB of storage. This can be further modified in the next step.

Under **Compute + storage**, select **Configure database**.

This quickstart uses a serverless database, so leave **Service tier** set to **General Purpose (Most budget-friendly, serverless compute)** and set **Compute tier** to **Serverless**. Select **Apply**.

Under **Backup storage redundancy**, choose a redundancy option for the storage account where your backups will be saved. To learn more, see [backup storage redundancy](automated-backups-overview?view=azuresql#backup-storage-redundancy).

Select **Next: Networking**.

On the **Networking** tab, for **Connectivity method**, select **Public endpoint**.

For **Firewall rules**, set **Add current client IP address** to **Yes**. Leave **Allow Azure services and resources to access this server** set to **No**.

Under **Connection policy**, choose the **Default** [connection policy](connectivity-architecture?view=azuresql#connection-policy), and leave the **Minimum TLS version** at the default of TLS 1.2.

Select **Next: Security**.

On the **Security** page, you can choose to start a free trial of [Microsoft Defender for SQL](azure-defender-for-sql?view=azuresql), as well as configure [Ledger](/en-us/sql/relational-databases/security/ledger/ledger-overview), [Managed identities](/en-us/azure/active-directory/managed-identities-azure-resources/overview) and [Azure SQL transparent data encryption with customer-managed key](transparent-data-encryption-byok-overview?view=azuresql) if you desire. Select **Next: Additional settings**.

On the **Additional settings** tab, in the **Data source** section, for **Use existing data**, select **Sample**. This creates an `AdventureWorksLT` sample database so there's some tables and data to query and experiment with, as opposed to an empty blank database. You can also configure [database collation](/en-us/sql/t-sql/statements/collations) and a [maintenance window](maintenance-window?view=azuresql).

Select **Review + create**.

On the **Review + create** page, after reviewing, select **Create**.

The Azure CLI code blocks in this section create a resource group, server, single database, and server-level IP firewall rule for access to the server. Make sure to record the generated resource group and server names, so you can manage these resources later.

First, install the latest [Azure CLI](/en-us/cli/azure/install-azure-cli-windows).

If you don't have an [Azure subscription](/en-us/azure/guides/developer/azure-developer-guide#understanding-accounts-subscriptions-and-billing), create an [Azure free account](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn) before you begin.

### Prepare your environment for the Azure CLI

Use the Bash environment in [Azure Cloud Shell](/en-us/azure/cloud-shell/overview). For more information, see [Get started with Azure Cloud Shell](/en-us/azure/cloud-shell/quickstart).

If you prefer to run CLI reference commands locally, [install](/en-us/cli/azure/install-azure-cli) the Azure CLI. If you're running on Windows or macOS, consider running Azure CLI in a Docker container. For more information, see [How to run the Azure CLI in a Docker container](/en-us/cli/azure/run-azure-cli-docker).

If you're using a local installation, sign in to the Azure CLI by using the [az login](/en-us/cli/azure/reference-index#az-login) command. To finish the authentication process, follow the steps displayed in your terminal. For other sign-in options, see [Authenticate to Azure using Azure CLI](/en-us/cli/azure/authenticate-azure-cli).

When you're prompted, install the Azure CLI extension on first use. For more information about extensions, see [Use and manage extensions with the Azure CLI](/en-us/cli/azure/azure-cli-extensions-overview).

Run [az version](/en-us/cli/azure/reference-index?#az-version) to find the version and dependent libraries that are installed. To upgrade to the latest version, run [az upgrade](/en-us/cli/azure/reference-index?#az-upgrade).

### Launch Azure Cloud Shell

The Azure Cloud Shell is a free interactive shell that you can use to run the steps in this article. It has common Azure tools preinstalled and configured to use with your account.

To open the Cloud Shell, select **Try it** from the upper right corner of a code block. You can also launch Cloud Shell in a separate browser tab by going to [https://shell.azure.com](https://shell.azure.com).

When Cloud Shell opens, verify that **Bash** is selected for your environment. Subsequent sessions will use Azure CLI in a Bash environment. Select **Copy** to copy the blocks of code, paste it into the Cloud Shell, and press **Enter** to run it.

### Sign in to Azure

Cloud Shell is automatically authenticated under the initial account signed-in with. Use the following script to sign in using a different subscription, replacing `<Subscription ID>` with your Azure Subscription ID.  If you don't have an [Azure subscription](/en-us/azure/guides/developer/azure-developer-guide#understanding-accounts-subscriptions-and-billing), create an [Azure free account](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn) before you begin.

```
subscription="<subscriptionId>" # add subscription here

az account set -s $subscription # ...or use 'az login'

```

For more information, see [set active subscription](/en-us/cli/azure/account#az-account-set) or [log in interactively](/en-us/cli/azure/reference-index#az-login)

### Set parameter values

The following values are used in subsequent commands to create the database and required resources. Server names need to be globally unique across all of Azure so the $RANDOM function is used to create the server name.

Change the location as appropriate for your environment. Replace `0.0.0.0` with the IP address range that matches your specific environment. Use the public IP address of the computer you're using to restrict access to the server to only your IP address.

```
# Variable block
let "randomIdentifier=$RANDOM*$RANDOM"
location="East US"
resourceGroup="msdocs-azuresql-rg-$randomIdentifier"
tag="create-and-configure-database"
server="msdocs-azuresql-server-$randomIdentifier"
database="msdocsazuresqldb$randomIdentifier"
login="azureuser"
password="Pa$$w0rD-$randomIdentifier"
# Specify appropriate IP address values for your environment
# to limit access to the SQL Database server
startIp=0.0.0.0
endIp=0.0.0.0

echo "Using resource group $resourceGroup with login: $login, password: $password..."

```

Important

Do not include any personal, sensitive, or confidential information in the server admin login name field. Data entered in this field is not considered *customer data*.

### Create a resource group

Create a resource group with the [az group create](/en-us/cli/azure/group) command. An Azure resource group is a logical container into which Azure resources are deployed and managed. The following example creates a resource group named *myResourceGroup* in the *eastus* Azure region:

```
echo "Creating $resourceGroup in $location..."
az group create --name $resourceGroup --location "$location" --tags $tag

```

### Create a server

Create a server with the [az sql server create](/en-us/cli/azure/sql/server) command.

```
echo "Creating $server in $location..."
az sql server create --name $server --resource-group $resourceGroup --location "$location" --admin-user $login --admin-password $password

```

### Configure a server-based firewall rule

Create a firewall rule with the [az sql server firewall-rule create](/en-us/cli/azure/sql/server/firewall-rule) command.

```
echo "Configuring firewall..."
az sql server firewall-rule create --resource-group $resourceGroup --server $server -n AllowYourIp --start-ip-address $startIp --end-ip-address $endIp

```

### Create a single database

Create a database with the [az sql db create](/en-us/cli/azure/sql/db) command in the [serverless compute tier](serverless-tier-overview?view=azuresql).

```
echo "Creating $database in serverless tier"
az sql db create \
    --resource-group $resourceGroup \
    --server $server \
    --name $database \
    --sample-name AdventureWorksLT \
    --edition GeneralPurpose \
    --compute-model Serverless \
    --family Gen5 \
    --capacity 2

```

You can create a resource group, server, and single database using Azure PowerShell.

First, install the latest [Azure PowerShell](/en-us/powershell/azure/install-az-ps).

### Launch Azure Cloud Shell

The Azure Cloud Shell is a free interactive shell that you can use to run the steps in this article. It has common Azure tools preinstalled and configured to use with your account.

To open the Cloud Shell, select **Try it** from the upper right corner of a code block. You can also launch Cloud Shell in a separate browser tab by going to [https://shell.azure.com](h

... [Content truncated]