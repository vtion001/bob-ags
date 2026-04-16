# Authenticate to Azure from GitHub Actions workflows | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/developer/github/connect-from-azure
> Cached: 2026-04-16T20:57:55.148Z

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
					
				
			
		
	
					# Use GitHub Actions to connect to Azure

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					Learn how to use [Azure Login action](https://github.com/Azure/login) with either [Azure PowerShell action](https://github.com/Azure/PowerShell) or [Azure CLI action](https://github.com/Azure/CLI) to interact with your Azure resources.

To use Azure PowerShell or Azure CLI in a GitHub Actions workflow, you need to first log in with the [Azure Login action](https://github.com/marketplace/actions/azure-login) action.

The Azure Login action supports different ways of authenticating with Azure:

- [Sign in with OpenID Connect using a Microsoft Entra application or a user-assigned managed identity](connect-from-azure-openid-connect)

- [Sign in with a managed identity configured on an Azure virtual machine](connect-from-azure-identity) (Only available for self-hosted GitHub runners)

- [Sign in with a service principal and secret](connect-from-azure-secret) (Not recommended)

By default, the Azure Login action logs in with the Azure CLI and sets up the GitHub Actions runner environment for Azure CLI. You can use Azure PowerShell with `enable-AzPSSession` property of the Azure Login action. This property sets up the GitHub Actions runner environment with the Azure PowerShell module.

You can also use the Azure Login action to connect to public or sovereign clouds including Azure Government and Azure Stack Hub.

## Connect with other Azure services

The following articles provide details on connecting from GitHub to Azure and other services.

Service
Tutorial

Microsoft Entra ID
[Sign in to GitHub Enterprise with Microsoft Entra ID (single sign-on)](/en-us/azure/active-directory/saas-apps/github-tutorial)

Power BI
[Connect Power BI with GitHub](/en-us/power-bi/service-connect-to-github)

GitHub Connectors
[GitHub connector for Azure Logic Apps, Power Automate, and Power Apps](/en-us/connectors/github/)

Azure Databricks
[Use GitHub as version control for notebooks](/en-us/azure/databricks/notebooks/github-version-control)

[Deploy apps from GitHub to Azure](deploy-to-azure)

					
		
	 
		
		
	
					
		
		
			
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
		2024-08-08