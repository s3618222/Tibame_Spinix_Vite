<?php
  // 後台管理員登入驗證 API
  // 比照前台會員登入 php/member/signIn_post.php，改查 admin 表、改用獨立 session key ADMIN_ID

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 限定僅能以 POST 串接
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 取得前端 FormData 傳入的管理員帳號與密碼
  $account = trim($_POST["account"] ?? "");
  $password = $_POST["password"] ?? "";

  // 向資料庫查詢帳號是否存在於 admin 資料表
  $sql = "
    SELECT
      admin_id,
      account,
      password,
      name,
      admin_type,
      admin_state
    FROM admin
    WHERE account = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$account]);

  $admin = $stmt->fetch(PDO::FETCH_ASSOC);

  // 驗證密碼：admin 表密碼以 bcrypt 雜湊儲存，用 password_verify 比對（比照前台會員登入）
  $isPasswordCorrect = false;
  if ($admin) {
    $isPasswordCorrect = password_verify($password, $admin["password"]);
  }

  // 帳號不存在或密碼錯誤 → 登入失敗
  if (!$admin || !$isPasswordCorrect) {

    echo json_encode([
        "success" => false,
        "message" => "帳號或密碼錯誤"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 帳號密碼正確，但帳號非「在職」狀態 → 拒絕登入
  if ($admin["admin_state"] !== "在職") {

    echo json_encode([
        "success" => false,
        "message" => "此帳號已停用"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 通過驗證，建立登入 Session（獨立 key ADMIN_ID，與前台會員 MEM_ID 分開）
  session_regenerate_id(true);
  $_SESSION["ADMIN_ID"] = $admin["admin_id"];

  echo json_encode([
    "success" => true,
    "message" => "登入成功",
    "adminName" => $admin["name"],
    "adminType" => $admin["admin_type"]
  ], JSON_UNESCAPED_UNICODE);

?>
