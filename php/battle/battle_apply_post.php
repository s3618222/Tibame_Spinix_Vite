<?php
  //申請加入約戰API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");
  require_once("./battle_access_check.php");

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

  //取得當前登入會員的約戰功能使用權限
  $battleAccess = checkBattleAccess($pdo, $memberId);

  //若有登入，檢查該會員的約戰功能是否有被限制；限制狀態下，禁止送出約戰申請
  if (!$battleAccess["allowed"]) {

    http_response_code(403);

    if ($battleAccess["status"] === "TEMP-RESTRICT") {

      $message = "你的約戰功能目前暫時受限，受限期間無法申請加入約戰。";

    } elseif ($battleAccess["status"] === "PERMA-RESTRICT") {

      $message = "你的約戰功能目前已被限制使用，無法申請加入約戰。";

    } else {

      $message = "目前無法使用約戰相關功能。";
    }

    echo json_encode([
      "success" => false,
      "message" => $message
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
        BATTLE_TITLE,
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


  try {
    //開始資料庫交易
    $pdo->beginTransaction();

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

      //前面UPDATE沒有成功，就取消這次交易
      $pdo->rollBack();

      http_response_code(409);

      echo json_encode([
          "success" => false,
          "message" => "這場約戰目前已無法申請，請重新整理後再試"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

    //約戰申請成功後，新增通知給該約戰的發起人
    $notificationMemberId = (int) $battle["INITIATOR_ID"];

    //取得申請加入約戰 (即當前會員)的會員名稱
    $sql = "
      SELECT MEM_NAME
      FROM member
      WHERE MEM_ID = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);
    $applicant = $stmt->fetch(PDO::FETCH_ASSOC);

    //通知內容
    $notificationContent = $applicant["MEM_NAME"] . " 已申請加入你的約戰「" . $battle["BATTLE_TITLE"] . "」";

    //將通知訊息放進notification資料表
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
        $notificationMemberId,
        $notificationContent
    ]);

    //當約戰資料與通知資料都成功寫入後，再正式提交交易
    $pdo->commit();
    
    echo json_encode([
      "success" => true,
      "message" => "申請已送出，請等待發起人確認"
    ], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {
    
    //將此次交易中的資料修改內容全取消
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
      "success" => false,
      "message" => "申請加入約戰時發生錯誤"
    ], JSON_UNESCAPED_UNICODE);
  }

?>