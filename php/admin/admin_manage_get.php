<?php
  // 後台「管理員帳號管理」— 取得管理員清單
  // 僅回傳列表所需欄位，不含 password

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 守衛：僅允許已登入的管理員存取
  require_once("../common/admin_guard.php");

  $sql = "
    SELECT
      admin_id                                   AS id,
      account,
      name,
      DATE_FORMAT(create_time, '%Y-%m-%d')       AS createTime,
      admin_type                                 AS type,
      admin_state                                AS state
    FROM admin
    ORDER BY admin_id
  ";

  $stmt = $pdo->query($sql);
  $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    "success" => true,
    "admins"  => $admins
  ], JSON_UNESCAPED_UNICODE);

?>
