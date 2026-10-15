import { test, expect } from '@playwright/test';

test('login rejects invalid credentials and remains usable', async ({ page }) => {
    await page.goto('/login');
    await page.locator('#email').fill('nobody@example.invalid');
    await page.locator('#password').fill('IncorrectPassword123!');
    await page.locator('form button[type="submit"]').click();
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('#email')).toBeVisible();
});

test('a new account can log out without stale authenticated navigation', async ({ page }) => {
    const email = `browser-${Date.now()}-${Math.random().toString(36).slice(2, 8)}@example.invalid`;
    await page.goto('/register');
    await page.locator('#name').fill('Browser Test Author');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill('SecurePassword123!');
    await page.locator('#password_confirmation').fill('SecurePassword123!');
    await page.locator('form button[type="submit"]').click();
    await expect(page).not.toHaveURL(/\/register/);
    const logout = page.locator('button').filter({ hasText: /Log Out|Log out|Iziet/i });
    // Account dropdown is opened through the signed-in user's name.
    await page.getByText('Browser Test Author', { exact: true }).first().click();
    await expect(logout.first()).toBeVisible();
    await logout.first().click();
    await page.waitForURL(url => !url.pathname.includes('/logout'));
    await page.reload();
    await expect(page.getByRole('link', { name: /log in|pieslēgties/i }).first()).toBeVisible();
});
