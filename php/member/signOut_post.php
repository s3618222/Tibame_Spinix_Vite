<?php
  //會員登出api
  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 限制api請求方式只能使用 POST (GET適用取得資料，當牽涉狀態改變時，適用POST)
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {

      http_response_code(405);

      echo json_encode([
          "success" => false,
          "message" => "僅允許 POST 請求"
      ], JSON_UNESCAPED_UNICODE);

      exit;
  }

  // 清除目前 Session中原先儲存的登入者資訊
  $_SESSION = [];

  // 再銷毀目前的Session
  session_destroy();

  //回傳登出成功
  echo json_encode([
      "success" => true,
      "message" => "登出成功"
  ], JSON_UNESCAPED_UNICODE);
?>