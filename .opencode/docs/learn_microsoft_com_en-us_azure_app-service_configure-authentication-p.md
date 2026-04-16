# Configure Microsoft Entra Authentication - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/configure-authentication-provider-aad
> Cached: 2026-04-16T20:57:46.307Z

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
					
				
			
		
	
					# Configure your App Service or Azure Functions app to use Microsoft Entra sign-in

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					Select another authentication provider to jump to it.

- [Microsoft Entra](configure-authentication-provider-aad)

- [Facebook](configure-authentication-provider-facebook)

- [Google](configure-authentication-provider-google)

- [GitHub](configure-authentication-provider-github)

- [X](configure-authentication-provider-twitter)

- [OpenID Connect provider](configure-authentication-provider-openid-connect)

- [Sign in with Apple (preview)](configure-authentication-provider-apple)

This article shows you how to configure authentication for Azure App Service or Azure Functions so that your app signs in users with the [Microsoft identity platform](../active-directory/develop/v2-overview) (Microsoft Entra) as the authentication provider.

## Choose a tenant for your application and its users

Before your application can sign in users, you need to register it in a workforce tenant or an external tenant. If you're making your app available to employee or business guests, register your app in a workforce tenant. If your app is for consumers and business customers, register it in an external tenant.

Sign in to the [Azure portal](https://portal.azure.com/) and go to your App Service app or Functions app.

On your app's left menu, select **Settings** > **Authentication**, and then select **Add identity provider**.

On the **Add an identity provider** page, select **Microsoft** as the **Identity provider** value to sign in Microsoft and Microsoft Entra identities.

Under **Choose a tenant for your application and its users**, select either:

- **Workforce configuration (current tenant)** for employees and business guests

- **External configuration** for consumers and business customers

## Choose the app registration

The App Service authentication feature can automatically create an app registration for you. Or, you can use a registration that you or a directory admin creates separately.

Create a new app registration automatically, unless you need to create an app registration separately. You can customize the app registration in the [Microsoft Entra admin center](https://entra.microsoft.com) later if you want.

The following situations are the most common cases for using an existing app registration:

- Your account doesn't have permissions to create app registrations in your Microsoft Entra tenant.

- You want to use an app registration from a different Microsoft Entra tenant than the one that contains your app. This is always the case if you selected **External configuration** when you chose a tenant.

- The option to create a new registration isn't available for government clouds.

[Workforce configuration](#tabpanel_1_workforce-configuration)

[External configuration](#tabpanel_1_external-configuration)

###  Option 1: Create and use a new app registration

Select **Create new app registration**.

For **Name**, enter the name of the new app registration.

Select the **Supported account type** value:

- **Current tenant - Single tenant**. Accounts in this organizational directory only. All user and guest accounts in your directory can use your application or API. Use this option if your target audience is internal to your organization.

- **Any Microsoft Entra directory - Multitenant**. Accounts in any organizational directory. All users with a work or school account from Microsoft can use your application or API. These accounts include schools and businesses that use Office 365. Use this option if your target audience is business or educational customers and to enable multitenancy.

- **Any Microsoft Entra directory & personal Microsoft accounts**. Accounts in any organizational directory and personal Microsoft accounts (for example, Skype or Xbox). All users with a work or school account, or a personal Microsoft account, can use your application or API. It includes schools and businesses that use Office 365, along with personal accounts that are used to sign in to services like Xbox and Skype. Use this option to target the widest set of Microsoft identities and to enable multitenancy.

- **Personal Microsoft accounts only**. Personal accounts that are used to sign in to services like Xbox and Skype. Use this option to target the widest set of Microsoft identities.

You can change the name of the registration or the supported account types later if you want.

A client secret is created as a slot-sticky [application setting](configure-common#configure-app-settings) named `MICROSOFT_PROVIDER_AUTHENTICATION_SECRET`. If you want to manage the secret in Azure Key Vault, you can update that setting later to use [Key Vault references](app-service-key-vault-references). Alternatively, you can change this to [use an identity instead of a client secret](#use-a-managed-identity-instead-of-a-secret-preview). Support for using an identity is currently in preview.

###  Option 2: Use an existing registration created separately

To use an existing registration, select either:

**Pick an existing app registration in this directory**. Then select an app registration from the dropdown list.

**Provide the details of an existing app registration**. Then provide:

**Application (client) ID**.

**Client secret (recommended)**. A secret value that the application uses to prove its identity when it requests a token. This value is saved in your app's configuration as a slot-sticky application setting named `MICROSOFT_PROVIDER_AUTHENTICATION_SECRET`. If the client secret isn't set, sign-in operations from the service use the OAuth 2.0 implicit grant flow, which we *don't* recommend.

You can also configure the application to [use an identity instead of a client secret](#use-a-managed-identity-instead-of-a-secret-preview). Support for using an identity is currently in preview.

**Issuer URL**. This URL takes the form `<authentication-endpoint>/<tenant-id>/v2.0`. Replace `<authentication-endpoint>` with the authentication endpoint [value that's specific to the cloud environment](/en-us/entra/identity-platform/authentication-national-cloud#azure-ad-authentication-endpoints). For example, a workforce tenant in global Azure would use `https://login.microsoftonline.com` as its authentication endpoint.

You can find this value in the Microsoft Entra admin center. Go to **App registrations**, select your app, and then select **Endpoints**. Copy the **OpenID Connect metadata document** endpoint for your tenant, and then remove `/.well-known/openid-configuration` from the end of the URL. For example, if the metadata endpoint is `https://login.microsoftonline.com/<tenant-id>/v2.0/.well-known/openid-configuration`, use `https://login.microsoftonline.com/<tenant-id>/v2.0` as the issuer URL.

Note

If you created your identity provider using the express setup (Option 1), the issuer URL is automatically set to use the legacy `https://sts.windows.net` endpoint. To align with current Microsoft Entra ID best practices, edit your identity provider and update the issuer URL to use `https://login.microsoftonline.com/<tenant-id>/v2.0` instead.

If you need to manually create an app registration in a workforce tenant, see [Register an application with the Microsoft identity platform](/en-us/entra/identity-platform/quickstart-register-app). As you go through the registration process, be sure to note the application (client) ID and client secret values.

During the registration process, in the **Redirect URIs** section, select **Web** for platform, and enter a redirect URI. For example, enter `https://contoso.azurewebsites.net/.auth/login/aad/callback`.

Now, modify the app registration:

On the left pane, select **Expose an API** > **Add** > **Save**. This value uniquely identifies the application when it's used as a resource, which allows tokens that grant access to be requested. The value is a prefix for scopes that you create.

For a single-tenant app, you can use the default value, which is in the form `api://<application-client-id>`. You can also specify a more readable URI like `https://contoso.com/api`, based on one of the verified domains for your tenant. For a multitenant app, you must provide a custom URI. For more information about accepted formats for app ID URIs, see [Security best practices for application properties in Microsoft Entra ID](../active-directory/develop/security-best-practices-for-app-registration#application-id-uri).

Select **Add a scope**, and then:

- In **Scope name**, enter **user_impersonation**.

- In **Who can consent**, select **Admins and users** if you want to allow users to consent to this scope.

- Enter the consent scope name. Enter a description that you want users to see on the consent page. For example, enter **Access** *application-name*.

- Select **Add scope**.

(Recommended) Create a client assertion for the app. To create a client secret:

- On the left pane, select **Certificates & secrets** > **Client secrets** > **New client secret**.

- Enter a description and expiration, and then select **Add**.

- In the **Value** field, copy the client secret value. After you move away from this page, it doesn't appear again.

You can also configure the application to [use an identity instead of a client secret](#use-a-managed-identity-instead-of-a-secret-preview). Support for using an identity is currently in preview.

(Optional) To add multiple reply URLs, select **Authentication**.

###  Option 1: Create and use a new app registration

Select **Create new app registration**.

For **Select a tenant**, take one of the following actions:

Select an existing tenant to use.

Select **Create new**, and then:

On the **Create a tenant** page, add the **Tenant Name** and **Domain Name** values.

Select a **Location** value, and then select **Review and create** > **Create**. The tenant creation process takes a few minutes.

For more information about creating a tenant, see [Use your Azure subscription to create an external tenant](/en-us/entra/external-id/customers/quickstart-tenant-setup).

#### Set up sign-in

Select **Configure** to configure external authentication for the new tenant.

The browser opens **Configure customer authentication**.

Select or create a user flow. The user flow defines the sign-in methods that your external users can use. Each app can only have one user flow, but you can reuse the same user flow for multiple apps.

Take one of the following actions:

Select a user flow from the dropdown list.

Select **Create new**, and then:

For **Name**, enter a name for the user flow.

Select the sign-in method for your external users.

**Email and password** and **Email and one-time passcode** are already configured in the new tenant. You can also add [Google](/en-us/entra/external-id/customers/how-to-google-federation-customers) or [Facebook](/en-us/entra/external-id/customers/how-to-facebook-federation-customers) as identity providers.

Select **Create**.

#### Customize branding

Select **Next** to customize branding.

Add your logo, select a background color, and select a sign-in layout.

Select **Next**, and then select **Yes, update the changes** to accept the branding changes.

On the **Review** tab, select **Configure** to confirm the external tenant update.

The browser opens to the **Add an identity provider** page.

###  Option 2: Use an existing registration created separately

To use an existing registration, select **Provide the details of an existing app registration**. Then provide values for:

- **Application (client) ID**

- **Client secret**

- **Issuer URL**. In the Microsoft Entra admin center, go to **App registrations**, select your app, and then select **Endpoints**. Copy the **OpenID Connect metadata document** endpoint for your tenant, and then remove `/.well-known/openid-configuration` from the end of the URL. For example, if the metadata endpoint is `https://login.microsoftonline.com/<tenant-id>/v2.0/.well-known/openid-configuration`, use `https://login.microsoftonline.com/<tenant-id>/v2.0` as the issuer URL.

If you need to manually create an app registration in an external tenant, see [Register an app in your external tenant](/en-us/entra/external-id/customers/how-to-register-ciam-app?tabs=webapp#register-your-web-app).

During the registration process, in the **Redirect URIs** section, select **Web** for platform, and enter a redirect URI. For example, enter `https://contoso.azurewebsites.net/.auth/login/aad/callback`.

Now, modify the app registration:

On the left pane, select **Expose an API** > **Add** > **Save**. This value uniquely identifies the application when it's used as a resource, which allows tokens that grant access to be requested. The value is a prefix for scopes that you create.

For a single-tenant app, you can use the default value, which is in the form `api://<application-client-id>`. You can also specify a more readable URI like `https://contoso.com/api`, based on one of the verified domains for your tenant. For a multitenant app, you must provide a custom URI. For more information about accepted formats for app ID URIs, see [Security best practices for application properties in Microsoft Entra ID](../active-directory/develop/security-best-practices-for-app-registration#application-id-uri).

Select **Add a scope**, and then:

- In **Scope name**, enter **user_impersonation**.

- In **Who can consent**, select **Admins and users** if you want to allow users to consent to this scope.

- Enter the consent scope name. Then enter a description that you want users to see on the consent page. For example, enter **Access** *application-name*.

- Select **Add s

... [Content truncated]