# Authentication and Authorization - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/overview-authentication-authorization
> Cached: 2026-04-16T20:57:39.466Z

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
					
				
			
		
	
					# Authentication and authorization in Azure App Service and Azure Functions

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					Azure App Service provides built-in authentication (signing in users) and authorization (providing access to secure data) capabilities. These capabilities are sometimes called *Easy Auth*. You can use them to sign in users and access data by writing little or no code in your web app, RESTful API, mobile server, and [functions](../azure-functions/functions-overview).

This article describes how App Service helps simplify authentication and authorization for your app.

## Reasons to use built-in authentication

To implement authentication and authorization, you can use the bundled security features in your web framework of choice, or you can write your own tools. Implementing a secure solution for authentication and authorization can take significant effort. You need to follow industry best practices and standards. You also need to ensure that your solution stays up to date with the latest security, protocol, and browser updates.

The built-in capabilities of App Service and Azure Functions can save you time and effort by providing out-of-the-box authentication with federated identity providers, so you can focus on the rest of your application.

With App Service, you can integrate authentication capabilities into your web app or API without implementing them yourself. This feature is built directly into the platform and doesn't require any particular language, SDK, security expertise, or code. You can integrate it with multiple sign-in providers, such as Microsoft Entra, Facebook, Google, and X.

Your app might need to support more complex scenarios, such as Visual Studio integration or incremental consent. Several authentication solutions are available to support these scenarios. To learn more, see [Authentication scenarios and recommendations](identity-scenarios).

## Identity providers

