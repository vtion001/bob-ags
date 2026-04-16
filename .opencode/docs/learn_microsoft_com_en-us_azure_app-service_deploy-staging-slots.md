# Set Up Staging Environments - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/deploy-staging-slots
> Cached: 2026-04-16T20:57:54.754Z

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
					
				
			
		
	
					# Set up staging environments in Azure App Service

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					

When you deploy your web app, web app on Linux, mobile back end, or API app to [Azure App Service](overview), you can use a separate deployment slot instead of the default production slot. This approach is available if you run in the Standard, Premium, or Isolated tier of an App Service plan. Deployment slots are live apps with their own host names. App content and configuration elements can be swapped between two deployment slots, including the production slot.

Deploying your application to a nonproduction slot has the following benefits:

You can validate app changes before you swap the slot into production.

You can make sure that all instances of the slot are warmed up before you swap it into production. This approach eliminates downtime when you deploy your app. The traffic redirection is seamless. No requests are dropped because of swap operations.

You can automate this entire workflow by configuring [auto swap](#Auto-Swap) when pre-swap validation isn't needed.

After a swap, the slot with previously staged app now has the previous production app. If the changes swapped into the production slot aren't as you expect, you can perform the same swap immediately to get your *last known good site* back.

There's no extra charge for using deployment slots. Each App Service plan tier supports a different number of deployment slots. To find out the number of slots that your app's tier supports, see [App Service limits](../azure-resource-manager/management/azure-subscription-service-limits#azure-app-service-limits).

To scale your app to a different tier, make sure that the target tier supports the number of slots that your app already uses. For example, if your app has more than five slots, you can't scale it down to the Standard tier. The Standard tier supports only five deployment slots.

The following video complements the steps in this article by illustrating how to set up staging environments in Azure App Service.

## Prerequisites

- Permissions to perform the slot operation that you want. For information on the required permissions, see [Resource provider operations](../role-based-access-control/permissions/web-and-mobile#microsoftweb). Search for **slot**, for example.

## Add a slot

For you to enable multiple deployment slots, the app must be running in the Standard, Premium, or Isolated tier.

[Azure portal](#tabpanel_1_portal)

[Azure CLI](#tabpanel_1_cli)

[Azure PowerShell](#tabpanel_1_powershell)

In the [Azure portal](https://portal.azure.com), go to your app's management page.

On the left menu, select **Deployment** > **Deployment slots**. Then select **Add**.

Note

If the app isn't already in the Standard, Premium, or Isolated tier, select **Upgrade**. Go to the **Scale** tab of your app before continuing.

In the **Add Slot** dialog, give the slot a name, and select whether to clone an app configuration from another deployment slot. Select **Add** to continue.

You can clone a configuration from any existing slot. Settings that can be cloned include app settings, connection strings, language framework versions, web sockets, HTTP version, and platform bitness.

Note

Currently, a private endpoint isn't cloned across slots.

After you enter the settings, select **Close** to close the dialog. The new slot now appears on the **Deployment slots** page. By default, **Traffic %** is set to **0** for the new slot, with all customer traffic routed to the production slot.

Select the new deployment slot to open its resource page.

The staging slot has a management page just like any other App Service app. You can change the slot's configuration. To remind you that you're viewing the deployment slot, the app name and the slot name appear in the URL. The app type is **App Service (Slot)**. You can also see the slot as a separate app in your resource group, with the same designations.

On the slot's resource page, select the app URL. The deployment slot has its own host name and is also a live app. To limit public access to the deployment slot, see [Set up Azure App Service access restrictions](app-service-ip-restrictions).

Run the following command in a terminal:

```
az webapp deployment slot create --name <app-name> --resource-group <group-name> --slot <slot-name>

```

For more information, see [az webapp deployment slot create](/en-us/cli/azure/webapp/deployment/slot#az-webapp-deployment-slot-create).

Run the following cmdlet in an Azure PowerShell terminal:

```
New-AzWebAppSlot -ResourceGroupName <group-name> -Name <app-name> -Slot <slot-name> -AppServicePlan <plan-name>

```

For more information, see [New-AzWebAppSlot](/en-us/powershell/module/az.websites/new-azwebappslot).

The new deployment slot has no content, even if you clone the settings from a different slot. For example, you can [publish to this slot with Git](deploy-local-git). You can deploy to the slot from a different repository branch or a different repository. The article [Get a publish profile from Azure App Service](/en-us/visualstudio/azure/how-to-get-publish-profile-from-azure-app-service) can provide the required information for deploying to the slot. Visual Studio can import the profile to deploy contents to the slot.

The slot's URL has the format `http://sitename-slotname.azurewebsites.net`. To keep the URL length within necessary DNS limits, the site name can be up to 40 characters, and the slot name can be up to 19 characters. The combined length of the site name and slot name must be fewer than 59 characters.

## Understand what happens during a swap

### Swap operation steps

When you swap two slots, App Service does the following to ensure that the target slot doesn't experience downtime:

Apply the following settings from the target slot (for example, the production slot) to all instances of the source slot:

- [Slot-specific](#which-settings-are-swapped) app settings and connection strings, if applicable

- [Continuous deployment](deploy-continuous-deployment) settings, if enabled

- [App Service authentication](overview-authentication-authorization) settings, if enabled

When any of the settings is applied to the source slot, the change triggers all instances in the source slot to restart. During a [swap with preview](#Multi-Phase), this action marks the end of the first phase. The swap operation is paused. You can validate that the source slot works correctly with the target slot's settings.

Wait for every instance in the source slot to complete its restart. If any instance fails to restart, the swap operation reverts all changes to the source slot and stops the operation.

If [local cache](overview-local-cache) is enabled, trigger local cache initialization by making an HTTP request to the application root (`/`) on each instance of the source slot. Wait until each instance returns any HTTP response. Local cache initialization causes another restart on each instance.

If [auto swap](#Auto-Swap) is enabled with [custom warm-up](#Warm-up), trigger the custom [application initialization](/en-us/iis/get-started/whats-new-in-iis-8/iis-80-application-initialization) on each instance of the source slot.

If `applicationInitialization` isn't specified, trigger an HTTP request to the application root of the source slot on each instance.

If an instance returns any HTTP response, it's considered to be warmed up.

If all instances on the source slot are warmed up successfully, swap the two slots by switching their routing rules. After this step, the target slot (for example, the production slot) has the app that's previously warmed up in the source slot.

Now that the source slot has the pre-swap app that was previously in the target slot, perform the same operation by applying all settings and restarting the instances.

At any point in the swap operation, all work of initializing the swapped apps happens on the source slot. The target slot remains online while the source slot is being prepared and warmed up, regardless of whether the swap succeeds or fails. To swap a staging slot with the production slot, make sure that the production slot is always the target slot. This way, the swap operation doesn't affect your production app.

Note

Your former production instances are swapped into staging after this swap operation. Those instances are recycled in the last step of the swap process. If you have any long-running operations in your application, they're abandoned when the workers recycle. This fact also applies to function apps. Make sure that your application code is written in a fault-tolerant way.

###  Which settings are swapped

When you clone a configuration from another deployment slot, the cloned configuration is editable. Some configuration elements follow the content across a swap (they're *not slot specific*). Other configuration elements stay in the same slot after a swap (they're *slot specific*).

**When you swap slots, these settings are swapped:**

- Language framework settings (such as .NET version, Java version, PHP version, Python version, Node.js version)

- 32-bit vs 64-bit platform setting

- WebSockets enabled/disabled

- App settings (can be [configured to stick to a slot](deploy-staging-slots#make-an-app-setting-unswappable))

- Connection strings (can be [configured to stick to a slot](deploy-staging-slots#make-an-app-setting-unswappable))

- Mounted storage accounts (can be [configured to stick to a slot](deploy-staging-slots#make-an-app-setting-unswappable))

- Handler mappings

- Public certificates

- WebJobs content

- Hybrid connections

- Service endpoints

- Azure Content Delivery Network

- Path mappings

**When you swap slots, these settings aren't swapped**

- Protocol settings (Https Only, TLS version, client certificates) *

- Publishing endpoints

- Custom domain names

- Nonpublic certificates and TLS/SSL settings

- Scale settings

- WebJobs schedulers

- IP restrictions *

- Always On *

- Diagnostic log settings *

- Cross-origin resource sharing (CORS) *

- Managed identities

- Settings ending with the suffix `_EXTENSION_VERSION`

- Settings that [Service Connector](../service-connector/overview) created

- Virtual network integration

Note

Settings marked with * can be made swappable by adding the app setting `WEBSITE_OVERRIDE_PRESERVE_DEFAULT_STICKY_SLOT_SETTINGS` to every slot of the app and setting its value to `0` or `false`. This reverts to legacy swap behavior from before these settings were made slot-specific. When you use this override, these marked settings become either all swappable or all not swappable. You can't make just some settings swappable and not the others.

Certain app settings that apply to unswapped settings are also not swapped. For example, because diagnostic settings aren't swapped, related app settings like `WEBSITE_HTTPLOGGING_RETENTION_DAYS` and `DIAGNOSTICS_AZUREBLOBRETENTIONDAYS` are also not swapped, even if they don't show up as slot settings.

##  Swap deployment slots

You can swap deployment slots on your app's **Deployment slots** page and the **Overview** page.

Before you swap an app from a deployment slot into production, make sure that production is your target slot and that all settings in the source slot are configured exactly as you want to have them in production.

[Azure portal](#tabpanel_2_portal)

[Azure CLI](#tabpanel_2_cli)

[Azure PowerShell](#tabpanel_2_powershell)

Go to your app's **Deployment slots** page and select **Swap**.

The **Swap** dialog shows settings in the selected source and target slots to be changed.

Select the desired **Source** and **Target** slots. Usually, the target is the production slot. Also, select the **Source slot changes** and **Target slot changes** tabs and verify that the configuration changes are expected. When you finish, you can swap the slots immediately by selecting **Start Swap**.

To see how your target slot would run with the new settings before the swap happens, don't select **Start Swap**. Follow the instructions in [Swap with preview](#Multi-Phase) later in this article.

Select **Close** to close the dialog.

Run the following command in a terminal:

```
az webapp deployment slot swap --resource-group <group-name> --name <app-name> --slot <source-slot-name> --target-slot production

```

For more information, see [az webapp deployment slot swap](/en-us/cli/azure/webapp/deployment/slot#az-webapp-deployment-slot-swap).

Run the following cmdlet in an Azure PowerShell terminal:

```
Switch-AzWebAppSlot -SourceSlotName "<source-slot-name>" -DestinationSlotName "production" -ResourceGroupName "<group-name>" -Name "<app-name>"

```

For more information, see [Switch-AzWebAppSlot](/en-us/powershell/module/az.websites/switch-azwebappslot).

If you have any problems, see [Troubleshoot swaps](#troubleshoot-swaps) later in this article.

### Swap with preview (multiple-phase swap)

Before you swap into production as the target slot, validate that the app runs with the swapped settings. The source slot is also warmed up before the swap completion, which is desirable for mission-critical applications.

When you perform a swap with preview, App Service performs the same [swap operation](#AboutConfiguration) but pauses after the first step. You can then verify the result on the staging slot before completing the swap.

If you cancel the swap, App Service reapplies configuration elements to the source slot.

Note

You can't use swap with preview when site authentication is enabled in one of the slots.

[Azure portal](#tabpanel_3_portal)

[Azure CLI](#tabpanel_3_cli)

[Azure PowerShell](#tabpanel_3_powershell)

Foll

... [Content truncated]