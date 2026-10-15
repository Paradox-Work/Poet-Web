import { test, expect } from '@playwright/test';

test('public pages remain navigable without JS errors', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('/');
    await expect(page.locator('body')).toContainText('Poet-Web');
    await page.goto('/groups');
    await expect(page.locator('body')).not.toBeEmpty();
    await page.goto('/about');
    await expect(page.locator('body')).not.toBeEmpty();
    expect(errors).toEqual([]);
});

test('branded missing-page response lets visitors return home', async ({ page }) => {
    await page.goto('/posts/999999999');
    await expect(page.locator('a[href="/"]')).toBeVisible();
    await page.locator('a[href="/"]').click();
    await expect(page).toHaveURL('/');
});

test('dark mode is applied before the first paint on reload', async ({ page }) => {
    await page.goto('/');
    await page.evaluate(() => localStorage.setItem('theme', 'dark'));
    await page.reload();
    await expect(page.locator('html')).toHaveClass(/dark/);
    const bg = await page.locator('html').evaluate(element => getComputedStyle(element).backgroundColor);
    expect(bg).not.toBe('rgb(255, 255, 255)');
});

test('mobile and desktop layouts do not overflow horizontally', async ({ page }) => {
    for (const url of ['/', '/groups', '/about']) {
        await page.goto(url);
        const overflow = await page.evaluate(() =>
            document.documentElement.scrollWidth - document.documentElement.clientWidth
        );
        expect(overflow, `Horizontal overflow at ${url}`).toBeLessThanOrEqual(2);
    }
});
