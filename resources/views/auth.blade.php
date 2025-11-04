<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Giriş yap kayit ol</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji'; margin: 0; padding: 2rem; display: grid; place-items: start center; min-height: 100vh; background: #0b1020; color: #e6e6e6; }
    .card { width: 100%; max-width: 780px; background: #11172b; border: 1px solid #1f2a44; border-radius: 12px; padding: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,.35); }
    .tabs { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .tab { padding: .6rem 1rem; background: #0d1426; border: 1px solid #1f2a44; color: #c7d2fe; border-radius: 8px; cursor: pointer; }
    .tab.active { background: #1b2550; border-color: #3b82f6; }
    form { display: grid; gap: .75rem; margin-top: .5rem; }
    label { font-size: .9rem; color: #9fb0ff; }
    input { padding: .6rem .7rem; border-radius: 8px; border: 1px solid #273353; background: #0d1426; color: #e6e6e6; }
    button { padding: .7rem 1rem; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; }
    button.secondary { background: #334155; }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    .muted { color: #9aa4bf; font-size: .9rem; }
    .log { white-space: pre-wrap; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; background: #0a0f1f; border: 1px dashed #26314f; padding: .75rem; border-radius: 10px; min-height: 56px; color: #b6c2ff; }
    .token { display: grid; grid-template-columns: 1fr auto; gap: .5rem; align-items: center; }
    .ok { color: #22c55e; }
    .err { color: #f87171; }
    .status-icon { 
      display: inline-block; 
      width: 16px; 
      height: 16px; 
      border-radius: 50%; 
      margin-right: 8px; 
      vertical-align: middle;
    }
    .ok .status-icon { background: #22c55e; }
    .err .status-icon { background: #f87171; }
    .ok .status-icon::before { content: '✓'; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
    .err .status-icon::before { content: '✗'; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
  </style>
</head>
<body>
  <div class="card">
    <div class="tabs">
      <button class="tab active" id="tab-register" onclick="switchTab('register')">Kayıt Ol</button>
      <button class="tab" id="tab-login" onclick="switchTab('login')">Giriş Yap</button>
    </div>

    <section id="section-register">
      <form id="form-register">
        <div class="row">
          <div>
            <label>Ad Soyad</label>
            <input type="text" name="name" placeholder="Adınız" required />
          </div>
          <div>
            <label>E-posta</label>
            <input type="email" name="email" placeholder="mail@ornek.com" required />
          </div>
        </div>
        <div class="row">
          <div>
            <label>Şifre</label>
            <input type="password" name="password" placeholder="En az 8 karakter" minlength="8" required />
          </div>
          <div>
            <label>Şifre (Tekrar)</label>
            <input type="password" name="password_confirmation" placeholder="Şifre tekrar" minlength="8" required />
          </div>
        </div>
        <div style="display:flex; gap:.5rem; align-items:center;">
          <button type="submit">Kayıt Ol</button>
          <span class="muted">Zaten hesabın var mı? <a href="#" onclick="switchTab('login')">Giriş yap</a></span>
        </div>
      </form>
    </section>

    <section id="section-login" style="display:none;">
      <form id="form-login">
        <div class="row">
          <div>
            <label>E-posta</label>
            <input type="email" name="email" placeholder="mail@ornek.com" required />
          </div>
          <div>
            <label>Şifre</label>
            <input type="password" name="password" placeholder="Şifre" minlength="8" required />
          </div>
        </div>
        <div style="display:flex; gap:.5rem; align-items:center;">
          <button type="submit">Giriş Yap</button>
          <button type="button" class="secondary" onclick="logout()">Çıkış Yap</button>
        </div>
      </form>
    </section>

    <div style="margin-top:1rem;">
      <div class="muted">Bearer Token</div>
      <div class="token">
        <input id="token" readonly placeholder="Henüz token yok" />
        <button type="button" class="secondary" onclick="copyToken()">Kopyala</button>
      </div>
    </div>

    <div style="margin-top:1rem;">
      <div class="muted">Sonuç</div>
      <div id="log" class="log ok"><span class="status-icon"></span>Hazır! Formları kullanarak API'yi test edebilirsin.</div>
    </div>
  </div>

  <script>
    const baseUrl = `${location.protocol}//${location.host}`;

    function switchTab(tab){
      document.getElementById('tab-register').classList.toggle('active', tab==='register');
      document.getElementById('tab-login').classList.toggle('active', tab==='login');
      document.getElementById('section-register').style.display = tab==='register' ? '' : 'none';
      document.getElementById('section-login').style.display = tab==='login' ? '' : 'none';
    }

    function setLog(obj, ok=true){
      const el = document.getElementById('log');
      el.innerHTML = `<span class="status-icon"></span>` + JSON.stringify(obj, null, 2);
      el.classList.toggle('ok', ok); 
      el.classList.toggle('err', !ok);
    }

    function saveToken(token){
      try { localStorage.setItem('auth_token', token || ''); } catch {}
      document.getElementById('token').value = token || '';
    }

    function copyToken(){
      const t = document.getElementById('token');
      t.select(); t.setSelectionRange(0, 99999);
      document.execCommand('copy');
    }

    async function postJSON(url, data){
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await res.json().catch(()=>({ message: 'JSON parse error', status: res.status }));
      if(!res.ok){ throw { status: res.status, ...json }; }
      return json;
    }

    document.getElementById('form-register').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const data = Object.fromEntries(fd.entries());
      try {
        const out = await postJSON(`${baseUrl}/api/register`, data);
        // API response: { success, message, data: { user, token } }
        const token = out?.data?.token;
        if(token) saveToken(token);
        setLog(out, true);
        switchTab('login');
      } catch(err){
        setLog(err, false);
      }
    });

    document.getElementById('form-login').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const data = Object.fromEntries(fd.entries());
      try {
        const out = await postJSON(`${baseUrl}/api/login`, data);
        const token = out?.data?.token;
        if(token) saveToken(token);
        setLog(out, true);
      } catch(err){
        setLog(err, false);
      }
    });

    async function logout(){
      const token = localStorage.getItem('auth_token');
      if(!token){ setLog({ message: 'Önce giriş yapmalısın' }, false); return; }
      try {
        const res = await fetch(`${baseUrl}/api/logout`, { method: 'POST', headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } });
        const json = await res.json().catch(()=>({ status: res.status }));
        if(!res.ok) throw json;
        saveToken('');
        setLog(json, true);
      } catch(err){
        setLog(err, false);
      }
    }

    // Init
    saveToken(localStorage.getItem('auth_token') || '');
  </script>
</body>
</html>
