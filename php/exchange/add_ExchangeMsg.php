<?php
session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

$memberId = $_SESSION["MEM_ID"] ?? null;
$ExchangeID = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if (!$memberId) {
   echo json_encode(
      ['success' => false, 'message' => '請先登入'],
      JSON_UNESCAPED_UNICODE
   );

   exit;
}

$checkStmt = $pdo->prepare("SELECT `post_id` FROM `exchange_post` WHERE `post_id` = ? AND `status` = 'available'");
$checkStmt->execute([$ExchangeID]);

if (!$checkStmt) {
   echo json_encode(
      ['success' => false, 'message' => '商品不存在或已下架'],
      JSON_UNESCAPED_UNICODE
   );
}

$sql = "INSERT INTO `exchange_comment`(`post_id`,`mem_id`,`content`,`comm_contact`) VALUES (?,?,?,?)";

$stmt = $pdo->prepare($sql);
$content = $_POST['content'] ?? "";
$comm_contact = $_POST['comm_contact'] ?? "";
$stmt->execute([$ExchangeID, $memberId, $content, $comm_contact]);

echo json_encode([
   "success" => true,
   "message" => "申請成功"
], JSON_UNESCAPED_UNICODE);
