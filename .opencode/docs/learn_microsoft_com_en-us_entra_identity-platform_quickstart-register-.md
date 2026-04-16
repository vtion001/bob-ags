# How to register an app in Microsoft Entra ID - Microsoft identity platform | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/entra/identity-platform/quickstart-register-app
> Cached: 2026-04-16T20:57:33.345Z

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
					
				
			
		
	
					# Register an application in Microsoft Entra ID

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					In this how-to guide, you learn how to register an application in Microsoft Entra ID. This process is essential for establishing a trust relationship between your application and the Microsoft identity platform. By completing this quickstart, you enable identity and access management (IAM) for your app, allowing it to securely interact with Microsoft services and APIs.

## Prerequisites

- An Azure account that has an active subscription. [Create an account for free](https://azure.microsoft.com/pricing/purchase-options/azure-account?cid=msft_learn).

- The Azure account must be at least a [Application Developer](../identity/role-based-access-control/permissions-reference#application-developer).

- A workforce or external tenant. You can use your **Default Directory** for this quickstart. If you need an external tenant, complete [set up an external tenant](/en-us/entra/external-id/customers/quickstart-tenant-setup).

## Register an application

Registering your application in Microsoft Entra establishes a trust relationship between your app and the Microsoft identity platform. The trust is unidirectional. Your app trusts the Microsoft identity platform, and not the other way around. Once created, the application object can't be moved between different tenants.

Follow these steps to create the app registration:

Sign in to the [Microsoft Entra admin center](https://entra.microsoft.com) as at least an [Application Developer](../identity/role-based-access-control/permissions-reference#application-developer).

If you have access to multiple tenants, use the **Settings** icon 
 in the top menu to switch to the tenant in which you want to register the application.

Browse to **Entra ID** > **App registrations** and select **New registration**.

Enter a meaningful **Name** for your app, for example *identity-client-app*. App users can see this name, and it can be changed at any time. You can have multiple app registrations with the same name.

Under **Supported account types**, specify who can use the application. We recommend you select **Accounts in this organizational directory only** for most applications. Refer to the table for more information on each option.

Supported account types
Description

**Accounts in this organizational directory only**
For *single-tenant* apps for use only by users (or guests) in *your* tenant.

**Accounts in any organizational directory**
For *multitenant* apps and you want users in *any* Microsoft Entra tenant to be able to use your application. Ideal for software-as-a-service (SaaS) applications that you intend to provide to multiple organizations.

**Accounts in any organizational directory and personal Microsoft accounts**
For *multitenant* apps that support both organizational and personal Microsoft accounts (for example, Skype, Xbox, Live, Hotmail).

**Personal Microsoft accounts**
For apps used only by personal Microsoft accounts (for example, Skype, Xbox, Live, Hotmail).

Select **Register** to complete the app registration.

The application's **Overview** page is displayed. Record the **Application (client) ID**, which uniquely identifies your application and is used in your application's code as part of validating the security tokens it receives from the Microsoft identity platform.

Important

New app registrations are hidden to users by default. When you're ready for users to see the app on their [My Apps page](https://support.microsoft.com/account-billing/sign-in-and-start-apps-from-the-my-apps-portal-2f3b1bae-0e5a-4a86-a33e-876fbd2a4510) you can enable it. To enable the app, in the Microsoft Entra admin center navigate to **Entra ID** > **Enterprise apps** and select the app. Then on the **Properties** page, set **Visible to users?** to **Yes**.

## Grant admin consent (external tenants only)

Once you register your application, it gets assigned the **User.Read** permission. However, for external tenants, the customer users themselves can't consent to permissions themselves. You as the admin must consent to this permission on behalf of all the users in the tenant:

- From the **Overview** page of your app registration, under **Manage** select **API permissions**.

- Select **Grant admin consent for < tenant name >**, then select **Yes**.

- Select **Refresh**, then verify that **Granted for < tenant name >** appears under **Status** for the permission.

## Related content

- [Add a redirect URI to your application](how-to-add-redirect-uri)

- [Add credentials to your application](how-to-add-credentials)

- [Configure an application to expose a web API](quickstart-configure-app-expose-web-apis)

- [Microsoft identity platform code samples](sample-v2-code)

- [Add your application to a user flow](/en-us/entra/external-id/customers/how-to-user-flow-add-application)

					
		
	 
		
		
	
					
		
		
			
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
		2025-04-08