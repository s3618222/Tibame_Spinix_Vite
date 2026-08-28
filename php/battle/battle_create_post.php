<?php
  //建立約戰API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");
  require_once("./battle_upload_config.php");
  require_once("./battle_access_check.php");

  header("Content-Type: application/json; charset=utf-8");

  //設定REQUEST METHOD 必須為POST
  if($_SERVER["REQUEST_METHOD"] !== "POST") {  
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
      ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得當前登入會員ID
  $memberId = $_SESSION["MEM_ID"] ?? null;

  //若沒有登入會員，不能建立約戰，停止後續執行
  if( !$memberId ) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再建立約戰"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得當前登入會員的約戰功能使用權限
  $battleAccess = checkBattleAccess($pdo, $memberId);

  //約戰功能受到限制時，禁止建立新的對戰邀約
  if (!$battleAccess["allowed"]) {

    http_response_code(403);

    if ($battleAccess["status"] === "TEMP-RESTRICT") {

      $message ="你的約戰功能目前暫時受限，受限期間無法建立對戰邀約。";

    } elseif ($battleAccess["status"] === "PERMA-RESTRICT") {

      $message ="你的約戰功能目前已被限制使用，無法建立對戰邀約。";

    } else {

      $message ="目前無法使用約戰相關功能。";
    }

    echo json_encode([
      "success" => false,
      "message" => $message
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //接收前端傳回的formData
  $title = trim($_POST["title"] ?? "");
  $mode = $_POST["mode"] ?? "";
  $level = $_POST["level"] ?? "";
  $target = $_POST["target"] ?? "";

  $cityId = (int) ($_POST["city_id"] ?? 0);
  $districtId = (int) ($_POST["district_id"] ?? 0);

  $battleDate = $_POST["battle_date"] ?? "";
  $deadline = $_POST["deadline"] ?? "";
  $address = trim($_POST["address"] ?? "");
  $contact = trim($_POST["contact"] ?? "");
  $description = trim($_POST["description"] ?? "");

  //檢查所有必填欄位是否都有填寫
  if (
      $title === "" ||
      $mode === "" ||
      $level === "" ||
      $target === "" ||
      $cityId <= 0 ||
      $districtId <= 0 ||
      $battleDate === "" ||
      $deadline === "" ||
      $address === "" ||
      $contact === "" ||
      $description === ""
  ) {
      http_response_code(400);

      echo json_encode([
          "success" => false,
          "message" => "請確認所有必填欄位皆已完成"
      ], JSON_UNESCAPED_UNICODE);

      exit;
  }

  //檢查有固定選項的SELECT欄位，回傳內容是否符合資料庫中設定的ENUM值
  $allowedModes = [ //允許的模式名稱
    "COMPETITIVE",
    "CASUAL"
  ];

  $allowedLevels = [ //允許的程度名稱
    "ALL",
    "BEGINNER",
    "INTERMEDIATE",
    "ADVANCED"
  ];

  $allowedTargets = [ //允許的對象名稱
    "ALL",
    "ADULT",
    "FAMILY"
  ];

  //針對模式、程度、對象的回傳值進行白名單檢查
  if (!in_array($mode, $allowedModes, true)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "對戰模式資料不正確"
      ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  if (!in_array($level, $allowedLevels, true)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "玩家程度資料不正確"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  if (!in_array($target, $allowedTargets, true)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "適合對象資料不正確"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //驗證時間相關的資料是否符合限制條件
  //先將回傳的時間字串轉成timestamp

  $battleTimestamp = strtotime($battleDate);
  $deadlineTimestamp = strtotime($deadline);
  $currentTimestamp = time();

  //防呆：先檢查日期格式正確
  if ($battleTimestamp === false || $deadlineTimestamp === false) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "日期時間格式不正確"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //條件限制；約戰時間不能是過去時間
  if ($battleTimestamp <= $currentTimestamp) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "約戰日期與時間必須晚於目前時間"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //截止時間也不能是過去時間
  if ($deadlineTimestamp <= $currentTimestamp) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "報名截止時間必須晚於目前時間"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查截止時間是否至少晚於當前時間一小時

  $oneHourLater = $currentTimestamp + 3600; //截止時間最低限制為當前時間再加上3600秒(即一小時)
  if ($deadlineTimestamp < $oneHourLater) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "報名截止時間至少需晚於目前時間一小時"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //截止時間必須早於約戰時間
  if ($deadlineTimestamp >= $battleTimestamp) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "報名截止時間必須早於約戰時間"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //驗證前端傳回的縣市與行政區資料是否存在
  $sql = "
    SELECT
      DISTRICT_ID,
      CITY_ID
    FROM district
    WHERE DISTRICT_ID = ?
      AND CITY_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$districtId, $cityId]);
  $location = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$location) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "縣市與行政區資料不正確"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //處理約戰封面圖片
  //當會員沒有上傳圖片時，圖片會使用平台的預設封面
  $battleImage = "battle_card_default.jpg";


  //紀錄此次是否有實際新增自訂圖片
  //若後面約戰資料新增失敗，就可以用這個路徑刪除剛上傳的圖片
  $uploadedFilePath = null;


  //確認使用者有上傳圖片，才接著處理
  if ( isset($_FILES["cover_image"]) && $_FILES["cover_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
  
    //先確認圖片上傳成功
    if ($_FILES["cover_image"]["error"] !== UPLOAD_ERR_OK) { // error不等於0，即代表上傳過程有問題。停止後續動作
      http_response_code(400);

      echo json_encode([
        "success" => false,
        "message" => "封面圖片上傳失敗"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

    //圖片大小限制設定：最大不能超過2mb
    $maxFileSize = 2 * 1024 * 1024;

    if ($_FILES["cover_image"]["size"] > $maxFileSize) {
      http_response_code(400);

      echo json_encode([
          "success" => false,
          "message" => "封面圖片大小不可超過2MB"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }


    //讀取圖片內容，確認檔案是否真的是圖片
    $imageInfo = getimagesize(
      $_FILES["cover_image"]["tmp_name"]
    );

    // 若讀不到圖片資訊，代表上傳的非有效圖片
    if ($imageInfo === false) {
      http_response_code(400);

      echo json_encode([
        "success" => false,
        "message" => "上傳的檔案不是有效圖片"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

    // 取得實際圖片 MIME type
    $mimeType = $imageInfo["mime"];

    // 設定允許的圖片格式，以及對應副檔名
    $allowedMimeTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png"
    ];

    // 若圖片不是 JPG 或 PNG，就停止往下執行
    if (!isset($allowedMimeTypes[$mimeType])) {
      http_response_code(400);

      echo json_encode([
          "success" => false,
          "message" => "封面圖片僅支援 JPG、PNG 格式"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

    // 取得圖片最後要使用的副檔名
    $extension = $allowedMimeTypes[$mimeType];

    //將上傳的封面圖以新檔名儲存
    $newFileName = "battle_" . bin2hex(random_bytes(8)) . "." . $extension;

    //設定上傳圖片檔要存放的資料夾位置
    // $uploadDir = __DIR__ . "/../../public/battle_uploads/";
    $uploadDir = $battleUploadDir; //取得約戰封面圖片統一存放位置

    //防呆：確定存放上傳圖片的資料夾存在；若不存在，就自動建立
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    //再加上更新後的圖片新檔名組成完整檔案路徑
    $targetPath = $uploadDir . $newFileName;

    //接著將php暫存圖片搬至正式資料夾
    if (!move_uploaded_file(
        $_FILES["cover_image"]["tmp_name"],
        $targetPath
    )) {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "封面圖片儲存失敗"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // 記住這次實際新增到伺服器的圖片位置
    $uploadedFilePath = $targetPath;

    // 最後寫進資料庫的圖片路徑
    $battleImage = "uploads/battle/" . $newFileName;
  }

  //建立新約戰資料
  $sql = "
    INSERT INTO battle_record (
      INITIATOR_ID,

      BATTLE_TITLE,
      BATTLE_IMG,
      BATTLE_DESC,

      CITY_ID,
      DISTRICT_ID,

      BATTLE_MODE,
      BATTLE_TARGET,
      BATTLE_LEVEL,

      BATTLE_DATE,
      BATTLE_DEADLINE,

      BATTLE_LOC,
      INI_CONTACT
    )
    VALUES (
      ?,
      ?,
      ?,
      ?,
      ?,
      ?,
      ?,
      ?,
      ?,
      ?,
      ?,
      ?,
      ?
    )
  ";

  //將已驗證的時間統一轉成 MySQL DATETIME 格式
  $battleDateForDb = date(
      "Y-m-d H:i:s",
      $battleTimestamp
  );

  $deadlineForDb = date(
      "Y-m-d H:i:s",
      $deadlineTimestamp
  );

  try {
    $stmt = $pdo->prepare($sql); 

    $stmt->execute([ 
      $memberId, 
  
      $title, 
      $battleImage, 
      $description, 
  
      $cityId, 
      $districtId, 
  
      $mode, 
      $target, 
      $level, 
  
      $battleDateForDb, 
      $deadlineForDb, 
  
      $address, 
      $contact 
    ]); 
  
    // 取得此次資料庫自動產生的新 BATTLE_ID 
    $newBattleId = (int) $pdo->lastInsertId();

  } catch (PDOException $e) {

    // 當會員有上傳自訂圖片，但後續資料庫新增約戰失敗時，刪除剛存進 uploads/battle 的圖片
    if ( $uploadedFilePath !== null && file_exists($uploadedFilePath)) {
      unlink($uploadedFilePath);
    }

    http_response_code(500);

    echo json_encode([
      "success" => false,
      "message" => "建立約戰失敗，請稍後再試"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //建立成功
  echo json_encode([
    "success" => true,
    "message" => "對戰邀約建立成功",
    "battleId" => $newBattleId
  ], JSON_UNESCAPED_UNICODE);

?>