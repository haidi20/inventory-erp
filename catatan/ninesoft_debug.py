import asyncio
from playwright.async_api import async_playwright
import os

async def debug_ninesoft():
    print("Debug: Memulai Ninesoft GUI Screenshot...")
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page(viewport={"width": 1280, "height": 720})

        print("Navigasi login...")
        await page.goto("http://web.ninesoft.id/")
        await page.wait_for_timeout(3000)
        
        # Login
        input_email = page.locator("input[placeholder*='sername'], input[placeholder*='mail'], input[name='username'], input[type='text']")
        if await input_email.count() > 0:
            await input_email.first.fill("web@demo.com")
            
        input_pass = page.locator("input[type='password'], input[name='password']")
        if await input_pass.count() > 0:
            await input_pass.first.fill("admin")
            
        btn_login = page.locator("button:has-text('Login'), button:has-text('Sign In'), button[type='submit']")
        if await btn_login.count() > 0:
            await btn_login.first.click()
            
        print("Tunggu load state...")
        await page.wait_for_load_state("networkidle")
        await page.wait_for_timeout(5000)

        print("URL Akhir:", page.url)

        # Coba screenshot full page
        ss_path = os.path.join(os.path.dirname(__file__), "ninesoft_debug.png")
        await page.screenshot(path=ss_path, full_page=True)
        print("Screenshot disimpan di", ss_path)
        
        # Cetak beberapa menu navigasi untuk dianalisa
        print("Menu Ninesoft:")
        navs = await page.locator(".nav-item, li, a, .menu-item").all()
        for nav in navs[:50]:
            try:
                txt = await nav.inner_text()
                href = await nav.get_attribute("href")
                if txt and txt.strip():
                    print(f"[{txt[:30]}] -> {href}")
            except:
                pass
                
        await browser.close()

if __name__ == "__main__":
    asyncio.run(debug_ninesoft())
