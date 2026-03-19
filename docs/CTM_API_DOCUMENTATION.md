# CallTrackingMetrics (CTM) API Documentation

## Overview

CallTrackingMetrics (CTM) provides a comprehensive API for managing call tracking, routing, voice menus, and analytics. The API is designed for agencies and businesses that need advanced call tracking capabilities.

**API Base URL:** `https://api.calltrackingmetrics.com`

**Documentation:** https://postman.calltrackingmetrics.com/

**Postman Collection:** https://documenter.getpostman.com/view/213868/ctm-api/2FxGgg

---

## Table of Contents

1. [API Keys & Authentication](#api-keys--authentication)
2. [Base URL & Headers](#base-url--headers)
3. [Sub-Accounts](#sub-accounts)
4. [Phone Numbers](#phone-numbers)
5. [Receiving Numbers](#receiving-numbers)
6. [Tracking Sources](#tracking-sources)
7. [Schedules](#schedules)
8. [Voice Menus](#voice-menus)
9. [Call Routes](#call-routes)
10. [Calls API](#calls-api)
11. [Python Integration](#python-integration)
12. [Related Resources](#related-resources)

---

## API Keys & Authentication

### Obtaining API Keys

You can obtain API keys from:
- **Agency Keys:** Settings > Agency Settings (can access all sub-accounts)
- **Account Keys:** Settings > Account Settings (limited to specific account)

### Environment Setup

```bash
export CTM_API_HOST='api.calltrackingmetrics.com'
export ACCESS_KEY='your_access_key'
export SECRET_KEY='your_secret_key'
```

### Authentication Method

The CTM API uses **Basic Authentication**.

```bash
# Using curl with -u flag
curl -u ${ACCESS_KEY}:${SECRET_KEY}

# Or Authorization header
Authorization: Basic base64(ACCESS_KEY:SECRET_KEY)
```

---

## Base URL & Headers

### API Base URL

```
https://api.calltrackingmetrics.com/api/v1
```

### Required Headers

```bash
--header 'content-type: application/json'
```

---

## Sub-Accounts

### Create Sub-Account

```bash
curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"account": { "name": "My First Account", "timezone_hint":"America/Los_Angeles"}, "billing_type":"existing"}'
```

### Response

```json
{
  "status": "success",
  "id": 18647,
  "name": "My First Account"
}
```

### List Accounts

```bash
curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts \
  -u ${ACCESS_KEY}:${SECRET_KEY}
```

---

## Phone Numbers

### Search for Numbers

Numbers can be searched by area code, address, zip code, or pattern.

#### Search by Area Code

```bash
curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/numbers/search.json?country=US&searchby=area&areacode=443' \
  --header 'content-type: application/json'
```

#### Search by Area Code with Pattern

```bash
curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/numbers/search.json?country=US&searchby=area&areacode=443&pattern=341' \
  --header 'content-type: application/json'
```

#### Search by Zip Code

```bash
curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/numbers/search.json?country=US&searchby=address&address=21401' \
  --header 'content-type: application/json'
```

#### Search by Address (City, State)

```bash
curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/numbers/search.json?country=US&searchby=address&address=beverly%20hills%2C%20ca' \
  --header 'content-type: application/json'
```

### Search Response Format

```json
{
  "numbers": [
    {
      "source": 1,
      "friendly_name": "(424) 332-5093",
      "latitude": "34.073600",
      "longitude": "-118.400400",
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
      "ratecenter": "Los Angeles",
      "distance": 0
    }
  ]
}
```

### Purchase Number

```bash
curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/numbers \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"phone_number": "+14105551212","test": true}'
```

> **Note:** Set `"test": true` for test numbers (no charge). Omit for live numbers.

### Get Number Details

```bash
curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/numbers/${TPN_ID} \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json'
```

### Update Number Route

```bash
curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/numbers/${TPN_ID}/update_number \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"dial_route": "number","numbers": ["3105552222","4165553333"],"country_codes": ["US","CA"]}'
```

---

## Receiving Numbers

### List Receiving Numbers

```bash
curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/receiving_numbers \
  -u ${ACCESS_KEY}:${SECRET_KEY}
```

### Create Receiving Number

```bash
curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/receiving_numbers \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"number": "+13105552222", "name": "Sales Team"}'
```

### Update Receiving Number

```bash
curl --request PUT \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/receiving_numbers/${RPN_ID} \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"name": "Updated Name"}'
```

---

## Tracking Sources

### Create Tracking Source

```bash
curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/sources \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"name": "Google AdWords","online": "1","referring_url": "", "landing_url": "gclid=.+", "position": "1"}'
```

### Response

```json
{
  "status": "success",
  "source": {
    "id": "TSOF6FC2D2594D22C52C7F22FA76C7AEAFA11100A5A494E400EAD97ABA6AD0276CD",
    "name": "Google AdWords",
    "account_id": 18649,
    "referring_url": "",
    "landing_url": "gclid=.+",
    "position": 1,
    "online": true,
    "geo_mode": "off"
  }
}
```

### Assign Number to Source

```bash
curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/sources/${TSO_ID}/numbers/${TPN_ID}/add \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json'
```

### List Sources

```bash
curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/sources \
  -u ${ACCESS_KEY}:${SECRET_KEY}
```

---

## Schedules

### Create Schedule

```bash
curl --request POST \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/schedules \
  --header 'content-type: application/json' \
  --data '{
    "schedule": {
      "name": "Business Hours",
      "times": [
        {"start_time": "540", "days": {"sun": false, "mon": true, "tue": true, "wed": true, "thu": true, "fri": true, "sat": false}, "end_time": "720", "position": "0"},
        {"start_time": "780", "days": {"sun": false, "mon": true, "tue": true, "wed": true, "thu": true, "fri": true, "sat": false}, "end_time": "1020", "position": "1"}
      ],
      "timezone": "Pacific Time (US & Canada)"
    }
  }'
```

> **Note:** `start_time` and `end_time` are in minutes past midnight. 540 = 9:00 AM, 720 = 12:00 PM, 780 = 1:00 PM, 1020 = 5:00 PM

### List Schedules

```bash
curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/schedules \
  -u ${ACCESS_KEY}:${SECRET_KEY}
```

### Update Schedule

```bash
curl --request PUT \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/schedules/${SCH_ID} \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"schedule": {"name": "Updated Schedule Name"}}'
```

---

## Voice Menus

### Create Voice Menu

```bash
curl --request POST \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/voice_menus \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{
    "voice_menu": {
      "name": "Main Menu",
      "play_message": "say:alice:en-US:Press 1 for sales, 2 for support",
      "input_maxkeys": "1",
      "input_timeout": "7",
      "prompt_retries": "5",
      "items": [
        {"keypress": "1", "voice_action_type": "dial", "dial_number_id": "${RPN_ID}"},
        {"keypress": "2", "voice_action_type": "menu", "next_voice_menu_id": "${VOICEMAIL_VOM_ID}"}
      ]
    }
  }'
```

### Voice Menu Action Types

| Action Type | Description |
|------------|-------------|
| `dial` | Transfer to a receiving number |
| `menu` | Go to another voice menu |
| `message` | Play a message/voicemail |
| `hangup` | End the call |

### Say Message Format

```
say:{{voice}}:{{language}}:{{message}}
```

**Voices:** `man`, `woman`, `alice`

**Languages (man/woman):** `en`, `en-gb`, `es`, `fr`, `de`

**Languages (alice):** `en-US`, `en-GB`, `es-ES`, `fr-FR`, `de-DE`, `ja-JP`, etc.

### Play Audio Format

```
play:{{url}}
```

Where URL points to MP3, WAV, AIFF, GSM, or μ-law audio file (< 7MB).

### List Voice Menus

```bash
curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/voice_menus \
  -u ${ACCESS_KEY}:${SECRET_KEY}
```

### Update Voice Menu

```bash
curl --request PUT \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/voice_menus/${VOM_ID} \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"voice_menu": {"name": "Updated Menu Name"}}'
```

---

## Call Routes

### Set Route to Voice Menu

```bash
curl --request PUT \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/numbers/${TPN_ID}/dial_routes \
  -u ${ACCESS_KEY}:${SECRET_KEY} \
  --header 'content-type: application/json' \
  --data '{"virtual_phone_number": {"dial_route": "voice_menu", "voice_menu_id": "${MAIN_VOM_ID}"}}'
```

### Route Types

| Route Type | Description |
|------------|-------------|
| `number` | Route to specific receiving numbers |
| `voice_menu` | Route to IVR voice menu |
| `schedule` | Route based on schedule |
| `wholesale` | Route to wholesale carrier |

---

## Calls API

### List Calls

```bash
curl --request GET \
  --url 'https://'${CTM_API_HOST}'/api/v1/accounts/'${ACCOUNT_ID}'/calls.json?limit=100&hours=24' \
  -u ${ACCESS_KEY}:${SECRET_KEY}
```

### Query Parameters

| Parameter | Description |
|-----------|-------------|
| `limit` | Number of calls to return (default: 100) |
| `hours` | Look back period in hours (default: 24) |
| `status` | Filter by status (completed, missed, voicemail) |
| `source_id` | Filter by tracking source |

### Get Call Details

```bash
curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/calls/${CALL_ID} \
  -u ${ACCESS_KEY}:${SECRET_KEY}
```

### Get Call Transcript

```bash
curl --request GET \
  --url https://${CTM_API_HOST}/api/v1/accounts/${ACCOUNT_ID}/calls/${CALL_ID}/transcript \
  -u ${ACCESS_KEY}:${SECRET_KEY}
```

---

## Python Integration

### Example Application

GitHub: https://github.com/calltracking/ctm-python3-example

### Installation

```bash
git clone https://github.com/calltracking/ctm-python3-example.git
cd ctm-python3-example
python3 -m virtualenv env
source env/bin/activate
pip3 install -r requirements.txt
```

### Configuration

Create a `.env` file:

```bash
export CTM_ENV=development
export CTM_TOKEN=your_token
export CTM_SECRET=your_secret
export CTM_HOST=api.calltrackingmetrics.com
```

### Running

```bash
python app.py
```

Access at: http://127.0.0.1:5000

### Python API Client Usage

```python
import requests

API_HOST = 'api.calltrackingmetrics.com'
TOKEN = 'your_token'
SECRET = 'your_secret'

# List accounts
response = requests.get(
    f'https://{API_HOST}/api/v1/accounts',
    auth=(TOKEN, SECRET)
)
print(response.json())

# Search numbers
response = requests.get(
    f'https://{API_HOST}/api/v1/accounts/{account_id}/numbers/search.json',
    params={'country': 'US', 'searchby': 'area', 'areacode': '443'},
    auth=(TOKEN, SECRET)
)
print(response.json())
```

---

## Related Resources

### Official Documentation

| Resource | URL |
|----------|-----|
| API Documentation | https://postman.calltrackingmetrics.com/ |
| Postman Collection | https://documenter.getpostman.com/view/213868/ctm-api/2FxGgg |
| Getting Started Guide | https://github.com/calltracking/calltracking.github.io/blob/master/api_users_guide.md |
| Python Example | https://github.com/calltracking/ctm-python3-example |
| API Office Hours | http://apioh.calltrackingmetrics.com/ |

### CTM Platforms

| Platform | URL |
|----------|-----|
| Main Website | https://www.calltrackingmetrics.com/ |
| App (Login) | https://app.calltrackingmetrics.com/login |
| Knowledge Base | https://calltrackingmetrics.zendesk.com/hc/en-us |
| Training Center | https://launchpad.calltrackingmetrics.com |
| API Office Hours | https://apioh.calltrackingmetrics.com/ |
| Status Page | https://status.calltrackingmetrics.com/ |

### Integration Options

| Integration | Description |
|-------------|-------------|
| Zapier | Connect to 1000s of apps |
| Make | Workflow automation |
| Google Ads | Conversion tracking |
| Google Analytics | Session integration |
| HubSpot | CRM integration |
| Salesforce | CRM integration |
| Facebook | Ad tracking |
| Zoom | Call recording |

### Partner Program

- Partner Ecosystem: https://www.calltrackingmetrics.com/solutions/partner-ecosystem/
- Partner Directory: https://www.calltrackingmetrics.com/solutions/partnership-ecosystem/partner-directory

---

## Common Use Cases

### 1. Dynamic Call Tracking

Track which marketing source generated a call:

1. Create tracking sources for each marketing channel
2. Assign tracking numbers to sources
3. Numbers dynamically route based on source

### 2. IVR Routing

Route calls based on caller input:

1. Create voice menus with keypress options
2. Assign menu items to dial numbers or sub-menus
3. Attach schedules for after-hours routing

### 3. Time-Based Routing

Route calls differently based on business hours:

1. Create schedules for open/closed hours
2. Configure different routing for each schedule
3. Set voicemail for after-hours

### 4. Multi-Location Routing

Route calls to nearest location:

1. Create receiving numbers for each location
2. Use geo-routing or IVR to direct callers
3. Track performance per location

---

## Error Handling

### Common Error Codes

| Code | Description |
|------|-------------|
| 401 | Unauthorized - Invalid API keys |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Invalid data |
| 429 | Rate Limited - Too many requests |
| 500 | Internal Server Error |

### Error Response Format

```json
{
  "error": "Error message description",
  "status": "failed"
}
```

---

## Rate Limits

- Default rate limit: 1000 requests per minute
- Bulk operations: Contact support for higher limits
- Office hours API support available Wednesdays

---

## Support

- **Support Portal:** https://calltrackingmetrics.zendesk.com/hc/en-us
- **API Office Hours:** Wednesdays (register at https://apioh.calltrackingmetrics.com/)
- **Phone:** (888) 898-0513
