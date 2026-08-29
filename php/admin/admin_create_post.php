<?php
  // 後台「管理員帳號管理」— 新增管理員
  // 密碼以 bcrypt 雜湊儲存；新帳號預設為「一般管理員」「在職」

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 守衛：僅允許已登入的管理員存取
  require_once("../common/admin_guard.php");

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
  $account         = trim($_POST["account"] ?? "");
  $name            = trim($_POST["name"] ?? "");
  $password        = $_POST["password"] ?? "";
  $confirmPassword = $_POST["confirmPassword"] ?? "";

  // 驗證：欄位非空
  if ($account === "" || $name === "" || $password === "") {
    echo json_encode([
      "success" => false,
      "message" => "帳號、名稱、密碼皆為必填"
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

  // 驗證：帳號未被使用
  $check = $pdo->prepare("SELECT admin_id FROM admin WHERE account = ?");
  $check->execute([$account]);
  if ($check->fetch()) {
    echo json_encode([
      "success" => false,
      "message" => "此帳號已被使用"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 寫入（密碼雜湊、預設一般管理員/在職）
  $hash = password_hash($password, PASSWORD_DEFAULT);

  $sql = "
    INSERT INTO admin (account, password, name, create_time, admin_type, admin_state)
    VALUES (?, ?, ?, NOW(), '一般管理員', '在職')
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$account, $hash, $name]);

  echo json_encode([
    "success" => true,
    "message" => "新增管理員成功"
  ], JSON_UNESCAPED_UNICODE);

?>
