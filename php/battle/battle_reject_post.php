<?php
  // 會員中心「我的約戰」，發起人拒絕約戰申請 API (改變約戰紀錄的狀態 CANCELLED)

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得目前登入會員id
  $memberId = $_SESSION["MEM_ID"] ?? null;

  // 取得前端傳來的此筆約戰id
  $battleId = (int) ($_POST["battle_id"] ?? 0);

  //檢查是否有登入會員
  if (!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再操作"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查約戰id是否有效
  if ($battleId <= 0) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "約戰編號無效"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //向資料庫查詢此筆約戰資料
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

  $battle = $stmt->fetch(PDO::FETCH_ASSOC);

  //檢查該筆約戰是否存在
  if (!$battle) {
    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到這筆約戰資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查當前會員是否為此約戰發起人
  if ((int) $battle["INITIATOR_ID"] !== (int) $memberId) {
    http_response_code(403);

    echo json_encode([
      "success" => false,
      "message" => "你沒有權限操作這筆約戰"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 檢查此約戰是已有申請者
  if ($battle["PARTICIPANT_ID"] === null) {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "目前沒有會員申請這場約戰"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查此筆約戰狀態是否為PENDING
  // 只有 PENDING 可以執行拒絕
  if ($battle["BATTLE_STATUS"] !== "PENDING") {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "這場約戰目前無法拒絕申請"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //上述條件檢查完後，再將該約戰狀態更新為CANCELLED
  $sql = "
    UPDATE battle_record
    SET BATTLE_STATUS = 'CANCELLED'
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

  if ($stmt->rowCount() === 0) {
    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "約戰狀態已變更，請重新整理後再試"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 更新成功
  echo json_encode([
    "success" => true,
    "message" => "已拒絕此次約戰申請"
  ], JSON_UNESCAPED_UNICODE);

?>