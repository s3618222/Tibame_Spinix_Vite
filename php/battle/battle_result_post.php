<?php
  //「我的約戰」中，當會員為約戰發起人、且該筆約戰為競技模式時，回傳「勝者資訊」API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //REQUEST METHOD 須為 POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得Session中當前會員的MEM_ID
  $memberId = $_SESSION["MEM_ID"] ?? null;

  //取得前端傳來的約戰ID、勝者資訊
  $battleId = (int) ($_POST["battle_id"] ?? 0);

  //0為發起人勝、1為參加人勝
  $winner = $_POST["winner"] ?? null;

  // 沒登入會員，無法回填
  if (!$memberId) {

    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再回填對戰結果"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查約戰ID是否有效
    if ($battleId <= 0) {
      http_response_code(400);

      echo json_encode([
        "success" => false,
        "message" => "約戰編號無效"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

  //winner的回傳資料只能是 "0" 和 "1" ($_POST收到的資料通常是字串)
  if ($winner !== "0" && $winner !== "1") {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "勝者資料不正確"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //通過初步檢查後，再將前端傳回的winner字串轉為整數
  $winner = (int) $winner;

  //接著查詢此筆約戰
  $sql = "
    SELECT
      BATTLE_ID,
      INITIATOR_ID,
      PARTICIPANT_ID,
      BATTLE_MODE,
      BATTLE_STATUS,
      WINNER
    FROM battle_record
    WHERE BATTLE_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$battleId]);

  $battle = $stmt->fetch(PDO::FETCH_ASSOC);

  //確定該筆約戰資料存在
  if (!$battle) {

    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到這筆約戰資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //確定當前會員為該約戰的發起人
  if ((int) $battle["INITIATOR_ID"] !== (int) $memberId) {

    http_response_code(403);

    echo json_encode([
      "success" => false,
      "message" => "只有約戰發起人可以回填對戰結果"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //防呆：確定此筆約戰已有參加人
  if ($battle["PARTICIPANT_ID"] === null) {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "這場約戰目前沒有參加人"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //確定此筆約戰為「競技模式」
  if ($battle["BATTLE_MODE"] !== "COMPETITIVE") {
    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "休閒模式不需要回填勝者"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //且約戰狀態必須為CONFIRMED
  // 必須已經配對成功
  if ($battle["BATTLE_STATUS"] !== "CONFIRMED") {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "這場約戰目前無法回填對戰結果"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //已經有勝者的情況下，無法重新回填 (防止連續點擊)
  // 已經有勝者就不允許再次修改
  if ($battle["WINNER"] !== null) {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "這場約戰已經回填過對戰結果"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //通過上述檢查後，再寫入約戰紀錄中的勝者資料
  $sql = "
    UPDATE battle_record
    SET WINNER = ?
    WHERE BATTLE_ID = ?
      AND INITIATOR_ID = ?
      AND BATTLE_MODE = 'COMPETITIVE'
      AND BATTLE_STATUS = 'CONFIRMED'
      AND WINNER IS NULL
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $winner,
    $battleId,
    $memberId
  ]);

  // 如果沒有資料真的被更新
  if ($stmt->rowCount() === 0) {

    http_response_code(409);

    echo json_encode([
      "success" => false,
      "message" => "對戰結果目前無法更新，請重新整理後再試"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //新增勝者成功
  echo json_encode([
    "success" => true,
    "message" => "對戰結果已成功回填"
  ], JSON_UNESCAPED_UNICODE);


?>