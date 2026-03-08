import { test, expect } from '@playwright/test';

test.describe('Front page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    // domcontentloaded avoids WebKit hanging on long-poll network requests
    await page.waitForLoadState('domcontentloaded');
  });

  test('matches screenshot', async ({ page }, testInfo) => {
    await expect(page).toHaveScreenshot(`front-page-${testInfo.project.name}.png`, {
      // Allow up to 0.2% pixel difference to tolerate minor rendering noise
      maxDiffPixelRatio: 0.002,
    });
  });
});

test.describe('Navigation', () => {
  test('Self-Preparation page renders correctly', async ({ page }, testInfo) => {
    await page.goto('/');
    await page.waitForLoadState('domcontentloaded');

    // On mobile the sidebar is hidden behind a hamburger menu
    const hamburger = page.getByRole('button', { name: /open navigation menu/i });
    if (await hamburger.isVisible()) {
      await hamburger.click();
    }

    await page.getByText('To Solo Pilot').click();
    await page.getByText('Self-Preparation').first().click();
    await page.waitForLoadState('domcontentloaded');

    await expect(page).toHaveScreenshot(`self-preparation-${testInfo.project.name}.png`, {
      maxDiffPixelRatio: 0.002,
    });
  });
});
