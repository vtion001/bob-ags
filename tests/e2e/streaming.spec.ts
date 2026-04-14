import { test, expect } from '@playwright/test';

const TEST_SESSION_ID = 'lm_d386512d-d834-4e59-9416-ea17ae3a9ee5';

test.describe('Streaming AI Functionality', () => {
  
  test.beforeEach(async ({ page }) => {
    // Login first
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    
    // Wait for dashboard to load
    await page.waitForURL('**/dashboard');
  });

  test('Floating window is visible on session page', async ({ page }) => {
    await page.goto(`/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Check floating window exists
    const floatingWindow = page.locator('#floatingWindow');
    await expect(floatingWindow).toBeAttached();
    
    // Check floating window header
    const header = page.locator('.floating-header');
    await expect(header).toBeVisible();
    
    // Check timer is displayed
    const timer = page.locator('#floatingTimer');
    await expect(timer).toBeVisible();
    
    // Check suggestion area
    const suggestion = page.locator('#floatingSuggestion');
    await expect(suggestion).toBeVisible();
  });

  test('Refresh button triggers streaming suggestion', async ({ page }) => {
    await page.goto(`/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Wait for floating window to be visible
    await page.waitForSelector('#floatingWindow.visible', { timeout: 5000 }).catch(() => {
      // Toggle it if not visible
      page.click('#floatingWindow .floating-close-btn');
      page.click('#floatingWindow .floating-close-btn');
    });
    
    // Capture console messages for latency measurement
    const consoleLogs: string[] = [];
    page.on('console', msg => {
      if (msg.text().includes('latency')) {
        consoleLogs.push(msg.text());
      }
    });
    
    // Click refresh button
    const refreshBtn = page.locator('button.floating-action-btn', { hasText: 'Refresh' });
    await refreshBtn.click();
    
    // Wait for typing indicator to appear
    const typingIndicator = page.locator('.floating-typing-indicator');
    await expect(typingIndicator).toBeVisible({ timeout: 5000 });
    
    // Wait for suggestion text to appear (should replace typing indicator)
    await page.waitForFunction(() => {
      const el = document.getElementById('floatingSuggestionText');
      return el && !el.textContent?.includes('Click refresh') && el.textContent?.trim().length > 0;
    }, { timeout: 30000 });
    
    // Check console for latency
    console.log('Console logs:', consoleLogs);
    
    // Verify suggestion text is not empty
    const suggestionText = await page.locator('#floatingSuggestionText').textContent();
    expect(suggestionText?.length).toBeGreaterThan(0);
    expect(suggestionText).not.toContain('Failed to load');
  });

  test('Chat input sends message and streams response', async ({ page }) => {
    await page.goto(`/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Ensure floating window is visible
    await page.waitForSelector('#floatingWindow.visible', { timeout: 5000 }).catch(() => {
      page.click('#floatingWindow .floating-close-btn');
      page.click('#floatingWindow .floating-close-btn');
    });
    
    // Capture console messages
    const consoleLogs: string[] = [];
    page.on('console', msg => {
      if (msg.text().includes('latency') || msg.text().includes('First response')) {
        consoleLogs.push(msg.text());
      }
    });
    
    // Type a message
    const chatInput = page.locator('#floatingChatInput');
    await chatInput.fill('Hello, can you help me?');
    
    // Send message
    const sendBtn = page.locator('.floating-send-btn');
    await sendBtn.click();
    
    // Wait for typing indicator
    const typingIndicator = page.locator('.floating-typing-indicator');
    await expect(typingIndicator).toBeVisible({ timeout: 5000 });
    
    // Wait for response
    await page.waitForFunction(() => {
      const el = document.getElementById('floatingSuggestionText');
      return el && el.textContent && !el.textContent.includes('Thinking') && el.textContent.trim().length > 0;
    }, { timeout: 30000 });
    
    // Check console for latency measurement
    console.log('Chat latency logs:', consoleLogs);
    
    // Verify response
    const responseText = await page.locator('#floatingSuggestionText').textContent();
    expect(responseText?.length).toBeGreaterThan(0);
  });

  test('Network shows streaming response', async ({ page }) => {
    await page.goto(`/live-monitoring/session/${TEST_SESSION_ID}`);
    
    // Ensure floating window is visible
    await page.waitForSelector('#floatingWindow.visible', { timeout: 5000 }).catch(() => {
      page.click('#floatingWindow .floating-close-btn');
      page.click('#floatingWindow .floating-close-btn');
    });
    
    // Setup network interception
    const streamedChunks: string[] = [];
    page.on('response', async (response) => {
      const url = response.url();
      if (url.includes('chat-stream') || url.includes('suggestion-stream')) {
        // For streaming, the response will be chunked
        try {
          const text = await response.text();
          streamedChunks.push(text);
        } catch (e) {}
      }
    });
    
    // Click refresh
    const refreshBtn = page.locator('button.floating-action-btn', { hasText: 'Refresh' });
    await refreshBtn.click();
    
    // Wait for response
    await page.waitForFunction(() => {
      const el = document.getElementById('floatingSuggestionText');
      return el && !el.textContent?.includes('Click refresh') && el.textContent?.trim().length > 0;
    }, { timeout: 30000 });
    
    // Log streamed chunks
    console.log('Streamed chunks received:', streamedChunks.length);
    
    // Verify we received chunked data
    expect(streamedChunks.length).toBeGreaterThan(0);
  });
});

test.describe('API Streaming Endpoints', () => {
  
  test('chat-stream endpoint returns streaming response', async ({ request }) => {
    // First login to get CSRF token
    const loginResponse = await request.get('/login');
    const loginBody = await loginResponse.text();
    
    // Extract CSRF token (simplified - in real test use page context)
    const sessionId = TEST_SESSION_ID;
    
    // Test the streaming endpoint
    const response = await request.post('/api/live-monitoring/chat-stream', {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/plain',
      },
      data: {
        session_id: sessionId,
        question: 'Hello, can you help me?',
      },
    });
    
    // Should get a response (may be streaming)
    expect(response.status()).toBe(200);
    
    const body = await response.body();
    console.log('Response body preview:', body.toString().substring(0, 500));
    
    // Check for streaming data format
    const bodyStr = body.toString();
    expect(bodyStr).toContain('data:');
  });

  test('suggestion-stream endpoint returns streaming response', async ({ request }) => {
    const response = await request.post('/api/live-monitoring/suggestion-stream', {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/plain',
      },
      data: {
        session_id: TEST_SESSION_ID,
        type: 'what_to_say',
      },
    });
    
    expect(response.status()).toBe(200);
    
    const body = await response.body();
    console.log('Suggestion stream body preview:', body.toString().substring(0, 500));
    
    const bodyStr = body.toString();
    expect(bodyStr).toContain('data:');
  });
});
