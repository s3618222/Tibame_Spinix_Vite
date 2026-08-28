<?php
  /*
  API：後台登入狀態確認
  功能：確認目前是否有管理員登入，並取得其基本資料
  使用情境：後台頁面載入時呼叫此 API 做守衛（未登入則導回登入頁）
  METHOD：GET

  Response（已登入）：
  { "success": true, "isLoggedIn": true, "admin": { "id":1, "name":"admin1", "type":"超級管理員" } }

  Response（未登入）：
  { "success": true, "isLoggedIn": false, "admin": null }
  */

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 先檢查 Session 中是否已有登入管理員的 ADMIN_ID
  $adminId = $_SESSION["ADMIN_ID"] ?? null;

  // 沒有 ADMIN_ID → 目前無管理員登入
  if (!$adminId) {

    echo json_encode([
        "success" => true,
        "isLoggedIn" => false,
        "admin" => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 有 ADMIN_ID → 查詢管理員基本資料（同時確認仍為「在職」）
  $sql = "
    SELECT
      admin_id,
      name,
      admin_type,
      admin_state
    FROM admin
    WHERE admin_id = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$adminId]);
  $admin = $stmt->fetch(PDO::FETCH_ASSOC);

  // 防呆：查無此人，或帳號已離職 → 視為未登入
  if (!$admin || $admin["admin_state"] !== "在職") {

    echo json_encode([
        "success" => true,
        "isLoggedIn" => false,
        "admin" => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 回傳目前登入管理員的基本資料
  echo json_encode([
    "success" => true,
    "isLoggedIn" => true,
    "admin" => [
        "id" => $admin["admin_id"],
        "name" => $admin["name"],
        "type" => $admin["admin_type"]
    ]
  ], JSON_UNESCAPED_UNICODE);

?>
