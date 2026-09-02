<?php

header('Content-Type: application/json; charset=utf-8');

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
define('IS_INCLUDED_AS_LIBRARY', true);
require_once("./get_Exchange.php");

$ExchangeID = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$memberId = $_SESSION["MEM_ID"] ?? null;
$isAdmin = isset($_SESSION["ADMIN_ID"]);


// 驗證是否有傳入有效 ID
if ($ExchangeID <= 0) {
   http_response_code(400);
   echo json_encode([
      'status' => 'error',
      'message' => '未傳入有效的商品 ID'
   ]);
   exit();
}


try {
   $rows = getExchange($pdo, $ExchangeID, [], true);
   $prod = $rows[0] ?? null;

   if ($prod) {
      // 成功找到商品
      $isAgreed = ($prod['status'] === 'exchanging') && !empty($prod['comm_id']);
      if (!$isAgreed) {
         unset($prod['post_contact']);
      }

      $isOwner = ($memberId !== null && (string)$prod['mem_id'] === (string)$memberId);


      // 文章被下架時
      if (!$prod['is_show'] && !$isOwner && !$isAdmin) {
         $hasComment = false;
         if ($memberId !== null) {
            $check = "SELECT COUNT(*) FROM `exchange_comment` WHERE post_id = ? AND mem_id = ?";
            $checkStmt = $pdo->prepare($check);
            $checkStmt->execute([$ExchangeID, $memberId]);
            $hasComment = $checkStmt->fetchColumn() > 0;
         }
         // 有留言過的人
         if ($hasComment) {
            echo json_encode([
               'status' => 'success',
               'data' => [
                  'post_id' => $prod['post_id'],
                  'is_show' => false,
                  'removed_notice' => true
               ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
         } else { // 完全無關的人
            http_response_code(404);
            echo json_encode([
               'status' => 'error',
               'message' => '此文章已被下架'
            ]);
            exit();
         }
      }
      echo json_encode([
         'status' => 'success',
         'agreen' => $isAgreed,
         'data' => $prod
      ], JSON_UNESCAPED_UNICODE);
   } else {
      // 資料庫查無此 ID
      http_response_code(404);
      echo json_encode([
         'status' => 'error',
         'message' => '找不到該商品資料'
      ]);
   }
} catch (PDOException $e) {
   http_response_code(500);
   echo json_encode([
      'status' => 'error',
      'message' => '資料庫讀取失敗：' . $e->getMessage()
   ]);
}

exit();
