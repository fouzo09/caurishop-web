'use strict';

const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');

puppeteer.use(StealthPlugin());

(async () => {
    const url     = process.argv[2];
    const timeout = parseInt(process.argv[3] ?? '40000', 10);

    if (!url) {
        process.stderr.write('Usage: node scrape.cjs <url> [timeout_ms]\n');
        process.exit(1);
    }

    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-blink-features=AutomationControlled',
                '--lang=fr-FR,fr',
                '--window-size=1440,900',
            ],
        });

        const page = await browser.newPage();

        await page.setViewport({ width: 1440, height: 900 });
        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
        );
        await page.setExtraHTTPHeaders({ 'Accept-Language': 'fr-FR,fr;q=0.9,en-US;q=0.8' });

        // Masquer navigator.webdriver avant tout chargement
        await page.evaluateOnNewDocument(() => {
            Object.defineProperty(navigator, 'webdriver', { get: () => undefined });
            Object.defineProperty(navigator, 'plugins', { get: () => [1, 2, 3, 4, 5] });
            Object.defineProperty(navigator, 'languages', { get: () => ['fr-FR', 'fr', 'en-US', 'en'] });
            window.chrome = { runtime: {} };
        });

        await page.goto(url, {
            waitUntil: 'networkidle0',
            timeout,
        });

        // Attendre un peu plus pour les lazy-loaded products
        await new Promise(r => setTimeout(r, 3000));

        // Scroller pour déclencher le lazy loading
        await page.evaluate(() => {
            window.scrollBy(0, window.innerHeight * 2);
        });
        await new Promise(r => setTimeout(r, 2000));

        const html = await page.content();
        process.stdout.write(html);

    } catch (err) {
        process.stderr.write('ERROR: ' + err.message + '\n');
        process.exit(1);
    } finally {
        if (browser) await browser.close();
    }
})();
