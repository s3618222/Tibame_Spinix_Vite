<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("../common/notification.php");

session_start();

$memberId = $_SESSION["MEM_ID"] ?? null;
if (!$memberId) {
   echo json_encode(['success' => false, 'message' => '請先登入'], JSON_UNESCAPED_UNICODE);
   exit();
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$post_id = (int)($data['postId'] ?? 0);
$action = $data['action'] ?? '';

if ($post_id <= 0) {
   http_response_code(400);
   echo json_encode(['success' => false, 'message' => '未提供有效的 post_id'], JSON_UNESCAPED_UNICODE);
   exit();
}

if (!in_array($action, ['complete', 'cancel'])) {
   http_response_code(400);
   echo json_encode(['success' => false, 'message' => '未傳入有效的狀態'], JSON_UNESCAPED_UNICODE);
   exit();
}

$checksql = "SELECT 
p.post_id, p.mem_id, p.title, m.mem_name, p.status, p.comm_id 
FROM `exchange_post` p 
JOIN member m on p.mem_id = m.MEM_ID 
WHERE post_id = ?";

$checkStmt = $pdo->prepare($checksql);
$checkStmt->execute([$post_id]);
$article = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
   http_response_code(404);
   echo json_encode(['success' => false, 'message' => '找不到此文章'], JSON_UNESCAPED_UNICODE);
   exit();
}

$isOwner = ($article['mem_id'] == $memberId);

// 直接查這篇貼文下,登入者的留言是不是 is_choose = 1,不依賴 exchange_post.comm_id
$applicantCheckStmt = $pdo->prepare(
   "SELECT comm_id, mem_id FROM exchange_comment WHERE post_id = ? AND mem_id = ? AND is_choose = 1"
);
$applicantCheckStmt->execute([$post_id, $memberId]);
$selectedComment = $applicantCheckStmt->fetch(PDO::FETCH_ASSOC);
$isSelectedApplicant = !empty($selectedComment);

// 查出這篇文章被選中的申請者是誰(不限定登入者,通知用)
$selectedApplicantStmt = $pdo->prepare(
   "SELECT mem_id FROM exchange_comment WHERE post_id = ? AND is_choose = 1"
);
$selectedApplicantStmt->execute([$post_id]);
$selectedApplicant = $selectedApplicantStmt->fetch(PDO::FETCH_ASSOC);

// 權限驗證:complete 只有擁有者;cancel 擁有者或被選中的申請者都可以
if ($action === 'complete') {
   if (!$isOwner) {
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => '無權限操作此貼文'], JSON_UNESCAPED_UNICODE);
      exit();
   }
} else { // cancel
   if (!$isOwner && !$isSelectedApplicant) {
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => '無權限操作此貼文'], JSON_UNESCAPED_UNICODE);
      exit();
   }
}

// 狀態驗證:complete 只能從 exchanging;cancel 可以從 pending 或 exchanging
if ($action === 'complete') {
   if ($article['status'] !== 'exchanging') {
      http_response_code(400);
      echo json_encode(['success' => false, 'message' => '目前狀態無法執行此操作'], JSON_UNESCAPED_UNICODE);
      exit();
   }
} else { // cancel
   if (!in_array($article['status'], ['pending', 'exchanging'])) {
      http_response_code(400);
      echo json_encode(['success' => false, 'message' => '目前狀態無法執行此操作'], JSON_UNESCAPED_UNICODE);
      exit();
   }
}

if ($action === 'complete') {
   $newStatus = 'completed';
   $successMessage = '成功交換';

   $sql = "UPDATE `exchange_post` SET `status` = ? WHERE post_id = ?";
   $stmt = $pdo->prepare($sql);
   $stmt->execute([$newStatus, $post_id]);

   // 通知被選中的申請者:交換已完成
   if ($selectedApplicant) {
      $notificationContent = "您與對方的交換「" . $article['title'] . "」已完成";
      createNotification($pdo, (int) $selectedApplicant['mem_id'], $notificationContent);
   }
} else { // cancel
   $newStatus = 'available';
   $successMessage = '取消交換';

   try {
      $pdo->beginTransaction();

      $sql = "UPDATE `exchange_post` SET `status` = ?, `comm_id` = NULL WHERE post_id = ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$newStatus, $post_id]);

      $resetStmt = $pdo->prepare("UPDATE exchange_comment SET is_choose = 0 WHERE post_id = ? AND is_choose = 1");
      $resetStmt->execute([$post_id]);

      $pdo->commit();
   } catch (PDOException $e) {
      $pdo->rollBack();
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => '取消交換失敗,請稍後再試'], JSON_UNESCAPED_UNICODE);
      exit();
   }

   // 通知另一方:交換已取消
   $notificationContent = "您的交換申請「" . $article['title'] . "」已被取消";

   if ($isOwner && $selectedApplicant) {
      // 擁有者取消 → 通知申請者
      createNotification($pdo, (int) $selectedApplicant['mem_id'], $notificationContent);
   } elseif ($isSelectedApplicant) {
      // 申請者取消 → 通知擁有者
      createNotification($pdo, (int) $article['mem_id'], $notificationContent);
   }
}

echo json_encode([
   'success' => true,
   'message' => $successMessage
], JSON_UNESCAPED_UNICODE);

exit();
