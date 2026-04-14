# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: streaming-ui.spec.ts >> Streaming UI Tests >> Refresh button triggers loading state
- Location: tests\e2e\streaming-ui.spec.ts:39:3

# Error details

```
TimeoutError: page.waitForURL: Timeout 10000ms exceeded.
=========================== logs ===========================
waiting for navigation to "**/dashboard" until "load"
============================================================
```

# Page snapshot

```yaml
- generic [ref=e2]:
  - generic [ref=e4]:
    - img "BOB Logo" [ref=e5]
    - paragraph [ref=e6]: AI-Powered Call Quality Assurance
    - paragraph [ref=e7]: Evaluates substance abuse helpline calls against a structured 25-criterion rubric with AI-powered analysis.
  - generic [ref=e12]:
    - generic [ref=e13]:
      - heading "Welcome Back" [level=2] [ref=e14]
      - paragraph [ref=e15]: Sign in to access your QA dashboard
    - generic [ref=e16]:
      - generic [ref=e17]: Email
      - textbox "Email" [active] [ref=e18]:
        - /placeholder: you@example.com
        - text: admin@example.com
      - list [ref=e19]:
        - listitem [ref=e20]: These credentials do not match our records.
    - generic [ref=e21]:
      - generic [ref=e22]: Password
      - textbox "Password" [ref=e23]:
        - /placeholder: ••••••••
    - generic [ref=e25]:
      - checkbox "Remember me" [ref=e26]
      - generic [ref=e27]: Remember me
    - button "Sign In" [ref=e28] [cursor=pointer]
```

# Test source

```ts
  1   | import { test, expect, Page } from '@playwright/test';
  2   | 
  3   | const TEST_SESSION_ID = 'lm_d386512d-d834-4e59-9416-ea17ae3a9ee5';
  4   | const BASE_URL = 'http://127.0.0.1:8000';
  5   | 
  6   | async function login(page: Page) {
  7   |   await page.goto('/login');
  8   |   await page.fill('input[name="email"]', 'admin@example.com');
  9   |   await page.fill('input[name="password"]', 'password');
  10  |   await page.click('button[type="submit"]');
> 11  |   await page.waitForURL('**/dashboard', { timeout: 10000 });
      |              ^ TimeoutError: page.waitForURL: Timeout 10000ms exceeded.
  12  | }
  13  | 
  14  | test.describe('Streaming UI Tests', () => {
  15  |   
  16  |   test('Floating window loads correctly', async ({ page }) => {
  17  |     // Login first
  18  |     await login(page);
  19  |     
  20  |     // Navigate to session page
  21  |     await page.goto(`${BASE_URL}/live-monitoring/session/${TEST_SESSION_ID}`);
  22  |     
  23  |     // Wait for floating window
  24  |     await page.waitForSelector('#floatingWindow', { timeout: 10000 });
  25  |     
  26  |     // Check all key elements
  27  |     await expect(page.locator('.floating-header')).toBeVisible();
  28  |     await expect(page.locator('#floatingTimer')).toBeVisible();
  29  |     await expect(page.locator('#floatingSuggestion')).toBeVisible();
  30  |     await expect(page.locator('#floatingChatInput')).toBeVisible();
  31  |     
  32  |     // Check buttons exist
  33  |     await expect(page.locator('button.floating-action-btn', { hasText: 'Refresh' })).toBeVisible();
  34  |     await expect(page.locator('button.floating-action-btn', { hasText: 'Use Suggestion' })).toBeVisible();
  35  |     
  36  |     console.log('✅ Floating window loaded successfully');
  37  |   });
  38  | 
  39  |   test('Refresh button triggers loading state', async ({ page }) => {
  40  |     // Login first
  41  |     await login(page);
  42  |     
  43  |     // Navigate to session page
  44  |     await page.goto(`${BASE_URL}/live-monitoring/session/${TEST_SESSION_ID}`);
  45  |     
  46  |     // Wait for floating window
  47  |     await page.waitForSelector('#floatingWindow', { timeout: 10000 });
  48  |     
  49  |     // Get initial state
  50  |     const initialText = await page.locator('#floatingSuggestionText').textContent();
  51  |     console.log('Initial suggestion text:', initialText);
  52  |     
  53  |     // Click refresh button
  54  |     await page.locator('button.floating-action-btn', { hasText: 'Refresh' }).click();
  55  |     
  56  |     // Check for typing indicator or loading state
  57  |     await page.waitForTimeout(1000); // Wait 1 second
  58  |     
  59  |     // Check if typing indicator appears (streaming is working)
  60  |     const typingIndicator = page.locator('.floating-typing-indicator');
  61  |     const hasTyping = await typingIndicator.count() > 0;
  62  |     
  63  |     if (hasTyping) {
  64  |       console.log('✅ Typing indicator appeared - streaming request initiated');
  65  |     } else {
  66  |       const currentText = await page.locator('#floatingSuggestionText').textContent();
  67  |       console.log('Current text after click:', currentText);
  68  |       console.log('⚠️ Typing indicator not found, but button was clicked');
  69  |     }
  70  |   });
  71  | 
  72  |   test('Chat input works correctly', async ({ page }) => {
  73  |     // Login first
  74  |     await login(page);
  75  |     
  76  |     // Navigate to session page
  77  |     await page.goto(`${BASE_URL}/live-monitoring/session/${TEST_SESSION_ID}`);
  78  |     
  79  |     // Wait for floating window
  80  |     await page.waitForSelector('#floatingWindow', { timeout: 10000 });
  81  |     
  82  |     // Type in chat input
  83  |     const chatInput = page.locator('#floatingChatInput');
  84  |     await chatInput.fill('Test message');
  85  |     
  86  |     // Check value
  87  |     const value = await chatInput.inputValue();
  88  |     expect(value).toBe('Test message');
  89  |     
  90  |     // Check send button is clickable
  91  |     const sendBtn = page.locator('.floating-send-btn');
  92  |     await expect(sendBtn).toBeVisible();
  93  |     
  94  |     console.log('✅ Chat input works correctly');
  95  |   });
  96  | 
  97  |   test('Console shows streaming latency logs', async ({ page }) => {
  98  |     // Login first
  99  |     await login(page);
  100 |     
  101 |     // Collect console messages
  102 |     const consoleLogs: string[] = [];
  103 |     const consoleErrors: string[] = [];
  104 |     
  105 |     page.on('console', msg => {
  106 |       if (msg.type() === 'error') {
  107 |         consoleErrors.push(msg.text());
  108 |       } else if (msg.text().includes('latency')) {
  109 |         consoleLogs.push(msg.text());
  110 |       }
  111 |     });
```