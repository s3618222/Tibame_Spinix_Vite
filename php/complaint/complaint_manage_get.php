<?php
  // 後台「申訴管理」— 取得三種申訴（對戰 / 二手交換 / 論壇）合併後的清單
  // 做法比照會員端 php/member/my_appeal_get.php，用 UNION ALL 合併三張表
  // 差異：不過濾申訴人（管理員看全部）、多 JOIN 一次 member 帶出「申訴人」、admin 用 LEFT JOIN

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // TODO: 之後補上「驗證管理員登入」的 session 檢查

  $sql = "
    /* 1. 對戰申訴 */
    SELECT
      battle_appeal.BATTLE_APPEAL_ID          AS id,
      'battle'                                AS sourceType,
      '對戰'                                  AS type,
      LOWER(battle_appeal.APPEAL_STATUS)      AS status,
      battle_appeal.CREATED_AT                AS createdAt,
      c.MEM_NAME                              AS complainant,
      r.MEM_NAME                              AS respondent,
      IFNULL(a.name, '未指派')                AS handler,
      battle_appeal.APPEAL_CONTENT            AS content,
      battle_appeal.PHOTO_EVIDENCE            AS evidence,
      battle_appeal.RESPONDED_TEXT            AS respondedText

    FROM battle_appeal
    LEFT JOIN member c ON c.MEM_ID   = battle_appeal.COMPLAINANT_MEM_ID
    LEFT JOIN member r ON r.MEM_ID   = battle_appeal.RESPONDENT_MEM_ID
    LEFT JOIN admin  a ON a.admin_id = battle_appeal.ADMIN_ID

    UNION ALL

    /* 2. 二手交換申訴 */
    SELECT
      appeal_exchange.ae_id                   AS id,
      'exchange'                              AS sourceType,
      '二手交換'                              AS type,
      LOWER(appeal_exchange.ae_status)        AS status,
      appeal_exchange.create_time             AS createdAt,
      c.MEM_NAME                              AS complainant,
      r.MEM_NAME                              AS respondent,
      IFNULL(a.name, '未指派')                AS handler,
      appeal_exchange.ae_content              AS content,
      appeal_exchange.ae_evidence             AS evidence,
      appeal_exchange.responded_text          AS respondedText

    FROM appeal_exchange
    LEFT JOIN member c ON c.MEM_ID   = appeal_exchange.complainant_mem_id
    LEFT JOIN member r ON r.MEM_ID   = appeal_exchange.respondent_mem_id
    LEFT JOIN admin  a ON a.admin_id = appeal_exchange.admin_id

    UNION ALL

    /* 3. 論壇申訴 */
    SELECT
      appeal_forum.af_id                      AS id,
      'forum'                                 AS sourceType,
      '論壇'                                  AS type,
      LOWER(appeal_forum.af_status)           AS status,
      appeal_forum.create_time                AS createdAt,
      c.MEM_NAME                              AS complainant,
      r.MEM_NAME                              AS respondent,
      IFNULL(a.name, '未指派')                AS handler,
      appeal_forum.af_content                 AS content,
      appeal_forum.af_evidence                AS evidence,
      appeal_forum.responded_text             AS respondedText

    FROM appeal_forum
    LEFT JOIN member c ON c.MEM_ID   = appeal_forum.complainant_mem_id
    LEFT JOIN member r ON r.MEM_ID   = appeal_forum.respondent_mem_id
    LEFT JOIN admin  a ON a.admin_id = appeal_forum.admin_id

    /* 三表合併後統一依申訴時間新到舊排序 */
    ORDER BY createdAt DESC
  ";

  try {
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 將 evidence 正規化為陣列（比照 battle_appeal_detail_get.php）
    //   battle：PHOTO_EVIDENCE 為 JSON 陣列字串；forum/exchange：單一檔名字串；NULL：空陣列
    foreach ($data as &$row) {
      $ev = $row["evidence"];
      if (empty($ev)) {
        $row["evidence"] = [];
      } else {
        $decoded = json_decode($ev, true);
        $row["evidence"] = is_array($decoded) ? $decoded : [$ev];
      }
    }
    unset($row);

    echo json_encode([
      "success" => true,
      "appeals" => $data
    ], JSON_UNESCAPED_UNICODE);
  } catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
      "success" => false,
      "message" => "取得申訴清單失敗"
    ], JSON_UNESCAPED_UNICODE);
  }
?>
