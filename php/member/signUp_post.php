<?php
  // 會員註冊 api

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 設定註冊API僅能以POST方式串接
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 取得前端 FormData 傳入的欄位（密碼不 trim，避免動到使用者實際輸入）
  $account         = trim($_POST["account"] ?? "");
  $password        = $_POST["password"] ?? "";
  $confirmPassword = $_POST["confirmPassword"] ?? "";
  $name            = trim($_POST["name"] ?? "");
  $gender          = trim($_POST["gender"] ?? "");
  $birth           = trim($_POST["birth"] ?? "");
  $repName         = trim($_POST["repName"] ?? "");
  $repRelation     = trim($_POST["repRelation"] ?? "");
  $repPhone        = trim($_POST["repPhone"] ?? "");

  // 統一的失敗回傳
  function fail($message) {
    echo json_encode([
        "success" => false,
        "message" => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 1. 基本必填
  if ($account === "" || $password === "" || $confirmPassword === "" ||
      $name === "" || $gender === "" || $birth === "") {
    fail("請填寫所有必填欄位");
  }

  // 2. Email 格式
  if (!filter_var($account, FILTER_VALIDATE_EMAIL)) {
    fail("帳號需為有效的 Email");
  }

  // 3. 密碼一致
  if ($password !== $confirmPassword) {
    fail("兩次輸入的密碼不一致");
  }

  // 4. 密碼最小長度
  if (mb_strlen($password) < 6) {
    fail("密碼至少需 6 碼");
  }

  // 5. 性別白名單
  if (!in_array($gender, ["MALE", "FEMALE"], true)) {
    fail("性別欄位不正確");
  }

  // 6. 生日合法性（格式 + 不可為未來日期）
  $birthDate = DateTime::createFromFormat("Y-m-d", $birth);
  $birthErrors = DateTime::getLastErrors();
  if (!$birthDate ||
      ($birthErrors && ($birthErrors["warning_count"] > 0 || $birthErrors["error_count"] > 0))) {
    fail("出生年月日不正確");
  }
  $today = new DateTime("today");
  if ($birthDate > $today) {
    fail("出生年月日不正確");
  }

  // 7. 年齡判定：未滿 18 歲須填緊急聯絡人
  $age = $today->diff($birthDate)->y;
  $isMinor = $age < 18;

  if ($isMinor) {
    if ($repName === "" || $repRelation === "" || $repPhone === "") {
      fail("未滿 18 歲須填寫緊急聯絡人資訊");
    }
    if (!in_array($repRelation, ["FATHER", "MOTHER", "OTHER"], true)) {
      fail("緊急聯絡人關係不正確");
    }
  } else {
    // 成年：忽略前端殘留值，一律存 NULL
    $repName = null;
    $repRelation = null;
    $repPhone = null;
  }

  // 8. 帳號唯一性
  $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE MEM_ACCOUNT = ?");
  $checkStmt->execute([$account]);
  if ($checkStmt->fetchColumn() > 0) {
    fail("此帳號已被註冊");
  }

  // 9. 寫入（MEM_ID 為 AUTO_INCREMENT，其餘欄位走 DB 預設值）
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $insertSql = "
    INSERT INTO member
      (MEM_ACCOUNT, MEM_PASSWORD, MEM_NAME, MEM_GENDER, MEM_BIRTH, REP_NAME, REP_RELATION, REP_PHONE)
    VALUES
      (?, ?, ?, ?, ?, ?, ?, ?)
  ";

  $insertStmt = $pdo->prepare($insertSql);
  $insertStmt->execute([
    $account,
    $hashedPassword,
    $name,
    $gender,
    $birth,
    $repName,
    $repRelation,
    $repPhone
  ]);

  // 10. 成功（不建立 Session，導回登入頁自行登入）
  echo json_encode([
    "success" => true,
    "message" => "註冊成功"
  ], JSON_UNESCAPED_UNICODE);

?>
