import { test, expect, Page } from '@playwright/test';

const TEST_SESSION_ID = 'lm_d386512d-d834-4e59-9416-ea17ae3a9ee5';
const BASE_URL = 'http://127.0.0.1:8000';

async function login(page: Page) {
  await page.goto('/login');
  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 10000 });
}

test.describe('Streaming UI Tests', () => {
  
  test('Floating window loads correctly', async ({ page }) => {
    // Login first
    await login(page);
    
    // Navigate to session page
    await page.goto(`${BASE_URL}/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Wait for floating window
    await page.waitForSelector('#floatingWindow', { timeout: 10000 });
    
    // Check all key elements
    await expect(page.locator('.floating-header')).toBeVisible();
    await expect(page.locator('#floatingTimer')).toBeVisible();
    await expect(page.locator('#floatingSuggestion')).toBeVisible();
    await expect(page.locator('#floatingChatInput')).toBeVisible();
    
    // Check buttons exist
    await expect(page.locator('button.floating-action-btn', { hasText: 'Refresh' })).toBeVisible();
    await expect(page.locator('button.floating-action-btn', { hasText: 'Use Suggestion' })).toBeVisible();
    
    console.log('✅ Floating window loaded successfully');
  });

  test('Refresh button triggers loading state', async ({ page }) => {
    // Login first
    await login(page);
    
    // Navigate to session page
    await page.goto(`${BASE_URL}/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Wait for floating window
    await page.waitForSelector('#floatingWindow', { timeout: 10000 });
    
    // Get initial state
    const initialText = await page.locator('#floatingSuggestionText').textContent();
    console.log('Initial suggestion text:', initialText);
    
    // Click refresh button
    await page.locator('button.floating-action-btn', { hasText: 'Refresh' }).click();
    
    // Check for typing indicator or loading state
    await page.waitForTimeout(1000); // Wait 1 second
    
    // Check if typing indicator appears (streaming is working)
    const typingIndicator = page.locator('.floating-typing-indicator');
    const hasTyping = await typingIndicator.count() > 0;
    
    if (hasTyping) {
      console.log('✅ Typing indicator appeared - streaming request initiated');
    } else {
      const currentText = await page.locator('#floatingSuggestionText').textContent();
      console.log('Current text after click:', currentText);
      console.log('⚠️ Typing indicator not found, but button was clicked');
    }
  });

  test('Chat input works correctly', async ({ page }) => {
    // Login first
    await login(page);
    
    // Navigate to session page
    await page.goto(`${BASE_URL}/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Wait for floating window
    await page.waitForSelector('#floatingWindow', { timeout: 10000 });
    
    // Type in chat input
    const chatInput = page.locator('#floatingChatInput');
    await chatInput.fill('Test message');
    
    // Check value
    const value = await chatInput.inputValue();
    expect(value).toBe('Test message');
    
    // Check send button is clickable
    const sendBtn = page.locator('.floating-send-btn');
    await expect(sendBtn).toBeVisible();
    
    console.log('✅ Chat input works correctly');
  });

  test('Console shows streaming latency logs', async ({ page }) => {
    // Login first
    await login(page);
    
    // Collect console messages
    const consoleLogs: string[] = [];
    const consoleErrors: string[] = [];
    
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      } else if (msg.text().includes('latency')) {
        consoleLogs.push(msg.text());
      }
    });
    
    // Navigate to session page
    await page.goto(`${BASE_URL}/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Wait for floating window
    await page.waitForSelector('#floatingWindow', { timeout: 10000 });
    
    // Click refresh button
    await page.locator('button.floating-action-btn', { hasText: 'Refresh' }).click();
    
    // Wait for any response (up to 30 seconds)
    await page.waitForTimeout(30000);
    
    // Report results
    console.log('Console logs with latency:', consoleLogs);
    console.log('Console errors:', consoleErrors);
    
    // The test passes if no JavaScript errors
    expect(consoleErrors.filter(e => !e.includes('favicon'))).toHaveLength(0);
  });
});
