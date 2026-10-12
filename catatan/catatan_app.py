import asyncio
from playwright.async_api import async_playwright
import os

MARKDOWN_FILE = os.path.join(os.path.dirname(__file__), "catatan_app.md")

async def scrape_klinik():
    print("Memulai scraping aplikasi Klinik Starter...")
    scraped_data = []
    
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(viewport={'width': 1280, 'height': 800})
        page = await context.new_page()
        
        print("1. Login ke sistem...")
        await page.goto("https://klinik.rhantech.com/auth/login")
        
        # Mengisi form login
        inputs = await page.locator("input").all()
        if len(inputs) >= 2:
            await inputs[0].fill("admin")
            await inputs[1].fill("password123")
            
        # Klik tombol Masuk
        login_btn = page.locator("button:has-text('Masuk')")
        if await login_btn.count() > 0:
            await login_btn.click()
        else:
            await page.locator("button[type='submit']").click()
            
        try:
            await page.wait_for_url("**/admin**", timeout=15000)
            await page.wait_for_load_state("networkidle")
            print("Login berhasil.")
        except:
            print("Timeout menunggu redirect login. Mencoba melanjutkan...")
            await page.wait_for_timeout(3000)
        
        # Menunggu sidebar termuat
        try:
            await page.wait_for_selector("nav", timeout=5000)
        except:
            print("Sidebar nav tidak ditemukan.")
        
        print("2. Mencari semua menu di sidebar...")
        
        # Click all buttons in nav to expand menus
        expand_buttons = await page.locator("nav button").all()
        for btn in expand_buttons:
            try:
                if await btn.is_visible():
                    await btn.click(timeout=1000)
                    await page.wait_for_timeout(500)
            except:
                pass
                
        # Dapatkan semua link menu
        menu_links = await page.locator("nav a[href^='/admin']").all()
        urls_to_visit = []
        for link in menu_links:
            href = await link.get_attribute("href")
            text = await link.inner_text()
            if href and href not in [u['href'] for u in urls_to_visit] and text.strip():
                urls_to_visit.append({"href": href, "text": text.strip()})
                
        print(f"Ditemukan {len(urls_to_visit)} menu. Memulai ekstraksi...")
        
        # Loop semua url menu
        for menu in urls_to_visit:
            url = f"https://klinik.rhantech.com{menu['href']}"
            print(f"Scraping {menu['text']} ({url})...")
            
            try:
                await page.goto(url)
                await page.wait_for_load_state("networkidle")
                await page.wait_for_timeout(2000) # Tunggu animasi/DOM berubah
                
                menu_data = {
                    "menu": menu['text'],
                    "url": url,
                    "table_headers": [],
                    "filters": [],
                    "buttons": [],
                    "inputs": [],
                    "cards": []
                }
                
                # Scraping Informasi HTML Card / Panel Statistik
                try:
                    # Mencari elemen yang memiliki class berkaitan dengan 'card', 'bg-white shadow', 'widget', atau identifikasi box-ringkasan
                    card_elements = await page.locator(".card, .bg-white.shadow, .bg-white.rounded-lg.shadow-sm, [class*='widget']").all()
                    for card in card_elements:
                        # Extract inner text structure using Javascript to preserve formatting if needed
                        txt = await card.inner_text()
                        if txt and len(txt.strip()) > 5:
                            # Bersihkan dan batasi karakter agar log Markdown tetap logis
                            cleaned_card = txt.replace("\n", " | ").strip()
                            if len(cleaned_card) > 150:
                                cleaned_card = cleaned_card[:147] + "..."
                            if cleaned_card not in menu_data["cards"]:
                                menu_data["cards"].append(cleaned_card)
                except Exception as e:
                    print(f"Error extracting cards for {url}: {e}")
                
                # Scraping table headers
                headers = await page.locator("th").all()
                for th in headers:
                    txt = await th.inner_text()
                    if txt.strip():
                        menu_data["table_headers"].append(txt.replace('\n', ' ').strip())
                        
                # Scraping buttons (biasanya filter atau aksi)
                buttons = await page.locator("button").all()
                for btn in buttons:
                    txt = await btn.inner_text()
                    if txt.strip() and len(txt) < 40 and txt.strip() not in menu_data["buttons"]:
                        menu_data["buttons"].append(txt.strip())
                        
                # Buka Modal Tambah jika ada untuk scrape form
                tambah_btn = page.locator("button", has_text="Tambah")
                if await tambah_btn.count() > 0 and await tambah_btn.nth(0).is_visible():
                    try:
                        await tambah_btn.nth(0).click(timeout=2000)
                        await page.wait_for_timeout(1000) # Tunggu modal terbuka
                        
                        inputs = await page.locator("input, textarea").all()
                        for inp in inputs:
                            placeholder = await inp.get_attribute("placeholder")
                            id_attr = await inp.get_attribute("id")
                            if placeholder or id_attr:
                                menu_data["inputs"].append(f"{id_attr or 'input'}: {placeholder or ''}")
                                
                        # Tutup modal dengan escape
                        await page.keyboard.press("Escape")
                        await page.wait_for_timeout(500)
                    except:
                        pass
                
                scraped_data.append(menu_data)
                
            except Exception as e:
                print(f"Gagal scrape {url}: {str(e)}")
                
        await browser.close()
        
    print("Scraping selesai. Menyusun hasil ke Markdown...")
    
    # Format to markdown
    md_content = "\n\n---\n\n### 12. Hasil Scraping Detail Sistem (Otomatis)\n\n"
    md_content += "Data berikut ini adalah hasil ekstraksi dari *Document Object Model* (DOM) yang digenerate oleh skrip otomatis.\n\n"
    
    for data in scraped_data:
        # Tampilkan jika ada data yang bermanfaat
        if not data["table_headers"] and len(data["buttons"]) < 3 and not data["inputs"]:
            continue
            
        md_content += f"#### • Menu: **{data['menu']}**\n"
        md_content += f"- **Endpoint**: `{data['url']}`\n"
        
        if data["table_headers"]:
            headers_str = ", ".join([f"`{h}`" for h in data["table_headers"]])
            md_content += f"- **Header Tabel**: {headers_str}\n"
            
        if data["buttons"]:
            # Abaikan tombol navigasi umum
            ignored_buttons = ["Kembali", "Keluar", "Admin", "Simpan"]
            valid_btns = [b for b in data["buttons"] if b not in ignored_buttons]
            if valid_btns:
                buttons_str = ", ".join([f"`{b}`" for b in valid_btns])
                md_content += f"- **Tombol/Aksi/Filter**: {buttons_str}\n"
                
        if data["inputs"]:
            inputs_str = ", ".join([f"`{i}`" for i in data["inputs"] if i.strip() != "input:"])
            if inputs_str:
                md_content += f"- **Field Input Modal**: {inputs_str}\n"
                
        if data["cards"]:
            md_content += f"- **Informasi HTML Card / Panel**: \n"
            for card in data["cards"]:
                md_content += f"  - `{card}`\n"
            
        md_content += "\n"
        
    # Append to file
    try:
        with open(MARKDOWN_FILE, "a", encoding="utf-8") as f:
            f.write(md_content)
        print(f"Berhasil menambahkan hasil scraping ke {MARKDOWN_FILE}")
    except Exception as e:
        print(f"Gagal menulis ke file Markdown: {str(e)}")

if __name__ == "__main__":
    asyncio.run(scrape_klinik())
