import { test, expect } from '@playwright/test';

const TEST_SESSION_ID = 'lm_d386512d-d834-4e59-9416-ea17ae3a9ee5';

test.describe('API Streaming Tests', () => {
  
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
    
    const body = await response.text();
    console.log('First 500 chars:', body.substring(0, 500));
    
    // Check for streaming data format
    expect(body).toContain('data:');
  });

  test('chat-stream endpoint returns streaming response', async ({ request }) => {
    const response = await request.post('/api/live-monitoring/chat-stream', {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/plain',
      },
      data: {
        session_id: TEST_SESSION_ID,
        question: 'Hello',
      },
    });
    
    expect(response.status()).toBe(200);
    
    const body = await response.text();
    console.log('First 500 chars:', body.substring(0, 500));
    
    expect(body).toContain('data:');
  });
});
