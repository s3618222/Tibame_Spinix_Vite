<?php
/*
  論壇功能是否被停權的共用檢查函式；此檔不直接輸出 JSON，
  而是提供讓其他論壇相關 PHP require_once 使用。

  1. 檢查當前會員論壇功能是否被停權
  2. 若停權期限已到，自動讓論壇功能恢復正常
  3. 停權日期一到，將違規次數歸0，重新計算
*/

function checkForumAccess($pdo, $memberId) {
    $sql = "
        SELECT
            FORUM_STATUS,
            FORUM_SUSPEND_UNTIL,
            FORUM_VIO_COUNTS,

            CASE
                WHEN FORUM_SUSPEND_UNTIL IS NOT NULL
                    AND FORUM_SUSPEND_UNTIL <= NOW()
                THEN 1
                ELSE 0
            END AS IS_SUSPEND_EXPIRED

        FROM member
        WHERE MEM_ID = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        return [
            "allowed" => false,
            "status" => null,
            "suspendUntil" => null
        ];
    }

    if ($member["FORUM_STATUS"] === "ACTIVE") {
        return [
            "allowed" => true,
            "status" => "ACTIVE",
            "suspendUntil" => null
        ];
    }

    if ($member["FORUM_STATUS"] === "PERMA-RESTRICT") {
        return [
            "allowed" => false,
            "status" => "PERMA-RESTRICT",
            "suspendUntil" => null
        ];
    }

    if ($member["FORUM_STATUS"] === "TEMP-RESTRICT") {
        $suspendUntil = $member["FORUM_SUSPEND_UNTIL"];

        if ((int) $member["IS_SUSPEND_EXPIRED"] === 1) {
            $sql = "
                UPDATE member
                SET
                    FORUM_STATUS = 'ACTIVE',
                    FORUM_SUSPEND_UNTIL = NULL,
                    FORUM_VIO_COUNTS = 0
                WHERE MEM_ID = ?
                    AND FORUM_STATUS = 'TEMP-RESTRICT'
                    AND FORUM_SUSPEND_UNTIL <= NOW()
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$memberId]);

            if ($stmt->rowCount() > 0) {
                $notificationContent = "您的論壇功能停權期限已結束，目前已恢復正常使用，您可以再次發文或留言。";

                $sql = "INSERT INTO notification (mem_id, content, is_read, create_time) VALUES (?, ?, 0, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$memberId, $notificationContent]);
            }

            return [
                "allowed" => true,
                "status" => "ACTIVE",
                "suspendUntil" => null
            ];
        }

        return [
            "allowed" => false,
            "status" => "TEMP-RESTRICT",
            "suspendUntil" => $suspendUntil
        ];
    }

    return [
        "allowed" => false,
        "status" => $member["FORUM_STATUS"],
        "suspendUntil" => $member["FORUM_SUSPEND_UNTIL"]
    ];
}
?>