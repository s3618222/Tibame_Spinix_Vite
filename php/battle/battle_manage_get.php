<?php
  // 後台「約戰管理」：取得所有約戰資訊、管理員約戰處置紀錄API

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");
  require_once("../common/admin_guard.php");

  require_once("./battle_status_sync.php");

  header("Content-Type: application/json; charset=utf-8");

  $sql = "
    SELECT
      /* 約戰基本資訊 */
      battle_record.BATTLE_ID,
      battle_record.BATTLE_TITLE,
      battle_record.BATTLE_IMG,
      battle_record.BATTLE_DESC,

      battle_record.BATTLE_MODE,
      battle_record.BATTLE_TARGET,
      battle_record.BATTLE_LEVEL,

      battle_record.BATTLE_STATUS,
      battle_record.IS_SHOW,

      battle_record.BATTLE_DATE,
      battle_record.BATTLE_DEADLINE,

      battle_record.BATTLE_LOC,
      battle_record.CREATED_AT,

      /* 約戰地區ID與名稱 */
      battle_record.CITY_ID,
      battle_record.DISTRICT_ID,

      city.CITY_NAME,
      district.DISTRICT_NAME,

      /* 發起人 */
      battle_record.INITIATOR_ID,
      initiator.MEM_NAME AS INITIATOR_NAME,
      battle_record.INI_CONTACT,

      /* 參加人 */
      battle_record.PARTICIPANT_ID,
      participant.MEM_NAME AS PARTICIPANT_NAME,
      battle_record.PAR_CONTACT,

      /* 對戰結果 */
      battle_record.WINNER,

      /* 最新一筆待處理申訴 */
      latest_pending_appeal.BATTLE_APPEAL_ID AS PENDING_APPEAL_ID,

      /* 最近一次管理處置資訊 */
      latest_manage.MANAGE_ACTION,
      latest_manage.MANAGE_REASON,
      latest_manage.ADMIN_ID AS MANAGED_BY_ADMIN_ID,
      manage_admin.name AS MANAGED_BY_ADMIN_NAME,
      latest_manage.CREATED_AT AS MANAGED_AT

    FROM battle_record

    /* 取得該場約戰最新一筆「待處理」的申訴 */
    LEFT JOIN battle_appeal AS latest_pending_appeal
      ON latest_pending_appeal.BATTLE_APPEAL_ID = (
        SELECT appeal.BATTLE_APPEAL_ID

        FROM battle_appeal AS appeal

        WHERE appeal.BATTLE_ID = battle_record.BATTLE_ID
          AND appeal.APPEAL_STATUS = 'PENDING'

        ORDER BY
          appeal.CREATED_AT DESC,
          appeal.BATTLE_APPEAL_ID DESC

        LIMIT 1
      )

    /* 取得該場約戰最新的管理處置紀錄 */
    LEFT JOIN battle_manage_record AS latest_manage
      ON latest_manage.BATTLE_MANAGE_ID = (
        SELECT manage_record.BATTLE_MANAGE_ID

        FROM battle_manage_record AS manage_record

        WHERE manage_record.BATTLE_ID = battle_record.BATTLE_ID

        ORDER BY
          manage_record.CREATED_AT DESC,
          manage_record.BATTLE_MANAGE_ID DESC

        LIMIT 1
      )

    /* 執行最近一次處置的管理員名稱 */
    LEFT JOIN admin AS manage_admin
      ON latest_manage.ADMIN_ID = manage_admin.admin_id

    /* 取得約戰發起人資料 */
    JOIN member AS initiator
      ON battle_record.INITIATOR_ID = initiator.MEM_ID

    /* 取得參加人資料 */
    LEFT JOIN member AS participant
      ON battle_record.PARTICIPANT_ID = participant.MEM_ID

    /* 取得縣市名稱 */
    JOIN city
      ON battle_record.CITY_ID = city.CITY_ID

    /* 取得行政區名稱 */
    JOIN district
      ON battle_record.DISTRICT_ID = district.DISTRICT_ID

    /* 依照約戰編號排序 */
    ORDER BY battle_record.BATTLE_ID DESC
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(
    $data,
    JSON_UNESCAPED_UNICODE
  );
?>