<?php
  /*
    二手交換功能是否被停權的共用檢查；此檔不直接輸出 JSON，
    而是提供讓其他交換相關 PHP require_once 使用。

    1. 檢查當前會員交換功能是否被停權
    2. 若停權期限已到，自動讓交換功能恢復正常
    3. 停權期限一到，將交換違規次數歸 0，重新計算

    註：交換功能狀態欄為 MARKET_STATUS / MARKET_SUSPEND_UNTIL，
        違規次數欄為 EXCHANGE_VIO_COUNTS（命名不一致，見資料表）。
  */

  function checkExchangeAccess($pdo, $memberId) {
    // 取得會員目前的交換功能狀態
    $sql = "
      SELECT
        MARKET_STATUS,
        MARKET_SUSPEND_UNTIL,
        EXCHANGE_VIO_COUNTS,

        /* 判斷暫時停權期限是否已經到期 */
        CASE
          WHEN MARKET_SUSPEND_UNTIL IS NOT NULL
            AND MARKET_SUSPEND_UNTIL <= NOW()
          THEN 1
          ELSE 0
        END AS IS_SUSPEND_EXPIRED

      FROM member
      WHERE MEM_ID = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);

    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    // 找不到當前會員時，直接視為不可使用交換功能
    if (!$member) {
      return [
        "allowed" => false,
        "status" => null,
        "suspendUntil" => null
      ];
    }

    // 若狀態為 ACTIVE，交換功能正常使用
    if ($member["MARKET_STATUS"] === "ACTIVE") {
      return [
        "allowed" => true,
        "status" => "ACTIVE",
        "suspendUntil" => null
      ];
    }

    // PERMA-RESTRICT: 永久限制
    if ($member["MARKET_STATUS"] === "PERMA-RESTRICT") {
      return [
        "allowed" => false,
        "status" => "PERMA-RESTRICT",
        "suspendUntil" => null
      ];
    }

    // TEMP-RESTRICT: 檢查暫時停權是否已到期
    if ($member["MARKET_STATUS"] === "TEMP-RESTRICT") {

      $suspendUntil = $member["MARKET_SUSPEND_UNTIL"];

      if ((int) $member["IS_SUSPEND_EXPIRED"] === 1) {
        // 已到期：恢復正常、清空停權期限、違規次數歸 0
        $sql = "
          UPDATE member
          SET
            MARKET_STATUS = 'ACTIVE',
            MARKET_SUSPEND_UNTIL = NULL,
            EXCHANGE_VIO_COUNTS = 0
          WHERE MEM_ID = ?
            AND MARKET_STATUS = 'TEMP-RESTRICT'
            AND MARKET_SUSPEND_UNTIL <= NOW()
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$memberId]);

        // 只有真的完成「停權 → 恢復」狀態轉換時，才發一筆恢復通知，避免重複
        if ($stmt->rowCount() > 0) {
          $notificationContent = "您的二手交換功能停權期限已結束，目前已恢復正常使用，您可以再次刊登或申請交換。";

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

      // 還處於停權期間，交換功能持續受限
      return [
        "allowed" => false,
        "status" => "TEMP-RESTRICT",
        "suspendUntil" => $suspendUntil
      ];
    }

    // 防呆：非上述情形，直接預設禁止使用交換功能
    return [
      "allowed" => false,
      "status" => $member["MARKET_STATUS"],
      "suspendUntil" => $member["MARKET_SUSPEND_UNTIL"]
    ];
  }
?>
