<?php
  // 後台「約戰管理」：
  // 管理員執行約戰下架 REMOVE / 恢復上架 RESTORE API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");
  require_once("../common/admin_guard.php");

  header("Content-Type: application/json; charset=utf-8");

  //涉及資料狀態更動，僅允許POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得當前登入管理員的ID
  $adminId = $_SESSION["ADMIN_ID"];

  //接收前端傳回的處置資料
  $battleId = (int) ($_POST["battle_id"] ?? 0); //約戰編號
  $action = $_POST["action"] ?? ""; //處置操作 REMOVE or RESOTRE
  $reason = trim($_POST["reason"] ?? ""); //處置說明


  //先進行初步資料驗證
  if ($battleId <= 0) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "約戰編號無效"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //處置操作只能叫 REMOVE 或 RESTORE
  if ($action !== "REMOVE" && $action !== "RESTORE") {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "管理處置類型不正確"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //處置說明不能空白
  if ($reason === "") {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "請填寫處置說明"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //向資料庫取得該筆約戰紀錄
  $sql = "
    SELECT
      BATTLE_ID,
      INITIATOR_ID,
      BATTLE_TITLE,
      BATTLE_STATUS,
      BATTLE_DEADLINE,
      IS_SHOW,

      /* 判斷目前是否已超過約戰申請截止時間 */
      CASE
        WHEN BATTLE_DEADLINE <= NOW() THEN 1
        ELSE 0
      END AS IS_EXPIRED

    FROM battle_record
    WHERE BATTLE_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $battleId
  ]);

  $battle = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$battle) {
    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到這筆約戰資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得該筆約戰紀錄後，確認該筆約戰符合對應操作處置的規定
  /*
    1. 只有 MATCHING 可以進行上下架處置
    2. 要執行REMOVE，約戰當前必須為上架中狀態
    3. 要執行RESTORE，約戰必須目前是下架狀態，且還未過期
  */

  if ($battle["BATTLE_STATUS"] !== "MATCHING") {
    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "只有配對中的約戰可進行上下架處置"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  $currentIsShow = (int) $battle["IS_SHOW"]; //取得當前約戰紀錄的上下架狀態

  //要執行REMOVE時，先確保該筆紀錄原先不能是已下架
  if ($action === "REMOVE" && $currentIsShow === 0) {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "此約戰目前已經是下架狀態"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //要執行RESTORE時，確保該筆紀錄原先不能是上架狀態
  if ($action === "RESTORE" && $currentIsShow === 1) {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "此約戰目前已經是上架狀態"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //已超過截止時間時，不得恢復上架
  if ($action === "RESTORE" && (int) $battle["IS_EXPIRED"] === 1) {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "此約戰已超過申請截止時間，無法恢復上架"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //=====通過以上條件，才將資料更新寫入資料庫=====

  //根據處置操作類型，決定要更新的顯示狀態
  //執行下架，IS_SHOW須更新為 0；上架則更新為 1
  $newIsShow = $action === "REMOVE" ? 0 : 1;

  //開始交易 (因為要確保更新 battle_record的 IS_SHOW，與更新battle_manage_record這兩張表格都成功)
  try {
    $pdo->beginTransaction();

    // 更新約戰紀錄目前的顯示狀態
    $sql = "
      UPDATE battle_record
      SET IS_SHOW = ?
      WHERE BATTLE_ID = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $newIsShow,
      $battleId
    ]);


    // 接著是新增一筆處置紀錄
    $sql = "
      INSERT INTO battle_manage_record (
        BATTLE_ID,
        ADMIN_ID,
        MANAGE_ACTION,
        MANAGE_REASON
      )
      VALUES (?, ?, ?, ?)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $battleId,
      $adminId,
      $action,
      $reason
    ]);


    //依照管理員操作，產生對應的會員通知內容
    if ($action === "REMOVE") {
      $notificationContent =
        "你發起的約戰「" .
        $battle["BATTLE_TITLE"] .
        "」，因內容不符合平台管理規範，已進行下架處置。";

    } else {
      $notificationContent =
        "你發起的約戰「" .
        $battle["BATTLE_TITLE"] .
        "」經管理員重新審核後，已恢復上架。";
    }


    //將處置結果通知約戰發起人
    $sql = "
      INSERT INTO notification (
        mem_id,
        content,
        is_read,
        create_time
      )
      VALUES (?, ?, 0, NOW())
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $battle["INITIATOR_ID"],
      $notificationContent
    ]);

    //當所有sql都執行成功後，才確認此次交易
    $pdo->commit();

    //回傳操作結果成功
    echo json_encode([
      "success" => true,
      "message" => $action === "REMOVE"
        ? "約戰已成功下架"
        : "約戰已成功恢復上架"
    ], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {

    //只要其中一個sql指令執行失敗，就全部取消
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
      "success" => false,
      "message" => "約戰處置失敗",
      "error" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

  }

?>