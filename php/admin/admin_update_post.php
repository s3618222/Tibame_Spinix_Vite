<?php
  // 後台「管理員帳號管理」— 編輯管理員（名稱 + 在職/離職狀態）

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 守衛：管理員帳號管理僅限超級管理員
  require_once("../common/admin_guard_super.php");

  // 限定 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 取得前端資料
  $adminId = (int) ($_POST["adminId"] ?? 0);
  $name    = trim($_POST["name"] ?? "");
  $state   = $_POST["state"] ?? "";

  // 驗證：名稱非空、狀態合法
  if ($name === "") {
    echo json_encode([
      "success" => false,
      "message" => "管理員名稱為必填"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if (!in_array($state, ["在職", "離職"], true)) {
    echo json_encode([
      "success" => false,
      "message" => "管理員狀態不正確"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 驗證：管理員存在
  $check = $pdo->prepare("SELECT admin_type FROM admin WHERE admin_id = ?");
  $check->execute([$adminId]);
  $target = $check->fetch(PDO::FETCH_ASSOC);
  if (!$target) {
    echo json_encode([
      "success" => false,
      "message" => "查無此管理員"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 防呆：超級管理員帳號不可被編輯
  if ($target["admin_type"] === "超級管理員") {
    http_response_code(403);
    echo json_encode([
      "success" => false,
      "message" => "超級管理員帳號不可編輯"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 更新
  $sql = "UPDATE admin SET name = ?, admin_state = ? WHERE admin_id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$name, $state, $adminId]);

  echo json_encode([
    "success" => true,
    "message" => "已更新管理員資料"
  ], JSON_UNESCAPED_UNICODE);

?>
