<?php
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$start = trim($_GET['start'] ?? '');
$end   = trim($_GET['end'] ?? '');

function is_date($s) {
  return preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) === 1;
}

if (!is_date($start) || !is_date($end)) {
  http_response_code(400);
  echo json_encode([
    'ok' => false,
    'msg' => '日付形式は YYYY-MM-DD で入力してください'
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($start > $end) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => '開始日は終了日より前の日付を指定してください'], JSON_UNESCAPED_UNICODE);
  exit;
}

$sql = "
SELECT DISTINCT
  m.id,
  m.user_name
FROM status_track s
JOIN main_list m ON m.id = s.id
WHERE s.status = 'B'
  AND s.status_date BETWEEN :start AND :end
ORDER BY m.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':start' => $start,
  ':end'   => $end,
]);

$rows = $stmt->fetchAll();

echo json_encode([
  'ok' => true,
  'start' => $start,
  'end' => $end,
  'count' => count($rows),
  'rows' => $rows
], JSON_UNESCAPED_UNICODE);

