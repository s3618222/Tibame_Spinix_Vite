<?php
  // 前台會員中心「違規詳情
  // 根據前端傳回 type + id，取得當前會員的特定成立違規紀錄

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

  //接收前端傳回的違規類型與id網址參數
  $type = $_GET["type"] ?? "";
  $id = $_GET["id"] ?? "";

  if (!$type || !$id) {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "缺少違規紀錄類型或編號"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //判斷根據違規類型，要查詢哪個資料庫表格
  if ($type === "battle") { //約戰違規

    $sql = "
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

      WHERE battle_appeal.BATTLE_APPEAL_ID = ?
        AND battle_appeal.RESPONDENT_MEM_ID = ?
        AND battle_appeal.APPEAL_STATUS = 'CONFIRMED'
    ";

  } elseif ($type === "forum") { //論壇違規

    $sql = "
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

      WHERE appeal_forum.af_id = ?
        AND appeal_forum.respondent_mem_id = ?
        AND appeal_forum.af_status = 'CONFIRMED'
    ";

  } elseif ($type === "exchange") { //交換違規

    $sql = "
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

      WHERE appeal_exchange.ae_id = ?
        AND appeal_exchange.respondent_mem_id = ?
        AND appeal_exchange.ae_status = 'CONFIRMED'
    ";

  } else {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "無效的違規紀錄類型"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $id,
    $memberId
  ]);

  $violation = $stmt->fetch(PDO::FETCH_ASSOC);

  //確認該筆記錄存在
  if (!$violation) {
    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到此違規紀錄"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //回傳查詢結果
  echo json_encode([
    "success" => true,
    "violation" => $violation
  ], JSON_UNESCAPED_UNICODE);

?>