<?php
  // 後台「申訴管理」— 送出處理結果（管理員裁決寫回）
  // 本次只做「申訴結果判定」：更新該筆申訴的 狀態 / 處理管理員 / 回覆時間 / 回覆內容
  // 違規次數+1、停權、達3次自動停權 → 之後步驟再處理（本檔不碰 member 表）

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
  $note        = trim($_POST["note"] ?? "");

  // 三種申訴表的欄位對應（白名單，避免用使用者字串拼表名/欄名）
  $map = [
    "battle" => [
      "table"  => "battle_appeal",
      "pk"     => "BATTLE_APPEAL_ID",
      "status" => "APPEAL_STATUS",
      "admin"  => "ADMIN_ID",
      "at"     => "RESPONDED_AT",
      "text"   => "RESPONDED_TEXT"
    ],
    "exchange" => [
      "table"  => "appeal_exchange",
      "pk"     => "ae_id",
      "status" => "ae_status",
      "admin"  => "admin_id",
      "at"     => "responded_at",
      "text"   => "responded_text"
    ],
    "forum" => [
      "table"  => "appeal_forum",
      "pk"     => "af_id",
      "status" => "af_status",
      "admin"  => "admin_id",
      "at"     => "responded_at",
      "text"   => "responded_text"
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

  $m = $map[$sourceType];
  $newStatus = $statusMap[$disposition];

  // 只更新「待處理(PENDING)」的案件，避免重複處理
  $sql = "
    UPDATE {$m['table']}
    SET {$m['status']} = ?,
        {$m['admin']}  = ?,
        {$m['at']}     = NOW(),
        {$m['text']}   = ?
    WHERE {$m['pk']} = ?
      AND {$m['status']} = 'PENDING'
  ";

  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newStatus, $adminId, $note, $appealId]);

    if ($stmt->rowCount() === 0) {
      // 找不到該筆，或該筆已非待處理狀態
      http_response_code(409);
      echo json_encode([
        "success" => false,
        "message" => "找不到案件，或該申訴已處理過"
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    echo json_encode([
      "success" => true,
      "message" => "處理結果已送出"
    ], JSON_UNESCAPED_UNICODE);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
      "success" => false,
      "message" => "送出處理結果失敗"
    ], JSON_UNESCAPED_UNICODE);
  }
?>
