<?php
  // 會員中心「個人資料」- 重設密碼 API（驗證舊密碼 → 更新為新密碼 hash）

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

  // 取值（密碼不 trim）
  $oldPassword     = $_POST["oldPassword"] ?? "";
  $newPassword     = $_POST["newPassword"] ?? "";
  $confirmPassword = $_POST["confirmPassword"] ?? "";

  // 統一失敗回傳
  function fail($message) {
    echo json_encode([
        "success" => false,
        "message" => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 驗證
  if ($oldPassword === "" || $newPassword === "" || $confirmPassword === "") {
    fail("請完整填寫密碼欄位");
  }
  if ($newPassword !== $confirmPassword) {
    fail("兩次新密碼不一致");
  }
  if (mb_strlen($newPassword) < 6) {
    fail("新密碼至少需 6 碼");
  }
  if ($newPassword === $oldPassword) {
    fail("新密碼不可與目前密碼相同");
  }

  // 取出目前密碼 hash 並比對舊密碼
  $stmt = $pdo->prepare("SELECT MEM_PASSWORD FROM member WHERE MEM_ID = ?");
  $stmt->execute([$memberId]);
  $member = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$member) {
    fail("會員資料不存在");
  }
  if (!password_verify($oldPassword, $member["MEM_PASSWORD"])) {
    fail("目前密碼不正確");
  }

  // 更新為新密碼 hash（不動 session，維持登入）
  $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
  $updateStmt = $pdo->prepare("UPDATE member SET MEM_PASSWORD = ? WHERE MEM_ID = ?");
  $updateStmt->execute([$newHash, $memberId]);

  echo json_encode([
    "success" => true,
    "message" => "密碼已更新"
  ], JSON_UNESCAPED_UNICODE);

?>
