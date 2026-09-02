<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("../common/notification.php");

session_start();

$memberId = $_SESSION["MEM_ID"] ?? null;
if (!$memberId) {
   echo json_encode([
      'success' => false,
      'message' => '請先登入'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$comm_id = (int)($data['comm_id'] ?? 0);


$checksql = "SELECT 
   exchange_comment.`post_id`,
   exchange_comment.`is_choose`, 
   exchange_comment.`mem_id`,
   exchange_post.`title` AS `post_title`,
   exchange_post.`mem_id` AS `post_owner_id`
   FROM `exchange_comment` 
   JOIN `exchange_post` ON exchange_comment.`post_id` = exchange_post.`post_id`
   WHERE exchange_comment.`comm_id` = ?
";

$checkStmt = $pdo->prepare($checksql);
$checkStmt->execute([$comm_id]);
$comment = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
   echo json_encode([
      'success' => false,
      'message' => '找不到此留言'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if ((int)$comment['mem_id'] !== (int)$memberId) {
   echo json_encode([
      'success' => false,
      'message' => '無權限確認此交換'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if (!$comment['is_choose']) {
   echo json_encode([
      'success' => false,
      'message' => '此留言尚未被選中,無法確認'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$allowedStatus = ['available', 'exchanging', 'pending', 'completed'];
$newStatus = 'exchanging';

if (!in_array($newStatus, $allowedStatus)) {
   echo json_encode([
      'success' => false,
      'message' => '無效的狀態'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$sql = "UPDATE `exchange_post` SET 
`comm_id`= ? ,
`status` = ?
WHERE exchange_post.`post_id` = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$comm_id, $newStatus, $comment['post_id']]);

if ($stmt->rowCount() === 0) {
   echo json_encode(['success' => false, 'message' => '更新失敗,找不到對應文章'], JSON_UNESCAPED_UNICODE);
   exit;
}

// 通知發文者:對方已確認交換
$notificationContent = "您刊登的物品「" . $comment['post_title'] . "」,對方已確認交換,請盡快聯繫進行後續流程";
createNotification($pdo, (int) $comment['post_owner_id'], $notificationContent);

echo json_encode([
   'success' => true,
   'message' => '已確認'
], JSON_UNESCAPED_UNICODE);
