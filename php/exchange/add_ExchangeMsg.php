<?php
session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("./exchange_access_check.php");
require_once("../common/notification.php");

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$memberId = $_SESSION["MEM_ID"] ?? null;
$ExchangeID = isset($data["id"]) ? (int)$data["id"] : 0;

if (!$memberId) {
   echo json_encode(
      ['success' => false, 'message' => '請先登入'],
      JSON_UNESCAPED_UNICODE
   );
   exit;
}

// 交換功能停權檢查(受限時禁止申請)
$access = checkExchangeAccess($pdo, $memberId);
if (!$access["allowed"]) {
   http_response_code(403);

   if ($access["status"] === "TEMP-RESTRICT") {
      $msg = "你的二手交換功能目前暫時受限,受限期間無法申請交換。";
   } elseif ($access["status"] === "PERMA-RESTRICT") {
      $msg = "你的二手交換功能目前已被限制使用,無法申請交換。";
   } else {
      $msg = "目前無法使用二手交換相關功能。";
   }

   echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
   exit;
}

$checkStmt = $pdo->prepare("SELECT `post_id`, `title`, `mem_id` FROM `exchange_post` WHERE `post_id` = ? AND `status` = 'available'");
$checkStmt->execute([$ExchangeID]);
$post = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
   echo json_encode(
      ['success' => false, 'message' => '商品不存在或已下架'],
      JSON_UNESCAPED_UNICODE
   );
   exit;
}

$sql = "INSERT INTO `exchange_comment`(`post_id`,`mem_id`,`content`,`comm_contact`) VALUES (?,?,?,?)";

$stmt = $pdo->prepare($sql);
$content = $data['content'] ?? "";
$comm_contact = $data['comm_contact'] ?? "";
$stmt->execute([$ExchangeID, $memberId, $content, $comm_contact]);

// 查詢新增的留言
$newCommentId = $pdo->lastInsertId();

$notificationContent = "您刊登的物品「" . $post["title"] . "」有新的交換留言,請盡快查看。";
createNotification($pdo, (int) $post["mem_id"], $notificationContent);

$selectSql = "SELECT 
   `comm_id`,
   `post_id`,
   exchange_comment.`mem_id`,
   member.`mem_name` AS `name`,
   member.`MEM_PHOTO` AS `headshot`,
   `content`,
   DATE(`create_time`) AS `create_time`,
   `is_show`,
   `remove_reason`,
   `is_choose`,
   `comm_contact` 
   FROM `exchange_comment` 
   JOIN member on exchange_comment.`mem_id` = member.`mem_id`
   WHERE comm_id = ?
   ";

$selectStmt = $pdo->prepare($selectSql);
$selectStmt->execute([$newCommentId]);
$newComment = $selectStmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
   "success" => true,
   "message" => "申請成功",
   "data" => $newComment
], JSON_UNESCAPED_UNICODE);
