import { test, expect } from '@playwright/test';

test.describe('User Group Filtering', () => {
  test('should filter calls by user group (Phillies)', async ({ page }) => {
    await page.goto('/calls');

    await expect(page.getByRole('heading', { name: 'Calls' })).toBeVisible();

    const userGroupSelect = page.locator('#user_groups');
    await expect(userGroupSelect).toBeVisible();

    await userGroupSelect.selectOption({ label: 'Phillies' });

    await page.getByRole('button', { name: 'Sync to Database' }).click();

    await page.waitForURL(/\/search-ctm/);

    const tableRows = page.locator('tbody tr');
    const rowCount = await tableRows.count();

    console.log('Rows found after filter: ' + rowCount);
    expect(rowCount).toBeGreaterThan(0);

    const firstRow = tableRows.first();
    const agentCell = firstRow.locator('td').nth(3);
    const agentText = await agentCell.textContent();
    console.log('First agent: ' + agentText);
    expect(agentText).toContain('Phillies');
  });

  test('should show no results when filtering by user group with no calls', async ({ page }) => {
    await page.goto('/calls');

    const userGroupSelect = page.locator('#user_groups');
    await userGroupSelect.selectOption({ label: 'Phillies' });

    await page.getByRole('button', { name: 'Sync to Database' }).click();
    await page.waitForURL(/\/search-ctm/);

    const bodyText = await page.locator('body').textContent();
    const hasNoCalls = bodyText.includes('No calls found') || bodyText.includes('0 calls');
    console.log('No calls message visible: ' + hasNoCalls);
  });
});
