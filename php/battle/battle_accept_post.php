<?php
  // 會員中心「我的約戰」，發起人接受約戰申請 API (改變約戰紀錄的狀態)

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //限制REQUEST METHOD 為 POST
  if($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得目前登入會員的ID
  $memberId = $_SESSION["MEM_ID"] ?? null;

  //取得前端送來的該筆約戰ID
  $battleId = (int) ($_POST["battle_id"] ?? 0);

  //檢查是否有登入
  if(!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再操作"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查約戰ID是否有效
  if ($battleId <= 0) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "約戰編號無效"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //接著查詢該筆約戰
  $sql = "
    SELECT
      BATTLE_ID,
      INITIATOR_ID,
      PARTICIPANT_ID,
      BATTLE_STATUS
    FROM battle_record
    WHERE BATTLE_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$battleId]);

  //只會有一筆符合，所以fetch就好
  $battle = $stmt->fetch(PDO::FETCH_ASSOC);

  // 確認該筆約戰存在
  if (!$battle) {

    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到這筆約戰資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //確認目前登入會員是此約戰的發起人
  if ((int) $battle["INITIATOR_ID"] !== (int) $memberId) {
    http_response_code(403);

    echo json_encode([
      "success" => false,
      "message" => "你沒有權限操作這筆約戰"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //確認此筆約戰 真的有申請者
  if ($battle["PARTICIPANT_ID"] === null) {
    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "目前沒有會員申請這場約戰"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //確認約戰當前狀態為PENDING
  if ($battle["BATTLE_STATUS"] !== "PENDING") {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "這場約戰目前無法確認"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //通過以上條件檢查後，再將後端資料庫的該筆約戰狀態更新為CONFIRMED
  $sql = "
    UPDATE battle_record
    SET BATTLE_STATUS = 'CONFIRMED'
    WHERE BATTLE_ID = ?
      AND INITIATOR_ID = ?
      AND PARTICIPANT_ID IS NOT NULL
      AND BATTLE_STATUS = 'PENDING'
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $battleId,
    $memberId
  ]);

  //防呆：避免使用者連點兩次 接受申請
  if ($stmt->rowCount() === 0) {
    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "約戰狀態已變更，請重新整理後再試"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  echo json_encode([
    "success" => true,
    "message" => "已接受申請，約戰配對成功"
  ], JSON_UNESCAPED_UNICODE);

?>