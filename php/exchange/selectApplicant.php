<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("../common/notification.php");

session_start();
$memberId = $_SESSION["MEM_ID"] ?? null;

if (!$memberId) {
   echo json_encode(['success' => false, 'message' => '請先登入'], JSON_UNESCAPED_UNICODE);
   exit;
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$allowedStatus = ['available', 'exchanging', 'pending', 'completed'];
$postId = (int)($data['post_id'] ?? 0);
$commId = (int)($data['comm_id'] ?? 0);

// 確認文章存在且是本人的
$ownerStmt = $pdo->prepare("SELECT mem_id, title FROM exchange_post WHERE post_id = ?");
$ownerStmt->execute([$postId]);
$post = $ownerStmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
   echo json_encode(['success' => false, 'message' => '找不到此商品'], JSON_UNESCAPED_UNICODE);
   exit;
}
if ((int)$post['mem_id'] !== (int)$memberId) {
   echo json_encode(['success' => false, 'message' => '無權限操作此商品'], JSON_UNESCAPED_UNICODE);
   exit;
}

// 確認要選中的留言存在,且屬於這篇文章,並取得留言者 mem_id
$commentStmt = $pdo->prepare("SELECT mem_id FROM exchange_comment WHERE comm_id = ? AND post_id = ?");
$commentStmt->execute([$commId, $postId]);
$comment = $commentStmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
   echo json_encode(['success' => false, 'message' => '找不到此留言'], JSON_UNESCAPED_UNICODE);
   exit;
}

$statusStmt = $pdo->prepare("UPDATE exchange_post SET status = 'pending' WHERE post_id = ?");
$statusStmt->execute([$postId]);

// 先把這篇文章所有留言的 is_choose 重設為 0,再把被選中的設為 1
$resetStmt = $pdo->prepare("UPDATE exchange_comment SET is_choose = 0 WHERE post_id = ?");
$resetStmt->execute([$postId]);

$chooseStmt = $pdo->prepare("UPDATE exchange_comment SET is_choose = 1 WHERE comm_id = ? AND post_id = ?");
$chooseStmt->execute([$commId, $postId]);

// 通知被選中的留言者
$notificationContent = "您在「" . $post['title'] . "」的留言已被選為交換對象,請盡快確認並聯繫對方吧。";
createNotification($pdo, (int) $comment['mem_id'], $notificationContent);

echo json_encode(['success' => true, 'message' => '已選擇交換對象'], JSON_UNESCAPED_UNICODE);
