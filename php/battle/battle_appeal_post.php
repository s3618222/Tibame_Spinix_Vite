<?php
  // 送出約戰申訴API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //REQUEST METHOD 必須為 POST
  if($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
      "success" => false,
      "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  $memberId = $_SESSION["MEM_ID"] ?? null;
  
  if (!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再提出申訴"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //接收前端傳來的約戰申訴資料：約戰id、申訴內容
  $battleId = (int) ($_POST["battle_id"] ?? 0);
  $appealContent = trim($_POST["description"] ?? "");

  //檢查約戰id
  if ($battleId <= 0) {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "約戰編號無效"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查申訴內容
  if ($appealContent === "") {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "請填寫申訴說明"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //查詢資料庫中該筆約戰紀錄
  $sql = "
    SELECT
      BATTLE_ID,
      INITIATOR_ID,
      PARTICIPANT_ID

    FROM battle_record

    WHERE BATTLE_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$battleId]);
  $battle = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$battle) {
    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到這筆約戰資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //檢查當前會員是否為此約戰中的其中一員
  $isInitiator = (int) $battle["INITIATOR_ID"] === (int) $memberId;
  $isParticipant = (int) $battle["PARTICIPANT_ID"] === (int) $memberId;

  //會員必須是約戰中的發起人或參加人，才可以進行約戰申訴
  if (!$isInitiator && !$isParticipant) {
    http_response_code(403);

    echo json_encode([
      "success" => false,
      "message" => "你沒有權限針對這筆約戰提出申訴"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //找出約戰紀錄中，被申訴的一方
  if ($isInitiator) { // 會員是發起人 → 被申訴人就是參加人
    $respondentId = $battle["PARTICIPANT_ID"];

  } else { // 會員是參加人 → 被申訴人就是發起人
    $respondentId = $battle["INITIATOR_ID"];
  }

  //檢查該筆約戰是否有對手存在
  if (!$respondentId) {
    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "這筆約戰目前沒有可申訴的對手"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }


  // =====驗證前端回傳的佐證圖片======
  $evidenceFiles = $_FILES["evidence_images"] ?? null;

  if ($evidenceFiles) {
    //檢查name陣列有幾筆資料，就可知道總共有幾張圖 (限制最多5張)
    $fileCount = count($evidenceFiles["name"]);

    if($fileCount > 5) {
      http_response_code(400);

      echo json_encode([
        "success" => false,
        "message" => "佐證圖片最多只能上傳 5 張"
      ], JSON_UNESCAPED_UNICODE);

      exit;
    }

    //約戰申訴佐證圖片的存取路徑
    $uploadDir = __DIR__ . "/../uploads/battle_appeal/";

    //防呆：如果資料夾不存在，就自動建立
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $imagePaths = []; //用來儲存會員上傳圖片的相對路徑

    for($i = 0; $i < $fileCount; $i++) { //跑迴圈，取得每張圖片資料
      $tmpName = $evidenceFiles["tmp_name"][$i];
      $error = $evidenceFiles["error"][$i];
      $size = $evidenceFiles["size"][$i];

      if ($error !== UPLOAD_ERR_OK) {
        http_response_code(400);

        echo json_encode([
          "success" => false,
          "message" => "佐證圖片上傳失敗"
        ], JSON_UNESCAPED_UNICODE);

        exit;
      }

      //限制單張圖片最大2mb
      $maxFileSize = 2 * 1024 * 1024;

      if ($size > $maxFileSize) {
        http_response_code(400);

        echo json_encode([
          "success" => false,
          "message" => "單張圖片不可超過 2MB"
        ], JSON_UNESCAPED_UNICODE);

        exit;
      }

      //檢查暫存檔是否為有效圖片
      $imageInfo = getimagesize($tmpName);

      if ($imageInfo === false) {
        http_response_code(400);

        echo json_encode([
          "success" => false,
          "message" => "上傳的檔案不是有效圖片"
        ], JSON_UNESCAPED_UNICODE);

        exit;
      }

      // 從圖片資訊中取得真正的 MIME 類型
      $mimeType = $imageInfo["mime"];

      $allowedMimeTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
      ];

      if (!isset($allowedMimeTypes[$mimeType])) {
        http_response_code(400);

        echo json_encode([
          "success" => false,
          "message" => "圖片僅支援 JPG、PNG、WEBP"
        ], JSON_UNESCAPED_UNICODE);

        exit;
      }

      //產生圖片的副檔名
      $extension = $allowedMimeTypes[$mimeType];

      //將上傳圖片更改檔名(避免名稱重複)
      $newFileName = "battle_appeal_" . $battleId . "_" . uniqid() . "." . $extension;

      //圖片實際存放位置
      $targetPath = $uploadDir . $newFileName;

      //將上傳圖片存進目標資料夾
      if (!move_uploaded_file($tmpName, $targetPath)) {
        http_response_code(500);

        echo json_encode([
          "success" => false,
          "message" => "圖片儲存失敗"
        ], JSON_UNESCAPED_UNICODE);

        exit;
      }

      //要存入資料庫的圖片"相對路徑"
      $relativePath = "uploads/battle_appeal/" . $newFileName;
      $imagePaths[] = $relativePath; //將每筆上傳圖片的相對路徑資訊放入imagePaths陣列中； $array[] 可理解為 js的 array.push(...)
    }

    //跑完迴圈後，將剛剛儲存會員上傳圖片的php陣列轉為JSON字串，存入photoEvidence變數中
    $photoEvidence = null;

    if (!empty($imagePaths)) {
      $photoEvidence = json_encode(
        $imagePaths,
        JSON_UNESCAPED_UNICODE
      );
    }

  }

  //將申訴資料寫入約戰申訴表中
  $sql = "
    INSERT INTO battle_appeal (
      BATTLE_ID,
      COMPLAINANT_MEM_ID,
      RESPONDENT_MEM_ID,
      APPEAL_CONTENT,
      PHOTO_EVIDENCE
    )
    VALUES (?, ?, ?, ?, ?)
  ";

  $stmt = $pdo->prepare($sql);

  $stmt->execute([
    $battleId,
    $memberId,
    $respondentId,
    $appealContent,
    $photoEvidence
  ]);

  //寫入成功回傳
  echo json_encode([
    "success" => true,
    "message" => "約戰申訴已成功送出"
  ], JSON_UNESCAPED_UNICODE);


?>