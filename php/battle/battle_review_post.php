<?php
  //會員中心「我的約戰」，會員約戰完成後，留下星等與文字評價API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //REQUEST METHOD 須為 POST
  if($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  $memberId = $_SESSION["MEM_ID"] ?? null;

  //取得前端回傳要評價的約戰id、評價星等、評論文字
  $battleId = (int) ($_POST["battle_id"] ?? 0);
  $stars = (int) ($_POST["stars"] ?? 0);
  $comment = trim($_POST["comment"] ?? "");

  //未登入，無法評價
  if (!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再留下評價"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查約戰id是否有效
    if ($battleId <= 0) {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "約戰編號無效"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //評價星等只能是1~5
  if ($stars < 1 || $stars > 5) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "評價星等必須介於 1 到 5 星"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //查詢該筆約戰資料
  $sql = "
    SELECT
      BATTLE_ID,
      INITIATOR_ID,
      PARTICIPANT_ID,
      BATTLE_STATUS,
      TO_INI_COMMENTED_AT,
      TO_PAR_COMMENTED_AT
    FROM battle_record
    WHERE BATTLE_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$battleId]);

  $battle = $stmt->fetch(PDO::FETCH_ASSOC);

  //針對資料庫查到的該筆約戰資料，繼續做相關條件檢查

   //檢查該筆約戰是否存在
  if (!$battle) {
    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到這筆約戰資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //狀態必須為CONFIRMED，才可以留評價
  if ($battle["BATTLE_STATUS"] !== "CONFIRMED") {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "目前約戰狀態無法留下評價"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //接著判斷當前會員在該約戰中的腳色為發起人或參加人
  $isInitiator = (int) $battle["INITIATOR_ID"] === (int) $memberId;
  
  $isParticipant = (int) $battle["PARTICIPANT_ID"] === (int) $memberId;

  //首先判斷當前會員必須為該場約戰中的其中一員
  if (!$isInitiator && !$isParticipant) { //會員既不是發起人，也不是參加人

    http_response_code(403);

    echo json_encode([
      "success" => false,
      "message" => "你不是這場約戰的參與會員"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //根據當前會員於約戰中的角色，判斷要回填哪一組欄位(to initiator 或 to participant)

  if($isInitiator) { //目前會員為發起人時，評價對象為參加人
    
  // 若已經評價過，不能重複送出
    if ($battle["TO_PAR_COMMENTED_AT"] !== null) {

      http_response_code(409);

      echo json_encode([
        "success" => false,
        "message" => "你已經留下過評價"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

    $sql = "
      UPDATE battle_record
      SET
        TO_PAR_STARS = ?,
        TO_PAR_COMMENT = ?,
        TO_PAR_COMMENTED_AT = NOW()
      WHERE BATTLE_ID = ?
        AND INITIATOR_ID = ?
        AND BATTLE_STATUS = 'CONFIRMED'
        AND TO_PAR_COMMENTED_AT IS NULL
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $stars,
      $comment,
      $battleId,
      $memberId
    ]);

  } else { //目前會員為參加人，評論對象為發起人

    //評價過，不得重複評論
    if ($battle["TO_INI_COMMENTED_AT"] !== null) {
      http_response_code(409);

      echo json_encode([
        "success" => false,
        "message" => "你已經留下過評價"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

    $sql = "
      UPDATE battle_record
      SET
        TO_INI_STARS = ?,
        TO_INI_COMMENT = ?,
        TO_INI_COMMENTED_AT = NOW()
      WHERE BATTLE_ID = ?
        AND PARTICIPANT_ID = ?
        AND BATTLE_STATUS = 'CONFIRMED'
        AND TO_INI_COMMENTED_AT IS NULL
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $stars,
      $comment,
      $battleId,
      $memberId
    ]);
  }

  //確認資料是否有成功寫入資料庫
  if ($stmt->rowCount() === 0) {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "評價目前無法送出，請重新整理後再試"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //回傳前端，評價寫入成功
  echo json_encode([
    "success" => true,
    "message" => "評價已成功送出"
  ], JSON_UNESCAPED_UNICODE);
?>