<?php
  //申請加入約戰API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //限制申請加入約戰需使用 POST METHOD
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);

      echo json_encode([
          "success" => false,
          "message" => "僅允許 POST 請求"
      ], JSON_UNESCAPED_UNICODE);

      exit;
  }

  //先從PHP Session中取得當前登入會員的MEM_ID
  $memberId = $_SESSION["MEM_ID"] ?? null;

  //再從前端傳送來的formData中取得欲加入的約戰編號、會員填寫的連絡資訊
  $battleId = (int) ($_POST["battle_id"] ?? 0);
  $contact = trim($_POST["contact"] ?? "");

  //檢查目前是否有登入會員；若Session中沒有MEM_ID資訊，就不能送出約戰申請
  if (!$memberId) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "請先登入後再申請加入約戰"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //防呆：檢查欲申請加入的約戰編號是否有效
  if ($battleId <= 0) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "缺少有效的約戰編號"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //接著檢查會員是否有填寫聯絡資訊
  if ($contact === "") {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "請填寫聯絡資訊"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //接著查詢目前欲申請加入的約戰
  $sql = "
    SELECT
        BATTLE_ID,
        INITIATOR_ID,
        PARTICIPANT_ID,
        BATTLE_STATUS,
        IS_SHOW,
        BATTLE_DEADLINE
    FROM battle_record
    WHERE BATTLE_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$battleId]);

  $battle = $stmt->fetch(PDO::FETCH_ASSOC);

  //查詢完後，先檢查該筆約戰是否存在
  if (!$battle) {
    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "找不到這筆約戰資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //約戰確定存在時：先檢查申請加入者是不是就是發起人自己 (不能加入自己發起的約戰)
  if ((int) $battle["INITIATOR_ID"] === (int) $memberId) {

      http_response_code(400);

      echo json_encode([
          "success" => false,
          "message" => "不能申請自己發起的約戰"
      ], JSON_UNESCAPED_UNICODE);

      exit;
  }

  //檢查該場約戰是否已有其他會員申請
  if ($battle["PARTICIPANT_ID"] !== null) {
    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "這場約戰目前已有其他會員申請"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查該筆約戰紀錄狀態是否仍為MATCHING
  if ($battle["BATTLE_STATUS"] !== "MATCHING") {
    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "這場約戰目前已無法申請"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查約戰的IS_SHOW狀態 是否仍設定為公開(1)
  if ((int) $battle["IS_SHOW"] !== 1) {
    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "這場約戰目前已停止公開"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查是否已超過申請截止時間
  if (strtotime($battle["BATTLE_DEADLINE"]) <= time()) {
    //當截止時間小於或等於目前時間時，禁止加入
    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "這場約戰已超過申請截止時間"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //將申請者的資料寫入該筆約戰紀錄中，並將約戰狀態改為PENDING
  // UPDATE資料時，再次確認該場約戰目前無申請者，避免當兩個會員同時按下送出時，有可能後者覆蓋前者
  $sql = "
    UPDATE battle_record
    SET
        PARTICIPANT_ID = ?,
        PAR_CONTACT = ?,
        BATTLE_STATUS = 'PENDING'
    WHERE BATTLE_ID = ?
        AND PARTICIPANT_ID IS NULL
        AND BATTLE_STATUS = 'MATCHING'
        AND IS_SHOW = 1
        AND BATTLE_DEADLINE > NOW()
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$memberId, $contact, $battleId]);


  //確認是否有成功更新約戰資料；rowCount會反映實際影響幾筆資料，當筆數為1時，代表有更新成功，若為0，即代表送出申請那刻，原約戰紀錄已經不符合申請條件了
  if ($stmt->rowCount() === 0) {

    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "這場約戰目前已無法申請，請重新整理後再試"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }


  //申請加入成功
  echo json_encode([
    "success" => true,
    "message" => "申請已送出，請等待發起人確認"
  ], JSON_UNESCAPED_UNICODE);


?>