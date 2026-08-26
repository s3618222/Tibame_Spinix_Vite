<?php
  // 前台會員中心「違規紀錄」- 取得當前會員被申訴成立時的相關違規紀錄API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  $memberId = $_SESSION["MEM_ID"] ?? null;

  if (!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再查看違規紀錄"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 取得會員被申訴成立的所有違規紀錄
  $sql = "
    /* 1. 約戰違規紀錄  */
    SELECT
      battle_appeal.BATTLE_APPEAL_ID AS id,

      'battle' AS sourceType,
      '約戰糾紛' AS type,

      respondent.MEM_NAME AS respondentName,

      battle_appeal.RESPONDED_TEXT AS respondedText,
      battle_appeal.RESPONDED_AT AS respondedAt

    FROM battle_appeal

    JOIN member AS respondent
      ON battle_appeal.RESPONDENT_MEM_ID = respondent.MEM_ID

    WHERE battle_appeal.RESPONDENT_MEM_ID = ?
      AND battle_appeal.APPEAL_STATUS = 'CONFIRMED'

    UNION ALL

    /* 2. 論壇違規紀錄 */
    SELECT
      appeal_forum.af_id AS id,

      'forum' AS sourceType,
      '論壇糾紛' AS type,

      respondent.MEM_NAME AS respondentName,

      appeal_forum.responded_text AS respondedText,
      appeal_forum.responded_at AS respondedAt

    FROM appeal_forum

    JOIN member AS respondent
      ON appeal_forum.respondent_mem_id = respondent.MEM_ID

    WHERE appeal_forum.respondent_mem_id = ?
      AND appeal_forum.af_status = 'CONFIRMED'


    UNION ALL


    /* 3. 交換違規紀錄 */
    SELECT
      appeal_exchange.ae_id AS id,

      'exchange' AS sourceType,
      '交換糾紛' AS type,

      respondent.MEM_NAME AS respondentName,

      appeal_exchange.responded_text AS respondedText,
      appeal_exchange.responded_at AS respondedAt

    FROM appeal_exchange

    JOIN member AS respondent
      ON appeal_exchange.respondent_mem_id = respondent.MEM_ID

    WHERE appeal_exchange.respondent_mem_id = ?
      AND appeal_exchange.ae_status = 'CONFIRMED'


    /* 全部紀錄合併後，再依照管理員的處理時間由新到舊排序 */
    ORDER BY respondedAt DESC
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
    "violations" => $data
  ], JSON_UNESCAPED_UNICODE);

?>