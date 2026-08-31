<?php
  // 會員帳號是否已被註冊 檢查 api（供註冊第一步即時檢查）

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 取得欲檢查的帳號
  $account = trim($_GET["account"] ?? "");

  // 格式兜底（前端已先擋 email 格式與空值）
  if ($account === "" || !filter_var($account, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "帳號格式不正確"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 查詢帳號是否已存在
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE MEM_ACCOUNT = ?");
  $stmt->execute([$account]);
  $exists = $stmt->fetchColumn() > 0;

  echo json_encode([
    "success" => true,
    "available" => !$exists // true = 可註冊（未被使用）
  ], JSON_UNESCAPED_UNICODE);

?>
