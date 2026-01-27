<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ステータスBユーザー検索</title>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    body{font-family:system-ui,Segoe UI,Arial;background:#f6f7fb;margin:0;padding:20px;}
    .card{max-width:820px;margin:0 auto;background:#fff;border:1px solid #e7e7e7;border-radius:12px;padding:16px;}
    .row{display:flex;gap:10px;flex-wrap:wrap;align-items:end;}
    label{font-size:12px;color:#6b7280;display:block;margin-bottom:6px;}
    input{padding:10px 12px;border:1px solid #ddd;border-radius:10px;min-width:200px;}
    button{padding:10px 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer;}
    .muted{color:#6b7280;font-size:12px;}
    pre{background:#0b1020;color:#d1d5db;border-radius:12px;padding:12px;overflow:auto;}
    ul{margin:8px 0 0 18px;}
  </style>
</head>
<body>
  <div class="card">
    <h2 style="margin:0 0 10px;">
      指定した日付区間：ステータスがBのユーザー一覧検索
    </h2>
    <div class="muted">
      日付形式：YYYY-MM-DD（例：2025-09-02）
    </div>

    <div class="row" style="margin-top:14px;">
      <div>
        <label>開始日</label>
        <input id="start" type="date">
      </div>
      <div>
        <label>終了日</label>
        <input id="end" type="date">
      </div>
      <div>
        <button id="btn">検索</button>
      </div>
    </div>

    <div id="result" style="margin-top:16px;"></div>
    <pre id="raw" style="display:none;margin-top:12px;"></pre>
  </div>

<script>
$('#btn').on('click', function () {
  const start = $('#start').val();
  const end = $('#end').val();

  $('#result').html('<div class="muted">検索中...</div>');
  $('#raw').hide();

  $.getJSON('/api.php', { start, end })
    .done(function (res) {
      if (!res.ok) {
        $('#result').html('<div style="color:red;">検索に失敗しました</div>');
        return;
      }

      const rows = res.rows || [];

      let html = `
        <div style="margin-bottom:10px;">
          期間：${res.start} ～ ${res.end}　
          件数：${res.count}
        </div>
      `;

      if (rows.length === 0) {
        html += '<div style="color:#666;">該当データはありません</div>';
      } else {
        html += `
          <table style="width:100%;border-collapse:collapse;">
            <thead>
              <tr>
                <th style="border-bottom:1px solid #ddd;padding:8px;text-align:left;">ID</th>
                <th style="border-bottom:1px solid #ddd;padding:8px;text-align:left;">ユーザー名</th>
              </tr>
            </thead>
            <tbody>
              ${rows.map(r => `
                <tr>
                  <td style="border-bottom:1px solid #eee;padding:8px;">${r.id}</td>
                  <td style="border-bottom:1px solid #eee;padding:8px;">${r.user_name}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        `;
      }

      $('#result').html(html);
    })
    .fail(function (xhr) {
      let msg = '通信エラーが発生しました';
      try { msg = xhr.responseJSON?.msg || msg; } catch(e){}
      $('#result').html('<div style="color:#dc2626;">' + msg + '</div>');
    });
});
</script>
</body>
</html>
