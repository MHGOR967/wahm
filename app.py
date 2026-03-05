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

# مسار الجلسة المعتمد في ريندر
SESSION_DIR = "/opt/render/project/src/wahm_session"
if not os.path.exists(SESSION_DIR):
    os.makedirs(SESSION_DIR)

HTML_PAGE = """
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بوابة واهم | WA7M.COM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #000; color: #00ff00; font-family: monospace; }
        .wa7m-card { border: 1px solid #0f0; box-shadow: 0 0 15px #0f0; background: #050505; }
        input { background: #111 !important; border: 1px solid #0f0 !important; color: #0f0 !important; }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4">
    <div class="wa7m-card p-8 rounded-2xl w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-6">WA7M SYSTEM</h1>
        <div class="space-y-4">
            <input type="text" id="u" class="w-full p-3 rounded" placeholder="يوزر انستا">
            <input type="password" id="p" class="w-full p-3 rounded" placeholder="كلمة المرور">
            <button onclick="login()" id="lBtn" class="w-full bg-blue-600 text-white p-3 rounded font-bold">حفظ الجلسة 🔑</button>
            <hr class="border-green-900 my-4">
            <input type="text" id="url" class="w-full p-3 rounded" placeholder="رابط المنشور">
            <button onclick="run()" id="rBtn" class="w-full bg-green-600 text-black p-4 rounded font-bold text-lg">إطلاق الرشق 🚀</button>
            <p id="st" class="text-center text-xs mt-4"></p>
        </div>
    </div>
    <script>
        async function login() {
            const b = document.getElementById('lBtn'); b.innerText = "جاري الحفظ...";
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({u: document.getElementById('u').value, p: document.getElementById('p').value})
            });
            const data = await res.json(); alert(data.msg); b.innerText = "حفظ الجلسة 🔑";
        }
        async function run() {
            const s = document.getElementById('st'); s.innerText = "جاري التنفيذ...";
            const res = await fetch('/api/run', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({url: document.getElementById('url').value})
            });
            const data = await res.json(); s.innerText = data.msg;
        }
    </script>
</body>
</html>
"""

def get_driver():
    chrome_options = Options()
    chrome_options.add_argument("--headless")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument(f"--user-data-dir={SESSION_DIR}")
    # تحديث لـ ريندر: استخدام المسار الافتراضي لكروميوم
    service = Service(ChromeDriverManager(chrome_type=ChromeType.CHROMIUM).install())
    return webdriver.Chrome(service=service, options=chrome_options)

@app.route('/')
def index(): return render_template_string(HTML_PAGE)

@app.route('/api/login', methods=['POST'])
def api_login():
    data = request.json
    def login_task():
        driver = get_driver()
        try:
            driver.get("https://www.instagram.com/accounts/login/")
            time.sleep(5)
            driver.find_element(By.NAME, "username").send_keys(data['u'])
            p_box = driver.find_element(By.NAME, "password")
            p_box.send_keys(data['p'])
            p_box.send_keys(Keys.ENTER)
            time.sleep(10)
        finally: driver.quit()
    threading.Thread(target=login_task).start()
    return jsonify({"msg": "جاري محاولة الدخول في الخلفية..."})

@app.route('/api/run', methods=['POST'])
def api_run():
    url = request.json['url']
    def run_task():
        driver = get_driver()
        try:
            driver.get(url)
            time.sleep(7)
            driver.find_element(By.XPATH, "//*[local-name()='svg' and @aria-label='Like']").parent.click()
        finally: driver.quit()
    threading.Thread(target=run_task).start()
    return jsonify({"msg": "بدأ الهجوم!"})

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=int(os.environ.get("PORT", 5000)))

