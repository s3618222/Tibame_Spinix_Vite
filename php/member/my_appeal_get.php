<?php
  // 會員中心 -「我的申訴」  取得目前會員的約戰、論壇、交換申訴API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //確認登入狀態
  $memberId = $_SESSION["MEM_ID"] ?? null;

  if (!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再查看我的申訴"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得當前會員提出的 約戰申訴、論壇申訴、交換申訴資料

  $sql = "
    /* 1. 約戰申訴表 */
    SELECT
      battle_appeal.BATTLE_APPEAL_ID AS APPEAL_ID,

      'battle' AS SOURCE_TYPE,
      '約戰糾紛' AS APPEAL_TYPE,

      battle_appeal.APPEAL_STATUS AS APPEAL_STATUS,
      battle_appeal.CREATED_AT AS CREATED_AT,

      battle_appeal.RESPONDENT_MEM_ID AS TARGET_ID,
      respondent.MEM_NAME AS TARGET_NAME,

      battle_appeal.APPEAL_CONTENT AS APPEAL_CONTENT

    FROM battle_appeal

    JOIN member AS respondent
      ON battle_appeal.RESPONDENT_MEM_ID = respondent.MEM_ID

    WHERE battle_appeal.COMPLAINANT_MEM_ID = ?

    UNION ALL


  /* 2. 取得論壇申訴表 */
    SELECT
      appeal_forum.af_id AS APPEAL_ID,

      'forum' AS SOURCE_TYPE,
      '論壇糾紛' AS APPEAL_TYPE,

      appeal_forum.af_status AS APPEAL_STATUS,
      appeal_forum.create_time AS CREATED_AT,

      appeal_forum.respondent_mem_id AS TARGET_ID,
      respondent.MEM_NAME AS TARGET_NAME,

      appeal_forum.af_content AS APPEAL_CONTENT

    FROM appeal_forum

    JOIN member AS respondent
      ON appeal_forum.respondent_mem_id = respondent.MEM_ID

    WHERE appeal_forum.complainant_mem_id = ?

    UNION ALL


  /* 3. 取得交換申訴 */
    SELECT
      appeal_exchange.ae_id AS APPEAL_ID,

      'exchange' AS SOURCE_TYPE,
      '交易糾紛' AS APPEAL_TYPE,

      appeal_exchange.ae_status AS APPEAL_STATUS,
      appeal_exchange.create_time AS CREATED_AT,

      appeal_exchange.respondent_mem_id AS TARGET_ID,
      respondent.MEM_NAME AS TARGET_NAME,

      appeal_exchange.ae_content AS APPEAL_CONTENT

    FROM appeal_exchange

    JOIN member AS respondent
      ON appeal_exchange.respondent_mem_id = respondent.MEM_ID

    WHERE appeal_exchange.complainant_mem_id = ?


    /* 所有申訴表單合併後，統一按照時間新到舊排序 */
    ORDER BY CREATED_AT DESC
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $memberId,
    $memberId,
    $memberId
  ]);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    "success" => true,
    "appeals" => $data
  ], JSON_UNESCAPED_UNICODE);

?>