<?php
  // 會員中心「個人資料」- 更新會員基本資料（名稱 / 性別 / 生日）API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 僅允許 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 登入守衛
  $memberId = $_SESSION["MEM_ID"] ?? null;
  if (!$memberId) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "請先登入"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 取值
  $name   = trim($_POST["name"] ?? "");
  $gender = trim($_POST["gender"] ?? "");
  $birth  = trim($_POST["birth"] ?? "");

  // 統一失敗回傳
  function fail($message) {
    echo json_encode([
        "success" => false,
        "message" => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 驗證：名稱
  if ($name === "") {
    fail("請填寫使用者名稱");
  }
  if (mb_strlen($name) > 30) {
    fail("使用者名稱不可超過 30 字");
  }

  // 驗證：性別 enum 白名單
  if (!in_array($gender, ["MALE", "FEMALE"], true)) {
    fail("性別欄位不正確");
  }

  // 驗證：生日（格式 + 非未來）
  if ($birth === "") {
    fail("請選擇出生年月日");
  }
  $birthDate = DateTime::createFromFormat("Y-m-d", $birth);
  $birthErrors = DateTime::getLastErrors();
  if (!$birthDate ||
      ($birthErrors && ($birthErrors["warning_count"] > 0 || $birthErrors["error_count"] > 0))) {
    fail("出生年月日不正確");
  }
  if ($birthDate > new DateTime("today")) {
    fail("出生年月日不正確");
  }

  // 更新
  $stmt = $pdo->prepare("
    UPDATE member
    SET MEM_NAME = ?, MEM_GENDER = ?, MEM_BIRTH = ?
    WHERE MEM_ID = ?
  ");
  $stmt->execute([$name, $gender, $birth, $memberId]);

  echo json_encode([
    "success" => true,
    "message" => "個人資料已更新",
    "member" => [
        "name" => $name,
        "gender" => $gender,
        "birth" => $birth
    ]
  ], JSON_UNESCAPED_UNICODE);

?>
