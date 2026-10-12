import asyncio
from playwright.async_api import async_playwright
import os

async def debug_html():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        await page.goto("http://web.ninesoft.id/")
        await page.wait_for_timeout(2000)
        content = await page.content()
        with open("d:/projects/klinik-starter/catatan/debug_login.html", "w", encoding="utf-8") as f:
            f.write(content)
        await browser.close()
        
if __name__ == "__main__":
    asyncio.run(debug_html())
