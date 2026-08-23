<?php
  // 將單筆通知標記為已讀 API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");


   // 需使用 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  $memberId = $_SESSION["MEM_ID"] ?? null;

  if (!$memberId) {

    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得前端傳來的通知ID
  $notificationId = (int) ($_POST["notification_id"] ?? 0);

  if ($notificationId <= 0) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "通知編號無效"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //將後端資料庫中對應的通知改為已讀
  $sql = "
    UPDATE notification
    SET is_read = 1
    WHERE ntfn_id = ?
      AND mem_id = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $notificationId,
    $memberId
  ]);

    echo json_encode([
    "success" => true,
    "message" => "通知已標記為已讀"
  ], JSON_UNESCAPED_UNICODE);
  
?>