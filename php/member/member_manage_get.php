<?php
  // 後台「會員管理」— 取得所有會員清單（供前端過濾/搜尋/分頁）
  // 將 member 表的功能狀態 enum 對映為前端 Pill 用的 normal/restricted + 到期日

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 守衛：僅允許已登入的管理員存取（會員管理兩種管理員皆可）
  require_once("../common/admin_guard.php");

  // 將功能狀態 enum 對映為前端用的 normal / restricted
  function mapStatus($status) {
    return $status === "ACTIVE" ? "normal" : "restricted";
  }

  // 受限到期日：暫時停權且有到期時間 → 格式化為 YYYY/M/D；永久停權或無到期 → 空字串
  function mapUntil($status, $until) {
    if ($status === "TEMP-RESTRICT" && !empty($until)) {
      return date("Y/n/j", strtotime($until));
    }
    return "";
  }

  $sql = "
    SELECT
      MEM_ID,
      MEM_NAME,
      MEM_ACCOUNT,
      BATTLE_STATUS,
      BATTLE_SUSPEND_UNTIL,
      FORUM_STATUS,
      FORUM_SUSPEND_UNTIL,
      MARKET_STATUS,
      MARKET_SUSPEND_UNTIL
    FROM member
    ORDER BY MEM_ID
  ";

  $stmt = $pdo->query($sql);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $members = [];
  foreach ($rows as $row) {
    $members[] = [
      "id"           => (int) $row["MEM_ID"],
      "name"         => $row["MEM_NAME"],
      "account"      => $row["MEM_ACCOUNT"],
      "battleStatus" => mapStatus($row["BATTLE_STATUS"]),
      "battleUntil"  => mapUntil($row["BATTLE_STATUS"], $row["BATTLE_SUSPEND_UNTIL"]),
      "forumStatus"  => mapStatus($row["FORUM_STATUS"]),
      "forumUntil"   => mapUntil($row["FORUM_STATUS"], $row["FORUM_SUSPEND_UNTIL"]),
      "marketStatus" => mapStatus($row["MARKET_STATUS"]),
      "marketUntil"  => mapUntil($row["MARKET_STATUS"], $row["MARKET_SUSPEND_UNTIL"])
    ];
  }

  echo json_encode([
    "success" => true,
    "members" => $members
  ], JSON_UNESCAPED_UNICODE);

?>
