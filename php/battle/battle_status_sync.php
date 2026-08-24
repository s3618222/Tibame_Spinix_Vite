<?php
  //同步更新約戰紀錄狀態，讓超過報名截止時間的約戰，狀態更新為FAILED
  //此檔本身不輸出JSON，而是讓其他會更新約戰資料畫面到前端的PHP使用


  try {
    //開始資料庫交易
    $pdo->beginTransaction();

    //找出這次因截止時間到其，狀態將變為「FAILED」的約戰
    $sql = "
      SELECT
        BATTLE_ID,
        INITIATOR_ID,
        BATTLE_TITLE
      FROM battle_record
      WHERE BATTLE_STATUS = 'MATCHING'
        AND BATTLE_DEADLINE <= NOW()
      FOR UPDATE
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $expiredBattles = $stmt->fetchAll(PDO::FETCH_ASSOC);


    //向資料庫查詢狀態為配對中(MATCHING)，且報名截止時間已經小於當前時間的約戰紀錄，將其更新為FAILED
    $sql = "
      UPDATE battle_record
      SET BATTLE_STATUS = 'FAILED'
      WHERE BATTLE_STATUS = 'MATCHING'
        AND BATTLE_DEADLINE <= NOW()
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    //針對此次呼叫API時，過期的每一筆約戰，發對應通知給發起人
    foreach($expiredBattles as $battle) {
      //通知內容
      $notificationContent = "你發起的約戰「" . $battle["BATTLE_TITLE"] . "」已超過申請截止時間，邀約已結束";

      //將通知內容寫入notification資料表
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
        $battle["INITIATOR_ID"],
        $notificationContent
      ]);
    }

    //約戰狀態與通知都成功後，才正式提交
    $pdo->commit();

  } catch (PDOException $e) {

    //只要更新約戰紀錄狀態(FAILED)、或發通知其中一個操作失敗，就全部取消
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }

    throw $e;

  }

?>