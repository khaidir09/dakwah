from playwright.sync_api import sync_playwright
import time

def test_login_and_create_post():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        # 1. Login
        print("Navigating to login...")
        page.goto('http://127.0.0.1:8000/login', timeout=60000)
        time.sleep(2)
        print("Taking screenshot of login...")
        page.screenshot(path='/home/jules/verification/login.png', full_page=True)

        print("Filling form...")
        page.fill('input[id="email"]', 'admin@example.com')
        page.fill('input[id="password"]', 'password')
        page.click('button[type="submit"]')
        time.sleep(2)

        print("Navigating to create post...")
        # 2. Go to Create Post Page
        page.goto('http://127.0.0.1:8000/admin/posts/create', timeout=60000)
        time.sleep(2)

        # 3. Screenshot
        print("Taking screenshot...")
        page.screenshot(path='/home/jules/verification/post_create.png', full_page=True)

        browser.close()
        print("Done!")

if __name__ == '__main__':
    test_login_and_create_post()