App Service uses [federated identity](https://en.wikipedia.org/wiki/Federated_identity). A Microsoft or non-Microsoft identity provider manages the user identities and authentication flow for you. The following identity providers are available by default:

Provider
Sign-in endpoint
How-to guidance

[Microsoft Entra](/en-us/entra/index)
`/.auth/login/aad`
[App Service Microsoft Entra platform sign-in](configure-authentication-provider-aad)

[Facebook](https://developers.facebook.com/docs/facebook-login)
`/.auth/login/facebook`
[App Service Facebook sign-in](configure-authentication-provider-facebook)

[Google](https://developers.google.com/identity/choose-auth)
`/.auth/login/google`
[App Service Google sign-in](configure-authentication-provider-google)

[X](https://developer.x.com/en/docs/basics/authentication)
`/.auth/login/x`
[App Service X sign-in](configure-authentication-provider-twitter)

[GitHub](https://docs.github.com/en/developers/apps/building-oauth-apps/creating-an-oauth-app)
`/.auth/login/github`
[App Service GitHub sign-in](configure-authentication-provider-github)

[Apple](https://developer.apple.com/sign-in-with-apple/)
`/.auth/login/apple`
[App Service sign-in via Apple sign-in (preview)](configure-authentication-provider-apple)

Any [OpenID Connect](https://openid.net/connect/) provider
`/.auth/login/<providerName>`
[App Service OpenID Connect sign-in](configure-authentication-provider-openid-connect)

When you configure this feature with one of these providers, its sign-in endpoint is available for user authentication and for validation of authentication tokens from the provider. You can provide your users with any number of these sign-in options.

## Considerations for using built-in authentication

Enabling built-in authentication causes all requests to your application to be automatically redirected to HTTPS, regardless of the App Service configuration setting to enforce HTTPS. You can disable this automatic redirection by using the `requireHttps` setting in the V2 configuration. However, we recommend that you keep using HTTPS and ensure that no security tokens are ever transmitted over nonsecure HTTP connections.

You can use App Service for authentication with or without restricting access to your site content and APIs. Set access restrictions in the **Settings** > **Authentication** > **Authentication settings** section of your web app:

- To restrict app access to only authenticated users, set **Action to take when request is not authenticated** to sign in with one of the configured identity providers.

- To authenticate but not restrict access, set **Action to take when request is not authenticated** to **Allow anonymous requests (no action)**.

Important

You should give each app registration its own permission and consent. Avoid permission sharing between environments by using separate app registrations for separate deployment slots. When you're testing new code, this practice can help prevent problems from affecting the production app.

## How it works

### Feature architecture

The authentication and authorization middleware component is a feature of the platform that runs on the same virtual machine as your application. When you enable it, every incoming HTTP request passes through that component before your application handles it.

The platform middleware handles several things for your app:

- Authenticates users and clients with the specified identity providers

- Validates, stores, and refreshes OAuth tokens that the configured identity providers issued

- Manages the authenticated session

- Injects identity information into HTTP request headers

The module runs separately from your application code. You can configure it by using Azure Resource Manager settings or by using [a configuration file](configure-authentication-file-based). No SDKs, specific programming languages, or changes to your application code are required.

#### Feature architecture on Windows (non-container deployment)

The authentication and authorization module runs as a native [IIS module](/en-us/iis/get-started/introduction-to-iis/iis-modules-overview) in the same sandbox as your application. When you enable it, every incoming HTTP request passes through it before your application handles it.

#### Feature architecture on Linux and containers

The authentication and authorization module runs in a separate container that's isolated from your application code. The module uses the [Ambassador pattern](/en-us/azure/architecture/patterns/ambassador) to interact with the incoming traffic to perform similar functionality as on Windows. Because it doesn't run in process, no direct integration with specific language frameworks is possible. However, the relevant information that your app needs is passed through in request headers.

### Authentication flow

The authentication flow is the same for all providers. It differs depending on whether you want to sign in with the provider's SDK:

**Without provider SDK**: The application delegates federated sign-in to App Service. This delegation is typically the case with browser apps, which can present the provider's sign-in page to the user. The server code manages the sign-in process, so it's also called *server-directed flow* or *server flow*.

This case applies to browser apps and mobile apps that use an embedded browser for authentication.

**With provider SDK**: The application signs in users to the provider manually. Then it submits the authentication token to App Service for validation. This process is typically the case with browserless apps, which can't present the provider's sign-in page to the user. The application code manages the sign-in process, so it's also called *client-directed flow* or *client flow*.

This case applies to REST APIs, [Azure Functions](../azure-functions/functions-overview), and JavaScript browser clients, in addition to browser apps that need more flexibility in the sign-in process. It also applies to native mobile apps that sign in users by using the provider's SDK.

Calls from a trusted browser app in App Service to another REST API in App Service or [Azure Functions](../azure-functions/functions-overview) can be authenticated through the server-directed flow. For more information, see [Customize sign-in and sign-out in Azure App Service authentication](configure-authentication-customize-sign-in-out).

The following table shows the steps of the authentication flow.

Step
Without provider SDK
With provider SDK

1. Sign in the user
Provider redirects the client to `/.auth/login/<provider>`.
Client code signs in the user directly with the provider's SDK and receives an authentication token. For more information, see the provider's documentation.

2. Conduct post-authentication
Provider redirects the client to `/.auth/login/<provider>/callback`.
Client code [posts the token from the provider](configure-authentication-customize-sign-in-out#client-directed-sign-in) to `/.auth/login/<provider>` for validation.

3. Establish an authenticated session
App Service adds an authenticated cookie to the response.
App Service returns its own authentication token to the client code.

4. Serve authenticated content
Client includes an authentication cookie in subsequent requests (automatically handled by the browser).
Client code presents the authentication token in the `X-ZUMO-AUTH` header.

For client browsers, App Service can automatically direct all unauthenticated users to `/.auth/login/<provider>`. You can also present users with one or more `/.auth/login/<provider>` links to sign in to your app by using their provider of choice.

### Authorization behavior

In the [Azure portal](https://portal.azure.com), you can configure App Service with various behaviors when an incoming request isn't authenticated. The following sections describe the options.

Important

By default, this feature provides only authentication, not authorization. Your application might still need to make authorization decisions, in addition to any checks that you configure here.

#### Restricted access

**Allow unauthenticated requests**: This option defers authorization of unauthenticated traffic to your application code. For authenticated requests, App Service also passes along authentication information in the HTTP headers.

This option provides more flexibility in handling anonymous requests. For example, it lets you [present multiple sign-in providers](configure-authentication-customize-sign-in-out#use-multiple-sign-in-providers) to your users. However, you must write code.

**Require authentication**: This option rejects any unauthenticated traffic to your application. Specific action to take is specified in the [Unauthenticated requests](#unauthenticated-requests) section later in this article.

With this option, you don't need to write any authentication code in your app. You can handle finer authorization, such as role-specific authorization, by [inspecting the user's claims](configure-authentication-user-identities).

Caution

Restricting access in this way applies to all calls to your app, which may not be desirable for apps wanting a publicly available home page, as in many single-page applications. If exceptions are needed, you need to [configure excluded paths in a configuration-file](configure-authentication-file-based).

Note

When using the Microsoft identity provider for users in your organization, the default behavior is that any user in your Microsoft Entra tenant can request a token for your application. You can [configure the application in Microsoft Entra](../active-directory/develop/howto-restrict-your-app-to-a-set-of-users) if you want to restrict access to your app to a defined set of users. App Service also offers some [basic built-in authorization checks](configure-authentication-provider-aad#authorize-requests) which can help with some validations. To learn more about authorization in Microsoft Entra, see [Microsoft Entra authorization basics](../active-directory/develop/authorization-basics).

#### Unauthenticated requests

- **HTTP 302 Found redirect: recommended for websites**: Redirects action to one of the configured identity providers. In these cases, a browser client is redirected to `/.auth/login/<provider>` for the provider that you choose.

- **HTTP 401 Unauthorized: recommended for APIs**: Returns an `HTTP 401 Unauthorized` response if the anonymous request comes from a native mobile app. You can also configure the rejection to be `HTTP 401 Unauthorized` for all requests.

- **HTTP 403 Forbidden**: Configures the rejection to be `HTTP 403 Forbidden` for all requests.

- **HTTP 404 Not found**: Configures the rejection to be `HTTP 404 Not found` for all requests.

### Token store

App Service provides a built-in token store. A token store is a repository of tokens that are associated with the users of your web apps, APIs, or native mobile apps. When you enable authentication with any provider, this token store is immediately available to your app.

If your application code needs to access data from these providers on the user's behalf, you typically must write code to collect, store, and refresh these tokens in your application. Actions might include:

- Post to the authenticated user's Facebook timeline.

- Read the user's corporate data by using the Microsoft Graph API.

With the token store, you just [retrieve the tokens](configure-authentication-oauth-tokens#retrieve-tokens-in-app-code) when you need them and [tell App Service to refresh them](configure-authentication-oauth-tokens#refresh-auth-tokens) when they become invalid.

The ID tokens, access tokens, and refresh tokens are cached for the authenticated session. Only the associated user can access th

... [Content truncated]