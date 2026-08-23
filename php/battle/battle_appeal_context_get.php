<?php
  // 取得約戰申訴的初始化資料 API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //取得當前會員ID與此筆約戰ID
  $memberId = $_SESSION["MEM_ID"] ?? null;
  $battleId = (int) ($_GET["battle_id"] ?? 0);

  if(!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再提出申訴"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查battleId是否有效
  if ($battleId <= 0) {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "約戰編號無效"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //查詢資料庫中該筆約戰資訊 (約戰ID、標題、發起人、參加人)
  $sql = "
    SELECT
      battle_record.BATTLE_ID,
      battle_record.BATTLE_TITLE,

      battle_record.INITIATOR_ID,
      battle_record.PARTICIPANT_ID,

      initiator.MEM_NAME AS INITIATOR_NAME,
      participant.MEM_NAME AS PARTICIPANT_NAME

    FROM battle_record

    JOIN member AS initiator
      ON battle_record.INITIATOR_ID = initiator.MEM_ID

    LEFT JOIN member AS participant
      ON battle_record.PARTICIPANT_ID = participant.MEM_ID

    WHERE battle_record.BATTLE_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$battleId]);
  $battle = $stmt->fetch(PDO::FETCH_ASSOC);

  //確定該筆約戰存在
  if (!$battle) {
    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到這筆約戰資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //確認當前會員有參與該筆約戰
  $isInitiator = (int) $battle["INITIATOR_ID"] === (int) $memberId;
  $isParticipant = (int) $battle["PARTICIPANT_ID"] === (int) $memberId;

  if (!$isInitiator && !$isParticipant) {
    http_response_code(403);

    echo json_encode([
      "success" => false,
      "message" => "你沒有權限針對這筆約戰提出申訴"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //根據當前會員在約戰中的角色，找出對手是誰
  if ($isInitiator) { //當前會員是發起人，對手就是參加人
    
  $opponentId = $battle["PARTICIPANT_ID"];
    $opponentName = $battle["PARTICIPANT_NAME"];

  } else { //當前會員是參加人，對手就是發起人

    $opponentId = $battle["INITIATOR_ID"];
    $opponentName = $battle["INITIATOR_NAME"];
  }

  //防呆：如果對手不存在，就無法提出申訴
  if (!$opponentId) {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "這筆約戰目前沒有可申訴的對手"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //回傳申訴頁面所需的初始化資料
  echo json_encode([
    "success" => true,

    "battle" => [
      "battleId" => (int) $battle["BATTLE_ID"],
      "battleTitle" => $battle["BATTLE_TITLE"],

      "opponentId" => (int) $opponentId,
      "opponentName" => $opponentName
    ]

  ], JSON_UNESCAPED_UNICODE);

?>