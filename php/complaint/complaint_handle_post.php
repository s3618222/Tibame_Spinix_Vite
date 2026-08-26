<?php
  // 後台「申訴管理」— 送出處理結果（管理員裁決寫回）
  // 成立(confirm)：更新申訴為 CONFIRMED，並「被申訴人違規次數+1」＋「下架該內容 is_show=0」（一個交易內完成）
  // 駁回(reject)：只更新申訴為 REJECTED，member 與內容上下架皆不變
  // 延後：達3次自動停權7天、remove_reason / battle_manage_record 記錄

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 僅允許 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 處理的管理員（沿用 battle_manage_post.php 慣例：登入 API 完成前先預設 key = admin_id、fallback 1）
  $adminId = $_SESSION["admin_id"] ?? 1;

  // 接收前端資料
  $sourceType  = $_POST["sourceType"] ?? "";
  $appealId    = (int) ($_POST["id"] ?? 0);
  $disposition = $_POST["disposition"] ?? "";      // 'confirm' | 'reject'
  $note        = mb_substr(trim($_POST["note"] ?? ""), 0, 200); // responded_text 上限 200

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
      "respondent" => "RESPONDENT_MEM_ID",
      "vio"        => "BATTLE_VIO_COUNTS",
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
      "respondent" => "respondent_mem_id",
      "vio"        => "EXCHANGE_VIO_COUNTS",
      "targets"    => [
        ["fk" => "post_id", "table" => "exchange_post",    "pk" => "post_id", "show" => "is_show"],
        ["fk" => "comm_id", "table" => "exchange_comment", "pk" => "comm_id", "show" => "is_show"]
      ]
    ],
    "forum" => [
      "table"      => "appeal_forum",
      "pk"         => "af_id",
      "status"     => "af_status",
      "admin"      => "admin_id",
      "at"         => "responded_at",
      "text"       => "responded_text",
      "respondent" => "respondent_mem_id",
      "vio"        => "FORUM_VIO_COUNTS",
      "targets"    => [
        ["fk" => "art_id", "table" => "article", "pk" => "art_id", "show" => "is_show"],
        ["fk" => "msg_id", "table" => "message", "pk" => "msg_id", "show" => "is_show"]
      ]
    ]
  ];

  // 對應處置 → 新狀態
  $statusMap = [
    "confirm" => "CONFIRMED",
    "reject"  => "REJECTED"
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

  $m         = $map[$sourceType];
  $newStatus = $statusMap[$disposition];

  // 先讀出被申訴人、狀態、各目標外鍵（值一律由後端取得，不信任前端）
  $fkCols     = array_column($m["targets"], "fk");
  $selectCols = array_merge([$m["respondent"], $m["status"]], $fkCols);
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

    // 2. 成立才有的副作用：違規次數+1、下架內容
    if ($disposition === "confirm") {
      // 2-1. 被申訴人違規次數 +1
      $respondentId = (int) $row[$m["respondent"]];
      if ($respondentId > 0) {
        $vioSql = "UPDATE member SET {$m['vio']} = {$m['vio']} + 1 WHERE MEM_ID = ?";
        $pdo->prepare($vioSql)->execute([$respondentId]);
      }

      // 2-2. 下架對應內容（找外鍵非空的那個目標，只下架一個）
      foreach ($m["targets"] as $t) {
        $fkVal = $row[$t["fk"]] ?? null;
        if (!empty($fkVal)) {
          $downSql = "UPDATE {$t['table']} SET {$t['show']} = 0 WHERE {$t['pk']} = ?";
          $pdo->prepare($downSql)->execute([(int) $fkVal]);
          break;
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
