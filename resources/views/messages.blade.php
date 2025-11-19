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
  <style>
    /* Suggestions dropdown styles */
    /* Öneri kutusunu görünür kılan CSS (kullanıcının istediği stil) */
    .suggestions-list {
      background-color: #1e293b; /* Koyu gri arka plan */
      border: 1px solid #3b82f6; /* Mavi çerçeve */
      border-radius: 0 0 8px 8px;
      position: absolute; /* Diğer öğelerin üzerine binmesi için */
      left: 0;
      right: 0;
      top: 100%; /* Inputun tam altına yapışsın */
      z-index: 9999; /* En üstte görünsün */
      max-height: 200px;
      overflow-y: auto;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
    }

    .suggestion-item {
      padding: 0.75rem 1rem;
      cursor: pointer;
      color: #e2e8f0; /* Yazı rengi açık olsun */
      border-bottom: 1px solid #334155;
    }

    .suggestion-item:hover {
      background-color: #334155; /* Üzerine gelince renk değişsin */
      color: #60a5fa;
    }

    .suggestion-item .muted {
      font-size: 0.8rem;
      color: #94a3b8;
    }
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
        <div style="display:flex; gap:.5rem; align-items:center;">
          <input id="search_q" placeholder="Arama (içerik veya kullanıcı email)" style="padding:.5rem .6rem; border-radius:8px; border:1px solid #273353; background:#071025; color:#e6e6e6;" />
          <button class="secondary" onclick="clearSearch()">Temizle</button>
        </div>
         <div style="width:12px"></div>
         <div style="position:relative; min-width:260px; display:flex; gap:.5rem; align-items:center;">
           <div style="flex:1; position:relative;">
             <input id="user_filter_q" placeholder="Kullanıcı filtrele (isim yaz)" style="padding:.5rem .6rem; border-radius:8px; border:1px solid #273353; background:#071025; color:#e6e6e6; width:100%;" autocomplete="off" />
             <div id="user-filter-suggestions" class="suggestions-list" style="display:none; position:absolute; left:0; right:0; top:40px;"></div>
           </div>
           <button id="user_filter_btn" class="secondary" onclick="applyUserFilter()">Ara</button>
         </div>
        
        <div>
          <button onclick="loadMessages()">📬 Mesajları Getir</button>
        </div>
      </div>
      <div id="list" class="list"></div>
      <div id="pagination" style="display:flex; gap:.5rem; align-items:center; margin-top:.5rem;"></div>
    </div>

    <div class="card" id="notifications-card">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem;">
        <div style="font-weight:600;">Bildirimler <span id="notif-count" style="color:#93c5fd; margin-left:.5rem;"></span></div>
        <div class="actions">
          <button class="secondary" onclick="fetchNotifications()">Yenile</button>
        </div>
      </div>
      <div id="notifications-list" class="list"><div class="muted">Bildirim yok</div></div>
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

    let searchPage = 1;
    const perPage = 20;
    let searchParticipantId = null; // set when selecting a suggested user

    async function loadMessages(){
      try {
        let out;
        const q = document.getElementById('search_q')?.value?.trim() || '';
        if (q) {
          // log the search query in the UI
          try { addSearchLog(q); } catch (e) { /* ignore logging errors */ }
          let url = `/api/messages/search?q=${encodeURIComponent(q)}&page=${searchPage}&per_page=${perPage}`;
          if (searchParticipantId) url += `&participant_id=${searchParticipantId}`;
          out = await api(url);
        } else {
          // No free-text query — include participant filter even when listing inbox/sent/all
          let url = '/api/messages';
          if (currentTab === 'sent') url = '/api/messages/sent';
          else if (currentTab === 'inbox') url = '/api/messages/inbox';
          if (searchParticipantId) {
            url += (url.indexOf('?') === -1 ? '?' : '&') + `participant_id=${searchParticipantId}`;
          }
          out = await api(url);
        }
        setLog(out, true);
        renderList(out?.data || [], out?.meta || null);
      } catch(err){ setLog(err, false); renderList([]); }
    }

    function clearSearch(){ document.getElementById('search_q').value = ''; loadMessages(); }

  function resetSearchPagination(){ searchPage = 1; }

    function switchTab(tab){
      currentTab = tab;
      // tab görünümünü güncelle
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.getElementById(`tab-${tab}`).classList.add('active');
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

  function prevPage(){ if(searchPage>1){ searchPage--; loadMessages(); } }
  function nextPage(totalPages){ if(searchPage<totalPages){ searchPage++; loadMessages(); } }

    function renderList(items, meta){
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
      // pagination
      const pag = document.getElementById('pagination');
      if(!meta){ pag.innerHTML = ''; return; }
      const total = meta.total || 0;
      const page = meta.page || 1;
      const totalPages = meta.total_pages || 1;
      pag.innerHTML = `
        <div class="muted">Toplam: ${total} — Sayfa ${page} / ${totalPages}</div>
        <div style="margin-left:auto; display:flex; gap:.5rem;">
          <button class="secondary" ${page<=1? 'disabled' : ''} onclick="prevPage()">◀ Önceki</button>
          <button ${page>=totalPages? 'disabled' : ''} onclick="nextPage(${totalPages})">Sonraki ▶</button>
        </div>
      `;
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

    // reset pagination when search query changes
    const searchInput = document.getElementById('search_q');
    if(searchInput){
      // reset pagination when search query changes
      searchInput.addEventListener('input', ()=> { searchPage = 1; });
      // trigger search on Enter and log the query
      searchInput.addEventListener('keydown', (e) => {
        if(e.key === 'Enter'){
          searchPage = 1;
          loadMessages();
        }
      });
    }

    // Debounce helper used by suggestions
    function debounceSuggest(fn, wait){
      let t = null;
      return function(...args){
        if(t) clearTimeout(t);
        t = setTimeout(()=> fn.apply(this, args), wait);
      };
    }

    // Wire the user-filter input to suggestions endpoint
    const userFilterInput = document.getElementById('user_filter_q');
    if(userFilterInput){
      const fetchSuggestions = async (q) => {
        const box = document.getElementById('user-filter-suggestions');
        if(!q || q.trim().length < 1){ box.style.display='none'; box.innerHTML=''; return; }
        try{
          const out = await api(`/api/messages/suggestions?q=${encodeURIComponent(q)}`);
          // Console debug to inspect what arrived client-side
          console.log("Gelen Veri:", out);
          console.log("Data Listesi:", out?.data);
          const items = out?.data || [];
          // Debug: show suggestions in the main log so we can confirm data arrives
          try { setLog({ suggestions: items }, true); } catch(_) { /* ignore */ }
          try { showSuggestionDebug(items); } catch(_) {}
          if(!items.length){ box.style.display='none'; box.innerHTML=''; return; }
          box.innerHTML = '';
          for(const u of items){
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            // some suggestions may not include email; show name only if available
            div.innerHTML = `${escapeHtml(u.name || '')} <div class="muted">${escapeHtml(u.email || '')}</div>`;
            div.addEventListener('click', ()=>{
              document.getElementById('user_filter_q').value = u.name || u.email || '';
              searchParticipantId = u.id || null;
              box.style.display='none';
              // trigger filtered search
              searchPage = 1; loadMessages();
            });
            box.appendChild(div);
          }
          box.style.display = 'block';
        } catch(e){
          console.warn('suggest error', e);
          try { setLog({ suggest_error: e }, false); } catch(_) {}
          try { showSuggestionDebug({ error: (e && e.message) ? e.message : String(e) }); } catch(_) {}
          // show a visible error item so the user knows why suggestions failed
          box.innerHTML = '';
          const div = document.createElement('div');
          div.className = 'suggestion-item';
          const msg = (e && e.message) ? e.message : 'Öneriler alınamadı';
          div.textContent = msg;
          div.style.color = '#fca5a5';
          box.appendChild(div);
          box.style.display = 'block';
        }
      };

      const debouncedFetch = debounceSuggest(fetchSuggestions, 220);
      userFilterInput.addEventListener('input', e => { searchParticipantId = null; debouncedFetch(e.target.value); });
      userFilterInput.addEventListener('keydown', e => { if(e.key === 'Escape'){ const b=document.getElementById('user-filter-suggestions'); if(b) b.style.display='none'; } });
      document.addEventListener('click', function(ev){ const box = document.getElementById('user-filter-suggestions'); if(!box) return; if(!box.contains(ev.target) && ev.target !== userFilterInput) box.style.display='none'; });
    }

    // Apply user filter when the "Ara" button is pressed.
    // If a suggestion was already selected, it uses `searchParticipantId`.
    // Otherwise it tries to resolve the input via the suggestions endpoint
    // and picks the first result (if any) before reloading messages.
    function applyUserFilter(){
      const q = document.getElementById('user_filter_q')?.value?.trim() || '';
      if(!q){
        searchParticipantId = null;
        searchPage = 1;
        loadMessages();
        return;
      }

      // if a suggestion click already set the participant id, just search
      if(searchParticipantId){ searchPage = 1; loadMessages(); return; }

      // otherwise try to resolve via suggestions API and pick the first match
      (async () => {
        try{
          const res = await api(`/api/messages/suggestions?q=${encodeURIComponent(q)}`);
          const items = res?.data || [];
          if(items.length){ searchParticipantId = items[0].id || null; }
          else { searchParticipantId = null; }
        } catch(e){
          console.warn('applyUserFilter error', e);
          searchParticipantId = null;
        } finally {
          searchPage = 1;
          loadMessages();
        }
      })();
    }

    function addSearchLog(q){
      const el = document.getElementById('search-log');
      if(!el) return;
      const time = new Date().toLocaleTimeString();
      const entry = document.createElement('div');
      entry.style.padding = '.25rem 0';
      entry.textContent = `[${time}] ${q}`;
      // prepend
      if(el.textContent === 'Henüz arama yok') el.textContent = '';
      el.insertBefore(entry, el.firstChild);
      // keep at most 10 entries
      while(el.children.length > 10){ el.removeChild(el.lastChild); }
    }

    // Notifications polling
    let notifInterval = null;
    function startNotificationsPoll(){
      // avoid multiple intervals
      if(notifInterval) return;
      fetchNotifications();
      notifInterval = setInterval(fetchNotifications, 10000); // every 10s
    }
    function stopNotificationsPoll(){ if(notifInterval){ clearInterval(notifInterval); notifInterval = null; } }

    async function fetchNotifications(){
      try {
        const out = await api('/api/notifications');
        const items = out?.data || [];
        renderNotifications(items);
        document.getElementById('notif-count').textContent = items.filter(i => !i.read_at).length ? `(${items.filter(i=>!i.read_at).length})` : '';
      } catch(err){ console.warn('Bildirim yüklenemedi', err); document.getElementById('notifications-list').innerHTML = '<div class="muted">Bildirimler alınamadı</div>'; }
    }

    function renderNotifications(items){
      const list = document.getElementById('notifications-list');
      if(!items.length){ list.innerHTML = '<div class="muted">Bildirim yok</div>'; return; }
      list.innerHTML = '';
      for(const n of items){
        const el = document.createElement('div');
        el.className = 'msg';
        const title = n.title || n.type || 'Bildirim';
        const content = n.content || (n.data && JSON.stringify(n.data)) || '';
        const time = new Date(n.created_at || Date.now()).toLocaleString('tr-TR');
        const read = n.read_at ? true : false;
        el.innerHTML = `
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-weight:600;">${escapeHtml(title)}</div>
              <div class="msg-meta">${escapeHtml(content)}<br><small>${time}</small></div>
            </div>
            <div class="actions">
              ${read ? '<button class="secondary" disabled>Okundu</button>' : `<button onclick="markNotifRead(${n.id})">Okundu olarak işaretle</button>`}
            </div>
          </div>
        `;
        list.appendChild(el);
      }
    }

    async function markNotifRead(id){
      try {
        const out = await api(`/api/notifications/${id}/read`, { method: 'POST' });
        setLog(out, true);
        // Refresh list after marking
        fetchNotifications();
      } catch(err){ setLog(err, false); }
    }

    // Ensure polling starts/stops with token changes
    const originalSaveToken = saveToken;
    saveToken = async function(){ await originalSaveToken(); if(getToken()) startNotificationsPoll(); else stopNotificationsPoll(); };
    const originalClearToken = clearToken;
    clearToken = function(){ originalClearToken(); stopNotificationsPoll(); };
 
    // init
    refreshTokenInput();
    if(getToken()) {
      // loadUsers();
      loadMessages();
      startNotificationsPoll();
    }
  
  </script>
</body>
</html>
