# Add and manage app credentials in Microsoft Entra ID - Microsoft identity platform | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/entra/identity-platform/how-to-add-credentials
> Cached: 2026-04-16T20:57:39.627Z

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
					
				
			
		
	
					# Add and manage application credentials in Microsoft Entra ID

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					When building confidential client applications, managing credentials effectively is critical. This article explains how to add client certificates, federated identity credentials, or client secrets to your app registration in Microsoft Entra. These credentials enable your application to authenticate itself securely and access web APIs without user interaction.

## Prerequisites

[Quickstart: Register an app in Microsoft Entra ID](quickstart-register-app).

## Add a credential to your application

When you create credentials for a confidential client application:

Microsoft recommends that you use a certificate instead of a client secret before moving the application to a production environment. For more information on how to use a certificate, see instructions in [Microsoft identity platform application authentication certificate credentials](certificate-credentials).

For testing purposes, you can create a self-signed certificate and configure your apps to authenticate with it. However, **in production**, you should purchase a certificate signed by a well-known certificate authority, then use [Azure Key Vault](/en-us/azure/key-vault/general/overview) to manage certificate access and lifetime.

To learn more about client secret vulnerabilities, refer to [Migrate applications away from secret-based authentication](/en-us/entra/identity/enterprise-apps/migrate-applications-from-secrets).

[Add a certificate](#tabpanel_1_certificate)

[Add a client secret](#tabpanel_1_client-secret)

[Add a federated credential](#tabpanel_1_federated-credential)

Sometimes called a *public key*, a certificate is the recommended credential type because they're considered more secure than client secrets.

In the Microsoft Entra admin center, in **App registrations**, select your application.

Select **Certificates & secrets** > **Certificates** > **Upload certificate**.

Select the file you want to upload. It must be one of the following file types: *.cer*, *.pem*, *.crt*.

Select **Add**.

Record the certificate **Thumbprint** for use in your client application code.

Sometimes called an *application password*, a client secret is a string value your app can use in place of a certificate to identify itself.

Client secrets are less secure than certificate or federated credentials and therefore should **not be used** in production environments. While they may be convenient for local app development, it's imperative to use certificate or federated credentials for any applications running in production to ensure higher security.

In the Microsoft Entra admin center, in **App registrations**, select your application.

Select **Certificates & secrets** > **Client secrets** > **New client secret**.

Add a description for your client secret.

Select an expiration for the secret or specify a custom lifetime.

- Client secret lifetime is limited to two years (24 months) or less. You can't specify a custom lifetime longer than 24 months.

- Microsoft recommends that you set an expiration value of less than 12 months.

Select **Add**.

Record the client secret **Value** for use in your client application code. This secret value is *never displayed again* after you leave this page.

Note

If you're using an Azure DevOps service connection that automatically creates a service principal, you need to update the client secret from the Azure DevOps portal site instead of directly updating the client secret. Refer to this document on how to update the client secret from the Azure DevOps portal site:
[Troubleshoot Azure Resource Manager service connections](/en-us/azure/devops/pipelines/release/azure-rm-endpoint#service-principals-token-expired).

Federated identity credentials are a type of credential that allows workloads, such as GitHub Actions, workloads running on Kubernetes, or workloads running in compute platforms outside of Azure access Microsoft Entra protected resources without needing to manage secrets using [workload identity federation](../workload-id/workload-identity-federation).

To add a federated credential, follow these steps:

In the Microsoft Entra admin center, in **App registrations**, select your application.

Select **Certificates & secrets** > **Federated credentials** > **Add credential**.

In the **Federated credential scenario** drop-down box, select one of the supported scenarios, and follow the corresponding guidance to complete the configuration.

- **Customer managed keys** for encrypting data in your tenant using Azure Key Vault in another tenant.

- **GitHub actions deploying Azure resources** to [configure a GitHub workflow](../workload-id/workload-identity-federation-create-trust#github-actions) to get tokens for your application and deploy assets to Azure.

- **Kubernetes accessing Azure resources** to configure a [Kubernetes service account](../workload-id/workload-identity-federation-create-trust#kubernetes) to get tokens for your application and access Azure resources.

- **Other issuer** to configure the application to [trust a managed identity](../workload-id/workload-identity-federation-config-app-trust-managed-identity) or an identity managed by an external [OpenID Connect provider](../workload-id/workload-identity-federation-create-trust#other-identity-providers) to get tokens for your application and access Azure resources.

For more information on how to get an access token with a federated credential, see [Microsoft identity platform and the OAuth 2.0 client credentials flow](v2-oauth2-client-creds-grant-flow#third-case-access-token-request-with-a-federated-credential).

## Related content

- [Microsoft identity platform application authentication certificate credentials](certificate-credentials)

- [Configure an application to trust a managed identity](/en-us/entra/workload-id/workload-identity-federation-config-app-trust-managed-identity?tabs=microsoft-entra-admin-center)

- [Public client and confidential client applications](msal-client-applications)

- [Create a sign-up and sign-in user flow for an external tenant app](../external-id/customers/how-to-user-flow-sign-up-sign-in-customers)

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