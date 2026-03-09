import { test, expect } from '@playwright/test';

/**
 * Tests for search result highlighting, following the real user flow:
 *   1. Land on the front page.
 *   2. Type a query into the search box and submit.
 *   3. Click the first result card on the search results page.
 *   4. Verify highlighting behaviour on the destination page.
 *
 * Behaviour being verified:
 *  - Multi-word queries highlight the exact phrase, not each word individually
 *    (matching the search engine's phrase-match behaviour documented in
 *    template-parts/search-zone.php).
 *  - Single-word queries highlight that word.
 *  - On load the page scrolls so the first highlight is visible in the viewport.
 *  - Navigating directly to a page (not via search) produces no highlights.
 */

/** Type a query into the hero search box on the current page and submit. */
async function submitSearch(page: import('@playwright/test').Page, query: string) {
    await page.locator('#hero-search').fill(query);
    await page.locator('#hero-search').press('Enter');
    await page.waitForLoadState('domcontentloaded');
}

/** Click the first result card and wait for the destination page to load. */
async function clickFirstResult(page: import('@playwright/test').Page) {
    const firstCard = page.locator('a[role="article"]').first();
    await expect(firstCard).toBeVisible();
    await firstCard.click();
    await page.waitForLoadState('domcontentloaded');
}

test.describe('Search highlight – phrase match', () => {
    test('multi-word query: marks contain the full phrase, not lone words', async ({ page }) => {
        await page.goto('/');
        await page.waitForLoadState('domcontentloaded');
        await submitSearch(page, 'ground loop');
        await clickFirstResult(page);

        // The URL must carry the highlight parameter
        expect(page.url()).toContain('highlight=');

        const marks = page.locator('mark.search-highlight');
        await expect(marks.first()).toBeVisible();

        const count = await marks.count();
        for (let i = 0; i < count; i++) {
            const text = ((await marks.nth(i).textContent()) ?? '').toLowerCase();
            // Every mark must contain the full phrase
            expect(text).toContain('ground loop');
            // A mark that is exactly "ground" or exactly "loop" alone is wrong
            expect(text.trim()).not.toBe('ground');
            expect(text.trim()).not.toBe('loop');
        }
    });
});

test.describe('Search highlight – single word', () => {
    test('single-word query: that word is highlighted', async ({ page }) => {
        await page.goto('/');
        await page.waitForLoadState('domcontentloaded');
        await submitSearch(page, 'stall');
        await clickFirstResult(page);

        expect(page.url()).toContain('highlight=');

        const marks = page.locator('mark.search-highlight');
        await expect(marks.first()).toBeVisible();

        const count = await marks.count();
        for (let i = 0; i < count; i++) {
            const text = (await marks.nth(i).textContent()) ?? '';
            expect(text.toLowerCase()).toContain('stall');
        }
    });
});

test.describe('Search – phrase match precision (regression)', () => {
    /**
     * Regression: "convert to single" was previously matching pages that only
     * contained "convert to a single" because the posts_search filter was
     * silently bypassed and WordPress fell back to its default word-split SQL
     * (LIKE '%convert%' AND LIKE '%single%', dropping "to" as a stop word).
     *
     * Every result returned for a multi-word query must contain the exact
     * phrase somewhere in its visible text.
     */
    test('every result for "convert to single" contains the exact phrase', async ({ page }) => {
        await page.goto('/');
        await page.waitForLoadState('domcontentloaded');
        await submitSearch(page, 'convert to single');

        // Collect all result card hrefs (may be empty if no pages match)
        const cards = page.locator('a[role="article"]');
        const cardCount = await cards.count();

        for (let i = 0; i < cardCount; i++) {
            const href = await cards.nth(i).getAttribute('href') ?? '';

            // Visit the page directly (no highlight param) so we see raw content
            const bare = href.split('?')[0];
            await page.goto(bare);
            await page.waitForLoadState('domcontentloaded');

            const bodyText = (await page.locator('body').innerText()).toLowerCase();
            expect(
                bodyText,
                `Result page "${bare}" does not contain the exact phrase "convert to single"`
            ).toContain('convert to single');

            await page.goBack();
            await page.waitForLoadState('domcontentloaded');
        }
    });
});

test.describe('Search highlight – apostrophe handling', () => {
    test('query with straight apostrophe highlights text curly-quoted by WordPress', async ({ page }) => {
        await page.goto('/');
        await page.waitForLoadState('domcontentloaded');
        await submitSearch(page, "I'M SAFE");
        await clickFirstResult(page);

        expect(page.url()).toContain('highlight=');

        const marks = page.locator('mark.search-highlight');
        await expect(marks.first()).toBeVisible();

        const count = await marks.count();
        for (let i = 0; i < count; i++) {
            const text = ((await marks.nth(i).textContent()) ?? '').toLowerCase();
            // WordPress converts straight apostrophes to curly (right single
            // quotation mark U+2019), so accept either variant.
            const normalised = text.replace(/\u2019/g, "'");
            expect(normalised).toContain("i'm safe");
        }
    });
});

test.describe('Search highlight – scroll to first match', () => {
    test('first highlighted mark is in the viewport after following a search result', async ({ page }) => {
        await page.goto('/');
        await page.waitForLoadState('domcontentloaded');
        await submitSearch(page, 'ground loop');
        await clickFirstResult(page);

        const firstMark = page.locator('mark.search-highlight').first();
        await expect(firstMark).toBeVisible();
        await expect(firstMark).toBeInViewport();
    });

    test('navigating directly to a page produces no highlights', async ({ page }) => {
        // Simulate a user browsing via the syllabus navigation rather than search
        await page.goto('/pilot/soaring/anticipation/');
        await page.waitForLoadState('domcontentloaded');

        await expect(page.locator('mark.search-highlight')).toHaveCount(0);
    });
});
