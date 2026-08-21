<?php
  //同步更新約戰紀錄狀態，讓超過報名截止時間的約戰，狀態更新為FAILED
  //此檔本身不輸出JSON，而是讓其他會更新約戰資料畫面到前端的PHP使用

  //向資料庫查詢狀態為配對中(MATCHING)，且報名截止時間已經小於當前時間的約戰紀錄，將其更新為FAILED
  $sql = "
    UPDATE battle_record
    SET BATTLE_STATUS = 'FAILED'
    WHERE BATTLE_STATUS = 'MATCHING'
      AND BATTLE_DEADLINE <= NOW()
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();
?>