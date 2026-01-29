<?php
require __DIR__.'/db.php';
header('Content-Type: application/json; charset=utf-8');

function fail($code,$http=400){
  http_response_code($http);
  echo json_encode(['ok'=>false,'code'=>$code],JSON_UNESCAPED_UNICODE);
  exit;
}

$start=$_GET['start']??'';
$end=$_GET['end']??'';

if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$start)
 || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$end)){
  fail('DATE_INVALID');
}

if($start>$end){
  fail('DATE_RANGE');
}

$sql="
SELECT DISTINCT m.id,m.user_name
FROM status_track s
JOIN main_list m ON m.id=s.id
WHERE s.status='B'
  AND s.status_date BETWEEN :s AND :e
ORDER BY m.id
";

$stmt=$pdo->prepare($sql);
$stmt->execute([':s'=>$start,':e'=>$end]);
$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'ok'=>true,
  'start'=>$start,
  'end'=>$end,
  'count'=>count($rows),
  'rows'=>$rows
],JSON_UNESCAPED_UNICODE);
