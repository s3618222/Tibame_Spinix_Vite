<?php
session_start();
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
header("Content-Type: application/json; charset=utf-8");

$membetId = $_SESSION["MEM_ID"] ?? null;

if (!$membetId) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '尚未登入'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$post_id = (int)($_GET['post_id'] ?? 0);
$comm_id = (int)($_GET['comm_id'] ?? 0);

if (($post_id <= 0 && $comm_id <= 0) || ($post_id > 0 && $comm_id > 0)) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '檢舉無效'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

try {
   if ($post_id > 0) { // 檢舉交換案件: 被檢舉人 = 刊登者
      $sql = "SELECT 
      `title` AS title,
      p.`mem_id` AS respondent_mem_id ,
      member.`MEM_NAME` AS respondent_mem_name 
      FROM `exchange_post` p 
      JOIN member on p.`mem_id` = member.`MEM_ID`
      WHERE p.`post_id`= ?
      ";

      $stmt = $pdo->prepare($sql);
      $stmt->execute([$post_id]);
   } else { // 檢舉留言: 被檢舉人 = 申請者
      $sql = "SELECT 
      `content` AS title,
      c.`mem_id` AS respondent_mem_id ,
      member.`MEM_NAME` AS respondent_mem_name 
      FROM `exchange_comment` c 
      JOIN member on c.`mem_id` = member.`MEM_ID`
      WHERE c.`comm_id`= ?
      ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$comm_id]);
   }

   $result = $stmt->fetch(PDO::FETCH_ASSOC);

   if (!$result) {
      http_response_code(400);
      echo json_encode([
         'success' => false,
         'message' => '檢舉失敗'
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }

   if ($comm_id > 0) {
      $plainContent = strip_tags($result['title']);

      if (mb_strlen($plainContent) > 15) {
         $title = mb_substr($plainContent, 0, 15) . "...";
      } else {
         $title = $plainContent;
      }
   } else {
      $title = $result['title'];
   }

   if ((int)$result['respondent_mem_id'] === (int)$membetId) {
      http_response_code(400);
      echo json_encode([
         'success' => false,
         'message' => '檢舉失敗'
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }

   echo json_encode([
      'success' => true,
      "title" => $title,
      "respondentName" => $result["respondent_mem_name"],
      "respondentMemId" => $result["respondent_mem_id"]
   ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
   http_response_code(500);
   echo json_encode([
      "success" => false,
      "message" => "取得申訴資料失敗"
   ], JSON_UNESCAPED_UNICODE);
}
