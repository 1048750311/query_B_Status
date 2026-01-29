<?php
session_start();

/* ================= 语言判定 ================= */
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'ja');
$_SESSION['lang'] = $lang;

/* ================= 文案表 ================= */
$I18N = [
  'ja' => [
    'title' => '指定した日付区間：ステータスがBのユーザー検索',
    'start' => '開始日',
    'end'   => '終了日',
    'search'=> '検索',
    'date_format' => '日付形式：YYYY-MM-DD'
  ],
  'en' => [
    'title' => 'Date Range: Users with Status B',
    'start' => 'Start Date',
    'end'   => 'End Date',
    'search'=> 'Search',
    'date_format' => 'Date format: YYYY-MM-DD'
  ],
  'zh-cn' => [
    'title' => '指定日期区间：查询状态为 B 的用户',
    'start' => '开始日期',
    'end'   => '结束日期',
    'search'=> '查询',
    'date_format' => '日期格式：YYYY-MM-DD'
  ],
  'zh-tw' => [
    'title' => '指定日期區間：查詢狀態為 B 的使用者',
    'start' => '開始日期',
    'end'   => '結束日期',
    'search'=> '查詢',
    'date_format' => '日期格式：YYYY-MM-DD'
  ],
  'vi' => [
    'title' => 'Khoảng ngày: Người dùng có trạng thái B',
    'start' => 'Ngày bắt đầu',
    'end'   => 'Ngày kết thúc',
    'search'=> 'Tìm kiếm',
    'date_format' => 'Định dạng ngày: YYYY-MM-DD'
  ],
];

$t = $I18N[$lang] ?? $I18N['ja'];

/* ====== 保留 query 参数（用于切语言时不丢参数） ====== */
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
<title><?= htmlspecialchars($t['title']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
body{
  font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial;
  background:#f6f7fb;
  margin:0;
  padding:20px;
}
.card{
  max-width:820px;
  margin:0 auto;
  background:#fff;
  border-radius:12px;
  padding:20px;
  box-shadow:0 8px 24px rgba(0,0,0,.08);
}
.row{display:flex;gap:12px;align-items:end;flex-wrap:wrap;}
label{font-size:13px;color:#555;}
input{padding:10px 12px;border:1px solid #ddd;border-radius:8px;}
button{
  padding:10px 18px;
  border:0;
  border-radius:8px;
  background:#2563eb;
  color:#fff;
  font-weight:600;
  cursor:pointer;
}
table{width:100%;border-collapse:collapse;margin-top:12px;}
th,td{padding:8px;border-bottom:1px solid #eee;text-align:left;}

/* ===== 右侧语言切换 ===== */
.lang-switch{
  position:fixed;
  top:40%;
  right:16px;
  background:#fff;
  border-radius:12px;
  box-shadow:0 6px 18px rgba(0,0,0,.12);
  overflow:hidden;
  z-index:9999;
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
}
.lang-item:not(:last-child){border-bottom:1px solid #eee;}
.lang-item:hover{background:#f3f3f3;}
.lang-item.active{background:#111;color:#fff;}
</style>
</head>

<body>

<!-- ===== 语言切换（点击即刷新） ===== -->
<nav class="lang-switch">
  <a class="lang-item <?= $lang==='ja'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'ja']) ?>">JA</a>
  <a class="lang-item <?= $lang==='en'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'en']) ?>">EN</a>
  <a class="lang-item <?= $lang==='zh-tw'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'zh-tw']) ?>">繁</a>
  <a class="lang-item <?= $lang==='zh-cn'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'zh-cn']) ?>">简</a>
  <a class="lang-item <?= $lang==='vi'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'vi']) ?>">VI</a>
</nav>

<div class="card">
  <h2><?= htmlspecialchars($t['title']) ?></h2>
  <div style="color:#666;font-size:13px;"><?= htmlspecialchars($t['date_format']) ?></div>

  <div class="row" style="margin-top:16px;">
    <div>
      <label><?= htmlspecialchars($t['start']) ?></label><br>
      <input type="date" id="start">
    </div>
    <div>
      <label><?= htmlspecialchars($t['end']) ?></label><br>
      <input type="date" id="end">
    </div>
    <div>
      <button id="btn"><?= htmlspecialchars($t['search']) ?></button>
    </div>
  </div>

  <div id="result" style="margin-top:16px;"></div>
</div>

<script>
$('#btn').on('click', function(){
  const start = $('#start').val();
  const end   = $('#end').val();
  $('#result').text('Loading...');

  $.getJSON('/api.php', {
    start, end,
    lang: '<?= htmlspecialchars($lang) ?>'
  }).done(res=>{
    if(!res.ok){
      $('#result').css('color','red').text(res.message);
      return;
    }
    let html = `<div>Count: ${res.count}</div>`;
    html += '<table><tr><th>ID</th><th>User</th></tr>';
    res.rows.forEach(r=>{
      html += `<tr><td>${r.id}</td><td>${r.user_name}</td></tr>`;
    });
    html += '</table>';
    $('#result').html(html);
  }).fail(()=>{
    $('#result').css('color','red').text('Request failed');
  });
});
</script>

</body>
</html>
