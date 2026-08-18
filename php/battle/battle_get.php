<?php
  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  //取得要放入約戰配對分頁上的對戰紀錄；條件： 狀態必須為MATCHING、IS_SHOW = true (非下架)、還未截止
  $sql = "
    SELECT
        battle_record.BATTLE_ID,
        battle_record.BATTLE_TITLE,
        battle_record.BATTLE_IMG,
        battle_record.BATTLE_DESC,

        battle_record.CITY_ID,
        city.CITY_NAME,

        battle_record.DISTRICT_ID,
        district.DISTRICT_NAME,

        battle_record.BATTLE_MODE,
        battle_record.BATTLE_TARGET,
        battle_record.BATTLE_LEVEL,

        battle_record.BATTLE_DATE,
        battle_record.BATTLE_DEADLINE,

        battle_record.BATTLE_STATUS,

        battle_record.INITIATOR_ID,
        member.MEM_NAME,
        member.MEM_PHOTO

    FROM battle_record

    JOIN member
        ON battle_record.INITIATOR_ID = member.MEM_ID

    JOIN city
        ON battle_record.CITY_ID = city.CITY_ID

    JOIN district
        ON battle_record.DISTRICT_ID = district.DISTRICT_ID

    WHERE battle_record.IS_SHOW = 1
    AND battle_record.BATTLE_STATUS = 'MATCHING'
    AND battle_record.BATTLE_DEADLINE > NOW()
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>