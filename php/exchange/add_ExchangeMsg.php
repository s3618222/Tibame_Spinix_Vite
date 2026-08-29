<?php
session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

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

$checkStmt = $pdo->prepare("SELECT `post_id` FROM `exchange_post` WHERE `post_id` = ? AND `status` = 'available'");
$checkStmt->execute([$ExchangeID]);

if (!$checkStmt->fetch()) {
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

echo json_encode([
   "success" => true,
   "message" => "申請成功"
], JSON_UNESCAPED_UNICODE);


$newCommentId = $pdo -> lastInsertId();

