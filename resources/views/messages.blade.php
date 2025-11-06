<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mesaj anasayfa</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial; margin: 0; padding: 2rem; min-height: 100vh; background: #0b1020; color: #e6e6e6; }
    .container { max-width: 1000px; margin: 0 auto; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; gap: 1rem; }
    .header a { color: #93c5fd; text-decoration: none; }
    .card { background: #10172b; border: 1px solid #1f2a44; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    label { font-size: .9rem; color: #9fb0ff; }
    input, textarea, select { padding: .6rem .7rem; border-radius: 8px; border: 1px solid #273353; background: #0d1426; color: #e6e6e6; width: 100%; }
    textarea { min-height: 90px; resize: vertical; }
    .actions { display: flex; gap: .5rem; align-items: center; }
    button { padding: .6rem .9rem; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; }
    button.secondary { background: #334155; }
    button.danger { background: #dc2626; }
    .muted { color: #9aa4bf; font-size: .9rem; }
    .list { display: grid; gap: .75rem; }
    .msg { border: 1px solid #273353; border-radius: 10px; padding: .75rem; background: #0d1426; }
    .msg-header { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: .5rem; }
    .msg-title { font-weight: 600; color: #dbeafe; }
    .msg-meta { color: #9aa4bf; font-size: .85rem; }
    .log { white-space: pre-wrap; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; background: #0a0f1f; border: 1px dashed #26314f; padding: .75rem; border-radius: 10px; min-height: 72px; color: #b6c2ff; }
    .token { display: grid; grid-template-columns: 1fr auto auto auto; gap: .5rem; align-items: center; }
    .tabs { display: flex; gap: .5rem; margin-bottom: 1rem; }
    .tab { padding: .6rem 1rem; background: #0d1426; border: 1px solid #1f2a44; color: #c7d2fe; border-radius: 8px; cursor: pointer; }
    .tab.active { background: #1b2550; border-color: #3b82f6; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div>
        <strong>Messages CRUD</strong>
        <div class="muted">Bearer token ile API test sayfası</div>
      </div>
      <div class="actions">
        <a href="/auth">Auth Sayfası</a>
      </div>
    </div>

    <div class="card">
      <div class="muted">Token</div>
      <div class="token">
        <input id="token" placeholder="Bearer token girin veya /auth'tan alın" />
        <button class="secondary" onclick="saveToken()">Kaydet</button>
        <button class="secondary" onclick="copyToken()">Kopyala</button>
        <button onclick="clearToken()">Temizle</button>
      </div>
    </div>

    <div class="card">
      <div style="margin-bottom:.5rem; font-weight:600;">Yeni Mesaj Gönder</div>
      <div class="row">
        <div>
          <label>Alıcı Email</label>
          <input id="receiver_email" placeholder="Alıcı email adresi" type="email" />
        </div>
        <div>
          <label>Başlık</label>
          <input id="title" placeholder="Başlık" />
        </div>
      </div>
      <div style="margin-top:.75rem;">
        <label>İçerik</label>
        <textarea id="content" placeholder="Mesaj içeriği"></textarea>
      </div>
      <div class="actions" style="margin-top:.75rem;">
        <button onclick="createMessage()">Gönder</button>
      </div>
    </div>

    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <!-- <div class="tabs">
          <button class="tab active" id="tab-all" onclick="switchTab('all')">Tüm Mesajlar</button>
          <button class="tab" id="tab-sent" onclick="switchTab('sent')">Gönderilenler</button>
          <button class="tab" id="tab-inbox" onclick="switchTab('inbox')">Gelen Kutusu</button>
        </div> -->
        <button onclick="loadMessages()">📬 Mesajları Getir</button>
      </div>
      <div id="list" class="list"></div>
    </div>

    <div class="card">
      <div class="muted">Sonuç</div>
      <div id="log" class="log">Hazır ✅ Mesajları görmek için "📬 Mesajları Getir" butonuna tıklayın.</div>
    </div>
  </div>

  <script>
    const baseUrl = `${location.protocol}//${location.host}`;
    let currentTab = 'all'; // all, sent, inbox

    function getToken(){ return localStorage.getItem('auth_token') || ''; }
    function setLog(obj, ok=true){
      const el = document.getElementById('log');
      el.textContent = (ok? '✓ ' : '✗ ') + JSON.stringify(obj, null, 2);
    }
    function refreshTokenInput(){ document.getElementById('token').value = getToken(); }
    async function saveToken(){
      const token = document.getElementById('token').value.trim();
      localStorage.setItem('auth_token', token);
      setLog({ message: token ? 'Token kaydedildi' : 'Token temizlendi' }, true);
      if(token) {
        await loadMessages();
      }
    }
    function copyToken(){ const t = document.getElementById('token'); t.select(); t.setSelectionRange(0, 99999); document.execCommand('copy'); }
    function clearToken(){ localStorage.removeItem('auth_token'); refreshTokenInput(); setLog({ message: 'Token temizlendi' }, true); }

    async function api(path, options={}){
      const token = getToken();
      if(!token) throw { message: 'Önce /auth sayfasından giriş yap, token al' };
      const { method='GET', body=null } = options;
      const res = await fetch(`${baseUrl}${path}`, {
        method,
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          ...(body ? { 'Content-Type': 'application/json' } : {})
        },
        body: body ? JSON.stringify(body) : null
      });
      
      // JSON parse (hataya karşı güvenli)
      let json = {};
      const text = await res.text();
      try { json = text ? JSON.parse(text) : {}; } catch { json = {}; }

      // Hata oluştu
      if(!res.ok){
        const err = {
          statusCode: res.status,
          success: false,
          ...json
        };
        // statusCode yoksa ekle, mesaj yoksa default mesaj koy
        if(!err.statusCode) err.statusCode = res.status;
        if(!err.message) err.message = mapErrorMessage(res.status);
        throw err;
      }
      
      // Başarılı
      if(!json.statusCode) json.statusCode = res.status;
      return json;
    }

    function mapErrorMessage(status){
      const messages = {
        401: 'Geçersiz veya süresi dolmuş token',
        403: 'Bu işlem için yetkiniz yok',
        404: 'Kaynak bulunamadı',
        422: 'Doğrulama başarısız',
        500: 'Sunucu hatası'
      };
      return messages[status] || 'Bir hata oluştu';
    }

    async function loadMessages(){
      try {
        let out;
        if(currentTab === 'sent') out = await api('/api/messages/sent');
        else if(currentTab === 'inbox') out = await api('/api/messages/inbox');
        else out = await api('/api/messages');
        setLog(out, true);
        renderList(out?.data || []);
      } catch(err){ setLog(err, false); renderList([]); }
    }

    function switchTab(tab){
      currentTab = tab;
      // tab görünümünü güncelle
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.getElementById(`tab-${tab}`).classList.add('active');
      // Manuel olarak "Mesajları Getir" butonuna basılmalı
    }



    async function createMessage(){
      const receiver_email = document.getElementById('receiver_email').value.trim();
      const title = document.getElementById('title').value.trim();
      const content = document.getElementById('content').value.trim();
      if(!receiver_email || !title || !content){ 
        setLog({message: 'Alıcı email, başlık ve içerik gerekli' }, false); 
        return; 
      }
      try {
        const out = await api('/api/messages', { method: 'POST', body: { receiver_email, title, content } });
        setLog(out, true);
        document.getElementById('receiver_email').value='';
        document.getElementById('title').value='';
        document.getElementById('content').value='';
      } catch(err){ setLog(err, false); }
    }

    function renderList(items){
      const list = document.getElementById('list');
      if(!items.length){ list.innerHTML = '<div class="muted">Mesaj yok</div>'; return; }
      list.innerHTML = '';
      for(const m of items){
        const el = document.createElement('div');
        el.className = 'msg';
        
        // Gönderen ve alıcı bilgisini göster
        const senderName = m.sender?.name || 'Bilinmiyor';
        const receiverName = m.receiver?.name || 'Bilinmiyor';
        
        el.innerHTML = `
          <div class="msg-header">
            <div>
              <div class="msg-title" id="t-${m.id}">${escapeHtml(m.title || '')}</div>
              <div class="msg-meta">
                #${m.id} · Gönderen: ${escapeHtml(senderName)} → Alıcı: ${escapeHtml(receiverName)}
                <br>${new Date(m.created_at || m.updated_at || Date.now()).toLocaleString('tr-TR')}
              </div>
            </div>
            <div class="actions">
              <button class="secondary" onclick="enterEdit(${m.id}, ${JSON.stringify(m).replace(/"/g,'&quot;')})">Düzenle</button>
              <button class="danger" onclick="deleteMessage(${m.id})">Sil</button>
            </div>
          </div>
          <div id="c-${m.id}" style="margin-top:.5rem;">${escapeHtml(m.content || '')}</div>
          <div id="edit-${m.id}" style="display:none; margin-top:.5rem;">
            <div class="row">
              <div><input id="e-title-${m.id}" value="${escapeAttr(m.title || '')}" /></div>
              <div><textarea id="e-content-${m.id}">${escapeHtml(m.content || '')}</textarea></div>
            </div>
            <div class="actions" style="margin-top:.5rem;">
              <button onclick="saveEdit(${m.id})">Kaydet</button>
              <button class="secondary" onclick="cancelEdit(${m.id})">Vazgeç</button>
            </div>
          </div>
        `;
        list.appendChild(el);
      }
    }

    function enterEdit(id, m){
      document.getElementById(`edit-${id}`).style.display = '';
      document.getElementById(`c-${id}`).style.display = 'none';
    }
    function cancelEdit(id){
      document.getElementById(`edit-${id}`).style.display = 'none';
      document.getElementById(`c-${id}`).style.display = '';
    }

    async function saveEdit(id){
      const title = document.getElementById(`e-title-${id}`).value.trim();
      const content = document.getElementById(`e-content-${id}`).value.trim();
      try {
        const out = await api(`/api/messages/${id}`, { method: 'PUT', body: { title, content } });
        setLog(out, true);
      } catch(err){ setLog(err, false); }
    }

    async function deleteMessage(id){
      if(!confirm('Bu mesaj silinsin mi?')) return;
      try {
        const out = await api(`/api/messages/${id}`, { method: 'DELETE' });
        setLog(out, true);
      } catch(err){ setLog(err, false); }
    }

    function escapeHtml(str){
      return String(str).replace(/[&<>\"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;'}[s]));
    }
    function escapeAttr(str){
      return String(str).replace(/["']/g, s => ({'\"':'&quot;','\'':'&#39;'}[s] || s));
    }

    // init
    refreshTokenInput();
    if(getToken()) {
      loadUsers();
      loadMessages();
    }
  </script>
</body>
</html>
