<?php

header('Content-Type: application/json; charset=utf-8');

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
define('IS_INCLUDED_AS_LIBRARY', true);
require_once("./get_Exchange.php");

$ExchangeID = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$From = isset($_GET["from"]);
// $memId = $_SESSION["MEM_ID"];


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
   $rows = getExchange($pdo, $ExchangeID);
   $prod = $rows[0] ?? null;

   if ($prod) {
      // 成功找到商品
      echo json_encode([
         'status' => 'success',
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
