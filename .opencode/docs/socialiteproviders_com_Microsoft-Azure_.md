# Socialite Providers

> Source: https://socialiteproviders.com/Microsoft-Azure/
> Cached: 2026-04-16T20:57:40.153Z

---

Maintained By  GitHub icon
          atymic
         [GitHub icon Github Repo](https://github.com/SocialiteProviders/Microsoft-Azure) | 
        Edit
     # [#](#azure) Azure

 ```
composer require socialiteproviders/microsoft-azure

```

## [#](#installation-basic-usage) Installation & Basic Usage

 Please see the [Base Installation Guide  (opens new window)](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

 ### [#](#add-configuration-to-config-services-php) Add configuration to `config/services.php`

 ```
'azure' => [    
  'client_id' => env('AZURE_CLIENT_ID'),
  'client_secret' => env('AZURE_CLIENT_SECRET'),
  'redirect' => env('AZURE_REDIRECT_URI'),
  'tenant' => env('AZURE_TENANT_ID'),
  'proxy' => env('PROXY')  // optionally
],

```

### [#](#add-provider-event-listener) Add provider event listener

 #### [#](#laravel-11) Laravel 11+

 In Laravel 11, the default `EventServiceProvider` provider was removed. Instead, add the listener using the `listen` method on the `Event` facade, in your `AppServiceProvider` `boot` method.

 
- Note: You do not need to add anything for the built-in socialite providers unless you override them with your own providers.

 ```
Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
    $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
});

```

Laravel 10 or below

Configure the package's listener to listen for `SocialiteWasCalled` events.
Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide  (opens new window)](https://socialiteproviders.com/usage/) for detailed instructions.

 ```
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \SocialiteProviders\Azure\AzureExtendSocialite::class.'@handle',
    ],
];

```

 ### [#](#usage) Usage

 You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

 ```
return Socialite::driver('azure')->redirect();

```

To logout of your app and Azure:

 ```
public function logout(Request $request) 
{
     Auth::guard()->logout();
     $request->session()->flush();
     $azureLogoutUrl = Socialite::driver('azure')->getLogoutUrl(route('login'));
     return redirect($azureLogoutUrl);
}

```

### [#](#returned-user-fields) Returned User fields

 
- `id`
 - `name`
 - `email`

 ## [#](#advanced-usage) Advanced usage

 In order to have multiple / different Active directories on Azure (i.e. multiple tenants) The same driver can be used but with a different config:

 ```
/**
 * Returns a custom config for this specific Azure AD connection / directory
 * @return \SocialiteProviders\Manager\Config
 */
function getConfig(): \SocialiteProviders\Manager\Config
{
  return new \SocialiteProviders\Manager\Config(
    env('AD_CLIENT_ID', 'some-client-id'), // a different clientID for this separate Azure directory
    env('AD_CLIENT_SECRET'), // a different secret for this separate Azure directory
    url(env('AD_REDIRECT_PATH', '/azuread/callback')), // the redirect path i.e. a different callback to the other azureAD callbacks
    ['tenant' => env('AD_TENANT_ID', 'common')], // this could be something special if need be, but can also be left out entirely
  );
}
//....//
Socialite::driver('azure')
    ->setConfig(getConfig())
    ->redirect();

```

This also applies to the callback for getting the user credentials that one has to remember to inject the `->setConfig($config)`-method i.e.:

 ```
$socialUser = Socialite::driver('azure')
    ->setConfig(getConfig())
    ->user();

```

If the application that you are authenticating against is anything other single tenant, use the following values in place of the tenant_id:

 
- Multitenant applications: "organizations"
 - Multitenant and personal accounts: "common"
 - Personal accounts only: "consumers"

   
      ←
      
        Microsoft
       
        Naver
      
      →