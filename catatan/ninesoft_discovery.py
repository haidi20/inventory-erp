import asyncio
from playwright.async_api import async_playwright

async def explore():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        print("Mencoba Akses http://web.ninesoft.id/")
        await page.goto("http://web.ninesoft.id/")
        
        # Mengisi form login
        try:
            input_email = page.locator("input[type='text'], input[name='email'], input[name='username']").first
            await input_email.fill("web@demo.com")
            
            input_pass = page.locator("input[type='password'], input[name='password']").first
            await input_pass.fill("admin")
            
            btn_login = page.locator("button:has-text('Login'), button:has-text('Sign In'), button[type='submit']").first
            await btn_login.click()
            
            await page.wait_for_load_state("networkidle")
            await page.wait_for_timeout(3000)
            print("Berhasil Melewati Layar Login. URL saat ini:", page.url)
        except Exception as e:
            print("Gagal menemukan elemen login:", str(e))
            
        print("Mencetak seluruh link dalam DOM untuk mengidentifikasi Master menu...")
        links = await page.locator("a").all()
        for link in links:
            txt = await link.inner_text()
            href = await link.get_attribute("href")
            if txt and txt.strip() and href and "#" not in href and "javascript" not in href:
                print(f"[{txt.strip()}] -> {href}")
                
        await browser.close()

if __name__ == "__main__":
    asyncio.run(explore())
