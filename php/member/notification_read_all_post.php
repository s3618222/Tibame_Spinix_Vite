<?php
  // 會員通知 - 將所有通知標記為已讀 API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 需用 POST
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

  //將當前登入會員的所有相關通知都更新成「已讀」狀態
  $sql = "
    UPDATE notification
    SET is_read = 1
    WHERE mem_id = ?
      AND is_read = 0
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$memberId]);

  echo json_encode([
    "success" => true,
    "message" => "所有通知已標記為已讀"
  ], JSON_UNESCAPED_UNICODE);
  
?>