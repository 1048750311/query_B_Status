<?php
/**
 * api.php
 * ------------------------------------------------------------
 * 【API】指定期間内に status='B' になっているユーザー一覧を返す
 * - GET: start=YYYY-MM-DD
 * - GET: end=YYYY-MM-DD
 * - GET: lang=ja/en/zh-cn/zh-tw/vi（任意）
 * ------------------------------------------------------------
 */

require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

/** 言語（メッセージ用） */
$lang = $_GET['lang'] ?? 'ja';

/** 簡易メッセージ辞書（必要なら増やせます） */
$msg = [
  'ja' => [
    'DATE_INVALID' => '日付形式は YYYY-MM-DD で入力してください',
    'DATE_RANGE'   => '開始日は終了日より前の日付を指定してください',
    'REQUEST_FAIL' => '通信エラーが発生しました',
  ],
  'en' => [
    'DATE_INVALID' => 'Date format must be YYYY-MM-DD',
    'DATE_RANGE'   => 'Start date must not be later than end date',
    'REQUEST_FAIL' => 'Request failed',
  ],
  'zh-cn' => [
    'DATE_INVALID' => '日期格式必须是 YYYY-MM-DD',
    'DATE_RANGE'   => '开始日期不能大于结束日期',
    'REQUEST_FAIL' => '请求失败',
  ],
  'zh-tw' => [
    'DATE_INVALID' => '日期格式必須是 YYYY-MM-DD',
    'DATE_RANGE'   => '開始日期不能大於結束日期',
    'REQUEST_FAIL' => '請求失敗',
  ],
  'vi' => [
    'DATE_INVALID' => 'Định dạng ngày phải là YYYY-MM-DD',
    'DATE_RANGE'   => 'Ngày bắt đầu không được sau ngày kết thúc',
    'REQUEST_FAIL' => 'Yêu cầu thất bại',
  ],
];

/** メッセージ取得（該当がなければjaへフォールバック） */
function t($lang, $msg, $key) {
  if (isset($msg[$lang][$key])) return $msg[$lang][$key];
  if (isset($msg['ja'][$key])) return $msg['ja'][$key];
  return $key;
}

/** エラー返却 */
function fail($code, $message, $http = 400) {
  http_response_code($http);
  echo json_encode([
    'ok' => false,
    'code' => $code,
    'message' => $message,
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$start = $_GET['start'] ?? '';
$end   = $_GET['end'] ?? '';

/** 日付形式チェック（YYYY-MM-DD） */
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
  fail('DATE_INVALID', t($lang, $msg, 'DATE_INVALID'));
}

/** 期間の大小チェック（文字列比較でOK：YYYY-MM-DD形式のため） */
if ($start > $end) {
  fail('DATE_RANGE', t($lang, $msg, 'DATE_RANGE'));
}

/**
 * status_track: 履歴（status='B' & 日付範囲）
 * main_list   : ユーザー情報（id, user_name）
 * ※ DISTINCT：同じユーザーが期間内で複数回Bになっても一覧は1回だけ表示
 */
$sql = "
SELECT DISTINCT m.id, m.user_name
FROM status_track s
JOIN main_list m ON m.id = s.id
WHERE s.status = 'B'
  AND s.status_date BETWEEN :s AND :e
ORDER BY m.id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':s' => $start, ':e' => $end]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'ok'    => true,
  'start' => $start,
  'end'   => $end,
  'count' => count($rows),
  'rows'  => $rows,
], JSON_UNESCAPED_UNICODE);
