# IP Firewall Rules - Azure SQL Database and Azure Synapse Analytics | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/azure-sql/database/firewall-configure
> Cached: 2026-04-16T20:58:05.929Z

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
					
				
			
		
	
					# Azure SQL Database and Azure Synapse IP firewall rules

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					**Applies to:** 
 [Azure SQL Database](/en-us/sql/sql-server/sql-docs-navigation-guide#applies-to) 
 [Azure Synapse Analytics](/en-us/sql/sql-server/sql-docs-navigation-guide#applies-to)
When you create a new server in Azure SQL Database or Azure Synapse Analytics named *mysqlserver*, for example, a server-level firewall blocks all access to the public endpoint for the server (which is accessible at *mysqlserver.database.windows.net*). For simplicity, *SQL Database* is used to refer to both SQL Database and Azure Synapse Analytics. This article does *not* apply to *Azure SQL Managed Instance*. For information about network configuration, see [Connect your application to Azure SQL Managed Instance](../managed-instance/connect-application-instance?view=azuresql).

Note

[Microsoft Entra ID](/en-us/entra/fundamentals/new-name) was previously known as Azure Active Directory (Azure AD).

## How the firewall works

Connection attempts from the internet and Azure must pass through the firewall before they reach your server or database, as the following diagram shows.

Important

Azure Synapse only supports server-level IP firewall rules. It doesn't support database-level IP firewall rules.

### Server-level IP firewall rules

These rules enable clients to access your entire server, that is, all the databases managed by the server. The rules are stored in the `master` database. The maximum number of server-level IP firewall rules is limited to 256 for a server. If you have the **Allow Azure Services and resources to access this server** setting enabled, this counts as a single firewall rule for the server.

You can configure server-level IP firewall rules by using the Azure portal, PowerShell, or Transact-SQL statements.

Note

The maximum number of server-level IP firewall rules is limited to 256 when configuring using the Azure portal.

- To use the portal or PowerShell, you must be the subscription owner or a subscription contributor.

- To use Transact-SQL, you must connect to the `master` database as the server-level principal login or as the Microsoft Entra administrator. (A server-level IP firewall rule must first be created by a user who has Azure-level permissions.)

Note

By default, during creation of a new logical SQL server from the Azure portal, the **Allow Azure Services and resources to access this server** setting is set to **No**.

### Database-level IP firewall rules

Database-level IP firewall rules enable clients to access certain (secure) databases. You create the rules for each database (including the `master` database), and they're stored in the individual database.

- You can only create and manage database-level IP firewall rules for `master` and user databases by using Transact-SQL statements and only after you configure the first server-level firewall.

- If you specify an IP address range in the database-level IP firewall rule that's outside the range in the server-level IP firewall rule, only those clients that have IP addresses in the database-level range can access the database.

- The default value is up to 256 database-level IP firewall rules for a database. For more information about configuring database-level IP firewall rules, see the example later in this article and see [sp_set_database_firewall_rule (Azure SQL Database)](/en-us/sql/relational-databases/system-stored-procedures/sp-set-database-firewall-rule-azure-sql-database).

### Recommendations for how to set firewall rules

Use database-level IP firewall rules whenever possible. This practice enhances security and makes your database more portable. Use server-level IP firewall rules for administrators. Also use them when you have many databases that have the same access requirements, and you don't want to configure each database individually.

Note

For information about portable databases in the context of business continuity, see [Configure and manage Azure SQL Database security for geo-restore or failover](active-geo-replication-security-configure?view=azuresql).

## Server-level versus database-level IP firewall rules

*Should users of one database be fully isolated from another database?*

If *yes*, use database-level IP firewall rules to grant access. This method avoids using server-level IP firewall rules, which permit access through the firewall to all databases. That would reduce the depth of your defenses.

*Do users at the IP addresses need access to all databases?*

If *yes*, use server-level IP firewall rules to reduce the number of times that you have to configure IP firewall rules.

*Does the person or team who configures the IP firewall rules only have access through the Azure portal, PowerShell, or the REST API?*

If so, you must use server-level IP firewall rules. Database-level IP firewall rules can only be configured through Transact-SQL.

*Is the person or team who configures the IP firewall rules prohibited from having high-level permission at the database level?*

If so, use server-level IP firewall rules. You need at least *CONTROL DATABASE* permission at the database level to configure database-level IP firewall rules through Transact-SQL.

*Does the person or team who configures or audits the IP firewall rules centrally manage IP firewall rules for many (perhaps hundreds) of databases?*

In this scenario, best practices are determined by your needs and environment. Server-level IP firewall rules might be easier to configure, but scripting can configure rules at the database-level. And even if you use server-level IP firewall rules, you might need to audit database-level IP firewall rules to see if users with *CONTROL* permission on the database create database-level IP firewall rules.

*Can I use a mix of server-level and database-level IP firewall rules?*

Yes. Some users, such as administrators, might need server-level IP firewall rules. Other users, such as users of a database application, might need database-level IP firewall rules.

### Connections from the internet

When a computer tries to connect to your server from the internet, the firewall first checks the originating IP address of the request against the database-level IP firewall rules for the database that the connection requests.

- If the address is within a range that's specified in the database-level IP firewall rules, the connection is granted to the database that contains the rule.

- If the address isn't within a range in the database-level IP firewall rules, the firewall checks the server-level IP firewall rules. If the address is within a range that's in the server-level IP firewall rules, the connection is granted. Server-level IP firewall rules apply to all databases managed by the server.

- If the address isn't within a range that's in any of the database-level or server-level IP firewall rules, the connection request fails.

Note

To access Azure SQL Database from your local computer, ensure that the firewall on your network and local computer allows outgoing communication on TCP port 1433.

### Connections from inside Azure

To allow applications hosted inside Azure to connect to your SQL server, you need to enable Azure connections. To do this, create a firewall rule with starting and ending IP addresses set to 0.0.0.0. This rule applies only to Azure SQL Database.

When an application from Azure tries to connect to the server, the firewall checks that Azure connections are allowed by verifying this firewall rule exists. This can be turned on directly from the Azure portal pane by switching the **Allow Azure Services and resources to access this server** to **ON** in the **Firewalls and virtual networks** settings. Switching the setting to ON creates an inbound firewall rule for IP 0.0.0.0 - 0.0.0.0 named **AllowAllWindowsAzureIps**. The rule can be viewed in your `master` database [sys.firewall_rules](/en-us/sql/relational-databases/system-catalog-views/sys-firewall-rules-azure-sql-database) view. Use PowerShell or the Azure CLI to create a firewall rule with start and end IP addresses set to 0.0.0.0 if you're not using the portal.

Warning

Enabling this option allows connections from **all** Azure services, **including services running in other customers' subscriptions**. This rule doesn't restrict access to your subscription or resource group — any Azure resource with outbound connectivity to Azure SQL Database can connect. When you enable this setting, make sure your login and user permissions limit access to authorized users only.

The following Azure services commonly use this rule to connect to Azure SQL Database:

- Azure App Service and Azure Functions

- Azure Data Factory

- Azure Stream Analytics

- Azure Logic Apps

- Azure Power BI

- Azure AI services

For enhanced security, consider using [virtual network service endpoints](vnet-service-endpoint-rule-overview?view=azuresql) or [private endpoints](private-endpoint-overview?view=azuresql) instead of the **AllowAllWindowsAzureIps** rule. These alternatives limit connectivity to specific subnets or private networks instead of allowing all Azure IP addresses.

## Permissions

To create and manage IP firewall rules for the Azure SQL Server, you need to have one of the following roles:

- in the [SQL Server Contributor](/en-us/azure/role-based-access-control/built-in-roles#sql-server-contributor) role

- in the [SQL Security Manager](/en-us/azure/role-based-access-control/built-in-roles#sql-security-manager) role

- the owner of the resource that contains the Azure SQL Server

## Create and manage IP firewall rules

You create the first server-level firewall setting by using the [Azure portal](https://portal.azure.com/) or programmatically by using [Azure PowerShell](/en-us/powershell/module/az.sql), [Azure CLI](/en-us/cli/azure/sql/server/firewall-rule), or an Azure [REST API](/en-us/rest/api/sql/firewall-rules/create-or-update). You create and manage additional server-level IP firewall rules by using these methods or Transact-SQL.

Important

Database-level IP firewall rules can only be created and managed by using Transact-SQL.

To improve performance, server-level IP firewall rules are temporarily cached at the database level. To refresh the cache, see [DBCC FLUSHAUTHCACHE](/en-us/sql/t-sql/database-console-commands/dbcc-flushauthcache-transact-sql).

Tip

You can use [Auditing for Azure SQL Database and Azure Synapse Analytics](auditing-overview?view=azuresql) to audit server-level and database-level firewall changes.

### Use the Azure portal to manage server-level IP firewall rules

To set a server-level IP firewall rule in the Azure portal, go to the overview page for your database or your server.

Tip

For a tutorial, see [Quickstart: Create a single database - Azure SQL Database](single-database-create-quickstart?view=azuresql).

#### From the database overview page

To set a server-level IP firewall rule from the database overview page, select **Set server firewall** on the toolbar, as the following image shows.

The **Networking** page for the server opens.

Add a rule in the **Firewall rules** section to add the IP address of the computer that you're using, and then select **Save**. A server-level IP firewall rule is created for your current IP address.

#### From the server overview page

The overview page for your server opens. It shows the fully qualified server name (such as *mynewserver20170403.database.windows.net*) and provides options for further configuration.

To set a server-level rule from this page, select **Networking** from the **Settings** menu on the left side.

Add a rule in the **Firewall rules** section to add the IP address of the computer that you're using, and then select **Save**. A server-level IP firewall rule is created for your current IP address.

### Use Transact-SQL to manage IP firewall rules

Catalog view or stored procedure
Level
Description

[sys.firewall_rules](/en-us/sql/relational-databases/system-catalog-views/sys-firewall-rules-azure-sql-database)
Server
Displays the current server-level IP firewall rules

[sp_set_firewall_rule](/en-us/sql/relational-databases/system-stored-procedures/sp-set-firewall-rule-azure-sql-database)
Server
Creates or updates server-level IP firewall rules

[sp_delete_firewall_rule](/en-us/sql/relational-databases/system-stored-procedures/sp-delete-firewall-rule-azure-sql-database)
Server
Removes server-level IP firewall rules

[sys.database_firewall_rules](/en-us/sql/relational-databases/system-catalog-views/sys-database-firewall-rules-azure-sql-database)
Database
Displays the current database-level IP firewall rules

[sp_set_database_firewall_rule](/en-us/sql/relational-databases/system-stored-procedures/sp-set-database-firewall-rule-azure-sql-database)
Database
Creates or updates the database-level IP firewall rules

[sp_delete_database_firewall_rule](/en-us/sql/relational-databases/system-stored-procedures/sp-delete-database-firewall-rule-azure-sql-database)
Databases
Removes database-level IP firewall rules

The following example reviews the existing rules, enables a range of IP addresses on the server *Contoso*, and deletes an IP firewall rule:

```
SELECT * FROM sys.firewall_rules ORDER BY name;

```

Next, add a server-level IP firewall rule.

```
EXECUTE sp_set_firewall_rule @name = N'ContosoFirewallRule',
   @start_ip_address = '192.168.1.1', @end_ip_address = '192.168.1.10'

```

To delete a server-level IP firewall rule, execute the *sp_delete_firewall_rule* stored procedure. The following example deletes the rule *ContosoFirewallRule*:

```
EXECUTE sp_delete_firewall_rule @name = N'ContosoFi

... [Content truncated]