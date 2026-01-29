<?php
/**
 * index.php
 * ------------------------------------------------------------
 * 画面：指定期間内の status=B ユーザー検索
 * - 言語切替：カード右上のボタンにホバーすると候補が表示される
 * - クリックするとページをリロードして言語を切替（デフォルト：ja）
 * - 検索は api.php をAJAXで呼び出し（langも送る）
 * ------------------------------------------------------------
 */

session_start();

/** 言語判定：GET優先 → session → デフォルト ja */
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'ja');
$_SESSION['lang'] = $lang;

/** 画面文言辞書（PHPで描画） */
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
    'id' => 'ID',
    'user' => 'User',
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
    'id' => 'ID',
    'user' => 'User',
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
    'id' => 'ID',
    'user' => '使用者',
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
    'id' => 'ID',
    'user' => '用户',
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
    'id' => 'ID',
    'user' => 'User',
  ],
];

$t = $I18N[$lang] ?? $I18N['ja'];

/** 右上ボタン表示用 */
$langLabel = [
  'ja' => 'JA',
  'en' => 'EN',
  'zh-tw' => '繁',
  'zh-cn' => '简',
  'vi' => 'VI',
];
$currentLabel = $langLabel[$lang] ?? 'JA';

/** クエリ維持：langだけ差し替え */
function qs_keep($override = []) {
  $params = $_GET;
  unset($params['lang']);
  return http_build_query(array_merge($params, $override));
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
    .card{
      max-width:820px;margin:0 auto;background:#fff;border:1px solid #e7e7e7;
      border-radius:14px;padding:18px 18px 20px; box-shadow:0 8px 24px rgba(0,0,0,.08);
      position:relative; /* 重要：言語ボタンをカード内右上に配置するため */
    }
    .row{display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-top:14px;}
    label{font-size:12px;color:#6b7280;display:block;margin-bottom:6px;}
    input{padding:10px 12px;border:1px solid #ddd;border-radius:10px;min-width:200px;}
    button{padding:10px 14px;border:0;border-radius:10px;background:#2563eb;color:#fff;font-weight:600;cursor:pointer;}
    .muted{color:#6b7280;font-size:12px;}
    table{width:100%;border-collapse:collapse;margin-top:10px;}
    th,td{border-bottom:1px solid #eee;padding:8px;text-align:left;}

    /* =========================
       カード右上：言語ドロップダウン（ホバーで展開）
       ========================= */
    .lang-float{
      position:absolute;
      top:14px;
      right:14px;
      z-index:10;
      font-weight:700;
    }
    .lang-box{
      background:#fff;
      border-radius:12px;
      box-shadow:0 6px 18px rgba(0,0,0,.12);
      border:1px solid rgba(0,0,0,.06);
      overflow:hidden;
      width:52px;
    }
    .lang-current{
      display:flex;
      align-items:center;
      justify-content:center;
      height:46px;
      color:#333;
      text-decoration:none;
      background:#fff;
    }
    .lang-current span{
      display:inline-block;
      padding:3px 7px;
      border:2px solid #666;
      border-radius:6px;
      line-height:1;
    }
    .lang-menu{
      display:none;
      border-top:1px solid #eee;
    }
    .lang-float:hover .lang-menu,
    .lang-float:focus-within .lang-menu{
      display:block;
    }
    .lang-item{
      display:flex;
      align-items:center;
      justify-content:center;
      height:44px;
      text-decoration:none;
      color:#333;
      background:#fff;
      border-top:1px solid #eee;
      font-size:14px;
    }
    .lang-item:hover{background:#f5f5f5;}
    .lang-item.active{background:#111;color:#fff;}
  </style>
</head>
<body>

<div class="card">

  <!-- 言語切替：カード内右上 -->
  <div class="lang-float">
    <div class="lang-box">
      <a class="lang-current" href="javascript:void(0)" aria-label="Current language">
        <span><?= htmlspecialchars($currentLabel) ?></span>
      </a>
      <div class="lang-menu" aria-label="Language menu">
        <a class="lang-item <?= $lang==='ja'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'ja']) ?>">JA</a>
        <a class="lang-item <?= $lang==='en'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'en']) ?>">EN</a>
        <a class="lang-item <?= $lang==='zh-tw'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'zh-tw']) ?>">繁</a>
        <a class="lang-item <?= $lang==='zh-cn'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'zh-cn']) ?>">简</a>
        <a class="lang-item <?= $lang==='vi'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'vi']) ?>">VI</a>
      </div>
    </div>
  </div>

  <h2 style="margin:6px 0 6px; padding-right:70px;"><?= htmlspecialchars($t['title']) ?></h2>
  <div class="muted"><?= htmlspecialchars($t['date_format']) ?></div>

  <div class="row">
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
 * - lang を送って、APIのエラーメッセージも現在言語に合わせる
 */
const LANG = '<?= htmlspecialchars($lang) ?>';
const UI = {
  loading: <?= json_encode($t['loading'], JSON_UNESCAPED_UNICODE) ?>,
  count:   <?= json_encode($t['count'], JSON_UNESCAPED_UNICODE) ?>,
  noData:  <?= json_encode($t['no_data'], JSON_UNESCAPED_UNICODE) ?>,
  reqFail: <?= json_encode($t['request_failed'], JSON_UNESCAPED_UNICODE) ?>,
  thId:    <?= json_encode($t['id'], JSON_UNESCAPED_UNICODE) ?>,
  thUser:  <?= json_encode($t['user'], JSON_UNESCAPED_UNICODE) ?>,
};

$('#btn').on('click', function(){
  const start = $('#start').val();
  const end   = $('#end').val();

  $('#result').html('<div class="muted">' + UI.loading + '</div>');

  $.getJSON('/api.php', { start, end, lang: LANG })
    .done(function(res){
      if(!res.ok){
        $('#result').html('<div style="color:red;">' + (res.message || res.code) + '</div>');
        return;
      }

      let html = `<div>${UI.count}: ${res.count}</div>`;

      if(res.rows.length === 0){
        html += `<div class="muted">${UI.noData}</div>`;
        $('#result').html(html);
        return;
      }

      html += `<table><thead><tr><th>${UI.thId}</th><th>${UI.thUser}</th></tr></thead><tbody>`;
      res.rows.forEach(r=>{
        html += `<tr><td>${r.id}</td><td>${r.user_name}</td></tr>`;
      });
      html += `</tbody></table>`;
      $('#result').html(html);
    })
    .fail(function(){
      $('#result').html('<div style="color:red;">' + UI.reqFail + '</div>');
    });
});
</script>
</body>
</html>
