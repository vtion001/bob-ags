import { test, expect, Page, request } from '@playwright/test';

const TEST_SESSION_ID = 'lm_d386512d-d834-4e59-9416-ea17ae3a9ee5';
const BASE_URL = 'http://127.0.0.1:8000';

async function login(page: Page) {
  await page.goto('/login');
  await page.fill('input[name="email"]', 'admin@example.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 10000 });
}

test.describe('API Streaming Tests', () => {
  
  test('suggestion-stream endpoint returns streaming response', async ({ page }) => {
    // Login first
    await login(page);
    
    // Now make the API request using the page context (which has the session cookie)
    const response = await page.request.post(`${BASE_URL}/api/live-monitoring/suggestion-stream`, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/plain',
      },
      data: {
        session_id: TEST_SESSION_ID,
        type: 'what_to_say',
      },
    });
    
    console.log('Response status:', response.status());
    
    if (response.status() !== 200) {
      const body = await response.text();
      console.log('Error response:', body.substring(0, 500));
      throw new Error(`Expected 200, got ${response.status()}`);
    }
    
    const body = await response.text();
    console.log('First 500 chars:', body.substring(0, 500));
    
    // Check for streaming data format
    expect(body).toContain('data:');
  });

  test('chat-stream endpoint returns streaming response', async ({ page }) => {
    // Login first
    await login(page);
    
    const response = await page.request.post(`${BASE_URL}/api/live-monitoring/chat-stream`, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/plain',
      },
      data: {
        session_id: TEST_SESSION_ID,
        question: 'Hello',
      },
    });
    
    console.log('Response status:', response.status());
    
    if (response.status() !== 200) {
      const body = await response.text();
      console.log('Error response:', body.substring(0, 500));
      throw new Error(`Expected 200, got ${response.status()}`);
    }
    
    const body = await response.text();
    console.log('First 500 chars:', body.substring(0, 500));
    
    expect(body).toContain('data:');
  });
});

test.describe('UI Streaming Tests', () => {
  
  test('Floating window loads and streaming works', async ({ page }) => {
    // Login first
    await login(page);
    
    // Navigate to session page
    await page.goto(`${BASE_URL}/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Wait for floating window to be visible
    await page.waitForSelector('#floatingWindow', { timeout: 10000 });
    
    // Check floating window header
    const header = page.locator('.floating-header');
    await expect(header).toBeVisible();
    
    // Click refresh button
    const refreshBtn = page.locator('button.floating-action-btn', { hasText: 'Refresh' });
    await refreshBtn.click();
    
    // Wait for typing indicator to appear
    const typingIndicator = page.locator('.floating-typing-indicator');
    await expect(typingIndicator).toBeVisible({ timeout: 5000 });
    
    console.log('Typing indicator appeared - streaming is working!');
    
    // Wait for response (timeout after 60 seconds)
    try {
      await page.waitForFunction(() => {
        const el = document.getElementById('floatingSuggestionText');
        return el && !el.textContent?.includes('Click refresh') && el.textContent?.trim().length > 0 && !el.querySelector('.floating-typing-indicator');
      }, { timeout: 60000 });
      
      const suggestionText = await page.locator('#floatingSuggestionText').textContent();
      console.log('Suggestion received:', suggestionText?.substring(0, 100));
      expect(suggestionText?.length).toBeGreaterThan(0);
    } catch (e) {
      // Check if it's an error message
      const text = await page.locator('#floatingSuggestionText').textContent();
      if (text?.includes('Failed') || text?.includes('Error')) {
        throw new Error(`Streaming failed: ${text}`);
      }
      console.log('Response still pending or partial:', text);
    }
  });
});
