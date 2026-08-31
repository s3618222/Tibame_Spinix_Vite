<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

session_start();

$memberId = $_SESSION["MEM_ID"] ?? null;
if (!$memberId) {
   echo json_encode([
      'success' => false,
      'message' => '請先登入'
   ], JSON_UNESCAPED_UNICODE);
   exit();
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$post_id = (int)($data['postId'] ?? 0);

// 驗證輸入放最前面，先擋掉無效值
if ($post_id <= 0) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '未提供有效的 post_id'
   ], JSON_UNESCAPED_UNICODE);
   exit();
}

$checksql = "SELECT 
p.post_id ,
p.mem_id,
m.mem_name,
p.status ,
p.comm_id 
FROM `exchange_post` p 
JOIN member m on p.mem_id = m.MEM_ID 
WHERE post_id = ?";

$checkStmt = $pdo->prepare($checksql);
$checkStmt->execute([$post_id]);
$article = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
   echo json_encode([
      'success' => false,
      'message' => '找不到此文章'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}


$checkstmt = $pdo->prepare($checksql);
$checkstmt->execute([$post_id]);
$article = $checkstmt->fetch(PDO::FETCH_ASSOC);


$allowedStatus = ['available', 'exchanging', 'pending', 'completed'];
$newStatus = 'completed';

if (!in_array($newStatus, $allowedStatus)) {
   echo json_encode([
      'success' => false,
      'message' => '無效的狀態'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$sql = "UPDATE `exchange_post` SET `status`=? WHERE post_id = ? ";
$stmt = $pdo->prepare($sql);
$stmt->execute([$newStatus, $post_id]);

echo json_encode([
   'success' => true,
   'message' => '已完成'
], JSON_UNESCAPED_UNICODE);
