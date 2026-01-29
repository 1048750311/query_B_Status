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

$lang = $_GET['lang'] ?? 'ja';

/** メッセージ辞書（必要に応じて追加OK） */
$MSG = [
  'ja' => [
    'DATE_INVALID' => '日付形式は YYYY-MM-DD で入力してください',
    'DATE_RANGE'   => '開始日は終了日より前の日付を指定してください',
    'DB_ERROR'     => 'データ取得に失敗しました',
  ],
  'en' => [
    'DATE_INVALID' => 'Date format must be YYYY-MM-DD',
    'DATE_RANGE'   => 'Start date must not be later than end date',
    'DB_ERROR'     => 'Failed to fetch data',
  ],
  'zh-cn' => [
    'DATE_INVALID' => '日期格式必须是 YYYY-MM-DD',
    'DATE_RANGE'   => '开始日期不能大于结束日期',
    'DB_ERROR'     => '获取数据失败',
  ],
  'zh-tw' => [
    'DATE_INVALID' => '日期格式必須是 YYYY-MM-DD',
    'DATE_RANGE'   => '開始日期不能大於結束日期',
    'DB_ERROR'     => '取得資料失敗',
  ],
  'vi' => [
    'DATE_INVALID' => 'Định dạng ngày phải là YYYY-MM-DD',
    'DATE_RANGE'   => 'Ngày bắt đầu không được sau ngày kết thúc',
    'DB_ERROR'     => 'Không thể lấy dữ liệu',
  ],
];

function msg($lang, $MSG, $code) {
  if (isset($MSG[$lang][$code])) return $MSG[$lang][$code];
  if (isset($MSG['ja'][$code])) return $MSG['ja'][$code];
  return $code;
}

/** エラー応答 */
function fail_json($code, $message, $http = 400) {
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

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
  fail_json('DATE_INVALID', msg($lang, $MSG, 'DATE_INVALID'));
}

if ($start > $end) {
  fail_json('DATE_RANGE', msg($lang, $MSG, 'DATE_RANGE'));
}

$sql = "
SELECT DISTINCT m.id, m.user_name
FROM status_track s
JOIN main_list m ON m.id = s.id
WHERE s.status = 'B'
  AND s.status_date BETWEEN :s AND :e
ORDER BY m.id
";

try {
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':s' => $start, ':e' => $end]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  // 本番では詳細エラーは返さない
  fail_json('DB_ERROR', msg($lang, $MSG, 'DB_ERROR'), 500);
}

echo json_encode([
  'ok'    => true,
  'start' => $start,
  'end'   => $end,
  'count' => count($rows),
  'rows'  => $rows,
], JSON_UNESCAPED_UNICODE);
