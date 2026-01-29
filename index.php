<?php
/**
 * index.php
 * ------------------------------------------------------------
 * 画面：指定期間内の status=B ユーザー検索
 * - 言語切替：右側のボタンにホバーすると候補が表示される（ドロップダウン）
 * - クリックするとページをリロードして言語を切替
 * - デフォルト言語：ja
 * ------------------------------------------------------------
 */

session_start();

/** 言語判定：GET優先 → session → デフォルト ja */
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'ja');
$_SESSION['lang'] = $lang;

/** 画面文言（PHPで描画する＝言語切替時はページ再読み込み） */
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
    'error' => '通信エラーが発生しました',
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
    'error' => 'Request failed',
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
    'error' => '請求失敗',
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
    'error' => '请求失败',
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
    'error' => 'Yêu cầu thất bại',
  ],
];

$t = $I18N[$lang] ?? $I18N['ja'];

/** 言語表示ラベル（右側ボタンに表示する） */
$langLabel = [
  'ja' => 'JA',
  'en' => 'EN',
  'zh-tw' => '繁',
  'zh-cn' => '简',
  'vi' => 'VI',
];

/** クエリ維持：lang だけ差し替える */
function qs_keep($override = []) {
  $params = $_GET;
  unset($params['lang']);
  return http_build_query(array_merge($params, $override));
}

/** 現在言語の表示（不明ならJA） */
$currentLabel = $langLabel[$lang] ?? 'JA';
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
    font-family: system-ui, -apple-system, Segoe UI, Arial;
    background:#f6f7fb;
    margin:0;
    padding:20px;
  }
  .card{
    max-width:820px;
    margin:0 auto;
    background:#fff;
    border-radius:14px;
    padding:20px;
    box-shadow:0 8px 24px rgba(0,0,0,.08);
  }
  .row{display:flex;gap:12px;flex-wrap:wrap;align-items:end;}
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
  .muted{color:#6b7280;font-size:12px;}

  /* =========================
     右側：ホバーで展開する言語ドロップダウン
     - 画像なし、文字のみ
     - :hover と :focus-within で表示（キーボード操作もOK）
     ========================= */
  .lang-float{
    position:fixed;
    top:36%;
    right:16px;
    z-index:9999;
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

  /* 上の「現在言語」ボタン */
  .lang-current{
    display:flex;
    align-items:center;
    justify-content:center;
    height:46px;
    color:#333;
    text-decoration:none;
    background:#fff;
    position:relative;
  }

  /* 小さな枠（画像っぽい見た目のバッジ） */
  .lang-current span{
    display:inline-block;
    padding:3px 7px;
    border:2px solid #666;
    border-radius:6px;
    line-height:1;
  }

  /* 下の候補リスト（初期は非表示） */
  .lang-menu{
    display:none;
    border-top:1px solid #eee;
  }

  /* ホバー or フォーカスで展開 */
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

  /* 現在言語の候補をハイライト */
  .lang-item.active{
    background:#111;
    color:#fff;
  }
</style>
</head>

<body>

<!-- 右側：ホバーで候補が出る -->
<div class="lang-float">
  <div class="lang-box">
    <!-- 現在言語（ここにホバーすると下が展開） -->
    <a class="lang-current" href="javascript:void(0)" aria-label="Current language">
      <span><?= htmlspecialchars($currentLabel) ?></span>
    </a>

    <!-- 候補（クリックで ?lang=xx に遷移 → ページ再読み込み） -->
    <div class="lang-menu" aria-label="Language menu">
      <a class="lang-item <?= $lang==='ja'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'ja']) ?>">JA</a>
      <a class="lang-item <?= $lang==='en'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'en']) ?>">EN</a>
      <a class="lang-item <?= $lang==='zh-tw'?'active':'' ?>" href="?<?= qs_keep(['lang'=>'zh-tw']) ?>">繁</a>
      <a class
