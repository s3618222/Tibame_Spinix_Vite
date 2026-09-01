<?php
session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

header("Content-Type: application/json; charset=utf-8");

$memberId = $_SESSION["MEM_ID"] ?? null;

if (!$memberId) {
   http_response_code(401);
   echo json_encode([
      "success" => false,
      "message" => "請先登入後再查看申訴詳情"
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$appealId = (int)($_GET['appeal_id'] ?? 0);
if ($appealId <= 0) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '申訴編號無效'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$sql = "SELECT 
ae.`ae_id`, 
ae.`post_id`, 
ae.`comm_id`,
ae.`complainant_mem_id`,
complainant.`MEM_NAME` AS complainant_name,
ae.`respondent_mem_id`,
respondent.`MEM_NAME` AS respondent_name,
ae.`ae_content`,
ae.`ae_status`,
ae. `create_time`,
ae.`ae_evidence`,
ae.`responded_at`,
ae.`responded_text` 
FROM `appeal_exchange` ae
JOIN member AS complainant on ae.`complainant_mem_id` = complainant.`MEM_ID`
JOIN member AS respondent on ae.`respondent_mem_id` = respondent.`MEM_ID`
WHERE ae.`ae_id` = ?
AND ae.`complainant_mem_id` = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$appealId, $memberId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '找不到這筆申訴資料'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if ($data["post_id"]) {
   $sql = "SELECT title FROM exchange_post WHERE post_id = ?";
   $stmt = $pdo->prepare($sql);
   $stmt->execute([$data["post_id"]]);
   $target = $stmt->fetch(PDO::FETCH_ASSOC);
   $title = $target ? $target["title"] : "（原文已不存在）";
} else {
   $sql = "SELECT content FROM exchange_comment WHERE comm_id= ?";
   $stmt = $pdo->prepare($sql);
   $stmt->execute([$data["comm_id"]]);
   $target = $stmt->fetch(PDO::FETCH_ASSOC);

   if ($target) {
      $plainContent = strip_tags($target["content"]);
      $title = mb_strlen($plainContent) > 15
         ? mb_substr($plainContent, 0, 15) . "..."
         : $plainContent;
   } else {
      $title = "（原留言已不存在）";
   }
}

$images = [];

if (!empty($data["ae_evidence"])) {
   $decodedImages = json_decode($data["ae_evidence"], true);
   if (is_array($decodedImages)) {
      $images = $decodedImages;
   }
}

echo json_encode([
   "success" => true,
   "appeal" => [
      "appealId" => (int) $data["ae_id"],
      "type" => $data["post_id"] ? "刊登交換文章" : "申請交換留言",
      "battleTitle" => $title,
      "targetId" => (int) $data["respondent_mem_id"],
      "reporterName" => $data["complainant_name"],
      "targetName" => $data["respondent_name"],
      "content" => $data["ae_content"],
      "status" => $data["ae_status"],
      "createdAt" => $data["create_time"],
      "images" => $images,
      "result" => $data["responded_text"],
      "resultDate" => $data["responded_at"]
   ]
], JSON_UNESCAPED_UNICODE);
