import asyncio
import os
from playwright.async_api import async_playwright

async def scraper_ninesoft_transaksi():
    print("Memulai Ninesoft Transaction Scraper...")
    scraped_data = []

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(viewport={"width": 1280, "height": 720})
        page = await context.new_page()

        print("1. Menuju Halaman Login Ninesoft")
        await page.goto("http://web.ninesoft.id/")
        await page.wait_for_timeout(3000)

        # Mengisi form login
        await page.locator("input[name='email']").fill("web@demo.com")
        await page.locator("input[name='password']").fill("admin")
                
        # Click login button
        login_btn = page.locator("#login_submit")
        await login_btn.click()
        print("2. Submit Auth Form...")
        await page.wait_for_load_state("networkidle")
        await page.wait_for_timeout(5000) # Tunggu dashboard load

        # Sidebar Expand
        print("3. Mengekspansi Sidebar Transaksi...")
        try:
            # Klik kategori utama (Kas & Bank, Purchasing, Inventory, Penjualan)
            main_navs = await page.locator("a:has-text('Purchasing'), span:has-text('Purchasing'), a:has-text('Penjualan'), span:has-text('Penjualan'), a:has-text('Inventory'), span:has-text('Inventory'), a:has-text('Kas & Bank'), span:has-text('Kas & Bank')").all()
            for nav in main_navs:
                if await nav.is_visible():
                    try:
                        await nav.click(timeout=1000)
                        await page.wait_for_timeout(1000)
                    except:
                        pass
        except Exception as e:
            print("Gagal klik menu transaksi utama:", e)

        # Koleksi Link
        links = await page.locator("a").all()
        urls_to_visit = []
        valid_paths = ["/purchasing/", "/sales/", "/inventory/", "/bkkb/", "/finance/"]
        
        for link in links:
            href = await link.get_attribute("href")
            text = await link.inner_text()
            if href and not "javascript" in href.lower():
                is_trans = any(p in href.lower() for p in valid_paths)
                if is_trans:
                    full_url = href if href.startswith("http") else f"http://web.ninesoft.id{href if href.startswith('/') else '/' + href}"
                    if full_url not in [u['href'] for u in urls_to_visit] and text.strip():
                        urls_to_visit.append({"href": full_url, "text": text.strip()})
                        print(f"Menu Transaksi Ditemukan: {text.strip()} -> {full_url}")

        print(f"Total ditemukan {len(urls_to_visit)} menu Transaksional. Memulai Ekstraksi...")

        for menu in urls_to_visit:
            url = menu['href']
            print(f"   => Scraping: {menu['text']} ({url})")
            
            try:
                await page.goto(url)
                await page.wait_for_load_state("domcontentloaded")
                await page.wait_for_timeout(3500) # Loading transaksi biasanya lebih lambat
                
                # Biarkan AJAX table loading sebentar
                try: 
                    await page.wait_for_selector(".loader, .overlay", state="hidden", timeout=3000)
                except: pass

                menu_data = {
                    "menu": menu['text'],
                    "url": url,
                    "table_headers": [],
                    "buttons": [],
                    "inputs": [],
                    "cards": [],
                    "validations": [],
                    "tabs": {}
                }
                
                # Ekstrak Info Panel/Card (Dashboard per menu misalnya)
                cards = await page.locator(".info-box-text, .small-box, .widget, .card-title, .box-title").all()
                for c in cards:
                    txt = await c.inner_text()
                    if txt and len(txt.strip()) > 3 and txt.strip() not in menu_data["cards"]:
                        menu_data["cards"].append(txt.strip().replace('\n', ' | '))
                        
                # Ekstrak Header Tabel List Data
                headers = await page.locator("th").all()
                for th in headers:
                    txt = await th.inner_text()
                    if txt.strip() and txt.strip() not in menu_data["table_headers"]:
                        menu_data["table_headers"].append(txt.replace('\n', ' ').strip())
                        
                # Ekstrak Tombol Main Page
                buttons = await page.locator("button, a.btn").all()
                for btn in buttons:
                    txt = await btn.inner_text()
                    if txt.strip() and len(txt) < 30 and txt.strip() not in menu_data["buttons"]:
                        menu_data["buttons"].append(txt.strip())
                        
                # Coba cari tombol TAMBAH TRANSAKSI
                btnAdd = page.locator("button:has-text('Tambah'), a:has-text('Tambah'), button:has-text('Add'), a:has-text('Add'), button:has-text('Baru'), a:has-text('Baru'), a.btn-success, button.btn-success").first
                if await btnAdd.is_visible():
                    try:
                        await btnAdd.click()
                        await page.wait_for_timeout(3000)
                        
                        # Deteksi Tabs di halaman/modal Form
                        tabs_elements = await page.locator(".nav-tabs a, [data-toggle='tab'], .nav-pills a, [role='tab']").all()
                        
                        if tabs_elements and len(tabs_elements) > 1:
                            for tab in tabs_elements:
                                tab_name = await tab.inner_text()
                                if not tab_name.strip(): continue
                                tab_name = tab_name.strip()
                                menu_data["tabs"][tab_name] = []
                                
                                try:
                                    await tab.click(timeout=1000)
                                    await page.wait_for_timeout(1000)
                                except: pass
                                
                                inputs = await page.locator("input:visible, select:visible, textarea:visible").all()
                                for inp in inputs:
                                    placeholder = await inp.get_attribute("placeholder") or ""
                                    name_attr = await inp.get_attribute("name") or ""
                                    id_attr = await inp.get_attribute("id") or ""
                                    label = placeholder or name_attr or id_attr
                                    if label and (label not in menu_data["tabs"][tab_name]):
                                        menu_data["tabs"][tab_name].append(label)
                        else:
                            # Standard Scan
                            inputs = await page.locator("input:visible, select:visible, textarea:visible").all()
                            for inp in inputs:
                                placeholder = await inp.get_attribute("placeholder") or ""
                                name_attr = await inp.get_attribute("name") or ""
                                id_attr = await inp.get_attribute("id") or ""
                                label = placeholder or name_attr or id_attr
                                if label and (label not in menu_data["inputs"]):
                                    menu_data["inputs"].append(label)
                                
                        # Abort POST request untuk testing Simpan (Validasi Pancingan)
                        async def abort_post(route):
                            if route.request.method in ["POST", "PUT", "DELETE"]:
                                await route.abort()
                            else:
                                await route.continue_()
                                
                        await page.route("**/*", abort_post)
                        
                        # Klik Save/Simpan
                        btnSave = page.locator("button:has-text('Simpan'), button:has-text('Save'), button:has-text('Proses'), button[type='submit']").first
                        if await btnSave.is_visible():
                            await btnSave.click(force=True)
                            await page.wait_for_timeout(2000)
                            
                            errors = await page.locator(".text-danger, .invalid-feedback, .help-block, input:invalid").all()
                            for err in errors:
                                e_txt = await err.evaluate("el => el.validationMessage || el.innerText")
                                if e_txt and len(e_txt) > 3:
                                    menu_data["validations"].append(e_txt.strip())
                                    
                        await page.unroute("**/*", abort_post)
                        
                        # Tutup modal/form (kalau modal bisa tutup, kalau navigasi kembali)
                        btnCancel = page.locator("button:has-text('Batal'), a:has-text('Batal'), button:has-text('Cancel'), .close").first
                        if await btnCancel.is_visible():
                            await btnCancel.click()
                        else:
                            await page.go_back()
                    except Exception as ef:
                        print("Error di form tambah/transaksi:", str(ef))
                        
                scraped_data.append(menu_data)
                
            except Exception as ex:
                print("Gagal di menu", url, str(ex))

        await browser.close()
        
        print("Selesai ekstrak Transaksi! Menulis ke Markdown...")
        md_file = os.path.join(os.path.dirname(__file__), "catatan_ninesoft.md")
        
        md_content = "\n---\n\n## 15. Bukti Scraping Otomatis Ninesoft - Modul Transaksional\n\nBagian ini dihasilkan secara mandiri oleh skrip `catatan_ninesoft_transaksi.py` (Bot AntiGravity). Berisi jejak ekstraksi dari form operasional harian yang mencakup Purchasing, Penjualan, Inventory, dan Finance Kas/Bank.\n\n"
        
        for data in scraped_data:
            if not data["table_headers"] and len(data["buttons"]) < 2:
                continue
                
            md_content += f"#### 💼 Modul: **{data['menu']}**\n"
            md_content += f"- **Endpoint GUI**: `{data['url']}`\n"
            
            if data["table_headers"]:
                hdrs = ", ".join([f"`{h}`" for h in data["table_headers"]])
                md_content += f"- **Konstruksi Data Tabel (Grids)**: {hdrs}\n"
                
            if data["buttons"]:
                valid_btns = [b for b in data["buttons"] if len(b) > 2]
                btns = ", ".join([f"`{b}`" for b in valid_btns])
                md_content += f"- **Fitur Modul**: {btns}\n"
                
            if data["tabs"]:
                md_content += f"- **Struktur Tab Transaksi & Input Fields**:\n"
                for tab_name, tab_inputs in data["tabs"].items():
                    inps = ", ".join([f"`{i}`" for i in tab_inputs]) if tab_inputs else "`Tidak ada field tertangkap`"
                    md_content += f"  - **Tab {tab_name}** ➔ {inps}\n"
                    
            if data["inputs"] and not data["tabs"]:
                inps = ", ".join([f"`{i}`" for i in data["inputs"]])
                md_content += f"- **Area Input Utama (Head/Lines)**: {inps}\n"
                
            if data["cards"]:
                md_content += f"- **Parameter Informasi Panel**: \n"
                for c in data["cards"]:
                    md_content += f"  - `{c}`\n"
                    
            if data["validations"]:
                md_content += f"- **Alert & Validasi (Contingency)**: \n"
                for v in data["validations"]:
                    # Hilangkan double escaping atau duplikat
                    md_content += f"  - 🔴 `{v}`\n"
                    
            md_content += "\n"
            
        # Hapus bagian bab 15 yang lama di file md jika ada (untuk safety write overwrite loop)
        with open(md_file, "r", encoding="utf-8") as f:
            lines = f.readlines()
        
        new_lines = []
        for line in lines:
            if "## 15. Bukti Scraping Otomatis Ninesoft - Modul Transaksional" in line:
                break
            new_lines.append(line)
            
        with open(md_file, "w", encoding="utf-8") as f:
            f.writelines(new_lines)
            
        with open(md_file, "a", encoding="utf-8") as f:
            f.write(md_content)
            
        print("Tuntas! Data Modul Transaksi sudah terlampir pada dokumen.")

if __name__ == "__main__":
    asyncio.run(scraper_ninesoft_transaksi())
