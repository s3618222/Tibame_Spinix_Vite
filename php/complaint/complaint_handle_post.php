<?php
  // 後台「申訴管理」— 送出處理結果（管理員裁決寫回）
  // 成立(confirm)：一個交易內完成 → 更新申訴為 CONFIRMED、被申訴人違規次數+1、達門檻(3)自動停權(TEMP-RESTRICT)7天；
  //                 並依 contentAction 決定是否下架內容(is_show=0)＋寫下架原因（論壇/交換寫 remove_reason、對戰寫 battle_manage_record）
  // 駁回(reject)：只更新申訴為 REJECTED，member 與內容上下架皆不變
  // 會員通知(三型別通用)：審核完成通知申訴人；成立通知被申訴人；觸發停權再通知被申訴人
  // 延後：升級永久停權、停權到期自動恢復

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 守衛：僅允許已登入的管理員存取
  require_once("../common/admin_guard.php");

  // 僅允許 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 處理的管理員（守衛已保證登入，直接取實際登入者的 ADMIN_ID）
  $adminId = $_SESSION["ADMIN_ID"];

  // 接收前端資料
  $sourceType  = $_POST["sourceType"] ?? "";
  $appealId    = (int) ($_POST["id"] ?? 0);
  $disposition = $_POST["disposition"] ?? "";      // 'confirm' | 'reject'
  $note        = mb_substr(trim($_POST["note"] ?? ""), 0, 200); // responded_text 上限 200
  $contentAction = $_POST["contentAction"] ?? "";                         // 'keep' | 'remove'（僅成立時）
  $removeReason  = mb_substr(trim($_POST["removeReason"] ?? ""), 0, 255); // remove_reason / MANAGE_REASON 上限 255

  // 三種申訴表的欄位對應（白名單，避免用使用者字串拼表名/欄名）
  //   respondent：被申訴人欄位；vio：member 對應違規次數欄；targets：可能的下架目標表（fk 非空者才是實際目標）
  $map = [
    "battle" => [
      "table"      => "battle_appeal",
      "pk"         => "BATTLE_APPEAL_ID",
      "status"     => "APPEAL_STATUS",
      "admin"      => "ADMIN_ID",
      "at"         => "RESPONDED_AT",
      "text"       => "RESPONDED_TEXT",
      "respondent"    => "RESPONDENT_MEM_ID",
      "complainant"   => "COMPLAINANT_MEM_ID",
      "vio"           => "BATTLE_VIO_COUNTS",
      "suspendStatus" => "BATTLE_STATUS",
      "suspendUntil"  => "BATTLE_SUSPEND_UNTIL",
      "targets"    => [
        ["fk" => "BATTLE_ID", "table" => "battle_record", "pk" => "BATTLE_ID", "show" => "IS_SHOW"]
      ]
    ],
    "exchange" => [
      "table"      => "appeal_exchange",
      "pk"         => "ae_id",
      "status"     => "ae_status",
      "admin"      => "admin_id",
      "at"         => "responded_at",
      "text"       => "responded_text",
      "respondent"    => "respondent_mem_id",
      "complainant"   => "complainant_mem_id",
      "vio"           => "EXCHANGE_VIO_COUNTS",
      "suspendStatus" => "MARKET_STATUS",
      "suspendUntil"  => "MARKET_SUSPEND_UNTIL",
      "targets"    => [
        ["fk" => "post_id", "table" => "exchange_post",    "pk" => "post_id", "show" => "is_show", "reasonCol" => "remove_reason"],
        ["fk" => "comm_id", "table" => "exchange_comment", "pk" => "comm_id", "show" => "is_show", "reasonCol" => "remove_reason"]
      ]
    ],
    "forum" => [
      "table"      => "appeal_forum",
      "pk"         => "af_id",
      "status"     => "af_status",
      "admin"      => "admin_id",
      "at"         => "responded_at",
      "text"       => "responded_text",
      "respondent"    => "respondent_mem_id",
      "complainant"   => "complainant_mem_id",
      "vio"           => "FORUM_VIO_COUNTS",
      "suspendStatus" => "FORUM_STATUS",
      "suspendUntil"  => "FORUM_SUSPEND_UNTIL",
      "targets"    => [
        ["fk" => "art_id", "table" => "article", "pk" => "art_id", "show" => "is_show", "reasonCol" => "remove_reason"],
        ["fk" => "msg_id", "table" => "message", "pk" => "msg_id", "show" => "is_show", "reasonCol" => "remove_reason"]
      ]
    ]
  ];

  // 對應處置 → 新狀態
  $statusMap = [
    "confirm" => "CONFIRMED",
    "reject"  => "REJECTED"
  ];

  // 自動停權門檻與天數（違規累計達門檻 → 該功能停權 N 天）
  $suspendThreshold = 3;
  $suspendDays      = 7;

  // 型別中文標籤（用於會員通知文案）
  $typeLabel = [
    "battle"   => "約戰",
    "forum"    => "論壇",
    "exchange" => "交換"
  ];

  // 參數驗證
  if (!isset($map[$sourceType]) || $appealId <= 0 || !isset($statusMap[$disposition])) {
    http_response_code(400);
    echo json_encode([
      "success" => false,
      "message" => "參數錯誤"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 成立時需指定內容處置；選下架則須有下架原因
  if ($disposition === "confirm") {
    if (!in_array($contentAction, ["keep", "remove"], true)) {
      http_response_code(400);
      echo json_encode([
        "success" => false,
        "message" => "請選擇內容處置（保留/下架）"
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
    if ($contentAction === "remove" && $removeReason === "") {
      http_response_code(400);
      echo json_encode([
        "success" => false,
        "message" => "下架時請填寫下架原因"
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }

  $m         = $map[$sourceType];
  $newStatus = $statusMap[$disposition];

  // 先讀出被申訴人、狀態、各目標外鍵（值一律由後端取得，不信任前端）
  $fkCols     = array_column($m["targets"], "fk");
  $selectCols = array_merge([$m["respondent"], $m["complainant"], $m["status"]], $fkCols);
  $selectSql  = "SELECT " . implode(", ", $selectCols)
              . " FROM {$m['table']} WHERE {$m['pk']} = ? LIMIT 1 FOR UPDATE";

  try {
    $pdo->beginTransaction();

    $sel = $pdo->prepare($selectSql);
    $sel->execute([$appealId]);
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      $pdo->rollBack();
      http_response_code(404);
      echo json_encode([
        "success" => false,
        "message" => "找不到申訴案件"
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    // 只處理待處理(PENDING)的案件，避免重複處理
    if ($row[$m["status"]] !== "PENDING") {
      $pdo->rollBack();
      http_response_code(409);
      echo json_encode([
        "success" => false,
        "message" => "該申訴已處理過"
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    // 1. 更新申訴記錄
    $updSql = "
      UPDATE {$m['table']}
      SET {$m['status']} = ?,
          {$m['admin']}  = ?,
          {$m['at']}     = NOW(),
          {$m['text']}   = ?
      WHERE {$m['pk']} = ?
    ";
    $pdo->prepare($updSql)->execute([$newStatus, $adminId, $note, $appealId]);

    //==== 會員通知：申訴結果（三型別通用）START =====
    $label = $typeLabel[$sourceType];

    // 通知申訴人：審核已完成（不分成立/駁回都發）
    $complainantId = (int) $row[$m["complainant"]];
    if ($complainantId > 0) {
      $pdo->prepare("
        INSERT INTO notification (mem_id, content, is_read, create_time)
        VALUES (?, ?, 0, NOW())
      ")->execute([
        $complainantId,
        "你提出的{$label}申訴已完成審核，請至會員中心「我的申訴」查看處理結果。"
      ]);
    }

    // 申訴成立 → 另外通知被申訴人
    if ($newStatus === "CONFIRMED") {
      $respondentNotifyId = (int) $row[$m["respondent"]];
      if ($respondentNotifyId > 0) {
        $pdo->prepare("
          INSERT INTO notification (mem_id, content, is_read, create_time)
          VALUES (?, ?, 0, NOW())
        ")->execute([
          $respondentNotifyId,
          "你涉及的一筆{$label}申訴經管理員審核，已判定成立，請至會員中心「違規紀錄」查看處分說明。"
        ]);
      }
    }
    //==== 會員通知：申訴結果 END =====


    // 2. 成立才有的副作用：違規次數+1、下架內容
    if ($disposition === "confirm") {
      // 2-1. 被申訴人違規次數 +1；累計達門檻則自動停權該功能
      $respondentId = (int) $row[$m["respondent"]];
      if ($respondentId > 0) {
        $pdo->prepare("UPDATE member SET {$m['vio']} = {$m['vio']} + 1 WHERE MEM_ID = ?")
            ->execute([$respondentId]);

        // 讀出加完後的違規次數
        $cntStmt = $pdo->prepare("SELECT {$m['vio']} FROM member WHERE MEM_ID = ?");
        $cntStmt->execute([$respondentId]);
        $newCount = (int) $cntStmt->fetchColumn();

        // 達門檻（含以上）→ 該功能停權 N 天，並刷新到期日；不覆蓋永久停權
        if ($newCount >= $suspendThreshold) {
          $suspendSql = "
            UPDATE member
            SET {$m['suspendStatus']} = 'TEMP-RESTRICT',
                {$m['suspendUntil']}  = DATE_ADD(NOW(), INTERVAL {$suspendDays} DAY)
            WHERE MEM_ID = ?
              AND {$m['suspendStatus']} <> 'PERMA-RESTRICT'
          ";
          $stmt = $pdo->prepare($suspendSql);
          $stmt->execute([$respondentId]);

          // ===== 會員通知：功能停權（三型別通用）=====
          if ($stmt->rowCount() > 0) {
            $pdo->prepare("
              INSERT INTO notification (mem_id, content, is_read, create_time)
              VALUES (?, ?, 0, NOW())
            ")->execute([
              $respondentId,
              "你的{$typeLabel[$sourceType]}功能因累積違規紀錄已達停權門檻，系統將暫停該功能使用 {$suspendDays} 天。"
            ]);
          }
        }
      }

      // 2-2. 內容處置：只有選「下架」才下架內容並記錄下架原因
      if ($contentAction === "remove") {
        foreach ($m["targets"] as $t) {
          $fkVal = $row[$t["fk"]] ?? null;
          if (empty($fkVal)) {
            continue;
          }

          if (isset($t["reasonCol"])) {
            // 論壇/交換：內容表本身有 remove_reason，一併寫入下架原因
            $downSql = "UPDATE {$t['table']} SET {$t['show']} = 0, {$t['reasonCol']} = ? WHERE {$t['pk']} = ?";
            $pdo->prepare($downSql)->execute([$removeReason, (int) $fkVal]);
          } else {
            // 對戰：battle_record 無 remove_reason，改新增一筆管理處置紀錄
            $pdo->prepare("UPDATE {$t['table']} SET {$t['show']} = 0 WHERE {$t['pk']} = ?")
                ->execute([(int) $fkVal]);
            $pdo->prepare("
              INSERT INTO battle_manage_record (BATTLE_ID, ADMIN_ID, MANAGE_ACTION, MANAGE_REASON)
              VALUES (?, ?, 'REMOVE', ?)
            ")->execute([(int) $fkVal, $adminId, $removeReason]);
          }
          break; // 只處理一個對應目標
        }
      }
    }

    $pdo->commit();

    echo json_encode([
      "success" => true,
      "message" => "處理結果已送出"
    ], JSON_UNESCAPED_UNICODE);
  } catch (PDOException $e) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
      "success" => false,
      "message" => "送出處理結果失敗"
    ], JSON_UNESCAPED_UNICODE);
  }
?>
