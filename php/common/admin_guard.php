<?php
  // 後台 API 共用守衛：確認請求來自「已登入的管理員」，否則回 401 擋下
  //
  // 用法：在後台專用 API 的開頭加入一行即可（放在 session_start / cors / connect 之後）：
  //   require_once("../common/admin_guard.php");
  //
  // 檢查的 session key 與登入 API（php/admin/admin_signin_post.php）一致：ADMIN_ID

  // 自足：若呼叫端尚未啟動 session，這裡補啟動，不依賴 require 的先後順序
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }

  // 沒有 ADMIN_ID → 視為未登入，直接中止，後面查資料/改資料的程式碼都不會執行
  if (!isset($_SESSION["ADMIN_ID"])) {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "未登入或登入已逾期"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }
?>
