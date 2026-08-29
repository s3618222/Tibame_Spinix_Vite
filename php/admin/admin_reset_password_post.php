<?php
  // 後台「管理員帳號管理」— 重設管理員密碼（以 bcrypt 雜湊儲存）

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 守衛：管理員帳號管理僅限超級管理員
  require_once("../common/admin_guard_super.php");

  // 限定 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 取得前端資料
  $adminId         = (int) ($_POST["adminId"] ?? 0);
  $password        = $_POST["password"] ?? "";
  $confirmPassword = $_POST["confirmPassword"] ?? "";

  // 驗證：密碼非空
  if ($password === "") {
    echo json_encode([
      "success" => false,
      "message" => "密碼為必填"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 驗證：兩次密碼一致
  if ($password !== $confirmPassword) {
    echo json_encode([
      "success" => false,
      "message" => "兩次輸入的密碼不一致"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 驗證：管理員存在
  $check = $pdo->prepare("SELECT admin_type FROM admin WHERE admin_id = ?");
  $check->execute([$adminId]);
  $target = $check->fetch(PDO::FETCH_ASSOC);
  if (!$target) {
    echo json_encode([
      "success" => false,
      "message" => "查無此管理員"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 防呆：不可重設超級管理員的密碼
  if ($target["admin_type"] === "超級管理員") {
    http_response_code(403);
    echo json_encode([
      "success" => false,
      "message" => "不可重設超級管理員的密碼"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 更新密碼（雜湊）
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
  $stmt->execute([$hash, $adminId]);

  echo json_encode([
    "success" => true,
    "message" => "已重設密碼"
  ], JSON_UNESCAPED_UNICODE);

?>
