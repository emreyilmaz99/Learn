<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <title>Cache'den Mesajlar</title>
  <style>
    body { background: #0b1020; color: #e6e6e6; font-family: system-ui, Arial; padding: 2rem; }
    .container { max-width: 800px; margin: 0 auto; }
    .msg { background: #10172b; border: 1px solid #1f2a44; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
    .msg-title { font-weight: 600; color: #dbeafe; }
    .msg-meta { color: #9aa4bf; font-size: .85rem; }
  </style>
</head>
<body>
  <div class="container">
      <h2>Cache'den Gelen Mesajlar</h2>
      <div style="margin-bottom:1rem;">
        <input id="token" placeholder="Sanctum tokenini buraya gir" style="width:60%;padding:.5rem;" />
        <button onclick="cachele()" style="padding:.5rem 1rem;">Cachele</button>
        <span id="cachele-sonuc" style="margin-left:1rem;color:#93c5fd;"></span>
      </div>
    @forelse($messages as $m)
      <div class="msg">
        <div class="msg-title">{{ $m['title'] ?? '' }}</div>
        <div class="msg-meta">#{{ $m['id'] ?? '' }} · Gönderen: {{ $m['sender_id'] ?? '?' }} → Alıcı: {{ $m['receiver_id'] ?? '?' }}<br>{{ $m['created_at'] ?? '' }}</div>
        <div>{{ $m['content'] ?? '' }}</div>
      </div>
    @empty
      <div class="msg">Cache'de hiç mesaj yok.</div>
    @endforelse
  </div>
  <script>
    function cachele() {
      const token = document.getElementById('token').value.trim();
      if(!token) {
        const sonuc = document.getElementById('cachele-sonuc');
        sonuc.textContent = 'Token giriniz!';
        sonuc.style.color = 'red';
        setTimeout(() => {
          sonuc.textContent = '';
        }, 5000);
        return;
      }
      fetch('/api/cache/set', {
        method: 'GET',
        headers: {
          'Authorization': 'Bearer ' + token,
          'Accept': 'application/json'
        }
      })
      .then(r => r.json())
      .then(data => {
        const sonuc = document.getElementById('cachele-sonuc');
        sonuc.textContent = data.message || JSON.stringify(data);
        sonuc.style.color = 'green';
        setTimeout(() => {
          sonuc.textContent = '';
        }, 5000);
      })
      .catch(e => {
        const sonuc = document.getElementById('cachele-sonuc');
        console.error('Hata Detayı:', e);
        sonuc.textContent = 'Hata: ' + (e.message || e);
        sonuc.style.color = 'red';
        setTimeout(() => {
          sonuc.textContent = '';
        }, 5000);
      });
    }
  </script>
</body>
</html>
