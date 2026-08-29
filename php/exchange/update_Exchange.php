<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");



$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$post_id =  (int)($data['post_id'] ?? 0);
$type = $data['type'] ?? '';
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$want_item = $data['want_item'] ?? '';
$condition = $data['condition'] ?? '';

$sql = "UPDATE `exchange_post` SET 
`type`= ?,
`title`= ?,
`description`= ?,
`want_item`= ?,
`condition`= ?
WHERE `post_id`= ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$type, $title, $description, $want_item, $condition, $post_id]);
echo json_encode(['success' => true, 'message' => '更新成功'], JSON_UNESCAPED_UNICODE);
