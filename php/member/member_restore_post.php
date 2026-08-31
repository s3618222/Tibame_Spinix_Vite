<?php
  // 後台「會員管理」— 恢復權限
  // 將選定範圍的功能狀態設回 ACTIVE、清空到期時間，並寫一則通知告知會員
  // 結構與 member_suspend_post.php 對稱

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

  // 驗證：範圍非空且皆合法
  if (empty($scopes)) {
    echo json_encode(["success" => false, "message" => "請至少選擇一項要恢復的權限"], JSON_UNESCAPED_UNICODE);
    exit;
  }
  foreach ($scopes as $s) {
    if (!isset($scopeMap[$s])) {
      echo json_encode(["success" => false, "message" => "恢復範圍不正確"], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }

  // 驗證：會員存在
  $check = $pdo->prepare("SELECT MEM_ID FROM member WHERE MEM_ID = ?");
  $check->execute([$memberId]);
  if (!$check->fetch()) {
    echo json_encode(["success" => false, "message" => "查無此會員"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  try {
    $pdo->beginTransaction();

    // 逐一將選定範圍設回 ACTIVE、清空到期時間
    foreach ($scopes as $s) {
      $statusCol = $scopeMap[$s]["status"];
      $untilCol  = $scopeMap[$s]["until"];

      $sql = "UPDATE member SET {$statusCol} = 'ACTIVE', {$untilCol} = NULL WHERE MEM_ID = ?";
      $pdo->prepare($sql)->execute([$memberId]);
    }

    // 組通知文案（一則列出所有恢復的權限）
    $labels  = implode("、", array_map(fn($s) => $scopeMap[$s]["label"], $scopes));
    $content = "【權限恢復通知】您的「{$labels}」權限已恢復正常使用，感謝您的配合。";

    $pdo->prepare("
      INSERT INTO notification (mem_id, content, is_read, create_time)
      VALUES (?, ?, 0, NOW())
    ")->execute([$memberId, $content]);

    $pdo->commit();

    echo json_encode(["success" => true, "message" => "已恢復權限"], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "恢復處理失敗"], JSON_UNESCAPED_UNICODE);
  }

?>
