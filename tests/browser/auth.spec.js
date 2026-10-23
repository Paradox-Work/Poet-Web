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
    // The responsive menu is the visible navigation at both tested widths
    // (the full desktop navbar starts at 1380px).
    await page.getByRole('button', { name: 'Toggle navigation menu' }).click();
    const logout = page.getByRole('button', { name: /Log Out|Izrakstīties/i });
    await expect(logout).toBeVisible();
    await logout.click();
    await expect(page).toHaveURL('/');
    await page.reload();

    // Unauthenticated visitors should still be able to open the login screen.
    await page.getByRole('button', { name: 'Toggle navigation menu' }).click();
    await expect(page.locator('nav a[href="/login"]:visible')).toBeVisible();
});
