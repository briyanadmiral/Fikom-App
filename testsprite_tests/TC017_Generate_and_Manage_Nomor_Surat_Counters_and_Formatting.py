import asyncio
from playwright import async_api

async def run_test():
    pw = None
    browser = None
    context = None

    try:
        # Start a Playwright session in asynchronous mode
        pw = await async_api.async_playwright().start()

        # Launch a Chromium browser in headless mode with custom arguments
        browser = await pw.chromium.launch(
            headless=True,
            args=[
                "--window-size=1280,720",         # Set the browser window size
                "--disable-dev-shm-usage",        # Avoid using /dev/shm which can cause issues in containers
                "--ipc=host",                     # Use host-level IPC for better stability
                "--single-process"                # Run the browser in a single process mode
            ],
        )

        # Create a new browser context (like an incognito window)
        context = await browser.new_context()
        context.set_default_timeout(5000)

        # Open a new page in the browser context
        page = await context.new_page()

        # Navigate to your target URL and wait until the network request is committed
        await page.goto("http://localhost:8000", wait_until="commit", timeout=10000)

        # Wait for the main page to reach DOMContentLoaded state (optional for stability)
        try:
            await page.wait_for_load_state("domcontentloaded", timeout=3000)
        except async_api.Error:
            pass

        # Iterate through all iframes and wait for them to load as well
        for frame in page.frames:
            try:
                await frame.wait_for_load_state("domcontentloaded", timeout=3000)
            except async_api.Error:
                pass

        # Interact with the page elements to simulate user flow
        # -> Navigate to http://localhost:8000
        await page.goto("http://localhost:8000", wait_until="commit", timeout=10000)
        
        # -> Fill email and password fields and submit the login form to access the application.
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=html/body/div/div[2]/div[2]/form/div[1]/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('agustina.anggitasari@unika.ac.id')
        
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=html/body/div/div[2]/div[2]/form/div[2]/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('123456')
        
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div/div[2]/div[2]/form/button').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Click the 'Buat Surat' button to open the create-letter flow (then select Surat Tugas to create the first letter).
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/div[1]/div[2]/div[3]/button').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Click the 'Surat Tugas' option to open the create Surat Tugas form and begin creating letters.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/div[1]/div[2]/div[3]/div/a[1]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Request the server-generated next letter number (click 'Reset/Siapkan nomor' button), extract the generated 'Nomor Surat Lengkap' and 'No. Urut', fill 'Judul Umum Surat', then save draft to create the first Surat Tugas.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[1]/div/div[2]/div/div[1]/div[5]/div/div/button[2]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Request server-generated nomor urut by clicking 'Siapkan nomor' (btn-reset-nomor), wait for response, then extract visible values of: input#nomor_surat_lengkap_display, input#nomor_urut, input#no_surat_manual so the generated number (if any) can be recorded.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[1]/div/div[2]/div/div[1]/div[5]/div/div/button[2]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Populate required form fields to allow server number generation (fill 'Judul Umum Surat'), open Klasifikasi selector, and open Penandatangan selector so the form can be completed or alternative manual-number path used.
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[1]/div/div[2]/div/div[1]/div[3]/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('Penugasan Test - Verifikasi Nomor 1')
        
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[1]/div/div[2]/div/div[1]/div[4]/div/div[1]/div[1]/div/button').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[2]/div[2]/div[2]/div/span/span[1]/span/span[1]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Select a classification from the Klasifikasi modal (choose A.1.1 - Promosi) so the form has a classification value, then proceed to select Penandatangan and set required fields for saving draft.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[1]/div/div[2]/div/div[1]/div[4]/div/div[1]/div[2]/div/div/div[2]/div[3]/div[1]/div/div[1]/button').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Open the 'Pilih Penandatangan' combobox so a penandatangan can be selected (required field). After that, choose a signer and then either enter a Nomor Surat Manual or add recipients and save the draft.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[2]/div[2]/div[2]/div/span/span[1]/span').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Select a penandatangan, fill Nomor Surat Manual (fallback since automatic generation failed), and save draft to create the first Surat Tugas. After save, extract the assigned number from the resulting page/listing.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/span/span/span[2]/ul/li[2]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        frame = context.pages[-1]
        # Input text
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[1]/div/div[2]/div/div[1]/div[6]/input').nth(0)
        await page.wait_for_timeout(3000); await elem.fill('ST-2026-001')
        
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[2]/div[2]/div[2]/button[1]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Dismiss the warning modal, then reveal the recipient selection controls (Pilih dari Pengguna Sistem or Tambah Penerima Luar) so a recipient can be added. After a recipient is added, save draft again to create the first Surat Tugas and capture the assigned nomor.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[5]/div/div[6]/button[1]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Add a recipient by opening 'Pilih dari Pengguna Sistem' so a system user can be selected as recipient (required), then save draft to create the first Surat Tugas.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[2]/div[1]/div[2]/button[1]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Add a recipient now. Use the modal to add a recipient. If selecting a system user is problematic, add an external/manual recipient. Then save draft to create the first Surat Tugas and extract the assigned nomor from the resulting draft/listing.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[2]/div[1]/div[2]/button[2]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Select a recipient from the system list (check Prof. BERNARDINUS HARNADI's checkbox), click 'Simpan Pilihan' to add recipient(s) to the surat, then click 'Simpan Draft' to create the first Surat Tugas and capture the assigned nomor from the resulting page.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/div[2]/div/div/div[2]/div/div[2]/div/table/tbody/tr[1]/td[1]/input').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/div[2]/div/div/div[3]/button[2]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[2]/div[1]/section[2]/div/form/div/div[2]/div[2]/div[2]/button[1]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        # -> Confirm save-draft by clicking 'Ya, simpan draft' to finalize creation of the first Surat Tugas, then capture the assigned nomor from the resulting page.
        frame = context.pages[-1]
        # Click element
        elem = frame.locator('xpath=html/body/div[6]/div/div[6]/button[3]').nth(0)
        await page.wait_for_timeout(3000); await elem.click(timeout=5000)
        
        await asyncio.sleep(5)

    finally:
        if context:
            await context.close()
        if browser:
            await browser.close()
        if pw:
            await pw.stop()

asyncio.run(run_test())
    