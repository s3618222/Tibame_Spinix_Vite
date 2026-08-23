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
      BATTLE_TITLE,
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

  try {
    //上述條件檢查完後，再將該約戰狀態更新為CANCELLED
    //開始資料庫交易
    $pdo->beginTransaction();

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

    //避免重複操作或約戰狀態已被改變
    if ($stmt->rowCount() === 0) {

      $pdo->rollBack();

      http_response_code(409);

      echo json_encode([
        "success" => false,
        "message" => "約戰狀態已變更，請重新整理後再試"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

    //取得約戰發起人的會員名稱
    $sql = "
      SELECT MEM_NAME
      FROM member
      WHERE MEM_ID = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);

    $initiator = $stmt->fetch(PDO::FETCH_ASSOC);

    //通知收件人：原本申請加入約戰的會員
    $notificationMemberId = (int) $battle["PARTICIPANT_ID"];

    //通知內容
    $notificationContent = "你的約戰申請「" . $battle["BATTLE_TITLE"] . "」已被 " . $initiator["MEM_NAME"] . " 拒絕";

    //新增通知至notification表格
    $sql = "
      INSERT INTO notification (
        mem_id,
        content,
        is_read,
        create_time
      )
      VALUES (?, ?, 0, NOW())
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $notificationMemberId,
      $notificationContent
    ]);

    //約戰狀態更新為CANCELLED與會員通知更新都成功後才正式提交
    $pdo->commit();

    echo json_encode([
      "success" => true,
      "message" => "已拒絕此次約戰申請"
    ], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {

    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
      "success" => false,
      "message" => "拒絕約戰申請時發生錯誤"
    ], JSON_UNESCAPED_UNICODE);

  }

?>