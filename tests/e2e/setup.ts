import { test as setup, expect } from '@wordpress/e2e-test-utils-playwright';
import path from 'path';

setup('login', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.waitForTimeout(1500);
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('password');
  await page.locator('#wp-submit').click();
  await page.waitForTimeout(1500);
  await expect(
    page.getByRole('heading', { name: 'Dashboard', level: 1 }),
  ).toBeVisible();
  await page.context().storageState({ path: path.join(__dirname, 'state.json') });
});
