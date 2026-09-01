<?php
  /* 
    約戰功能是否被停權的共用檢查API;此檔不直接輸出JSON，
    而是提供讓其他約戰相關 PHP require_once 使用。

    1.檢查當前會員約戰功能是否被停權
    2.若停權期限已到，自動讓約戰功能恢復正常
    3.停權日期一到，將違規次數歸0，重新計算
  */

  function checkBattleAccess($pdo, $memberId) { 
    //*$pdo參數是用來取得引用這支檔案的php檔內的資料庫連線
    //取得會員目前的約戰功能狀態

    $sql = "
      SELECT
        BATTLE_STATUS,
        BATTLE_SUSPEND_UNTIL,
        BATTLE_VIO_COUNTS,

        /* 判斷暫時停權期限是否已經到期 */
        CASE
          WHEN BATTLE_SUSPEND_UNTIL IS NOT NULL
            AND BATTLE_SUSPEND_UNTIL <= NOW()
          THEN 1
          ELSE 0
        END AS IS_SUSPEND_EXPIRED

      FROM member
      WHERE MEM_ID = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);

    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    // 找不到當前會員時，直接視為不可使用約戰功能
    if (!$member) {
      return [
        "allowed" => false,
        "status" => null,
        "suspendUntil" => null
      ];
    }

    //若狀態為ACTIVE，約戰功能正常使用
    if ($member["BATTLE_STATUS"] === "ACTIVE") {
      return [
        "allowed" => true,
        "status" => "ACTIVE",
        "suspendUntil" => null
      ];
    }

    //PERMA-RESTRICT: 永久限制
    if ($member["BATTLE_STATUS"] === "PERMA-RESTRICT") {

      return [
        "allowed" => false,
        "status" => "PERMA-RESTRICT",
        "suspendUntil" => null
      ];
    }

    //TEMP-RESTRICT: 檢查暫時停權是否已到期
    if ($member["BATTLE_STATUS"] === "TEMP-RESTRICT") {

      $suspendUntil = $member["BATTLE_SUSPEND_UNTIL"];

      if ( (int) $member["IS_SUSPEND_EXPIRED"] === 1 ) {
        // 檢查暫時停權是否已到期；已到期時，將停權功能恢復正常、停權期限清空、違規次數歸0
        $sql = "
          UPDATE member
          SET
            BATTLE_STATUS = 'ACTIVE',
            BATTLE_SUSPEND_UNTIL = NULL,
            BATTLE_VIO_COUNTS = 0
          WHERE MEM_ID = ?
            AND BATTLE_STATUS = 'TEMP-RESTRICT'
            AND BATTLE_SUSPEND_UNTIL <= NOW()
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$memberId]);

        //恢復權限功能時，連帶通知會員
        if ($stmt->rowCount() > 0) {  
          //只有這一次真的完成「停權 → 恢復」狀態轉換時，才建立一筆會員通知，避免重複產生恢復通知
          $notificationContent = "您的約戰功能停權期限已結束，目前已恢復正常使用，您可以再次建立或參與約戰。";

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
            $memberId,
            $notificationContent
          ]);
        }

        return [
          "allowed" => true,
          "status" => "ACTIVE",
          "suspendUntil" => null
        ];
      }


      // 還處於停權期間，約戰功能持續受限
      return [
        "allowed" => false,
        "status" => "TEMP-RESTRICT",
        "suspendUntil" => $suspendUntil
      ];
    }

    //防呆： 若有非上述情形，直接預設禁止使用約戰功能
    return [
      "allowed" => false,
      "status" => $member["BATTLE_STATUS"],
      "suspendUntil" => $member["BATTLE_SUSPEND_UNTIL"]
    ];

  }
?>