# calltracking.github.io/api_users_guide.md at master · calltracking/calltracking.github.io · GitHub

> Source: https://github.com/calltracking/calltracking.github.io/blob/master/api_users_guide.md
> Cached: 2026-04-14T16:28:03.054Z

---

# CTM API Getting Started

[](#ctm-api-getting-started)
The goal of this example is to show you how to use the API to setup more advanced routing. Using this example, you can direct callers to a voice menu during business hours or to a different voice menu to leave a message (voicemail) after hours. The business hours voice menu will be a simple example of "Press 1 for sales, 2 for support, or 3 to leave a message."

Although you can use the API to update routes, schedules, and voice menus, we will be creating the routing objects in a logical order based on dependenices.

## API Keys

[](#api-keys)
For the following examples, you will need either agency or account API keys and secrets. You can obtain these by logging into your account and going to either Settings > Agency Settings or Settings > Account Settings. This guide will create and use a sub-account, so it is recommended to use agency keys as you follow the examples.

> 
NOTE: Agency keys can be used across all accounts. Account keys are specific to an account and will be limited to making API requests against that account.

You can put your keys in your environment and then you should be able to copy and paste the examples.

export CTM_API_HOST='api.calltrackingmetrics.com'
export ACCESS_KEY='...'
export SECRET_KEY='...'
## Basic Auth

[](#basic-auth)
For curl, you can use -u ${ACCESS_KEY}:${SECRET_KEY} or an authorization header. This documentation will use -u for simplicity.

## Headers

[](#headers)
You want to send the `application/json` content type header with each request.

--header 'content-type: application/json'
## Sub-Accounts

[](#sub-accounts)
Before purchasing phone numbers, you will want to create a sub-account. This step requires agency API keys.

curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data   '{"account": { "name": "My First Account", "timezone_hint":"America/Los_Angeles"}, "billing_type":"existing"}'
If successful, you will now have an account id for the new sub-account (in this case named "My First Account.") The response will be similar to this; specifically, the account id will differ.

{
  "status": "success",
  "id": 18647,
  "name": "My First Account"
}
If you export the account id, you can use it to copy and paste the following examples.

export ACCOUNT_ID=id_from_response
## Purchase Phone Numbers

[](#purchase-phone-numbers)
Purchasing phone numbers is a two-step process. First, you search for numbers. Second, you purchase the desired numbers. There are several ways to search for phone numbers (which will be used as tracking numbers). You can search by area code, address (city, state, and/or zip code), and you can include a pattern as well (for example, the NXX or prefix of a phone number.)

> 
NOTE: Searching for US/CA numbers has more options than international searching.

### Area Code Search

[](#area-code-search)
To perform an area code search, you need to specify the `country`, set `searchby` to `area`, and set `areacode` to the desired area code.

curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/numbers/search.json?country=US&searchby=area&areacode=443' \
  --header 'content-type: application/json'
If you want to add a pattern (for example, in order to search for a prefix), you can add a `pattern` to search for.

curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/numbers/search.json?country=US&searchby=area&areacode=443&pattern=341' \
  --header 'content-type: application/json'
### Zip Code Search

[](#zip-code-search)
To perform a zip code search, set `searchby` to `address` and set `address` to the desired zip code. The list of results may include nearby zip codes if there are not enough numbers available in the specified area.

curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/numbers/search.json?country=US&searchby=address&address=21401' \
  --header 'content-type: application/json'
### Address Search

[](#address-search)
You can also specify a street address or a city and state as the `address`.

curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/numbers/search.json?country=US&searchby=address&address=beverly%20hills%2C%20ca' \
  --header 'content-type: application/json'
> 
NOTE: Make sure to properly encode your parameters. For example, if you do not use %20 or a `+` as the space character in the above url, the search will fail.

### Search Results

[](#search-results)
No matter the type of search, if numbers are found that match, you will receive a response similar to the following:

{
  "numbers": [
    {
      "source": 1,
      "friendly_name": "(424) 332-5093",
      "latitude": "34.073600",
      "longitude": "-118.400400",
      "lata": "730",
      "region": "CA",
      "postal_code": "90209",
      "iso_country": "US",
      "capabilities": {
        "voice": true,
        "SMS": true,
        "MMS": true
      },
      "number": "+14243325093",
      "phone_number": "+14243325093",
      "number_type": "local",
      "addr_required": "none",
      "ratecenter": "Los Angeles",
      "distance": 0
    },
    {
      "source": 1,
      "friendly_name": "(424) 332-5173",
      "latitude": "34.073600",
      "longitude": "-118.400400",
      "lata": "730",
      "region": "CA",
      "postal_code": "90209",
      "iso_country": "US",
      "capabilities": {
        "voice": true,
        "SMS": true,
        "MMS": true
      },
      "number": "+14243325173",
      "phone_number": "+14243325173",
      "number_type": "local",
      "addr_required": "none",
      "ratecenter": "Los Angeles",
      "distance": 0
    },
    ...
You can use either the `number` or `phone_number` field from the desired tracking number to make the purchase.

If no numbers are found, you will receive an empty list of numbers. However, there will be suggestions for overlays if they are available/known. The following is the result of a search for area code 212 (New York, NY):

{
  "numbers": [],
  "country": "US",
  "searchby": "area",
  "error": [],
  "format_style": "v2",
  "include_distance": false,
  "contains": "212",
  "areacode": "212",
  "status": "success",
  "overlays": [
    "646",
    "917"
  ]
}
If desired, a similar search can be repeated for area code 646 or 917.

### Purchasing Desired Numbers

[](#purchasing-desired-numbers)
Once you have a phone number you would like to purchase, you just need to POST a request to the numbers endpoint.

curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/numbers \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"phone_number": "+14105551212","test": true}'
> 
NOTE: If you set `test`, the number will be added to your account. However, the number will only be a "test" number that can be used in other API calls. You will not be charged for the simulated purchase, and the number will not be able to send or receive phone calls or SMS. To purchase a live number, omit the `test` field.

If the purchase is successful (i.e. the number was still available and the account had sufficient funds), you will receive a reponse similar to the following:

{
  "status": "success",
  "number": {
    "id": "TPNC3C4B23C348AEC2EE54EFD301979CD2EFB6E4F483E702C7FE233AC89EB9A978D",
    "filter_id": 67333,
    "name": null,
    "active": true,
    "status": "active",
    "account_id": "18600",
    "source": null,
    "number": "+14105551212",
    "call_setting": null,
    "country_code": "1",
    "next_billing_date": null,
    "purchased_time": "2016-08-09T16:09:32Z",
    "route_to": {
      "type": "receiving_number",
      "multi": true,
      "mode": "simultaneous",
      "dial": []
    },
    "split": [
      "1",
      "410",
      "555",
      "1212"
    ],
    "stats": {
      "since": 1470758972.4781718,
      "renewal_costs": "1.5",
      "calls": 0,
      "minutes": "0",
      "minute_costs": "0.0"
    },
    "formatted": "(410) 555-1212",
    "routing": "simultaneous",
    "url": "https://api.calltrackingmetrics.com/api/v1/accounts/18600/numbers/TPNC3C4B23C348AEC2EE54EFD301979CD2EFB6E4F483E702C7FE233AC89EB9A978D.json"
  }
}
Within the returned number object, we will be using the id (TPN...) for the following requests.

export TPN_ID=tpn_id_from_response
## Setup Routing for Phone Numbers

[](#setup-routing-for-phone-numbers)
In order to forward the newly purchased tracking number to a receiving number, we will update the tracking number using a PUT request.

curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/numbers/${TPN_ID}/update_number \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"dial_route": "number","numbers": ["3105552222","4165553333"],"country_codes": ["US","CA"]}'
For the body of the request, you send an array of numbers without country codes and an array of country codes (ISO-2 alpha codes) that align with the numbers.

{
    "dial_route": "number",
    "numbers": ["3105552222","4165553333"],
    "country_codes": ["US","CA"]
}
In the above request, we specified one US number and one CA number.

The response will be something like the following:

{
  "status": "success",
  "number": {
    "id": "TPNC3C4B23C348AEC2EE54EFD301979CD2EFB6E4F483E702C7FE233AC89EB9A978D",
    "filter_id": 67333,
    "name": null,
    "active": true,
    "status": "active",
    "account_id": "18600",
    "source": null,
    "number": "+14102051558",
    "call_setting": {
      "id": "NCF5B75DB2863BDEA0F8D82E7B25F78F3AEA94ED6FAB1EDB7AF11E25FEA2FCEE80F",
      "url": "https://api.calltrackingmetrics.com/api/v1/accounts/18600/call_settings/NCF5B75DB2863BDEA0F8D82E7B25F78F3AEA94ED6FAB1EDB7AF11E25FEA2FCEE80F",
      "name": "Account Level"
    },
    "country_code": "1",
    "next_billing_date": null,
    "purchased_time": "2016-08-09T16:09:32Z",
    "route_to": {
      "type": "receiving_number",
      "multi": true,
      "mode": "simultaneous",
      "dial": [
        {
          "id": "RPN34D8AC3F61E8848FEA641CEF711011AADF51559F51DBA0B436FA9716588BDE93",
          "filter_id": 40184,
          "name": null,
          "number": "+13105552222",
          "display_number": "(310) 555-2222",
          "account_id": 18600,
          "country_code": "1",
          "split": [
            "1",
            "310",
            "555",
            "2222"
          ],
          "formatted": "(310)-555-2222",
          "url": "https://api.calltrackingmetrics.com/api/v1/accounts/18600/receiving_numbers/RPN34D8AC3F61E8848FEA641CEF711011AADF51559F51DBA0B436FA9716588BDE93"
        },
        {
          "id": "RPN34D8AC3F61E8848FEA641CEF711011AADF51559F51DBA0B4896CD430F1FC1D84",
          "filter_id": 40185,
          "name": null,
          "number": "+14165553333",
          "display_number": "(416) 555-3333",
          "account_id": 18600,
          "country_code": "1",
          "split": [
            "1",
            "416",
            "555",
            "3333"
          ],
          "formatted": "(416)-555-3333",
          "url": "https://api.calltrackingmetrics.com/api/v1/accounts/18600/receiving_numbers/RPN34D8AC3F61E8848FEA641CEF711011AADF51559F51DBA0B4896CD430F1FC1D84"
        }
      ]
    },
    "split": [
      "1",
      "410",
      "205",
      "1558"
    ],
    "stats": {
      "since": 1470758972.4781718,
      "renewal_costs": "1.5",
      "calls": 0,
      "minutes": "0",
      "minute_costs": "0.0"
    },
    "formatted": "(410) 205-1558 (x67333)",
    "routing": "simultaneous",
    "url": "https://api.calltrackingmetrics.com/api/v1/accounts/18600/numbers/TPNC3C4B23C348AEC2EE54EFD301979CD2EFB6E4F483E702C7FE233AC89EB9A978D.json"
  }
}
Within the returned number object, we will be using each id (RPN...) for the following requests.

export RPN_ID_US=first_rpn_id_from_response
export RPN_ID_CA=second_rpn_id_from_response
### Tracking Source

[](#tracking-source)
We will create a Google AdWords tracking source and add a tracking number to the new tracking source.

curl --request POST \
--url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/sources \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"name": "Google Adwords","online": "1","referring_url": "", "landing_url": "gclid=.+", "position": "1"}'
The output will look something like the following:

{
  "status": "success",
  "source": {
    "id": "TSOF6FC2D2594D22C52C7F22FA76C7AEAFA11100A5A494E400EAD97ABA6AD0276CD",
    "name": "Google Adwords",
    "account_id": 18649,
    "referring_url": "",
    "not_referrer_url": null,
    "landing_url": "gclid=.+",
    "not_landing_url": null,
    "position": 1,
    "online": true,
    "crm_tag": "",
    "geo_mode": "off",
    "geo_sources": "https://${CTM_API_HOST}/api/v1/accounts/18649/sources/TSOF6FC2D2594D22C52C7F22FA76C7AEAFA11100A5A494E400EAD97ABA6AD0276CD/geo_sources.json",
    "url": "https://api.calltrackingmetrics.com/api/v1/accounts/18649/sources/TSOF6FC2D2594D22C52C7F22FA76C7AEAFA11100A5A494E400EAD97ABA6AD0276CD.json"
  }
}
Capture the TSO id from the response for the next request.

export TSO_ID=tso_id_from_reponse
curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/sources/${TSO_ID}/numbers/${TPN_ID}/add \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json'
The response will be similar to the following:

{
  "status": "success",
  "source": {
    "id": "TSOF6FC2D2594D22C52C7F22FA76C7AEAFA11100A5A494E400EAD97ABA6AD0276CD",
    "name": "Google Adwords",
    "account_id": 18649,
    "referring_url": "",
    "not_referrer_url": null,
    "landing_url": "gclid=.+",
    "not_landing_url": null,
    "position": 1,
    "online": true,
    "crm_tag": "",
    "geo_mode": "off",
    "geo_sources": "https://${CTM_API_HOST}/api/v1/accounts/18649/sources/TSOF6FC2D2594D22C52C7F22FA76C7AEAFA11100A5A494E400EAD97ABA6AD0276CD/geo_sources.json",
    "url": "https://api.calltrackingmetrics.com/api/v1/accounts/18649/sources/TSOF6FC2D2594D22C52C7F22FA76C7AEAFA11100A5A494E400EAD97ABA6AD0276CD.json"
  }
}
The tracking number is now assigned to the Google Adwords tracking source. You can see the tracking number settings which includes the tracking source information using the details for tracking number request:

curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/numbers/${TPN_ID} \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json'
In the response, you can see the source:

{
  "id": "TPNC3C4B23C348AEC2EE54EFD301979CD2EDEC86735B9EBFF398FA80FD85CAAB7B3",
  "filter_id": 67334,
  "name": null,
  "active": true,
  "status": "active",
  "account_id": "18649",
  "source": {
    "id": "TSOF6FC2D2594D22C52C7F22FA76C7AEAFA11100A5A494E400EAD97ABA6AD0276CD",
    "name"

... [Content truncated]