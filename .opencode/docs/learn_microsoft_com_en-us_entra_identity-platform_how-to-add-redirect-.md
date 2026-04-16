# How to add a redirect URI to your application - Microsoft identity platform | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/entra/identity-platform/how-to-add-redirect-uri
> Cached: 2026-04-16T20:57:39.030Z

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
					
				
			
		
	
					# How to add a redirect URI to your application

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					To sign in a user, your application must send a login request to the Microsoft Entra authorization endpoint, with a redirect URI specified as a parameter. The redirect URI is a critical security feature that ensures the Microsoft Entra authentication server only sends authorization codes and access tokens to the intended recipient.

## Prerequisites

- [Quickstart: Register an app in Microsoft Entra ID](quickstart-register-app).

## Add a redirect URI

A *redirect URI* is where the Microsoft identity platform sends security tokens after authentication. Redirect URIs are configured in **Platform configurations** in the Microsoft Entra admin center. For **Web** and **Single-page applications**, you need to specify a redirect URI manually. For **Mobile and desktop** platforms, you select from generated redirect URIs.

Follow these steps to configure settings based on your target platform or device:

In the Microsoft Entra admin center, in **App registrations**, select your application.

Under **Manage**, select **Authentication**.

Under **Platform configurations**, select **Add a platform**.

Under **Configure platforms**, select the tile for your application type (platform) to configure its settings.

Platform
Configuration settings
Example

**Web**
Enter the **Redirect URI** for a web app that runs on a server. Front channel logout URLs can also be added
`https://contoso.com/auth-response`  or 
 `http://localhost:3000/auth-response` if you run your app locally.

**Single-page application**
Enter a **Redirect URI** for client-side apps using JavaScript, Angular, React.js, or Blazor WebAssembly. Front channel logout URLs can also be added
`https://contoso.com/auth-response`  or 
 `http://localhost:3000/auth-response` if you run your app locally.

**iOS / macOS**
Enter the app **Bundle ID**, which generates a redirect URI for you. Find it in **Build Settings** or in Xcode in *Info.plist*.
`com.microsoft.identityapp.ciam.MSALiOS`.

**Android**
Enter the app **Package name**, which generates a redirect URI for you. Find it in the *AndroidManifest.xml* file. Also generate and enter the **Signature hash**.
Package name: 
• `com.azuresamples.msalandroidapp` 
 Signature has: 
• `aB1cD2eF-3gH4iJ5kL6-mN7oP8qR=`.

**Mobile and desktop applications**
Select this platform for desktop apps or mobile apps not using MSAL or a broker. Select a suggested **Redirect URI**, or specify one or more **Custom redirect URIs**
`https://login.microsoftonline.com/common/oauth2/nativeclient`

Select **Configure** to complete the platform configuration.

### Redirect URI restrictions

There are some restrictions on the format of the redirect URIs you add to an app registration. For details about these restrictions, see [Redirect URI (reply URL) restrictions and limitations](reply-url).

## Related content

- [Redirect URI (reply URL) restrictions and limitations](reply-url).

- [Add credentials to your application](how-to-add-credentials)

- [Create a sign-up and sign-in user flow for an external tenant app](../external-id/customers/how-to-user-flow-sign-up-sign-in-customers)

					
		
	 
		
		
	
					
		
		
			
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
		2025-05-29