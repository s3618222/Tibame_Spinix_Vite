<?php
  // 後台「會員管理」— 停權處置
  // 依 scopes + duration 更新 member 對應功能狀態/到期時間，並寫一則通知告知會員（含停權原因）
  // 沿用 complaint_handle_post.php 的「更新狀態 + DATE_ADD 到期 + 寫 notification + 交易」模式

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 守衛：僅允許已登入的管理員存取（會員管理兩種管理員皆可）
  require_once("../common/admin_guard.php");

  // 限定 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // scope → member 表欄位 / 中文（白名單，欄位名不由前端拼字）
  $scopeMap = [
    "battle" => ["status" => "BATTLE_STATUS", "until" => "BATTLE_SUSPEND_UNTIL", "label" => "約戰"],
    "forum"  => ["status" => "FORUM_STATUS",  "until" => "FORUM_SUSPEND_UNTIL",  "label" => "論壇"],
    "market" => ["status" => "MARKET_STATUS", "until" => "MARKET_SUSPEND_UNTIL", "label" => "交換"]
  ];

  // 取得前端資料
  $memberId = (int) ($_POST["memberId"] ?? 0);
  $scopes   = array_filter(array_map("trim", explode(",", $_POST["scopes"] ?? "")));
  $reason   = mb_substr(trim($_POST["reason"] ?? ""), 0, 50);
  $duration = $_POST["duration"] ?? "";

  // 驗證：範圍非空且皆合法
  if (empty($scopes)) {
    echo json_encode(["success" => false, "message" => "請至少選擇一項停權範圍"], JSON_UNESCAPED_UNICODE);
    exit;
  }
  foreach ($scopes as $s) {
    if (!isset($scopeMap[$s])) {
      echo json_encode(["success" => false, "message" => "停權範圍不正確"], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }

  // 驗證：處分合法
  $allowedDurations = ["7", "30", "90", "permanent"];
  if (!in_array($duration, $allowedDurations, true)) {
    echo json_encode(["success" => false, "message" => "停權處分不正確"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 驗證：原因必填
  if ($reason === "") {
    echo json_encode(["success" => false, "message" => "請填寫停權原因"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 驗證：會員存在
  $check = $pdo->prepare("SELECT MEM_ID FROM member WHERE MEM_ID = ?");
  $check->execute([$memberId]);
  if (!$check->fetch()) {
    echo json_encode(["success" => false, "message" => "查無此會員"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $isPermanent = ($duration === "permanent");
  $days        = $isPermanent ? 0 : (int) $duration;

  try {
    $pdo->beginTransaction();

    // 逐一更新各選定範圍的狀態與到期時間
    foreach ($scopes as $s) {
      $statusCol = $scopeMap[$s]["status"];
      $untilCol  = $scopeMap[$s]["until"];

      if ($isPermanent) {
        $sql = "UPDATE member SET {$statusCol} = 'PERMA-RESTRICT', {$untilCol} = NULL WHERE MEM_ID = ?";
        $pdo->prepare($sql)->execute([$memberId]);
      } else {
        $sql = "UPDATE member
                SET {$statusCol} = 'TEMP-RESTRICT',
                    {$untilCol}  = DATE_ADD(NOW(), INTERVAL {$days} DAY)
                WHERE MEM_ID = ?";
        $pdo->prepare($sql)->execute([$memberId]);
      }
    }

    // 組通知文案（一則列出所有被停權權限）
    $labels = implode("、", array_map(fn($s) => $scopeMap[$s]["label"], $scopes));

    if ($isPermanent) {
      $content = "【停權通知】您的「{$labels}」權限已被永久停權。停權原因：{$reason}。"
               . "若有疑問，歡迎來信 contact@spinix.com.tw，將由專人為您說明。";
    } else {
      $until = date("Y/m/d", strtotime("+{$days} days"));
      $content = "【停權通知】您的「{$labels}」權限已暫停使用，為期 {$days} 天，將於 {$until} 恢復。"
               . "停權原因：{$reason}。若有疑問，歡迎來信 contact@spinix.com.tw，將由專人為您說明。";
    }

    $pdo->prepare("
      INSERT INTO notification (mem_id, content, is_read, create_time)
      VALUES (?, ?, 0, NOW())
    ")->execute([$memberId, $content]);

    $pdo->commit();

    echo json_encode(["success" => true, "message" => "已完成停權處置"], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "停權處理失敗"], JSON_UNESCAPED_UNICODE);
  }

?>
