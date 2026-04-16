# Configure a PHP App - Azure App Service | Microsoft Learn

> Source: https://learn.microsoft.com/en-us/azure/app-service/configure-language-php
> Cached: 2026-04-16T20:57:22.089Z

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
					
				
			
		
	
					# Configure a PHP app in Azure App Service

					
		
			 
				
					
		
			
				
			
			Feedback
		
	
				
		  
		
	 
		
			
				
					
				
				
					
						Summarize this article for me
					
				
			
			
			
		
	 
		
			
				In this article
			
		
	
					

Warning

PHP on Windows reached the [end of support](https://github.com/Azure/app-service-linux-docs/blob/master/Runtime_Support/php_support.md#end-of-life-for-php-74) in November 2022. PHP is supported only for App Service on Linux. This article is for reference only.

This guide shows you how to configure your PHP web apps, mobile back ends, and API apps in Azure App Service. The most common configuration tasks are covered.

If you're new to App Service, you should first follow the [Create a PHP web app in Azure App Service](quickstart-php) quickstart and the [Deploy a PHP, MySQL, and Redis app to Azure App Service](tutorial-php-mysql-app) tutorial.

## Show the PHP version

To show the current PHP version, run the following command. You can use the [Azure Cloud Shell](https://shell.azure.com):

```
az webapp config show --resource-group <resource-group-name> --name <app-name> --query phpVersion

```

Replace `<resource-group-name>` and `<app-name>` with names that are appropriate for your web app.

Note

To address a development slot, include the parameter `--slot` followed by the name of the slot.

To show all supported PHP versions, run the following command:

```
az webapp list-runtimes --os windows | grep PHP

```

This guide shows you how to configure your PHP web apps, mobile back ends, and API apps in Azure App Service. The most common configuration tasks are covered.

If you're new to App Service, you should first follow the [Create a PHP web app in Azure App Service](quickstart-php) quickstart and the [Deploy a PHP, MySQL, and Redis app to Azure App Service](tutorial-php-mysql-app) tutorial.

## Show the PHP version

To show the current PHP version, run the following command. You can use the [Azure Cloud Shell](https://shell.azure.com).

```
az webapp config show --resource-group <resource-group-name> --name <app-name> --query linuxFxVersion

```

Replace `<resource-group-name>` and `<app-name>` with names that are appropriate for your web app.

Note

To address a development slot, include the parameter `--slot` followed by the name of the slot.

To show all supported PHP versions, run the following command:

```
az webapp list-runtimes --os linux | grep PHP

```

## Set the PHP version

To set the PHP version to 8.1, run the following command:

```
az webapp config set --resource-group <resource-group-name> --name <app-name> --php-version 8.1

```

To set the PHP version to 8.1, run the following command:

```
az webapp config set --resource-group <resource-group-name> --name <app-name> --linux-fx-version "PHP|8.1"

```

## What happens to outdated runtimes in App Service?

Outdated runtimes are deprecated by the maintaining organization or have significant vulnerabilities. Accordingly, they're removed from the create and configure pages in the portal. When an outdated runtime is hidden from the portal, any app that's still using that runtime continues to run.

If you want to create an app with an outdated runtime version that's no longer shown on the portal, use the Azure CLI, an ARM template, or Bicep. These deployment alternatives let you create deprecated runtimes that are removed from the portal but are still being supported.

If a runtime is fully removed from the App Service platform, your Azure subscription owner receives an email notice before the removal.

## Run Composer

If you want App Service to run [Composer](https://getcomposer.org/) at deployment time, the easiest way is to include Composer in your repository.

From a local terminal window, change the directory to your repository root. Then, follow the instructions at [Download Composer](https://getcomposer.org/download/) to download `composer.phar` to the directory root.

Run the following commands. To run them, you need [npm](https://www.npmjs.com/get-npm) installed.

```
npm install kuduscript -g
kuduscript --node --scriptType bash --suppressPrompt

```

Your repository root now has two new files: `.deployment` and `deploy.sh`.

Open `deploy.sh` and find the `Deployment` section, which looks like this example:

```
##################################################################################################################################
# Deployment
# ----------

```

*At the end* of the `Deployment` section, add the code section that you need to run the required tool:

```
# 4. Use composer
echo "$DEPLOYMENT_TARGET"
if [ -e "$DEPLOYMENT_TARGET/composer.json" ]; then
  echo "Found composer.json"
  pushd "$DEPLOYMENT_TARGET"
  php composer.phar install $COMPOSER_ARGS
  exitWithMessageOnError "Composer install failed"
  popd
fi

```

Commit all your changes and deploy your code by using Git or by using ZIP deploy with [build automation enabled](deploy-zip#enable-build-automation-for-zip-deploy). Composer should now be running as part of deployment automation.

## Run Bower, Gulp, or Grunt

If you want App Service to run popular automation tools at deployment time, such as Bower, Gulp, or Grunt, you need to supply a [custom deployment script](https://github.com/projectkudu/kudu/wiki/Custom-Deployment-Script). App Service runs this script when you deploy by using Git or by using ZIP deploy with [build automation enabled](deploy-zip#enable-build-automation-for-zip-deploy).

To enable your repository to run these tools, you need to add them to the dependencies in `package.json`. For example:

```
"dependencies": {
  "bower": "^1.7.9",
  "grunt": "^1.0.1",
  "gulp": "^3.9.1",
  ...
}

```

From a local terminal window, change the directory to your repository root and run the following commands. To run them, you need [npm](https://www.npmjs.com/get-npm) installed.

```
npm install kuduscript -g
kuduscript --node --scriptType bash --suppressPrompt

```

Your repository root now has two new files: `.deployment` and `deploy.sh`.

Open `deploy.sh` and find the `Deployment` section, which looks like this example:

```
##################################################################################################################################
# Deployment
# ----------

```

This section ends with running `npm install --production`. *At the end* of the `Deployment` section, add the code section that you need to run the required tool:

- [Bower](#bower)

- [Gulp](#gulp)

- [Grunt](#grunt)

See an [example in the MEAN.js sample](https://github.com/Azure-Samples/meanjs/blob/master/deploy.sh#L112-L135), where the deployment script also runs a custom `npm install` command.

### Bower

This snippet runs `bower install`:

```
if [ -e "$DEPLOYMENT_TARGET/bower.json" ]; then
  cd "$DEPLOYMENT_TARGET"
  eval ./node_modules/.bin/bower install
  exitWithMessageOnError "bower failed"
  cd - > /dev/null
fi

```

### Gulp

This snippet runs `gulp imagemin`:

```
if [ -e "$DEPLOYMENT_TARGET/gulpfile.js" ]; then
  cd "$DEPLOYMENT_TARGET"
  eval ./node_modules/.bin/gulp imagemin
  exitWithMessageOnError "gulp failed"
  cd - > /dev/null
fi

```

### Grunt

This snippet runs `grunt`:

```
if [ -e "$DEPLOYMENT_TARGET/Gruntfile.js" ]; then
  cd "$DEPLOYMENT_TARGET"
  eval ./node_modules/.bin/grunt
  exitWithMessageOnError "Grunt failed"
  cd - > /dev/null
fi

```

## Customize build automation

If you deploy your app by using Git or by using ZIP packages [with build automation enabled](deploy-zip#enable-build-automation-for-zip-deploy), the build automation in App Service steps through the following sequence:

- Run a custom script if `PRE_BUILD_SCRIPT_PATH` specifies it.

- Run `php composer.phar install`.

- Run a custom script if `POST_BUILD_SCRIPT_PATH` specifies it.

`PRE_BUILD_COMMAND` and `POST_BUILD_COMMAND` are environment variables that are empty by default. To run prebuild commands, define `PRE_BUILD_COMMAND`. To run post-build commands, define `POST_BUILD_COMMAND`.

The following example specifies the two variables to a series of commands, separated by commas:

```
az webapp config appsettings set --name <app-name> --resource-group <resource-group-name> --settings PRE_BUILD_COMMAND="echo foo, scripts/prebuild.sh"
az webapp config appsettings set --name <app-name> --resource-group <resource-group-name> --settings POST_BUILD_COMMAND="echo foo, scripts/postbuild.sh"

```

For other environment variables to customize build automation, see [Oryx configuration](https://github.com/microsoft/Oryx/blob/master/doc/configuration.md).

To learn how App Service runs and builds PHP apps in Linux, see the [Oryx documentation on how PHP apps are detected and built](https://github.com/microsoft/Oryx/blob/master/doc/runtimes/php.md).

## Customize startup

You can run a custom command at the container startup time. Run the following command:

```
az webapp config set --resource-group <resource-group-name> --name <app-name> --startup-file "<custom-command>"

```

## Access environment variables

In App Service, you can [set app settings](configure-common#configure-app-settings) outside your app code. You can then access those settings by using the standard [`getenv()`](https://secure.php.net/manual/function.getenv.php) pattern. For example, to access an app setting called `DB_HOST`, use the following code:

```
getenv("DB_HOST")

```

## Change the site root

The web framework of your choice might use a subdirectory as the site root. For example, [Laravel](https://laravel.com/) uses the `public/` subdirectory as the site root.

To customize the site root, set the virtual application path for the app by using the [`az resource update`](/en-us/cli/azure/resource#az-resource-update) command. The following example sets the site root to the `public/` subdirectory in your repository:

```
az resource update --name web --resource-group <group-name> --namespace Microsoft.Web --resource-type config --parent sites/<app-name> --set properties.virtualApplications[0].physicalPath="site\wwwroot\public" --api-version 2015-06-01

```

By default, Azure App Service points the root virtual application path (`/`) to the root directory of the deployed application files (`sites\wwwroot`).

The web framework of your choice might use a subdirectory as the site root. For example, [Laravel](https://laravel.com/) uses the `public/` subdirectory as the site root.

The default PHP image for App Service uses NGINX, and you change the site root by [configuring the NGINX server with the `root` directive](https://docs.nginx.com/nginx/admin-guide/web-server/serving-static-content/). This [example configuration file](https://github.com/Azure-Samples/laravel-tasks/blob/main/default) contains the following snippet that changes the `root` directive:

```
server {
    #proxy_cache cache;
    #proxy_cache_valid 200 1s;
    listen 8080;
    listen [::]:8080;
    root /home/site/wwwroot/public; # Changed for Laravel

    location / {            
        index  index.php index.html index.htm hostingstart.html;
        try_files $uri $uri/ /index.php?$args; # Changed for Laravel
    }
    ...

```

The default container uses the configuration file at `/etc/nginx/sites-available/default`. Any edit you make to this file is erased when the app restarts. To make a change that's effective across app restarts, [add a custom startup command](#customize-startup) like this example:

```
cp /home/site/wwwroot/default /etc/nginx/sites-available/default && service nginx reload

```

This command replaces the default NGINX configuration file with a file named `default` in your repository root, and it restarts NGINX.

## Detect an HTTPS session

In App Service, [TLS/SSL termination](https://wikipedia.org/wiki/TLS_termination_proxy) happens at the network load balancers, so all HTTPS requests reach your app as unencrypted HTTP requests. If your app logic needs to check whether the user requests are encrypted, inspect the `X-Forwarded-Proto` header:

```
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
// Do something when HTTPS is used
}

```

Popular web frameworks let you access the `X-Forwarded-*` information in your standard app pattern. In [CodeIgniter](https://codeigniter.com/), the [is_https()](https://github.com/bcit-ci/CodeIgniter/blob/master/system/core/Common.php#L338-L365) function checks the value of `X_FORWARDED_PROTO` by default.

## Customize php.ini settings

If you need to make changes to your PHP installation, you can change any of the [php.ini directives](https://www.php.net/manual/ini.list.php) by using the following steps.

Note

The best way to see the PHP version and the current `php.ini` configuration is to call [`phpinfo()`](https://php.net/manual/function.phpinfo.php) in your app.

### Customize non-PHP_INI_SYSTEM directives

To customize `PHP_INI_USER`, `PHP_INI_PERDIR`, and `PHP_INI_ALL` directives, add a `.user.ini` file to the root directory of your app.

Add configuration settings to the `.user.ini` file by using the same syntax that you would use in a `php.ini` file. For example, if you wanted to turn on the `display_errors` setting and set the `upload_max_filesize` setting to `10M`, your `.user.ini` file would contain this text:

```
 ; Example Settings
 display_errors=On
 upload_max_filesize=10M

 ; Write errors to d:\home\LogFiles\php_errors.log
 ; log_errors=On

```

Redeploy your app with the changes and restart it.

As an alternative to using a `.user.ini` file, you can use [`ini_set()`](https://www.php.net/manual/function.ini-set.php) in your app to customize these non-`PHP_INI_SYSTEM` directives.

This section applies to directives with a change mode of `PHP_INI_USER`, `PHP_INI_PERDIR`, or `PHP_INI_ALL`. For directives that require change mode `PHP_INI_SYSTEM

... [Content truncated]