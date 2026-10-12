import asyncio
import os
from urllib.parse import urlparse
from playwright.async_api import async_playwright

async def main():
    assets_dir = os.path.join(os.path.dirname(__file__), 'assets')
    if not os.path.exists(assets_dir):
        os.makedirs(assets_dir)
        
    auth_file = os.path.join(os.path.dirname(__file__), 'auth.json')

    async with async_playwright() as p:
        if os.path.exists(auth_file):
            print("Memulihkan sesi login dari auth.json...")
            browser = await p.chromium.launch(headless=True)
            context = await browser.new_context(storage_state=auth_file, viewport={"width": 1280, "height": 720})
        else:
            print("Memulai sesi login baru...")
            browser = await p.chromium.launch(headless=True)
            context = await browser.new_context(viewport={"width": 1280, "height": 720})
            
        page = await context.new_page()

        api_payloads = []
        async def handle_response(response):
            try:
                if response.ok and response.request.resource_type in ["xhr", "fetch"]:
                    url = response.url
                    # Tangkap data api
                    if ".json" in url or "/api/" in url or ("admin" not in url and "klinik" in url):
                        resp_json = await response.json()
                        if isinstance(resp_json, dict):
                            keys = list(resp_json.keys())[:5]
                            api_payloads.append((urlparse(url).path, str(keys)))
            except:
                pass
                        
        page.on("response", handle_response)

        if not os.path.exists(auth_file):
            await page.goto("https://klinik.rhantech.com/auth/login")
            await page.wait_for_timeout(2000)
            
            inputs = await page.locator("input").all()
            if len(inputs) >= 2:
                await inputs[0].fill("admin")
                await inputs[1].fill("password123")
                
            login_btn = page.locator("button:has-text('Masuk')")
            if await login_btn.count() > 0:
                await login_btn.click()
            else:
                await page.locator("button[type='submit']").click()
                
            await page.wait_for_load_state("networkidle")
            await context.storage_state(path=auth_file)
            print("Sesi Auth berhasil disimpan ke auth.json")
        else:
            await page.goto("https://klinik.rhantech.com/admin")
            await page.wait_for_load_state("networkidle")

        scraped_data = []

        # Target 3 Halaman Kunci untuk Demo Advanced Scraping
        target_menus = [
            {"href": "https://klinik.rhantech.com/admin/inventory", "text": "Data Obat/Alkes"},
            {"href": "https://klinik.rhantech.com/admin/bpjs/sep", "text": "SEP (Surat Eligibilitas)"},
            {"href": "https://klinik.rhantech.com/admin/analytics", "text": "Overview Intelijen"}
        ]

        for menu in target_menus:
            url = menu['href']
            print(f"Scraping Lanjutan: {menu['text']} ({url})...")
            
            try:
                await page.goto(url)
                await page.wait_for_load_state("domcontentloaded")
                await page.wait_for_timeout(3000)

                slug = url.split("/")[-1] or "dashboard"
                screenshot_filename = f"{slug}.png"
                
                # Fitur 2: Otomatisasi Screenshot Antarmuka
                screenshot_path = os.path.join(assets_dir, screenshot_filename)
                await page.screenshot(path=screenshot_path, full_page=True)

                menu_data = {
                    "menu": menu['text'],
                    "url": url,
                    "screenshot": screenshot_filename,
                    "apis": [],
                    "validations": []
                }
                
                menu_data["apis"] = list(api_payloads)
                api_payloads.clear()
                
                # Fitur 4: Simulasi Form Interaktif & Error Validation
                try:
                    btn_tambah = page.locator("button:has-text('Tambah'), button:has-text('Buat SEP')").first
                    if await btn_tambah.is_visible():
                        await btn_tambah.click()
                        await page.wait_for_timeout(1000)
                        
                        # Amankan database dengan memblokir network POST sementara!
                        async def abort_post(route):
                            if route.request.method in ["POST", "PUT", "DELETE"]:
                                await route.abort()
                            else:
                                await route.fallback()
                                
                        await page.route("**/*", abort_post)
                        
                        # Menembak pelatuk Submit Kosong!
                        btn_simpan = page.locator("button:has-text('Simpan'), button[type='submit']").last
                        if await btn_simpan.is_visible():
                            await btn_simpan.hover()
                            await btn_simpan.click(force=True)
                            await page.wait_for_timeout(1000)
                            
                            # Validation Capture
                            errors = await page.locator("input:invalid, select:invalid, .text-danger, .invalid-feedback").all()
                            for err in errors:
                                try:
                                    e_txt = await err.evaluate("el => el.validationMessage || el.innerText")
                                    if e_txt and len(e_txt) > 3:
                                        msg = e_txt.strip()
                                        if msg not in menu_data["validations"]:
                                            menu_data["validations"].append(msg)
                                except:
                                    pass
                                    
                        await page.unroute("**/*")
                        
                        btn_batal = page.locator("button:has-text('Batal'), button.close, .modal-header button.btn-close").first
                        if await btn_batal.is_visible():
                            await btn_batal.click()
                except Exception as eval_err:
                    print("Val error: " + str(eval_err))

                scraped_data.append(menu_data)
                    
            except Exception as e:
                print(f"Gagal memroses {url}: {e}")

        await browser.close()
        
        print("Menyusun tambahan Dokumentasi QA Tingkat Lanjut...")
        md_file = os.path.join(os.path.dirname(__file__), 'catatan_app.md')
        
        md_content = "\n---\n\n### 22. Advanced Scraping & QA Automation\n\n"
        md_content += "Bagian ini sepenuhnya di-*generate* menggunakan algoritma mutakhir yang mencegat antrian *Network API* (Interceptor), menyimpan salinan layar (*Screenshot*), serta menguji kekokohan formulir tanpa mencederai relasi database asli (melalui *Abort Route* POST request).\n\n"

        for data in scraped_data:
            md_content += f"#### • QA Menu: **{data['menu']}**\n"
            md_content += f"- **Endpoint Tested**: `{data['url']}`\n"
            md_content += f"- **Visual Render (Screenshot)**: `assets/{data['screenshot']}`\n"
            
            if data["apis"]:
                md_content += f"- **Intercepted API Payloads**: \n"
                # Remove duplicates
                unique_apis = []
                for a in data["apis"]:
                    if a not in unique_apis:
                        unique_apis.append(a)
                for api in unique_apis:
                    md_content += f"  - `FETCH {api[0]}` -> *Membawa objek JSON keys: {api[1]}*\n"
                md_content += "\n"
                
            if data["validations"]:
                md_content += f"- **Form Required Validation Responses**: \n"
                for v in data["validations"]:
                    md_content += f"  - 🔴 `{v}`\n"
            
            md_content += "\n"
            
        with open(md_file, "a", encoding="utf-8") as f:
            f.write(md_content)
            
        print("Selesai! Berhasil merekam jejak lanjutan.")

if __name__ == "__main__":
    asyncio.run(main())
