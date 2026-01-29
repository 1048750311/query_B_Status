<?php
/**
 * index.php
 * ------------------------------------------------------------
 * 【画面】日付範囲を指定して「status=B」のユーザー一覧を検索
 * - 右側に縦型の言語切り替え（クリックするとページをリロード）
 * - 検索は api.php をAJAXで呼び出し
 * ------------------------------------------------------------
 */

session_start();

/** 言語判定：GET優先 → session → デフォルト ja */
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'ja');
$_SESSION['lang'] = $lang;

/** 画面文言辞書（PHPで描画する＝言語切替時はページ再読み込み） */
$I18N = [
  'ja' => [
    'title' => '指定した日付区間：ステータスがBのユーザー検索',
    'date_format' => '日付形式：YYYY-MM-DD',
    'start' => '開始日',
    'end'   => '終了日',
    'search'=> '検索',
    'loading' => '検索中...',
    'count' => '件数',
    'no_data' => '該当データはありません',
    'request_failed' => '通信エラーが発生しました',
  ],
  'en' => [
    'title' => 'Date Range: Users with Status B',
    'date_format' => 'Date format: YYYY-MM-DD',
    'start' => 'Start date',
    'end'   => 'End date',
    'search'=> 'Search',
    'loading' => 'Searching...',
    'count' => 'Count',
    'no_data' => 'No data',
    'request_failed' => 'Request failed',
  ],
  'zh-cn' => [
    'title' => '指定日期区间：查询状态为 B 的用户',
    'date_format' => '日期格式：YYYY-MM-DD',
    'start' => '开始日期',
    'end'   => '结束日期',
    'search'=> '查询',
    'loading' => '查询中...',
    'count' => '数量',
    'no_data' => '没有数据',
    'request_failed' => '请求失败',
  ],
  'zh-tw' => [
    'title' => '指定日期區間：查詢狀態為 B 的使用者',
    'date_format' => '日期格式：YYYY-MM-DD',
    'start' => '開始日期',
    'end'   => '結束日期',
    'search'=> '查詢',
    'loading' => '查詢中...',
    'count' => '數量',
    'no_data' => '沒有資料',
    'request_failed' => '請求失敗',
  ],
  'vi' => [
    'title' => 'Khoảng ngày: Người dùng có trạng thái B',
    'date_format' => 'Định dạng ngày: YYYY-MM-DD',
    'start' => 'Ngày bắt đầu',
    'end'   => 'Ngày kết thúc',
    'search'=> 'Tìm kiếm',
    'loading' => 'Đang tìm...',
    'count' => 'Số lượng',
    'no_data' => 'Không có dữ liệu',
    'request_failed' => 'Yêu cầu thất bại',
  ],
];

$t = $I18N[$lang] ?? $I18N['ja'];

/**
 * 言語切替リンク用：現在のクエリを保持して lang だけ差し替える
 * - 例：?start=...&end=... が付いていても維持できる（任意）
 */
function qs_keep($override = []) {
  $params = $_GET;
  unset($params['lang']);
  $params = array_merge($params, $override);
  return http_build_query($params);
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($t['title']) ?></title>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <style>
    body{font-family:system-ui,Segoe UI,Arial;background:#f6f7fb;margin:0;padding:20px;}
    .card{max-width:820px;margin:0 auto;background:#fff;border:1px solid #e7e7e7;border-radius:12px;padding:16px;}
    .row{display:flex;gap:10px;flex-wrap:wrap;align-items:end;}
    label{font-size:12px;color:#6b7280;display:block;margin-bottom:6px;}
    input{padding:10px 12px;border:1px solid #ddd;border-radius:10px;min-width:200px;}
    button{padding:10px 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer;}
    .muted{color:#6b7280;font-size:12px;}
    table{width:100%;border-collapse:collapse;}
    th,td{border-bottom:1px solid #eee;padding:8px;text-align:left;}

    /* ===== 右側・縦型の言語スイッチ（クリックするとページ再読み込み） ===== */
    .lang-switch{
      position:fixed;
      top:40%;
      right:16px;
      background:#fff;
      border-radius:12px;
      box-shadow:0 6px 18px rgba(0,0,0,.12);
      overflow:hidden;
      z-index:9999;
      border:1px solid rgba(0,0,0,.06);
    }
    .lang-item{
      display:block;
      width:44px;
      height:44px;
      line-height:44px;
      text-align:center;
      font-size:14px;
      font-weight:700;
      color:#333;
      text-decoration:none;
      user-select:none;
      transition:background .15s ease,color .15s ease;
    }
    .lang-item:not(:last-child){border-bottom:1px solid #eee;}
    .lang-item:hover{background:#f5f5f5;}
    .lang-item.active{background:#111;color:#fff;}
  </style>
</head>

<body>

<!-- 言語切替（リンクなので必ずページがリロードされる） -->
<nav class="lang-switch" aria-label="Language switch">
  <a class="lang-item <?= $lang==='ja'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'ja']) ?>">JA</a>
  <a class="lang-item <?= $lang==='en'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'en']) ?>">EN</a>
  <a class="lang-item <?= $lang==='zh-tw'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'zh-tw']) ?>">繁</a>
  <a class="lang-item <?= $lang==='zh-cn'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'zh-cn']) ?>">简</a>
  <a class="lang-item <?= $lang==='vi'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'vi']) ?>">VI</a>
</nav>

<div class="card">
  <h2><?= htmlspecialchars($t['title']) ?></h2>
  <div class="muted"><?= htmlspecialchars($t['date_format']) ?></div>

  <div class="row" style="margin-top:14px;">
    <div>
      <label><?= htmlspecialchars($t['start']) ?></label>
      <input id="start" type="date">
    </div>
    <div>
      <label><?= htmlspecialchars($t['end']) ?></label>
      <input id="end" type="date">
    </div>
    <div>
      <button id="btn"><?= htmlspecialchars($t['search']) ?></button>
    </div>
  </div>

  <div id="result" style="margin-top:16px;"></div>
</div>

<script>
/**
 * 検索ボタン：api.php を呼び出して結果を表示
 * ※言語は PHP で確定した $lang を送る（エラーメッセージをAPI側でも言語化）
 */
$('#btn').on('click', function(){
  const start = $('#start').val();
  const end   = $('#end').val();

  $('#result').html('<div class="muted"><?= htmlspecialchars($t['loading']) ?></div>');

  $.getJSON('/api.php', {
    start: start,
    end: end,
    lang: '<?= htmlspecialchars($lang) ?>'
  }).done(function(res){
    if(!res.ok){
      // APIが message を返すのでそのまま表示
      $('#result').html('<div style="color:red;">' + (res.message || res.code) + '</div>');
      return;
    }

    let html = `<div><?= htmlspecialchars($t['count']) ?>: ${res.count}</div>`;

    if(res.rows.length === 0){
      html += `<div class="muted"><?= htmlspecialchars($t['no_data']) ?></div>`;
      $('#result').html(html);
      return;
    }

    html += '<table><thead><tr><th>ID</th><th>User</th></tr></thead><tbody>';
    res.rows.forEach(r=>{
      html += `<tr><td>${r.id}</td><td>${r.user_name}</td></tr>`;
    });
    html += '</tbody></table>';

    $('#result').html(html);
  }).fail(function(){
    $('#result').html('<div style="color:red;"><?= htmlspecialchars($t['request_failed']) ?></div>');
  });
});
</script>

</body>
</html>
