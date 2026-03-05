import os
import time
import random
import threading
from flask import Flask, render_template_string, request, jsonify
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from webdriver_manager.core.os_manager import ChromeType

# إعداد تطبيق الويب - wa7m.com
app = Flask(__name__)

# تحديد مسار الجلسة لضمان بقاء تسجيل الدخول
SESSION_DIR = os.path.join(os.getcwd(), "wahm_session")

# واجهة المستخدم البسيطة والاحترافية
HTML_PAGE = """
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم واهم | WA7M.COM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #000; color: #0f0; font-family: 'Courier New', monospace; }
        .card { border: 1px solid #0f0; box-shadow: 0 0 15px #0f0; background: rgba(0,20,0,0.8); }
        input { background: #111 !important; border: 1px solid #0f0 !important; color: #0f0 !important; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="card w-full max-w-md p-8 rounded-2xl">
        <h1 class="text-3xl font-bold text-center mb-6 italic">WA7M SYSTEM</h1>
        <p class="text-center text-xs mb-8 text-green-400">User: MHGOR967</p>
        
        <div class="space-y-4">
            <input type="text" id="url" class="w-full p-3 rounded-lg" placeholder="ضع رابط الفيديو هنا">
            <button onclick="startTask()" id="btn" class="w-full bg-green-600 hover:bg-green-500 text-black font-bold py-4 rounded-xl transition-all">بدء الرشق التلقائي 🚀</button>
            <div id="status" class="text-center text-sm mt-4"></div>
        </div>
    </div>

    <script>
        async function startTask() {
            const btn = document.getElementById('btn');
            const status = document.getElementById('status');
            const url = document.getElementById('url').value;
            
            if(!url) return alert("الرابط مطلوب يا واهم!");
            
            btn.disabled = true;
            status.innerText = "[*] جاري تنفيذ العملية في الخلفية...";

            const res = await fetch('/api/run', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ url: url })
            });
            const data = await res.json();
            status.innerText = data.message;
            btn.disabled = false;
        }
    </script>
</body>
</html>
"""

def execute_automation(url):
    options = Options()
    options.add_argument("--headless")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument(f"--user-data-dir={SESSION_DIR}")
    
    driver = None
    try:
        service = Service(ChromeDriverManager(chrome_type=ChromeType.CHROMIUM).install())
        driver = webdriver.Chrome(service=service, options=options)
        driver.get(url)
        time.sleep(8)
        
        # محاولة عمل لايك تلقائي كمثال
        try:
            like_btn = driver.find_element(By.XPATH, "//*[local-name()='svg' and @aria-label='Like']")
            like_btn.click()
            print(f"Success for {url}")
        except:
            print("Like button not found")
            
    except Exception as e:
        print(f"Automation Error: {e}")
    finally:
        if driver: driver.quit()

@app.route('/')
def home():
    return render_template_string(HTML_PAGE)

@app.route('/api/run', methods=['POST'])
def run_api():
    data = request.json
    threading.Thread(target=execute_automation, args=(data['url'],)).start()
    return jsonify({"message": "✔ تم استلام الطلب وبدأ التنفيذ!"})

if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    app.run(host='0.0.0.0', port=port)

