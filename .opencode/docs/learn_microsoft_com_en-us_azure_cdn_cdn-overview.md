# What is a content delivery network? - Azure | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/cdn/cdn-overview
> Cached: 2026-04-16T20:57:55.243Z

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
					
				
			
		
	
					# What is a content delivery network on Azure?

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					
Important

- Starting August 15, 2025, Azure CDN from Microsoft (classic) will no longer support new domain onboarding or profile creation. Migrate to [AFD Standard and Premium](/en-us/azure/cdn/migrate-tier?toc=%2Fazure%2Ffrontdoor%2Ftoc.json) to create new domains or profiles and avoid service disruption. [Learn more](https://azure.microsoft.com/updates?id=498522)

- Starting August 15, 2025, Azure CDN from Microsoft (classic) will [no longer support Managed certificates](/en-us/azure/security/fundamentals/managed-tls-changes). To avoid service disruption, either [switch to Bring Your Own Certificate (BYOC)](/en-us/azure/cdn/cdn-custom-ssl?toc=%2Fazure%2Ffrontdoor%2Ftoc.json&tabs=option-1-default-enable-https-with-a-cdn-managed-certificate) or migrate to [AFD Standard and Premium](/en-us/azure/cdn/migrate-tier?toc=%2Fazure%2Ffrontdoor%2Ftoc.json) by this date. Existing managed certificates will be auto renewed before August 15, 2025, and remain valid until April 14, 2026. [Learn more](https://azure.microsoft.com/updates?id=498522)

- Azure CDN Standard from Microsoft (classic) will be retired on September 30, 2027. To avoid service disruption ⁠[migrate to AFD Standard or Premium](/en-us/azure/cdn/migrate-tier). ⁠[Learn more.](https://azure.microsoft.com/updates?id=Azure-CDN-Standard-from-Microsoft-classic-will-be-retired-on-30-September-2027)

- Azure CDN from Edgio was retired on January 15, 2025. ⁠[Learn more.](/en-us/previous-versions/azure/cdn/edgio-retirement-faq?toc=%2Fazure%2Ffrontdoor%2FTOC.json)

A content delivery network is a distributed network of servers that can efficiently deliver web content to users. A content delivery network store cached content on edge servers in point of presence (POP) locations that are close to end users, to minimize latency.

Azure Content Delivery Network offers developers a global solution for rapidly delivering high-bandwidth content to users by caching their content at strategically placed physical nodes across the world. Azure Content Delivery Network can also accelerate dynamic content, which can't get cached, by using various network optimizations using content delivery network POPs. For example, route optimization to bypass Border Gateway Protocol (BGP).

The benefits of using Azure Content Delivery Network to deliver web site assets include:

- Better performance and improved user experience for end users, especially when using applications where multiple round-trips requests required by end users to load contents.

- Large scaling to better handle instantaneous high loads, such as the start of a product launch event.

- Distribution of user requests and serving of content directly from edge servers so that less traffic gets sent to the origin server.

For a list of current content delivery network node locations, see [Azure Content Delivery Network POP locations](cdn-pop-locations).

## How it works

A user (Alice) requests a file (also called an asset) by using a URL with a special domain name, such as *<endpoint name>*.azureedge.net. This name can be an endpoint hostname or a custom domain. The DNS routes the request to the best performing POP location, which is usually the POP that is geographically closest to the user.

If no edge servers in the POP have the file in their cache, the POP requests the file from the origin server. The origin server can be an Azure Web App, Azure Cloud Service, Azure Storage account, or any publicly accessible web server.

The origin server returns the file to an edge server in the POP.

An edge server in the POP caches the file and returns the file to the original requester (Alice). The file remains cached on the edge server in the POP until the time to live (TTL) specified by its HTTP headers expires. If the origin server didn't specify a TTL, the default TTL is seven days.

More users can then request the same file by using the same URL that Alice used, and gets directed to the same POP.

If the TTL for the file hasn't expired, the POP edge server returns the file directly from the cache. This process results in a faster, more responsive user experience.

## Requirements

- To use Azure Content Delivery Network, you must own at least one Azure subscription.

- You also need to create a content delivery network profile, which is a collection of content delivery network endpoints. Every content delivery network endpoint is a specific configuration which users can customize with required content delivery behavior and access. To organize your content delivery network endpoints by internet domain, web application, or some other criteria, you can use multiple profiles.

- For information about the Azure content delivery network billing structure, see [Understanding Azure Content Delivery Network billing](cdn-billing).

### Limitations

Each Azure subscription has default limits for the following resources:

- The number of content delivery network profiles created.

- The number of endpoints created in a content delivery network profile.

- The number of custom domains mapped to an endpoint.

For more information about content delivery network subscription limits, see [content delivery network limits](../azure-resource-manager/management/azure-subscription-service-limits).

## Azure Content Delivery Network features

Azure Content Delivery Network offers the following key features:

- [Dynamic site acceleration](cdn-dynamic-site-acceleration)

- [Content delivery network caching rules](cdn-caching-rules)

- [HTTPS custom domain support](cdn-custom-ssl)

- [Azure Diagnostics logs](cdn-azure-diagnostic-logs)

- [File compression](cdn-improve-performance)

- [Geo-filtering](cdn-restrict-access-by-country-region)

For a complete list of features that each Azure Content Delivery Network product supports, see [Compare Azure Content Delivery Network product features](cdn-features).

## Next steps

- To get started with content delivery network, see [Create an Azure Content Delivery Network profile and endpoint](cdn-create-new-endpoint).

- Manage your content delivery network endpoints through the [Microsoft Azure portal](https://portal.azure.com) or with [PowerShell](cdn-manage-powershell).

- Learn how to automate Azure Content Delivery Network with [.NET](cdn-app-dev-net) or [Node.js](cdn-app-dev-node).

- [Learn module: Introduction to Azure Content Delivery Network](/en-us/training/modules/intro-to-azure-content-delivery-network).

					
		
	 
		
		
	
					
		
		
			
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
		2026-02-28