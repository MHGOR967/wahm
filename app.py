import os
import time
import threading
from flask import Flask, render_template_string, request, jsonify
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.keys import Keys
from webdriver_manager.chrome import ChromeDriverManager
from webdriver_manager.core.os_manager import ChromeType

app = Flask(__name__)

# مسار الجلسة في سيرفر Render
SESSION_DIR = os.path.join(os.getcwd(), "wahm_session")

HTML_PAGE = """
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحكم واهم | WA7M.COM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #000; color: #0f0; font-family: monospace; }
        .box { border: 1px solid #0f0; box-shadow: 0 0 20px #0f0; background: #050505; }
        input { background: #111 !important; border: 1px solid #0f0 !important; color: #0f0 !important; }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4">
    <div class="box p-8 rounded-2xl w-full max-w-md mb-6">
        <h2 class="text-xl font-bold mb-4 border-b border-green-900 pb-2">🔑 تسجيل الدخول للسيرفر</h2>
        <input type="text" id="user" class="w-full p-3 rounded mb-2" placeholder="اسم المستخدم (انستا)">
        <input type="password" id="pass" class="w-full p-3 rounded mb-4" placeholder="كلمة المرور">
        <button onclick="login()" id="loginBtn" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-500">حفظ الجلسة في السيرفر</button>
    </div>

    <div class="box p-8 rounded-2xl w-full max-w-md">
        <h2 class="text-xl font-bold mb-4 border-b border-green-900 pb-2">🚀 إطلاق الرشق</h2>
        <input type="text" id="url" class="w-full p-3 rounded mb-4" placeholder="رابط المنشور المستهدف">
        <button onclick="boost()" id="boostBtn" class="w-full bg-green-600 text-black font-bold py-4 rounded hover:bg-green-400">بدء العملية</button>
        <p id="msg" class="text-center mt-4 text-xs"></p>
    </div>

    <script>
        async function login() {
            const btn = document.getElementById('loginBtn');
            btn.innerText = "جاري الدخول...";
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({u: document.getElementById('user').value, p: document.getElementById('pass').value})
            });
            const data = await res.json();
            alert(data.msg);
            btn.innerText = "حفظ الجلسة في السيرفر";
        }

        async function boost() {
            const url = document.getElementById('url').value;
            document.getElementById('msg').innerText = "تم إرسال الطلب للسيرفر...";
            const res = await fetch('/api/run', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({url: url})
            });
            const data = await res.json();
            document.getElementById('msg').innerText = data.msg;
        }
    </script>
</body>
</html>
"""

def get_driver(headless=True):
    options = Options()
    if headless: options.add_argument("--headless")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument(f"--user-data-dir={SESSION_DIR}")
    service = Service(ChromeDriverManager(chrome_type=ChromeType.CHROMIUM).install())
    return webdriver.Chrome(service=service, options=options)

@app.route('/')
def home(): return render_template_string(HTML_PAGE)

@app.route('/api/login', methods=['POST'])
def api_login():
    data = request.json
    def do_login():
        driver = get_driver(headless=True)
        try:
            driver.get("https://www.instagram.com/accounts/login/")
            time.sleep(5)
            driver.find_element(By.NAME, "username").send_keys(data['u'])
            p_box = driver.find_element(By.NAME, "password")
            p_box.send_keys(data['p'])
            p_box.send_keys(Keys.ENTER)
            time.sleep(10) # ننتظر تسجيل الدخول
            print("Login process finished in background")
        finally: driver.quit()
    
    threading.Thread(target=do_login).start()
    return jsonify({"msg": "بدأت عملية تسجيل الدخول في الخلفية، انتظر دقيقة ثم جرب الرشق!"})

@app.route('/api/run', methods=['POST'])
def run():
    url = request.json['url']
    def task():
        driver = get_driver(headless=True)
        try:
            driver.get(url)
            time.sleep(7)
            driver.find_element(By.XPATH, "//*[local-name()='svg' and @aria-label='Like']").click()
        except Exception as e: print(f"Error: {e}")
        finally: driver.quit()
    
    threading.Thread(target=task).start()
    return jsonify({"msg": "الطلب قيد المعالجة!"})

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=int(os.environ.get("PORT", 5000)))

