<?php
  // 我的申訴詳情頁：取得約戰申訴詳情資料API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  $memberId = $_SESSION["MEM_ID"] ?? null;

  if (!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再查看申訴詳情"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得前端串接網址的約戰編號資訊
  $appealId = (int) ($_GET["appeal_id"] ?? 0);

  if ($appealId <= 0) {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "申訴編號無效"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //查詢該筆約戰申訴資料
  $sql = "
    SELECT
      battle_appeal.BATTLE_APPEAL_ID,
      battle_appeal.BATTLE_ID,

      battle_appeal.APPEAL_CONTENT,
      battle_appeal.APPEAL_STATUS,

      battle_appeal.CREATED_AT,
      battle_appeal.RESPONDED_AT,

      battle_appeal.PHOTO_EVIDENCE,
      battle_appeal.RESPONDED_TEXT,

      battle_appeal.RESPONDENT_MEM_ID,

      complainant.MEM_NAME AS COMPLAINANT_NAME,
      respondent.MEM_NAME AS RESPONDENT_NAME,

      battle_record.BATTLE_TITLE

    FROM battle_appeal

    JOIN battle_record
      ON battle_appeal.BATTLE_ID = battle_record.BATTLE_ID

    JOIN member AS complainant
      ON battle_appeal.COMPLAINANT_MEM_ID = complainant.MEM_ID

    JOIN member AS respondent
      ON battle_appeal.RESPONDENT_MEM_ID = respondent.MEM_ID

    WHERE battle_appeal.BATTLE_APPEAL_ID = ?
      AND battle_appeal.COMPLAINANT_MEM_ID = ?
  ";

  $stmt = $pdo->prepare($sql);

  $stmt->execute([
    $appealId,
    $memberId
  ]);

  $appeal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appeal) {
    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到這筆約戰申訴資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //將佐證圖片JSON轉回陣列
  $images = [];

  if (!empty($appeal["PHOTO_EVIDENCE"])) {
    $decodedImages = json_decode($appeal["PHOTO_EVIDENCE"], true);

    if (is_array($decodedImages)) {
      $images = $decodedImages;
    }
  }

  //資料回傳前端
  echo json_encode([
    "success" => true,

    "appeal" => [
      "appealId" => (int) $appeal["BATTLE_APPEAL_ID"],
      "battleId" => (int) $appeal["BATTLE_ID"],

      "type" => "約戰糾紛",

      "battleTitle" => $appeal["BATTLE_TITLE"],

      "targetId" =>
        (int) $appeal["RESPONDENT_MEM_ID"],

      "reporterName" =>
        $appeal["COMPLAINANT_NAME"],

      "targetName" =>
        $appeal["RESPONDENT_NAME"],

      "content" =>
        $appeal["APPEAL_CONTENT"],

      "status" =>
        $appeal["APPEAL_STATUS"],

      "createdAt" =>
        $appeal["CREATED_AT"],

      "images" => $images,

      "result" =>
        $appeal["RESPONDED_TEXT"],

      "resultDate" =>
        $appeal["RESPONDED_AT"]
    ]
  ], JSON_UNESCAPED_UNICODE);

?>