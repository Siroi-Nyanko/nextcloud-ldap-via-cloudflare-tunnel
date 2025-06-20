document.getElementById('settingsForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const data = {
    cloudflaredPath: document.getElementById('cloudflaredPath').value,
    tunnelName: document.getElementById('tunnelName').value,
    configPath: document.getElementById('configPath').value,
    logFile: document.getElementById('logFile').value
  };
  fetch('/index.php/apps/ldapvct/admin/save', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data),
  }).then(resp => resp.json())
    .then(json => alert(json.status === 'success' ? '設定を保存しました' : 'エラー発生'))
    .catch(() => alert('通信エラー'));
});
document.getElementById('uploadConfigForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('/index.php/apps/ldapvct/admin/uploadConfig', {
    method: 'POST',
    body: formData
  }).then(r => r.json())
    .then(data => alert(data.status || data.error || 'エラー'))
    .catch(() => alert('通信エラー'));
});
document.getElementById('editConfigForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const content = document.getElementById('configContent').value;
  fetch('/index.php/apps/ldapvct/admin/saveConfigText', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({configContent: content}),
  }).then(r => r.json())
    .then(data => alert(data.status || data.error || 'エラー'))
    .catch(() => alert('通信エラー'));
});
document.getElementById('startBtn').addEventListener('click', function() {
  fetch('/index.php/apps/ldapvct/tunnel/start', {method: 'POST'})
    .then(r => r.json())
    .then(data => {
      alert(data.status || data.error);
    });
});
document.getElementById('stopBtn').addEventListener('click', function() {
  fetch('/index.php/apps/ldapvct/tunnel/stop', {method: 'POST'})
    .then(r => r.json())
    .then(data => {
      alert(data.status || data.error);
    });
});
