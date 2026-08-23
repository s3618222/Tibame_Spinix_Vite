<?php
  // 取得目前登入會員的通知列表 API
  
  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 GET 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  $memberId = $_SESSION["MEM_ID"] ?? null;

  //需登入，才可取得會員通知
  if (!$memberId) {

    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //查詢當前會員的所有通知
  $sql = "
    SELECT
      ntfn_id,
      mem_id,
      content,
      is_read,
      create_time

    FROM notification

    WHERE mem_id = ?

    ORDER BY create_time DESC
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$memberId]);

  $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    "success" => true,
    "notifications" => $notifications
  ], JSON_UNESCAPED_UNICODE);
  
?>