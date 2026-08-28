<?php
  // 後台管理員登出 API
  // 只清除 ADMIN_ID，不整個 session_destroy，避免波及不相關的前台會員 session

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 限定僅能以 POST 串接（牽涉狀態改變）
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 只移除管理員的登入狀態
  unset($_SESSION["ADMIN_ID"]);

  echo json_encode([
      "success" => true,
      "message" => "登出成功"
  ], JSON_UNESCAPED_UNICODE);

?>
