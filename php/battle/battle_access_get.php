<?php
  //查詢當前會員約戰功能權限API
  //提供讓battle.js 在會員準備要申請加入約戰、進入建立約戰頁時進行檢查

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");
  require_once("./battle_access_check.php");

  header("Content-Type: application/json; charset=utf-8");

  if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 GET 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  $memberId = $_SESSION["MEM_ID"] ?? null;

  if (!$memberId) {

    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入會員"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  try {
    // 使用battle_access_check.php的共用函式，檢查目前會員是否能正常使用約戰
    // 當目前會員約戰狀態為暫時受限時，期限如果到期，也可以在這邊同步恢復為ACTIVE

    $battleAccess = checkBattleAccess($pdo, $memberId);

    // 再將取得的檢查結果回傳給前端
    echo json_encode([
      "success" => true,
      "allowed" => $battleAccess["allowed"],
      "status" => $battleAccess["status"],
      "suspendUntil" => $battleAccess["suspendUntil"]

    ], JSON_UNESCAPED_UNICODE);


  } catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
      "success" => false,
      "message" => "取得約戰功能權限失敗"
    ], JSON_UNESCAPED_UNICODE);
  }
?>