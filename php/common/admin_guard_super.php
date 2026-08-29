<?php
  // 後台 API 強化守衛：僅允許「超級管理員」存取
  //
  // 用法：在僅限超管的 API 開頭 require（需在 connect_ckd101g2.php 之後，因為要用 $pdo）：
  //   require_once("../common/admin_guard_super.php");
  //
  // 分級界線：管理員帳號管理限超管；其他後台 API 用 admin_guard.php（僅需登入）即可。

  // 先做登入檢查（未登入 → 401 並 exit）；用 __DIR__ 確保路徑正確
  require_once(__DIR__ . "/admin_guard.php");

  // 查目前登入者的身分（以 DB 為準，反映最新的 admin_type）
  $stmt = $pdo->prepare("SELECT admin_type FROM admin WHERE admin_id = ?");
  $stmt->execute([$_SESSION["ADMIN_ID"]]);
  $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

  // 非超級管理員 → 403（已登入但無權限）
  if (!$currentAdmin || $currentAdmin["admin_type"] !== "超級管理員") {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "權限不足，僅限超級管理員"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }
?>
