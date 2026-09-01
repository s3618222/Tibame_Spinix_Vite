<?php
// forum_appeal_post.php
// 論壇申訴：送出申訴，INSERT 一筆進 appeal_forum
// 對應前端：complaintForm.vue 的 handleSubmit()（論壇分支）

session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo json_encode([
    "success" => false,
    "message" => "僅允許 POST 請求"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$currentMemberId = $_SESSION["MEM_ID"] ?? null;

if (!$currentMemberId) {
  http_response_code(401);
  echo json_encode([
    "success" => false,
    "message" => "請先登入後再提出申訴"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$artId = (int) ($_POST["art_id"] ?? 0);
$msgId = (int) ($_POST["msg_id"] ?? 0);
$appealContent = trim($_POST["description"] ?? "");

if (($artId <= 0 && $msgId <= 0) || ($artId > 0 && $msgId > 0)) {
  http_response_code(400);
  echo json_encode([
    "success" => false,
    "message" => "檢舉目標無效"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($appealContent === "") {
  http_response_code(400);
  echo json_encode([
    "success" => false,
    "message" => "請填寫申訴說明"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// 重新查一次被檢舉人是誰，不能只信任前端傳來的資料
if ($artId > 0) {
  $sql = "SELECT mem_id FROM article WHERE art_id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$artId]);
} else {
  $sql = "SELECT mem_id FROM message WHERE msg_id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$msgId]);
}

$target = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$target) {
  http_response_code(404);
  echo json_encode([
    "success" => false,
    "message" => "找不到檢舉目標"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$respondentId = (int) $target["mem_id"];

if ($respondentId === (int) $currentMemberId) {
  http_response_code(400);
  echo json_encode([
    "success" => false,
    "message" => "無法檢舉自己發表的內容"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// 防止重複申訴：同一人對同一內容，若已有 PENDING 狀態的申訴，擋下
$sql = "
  SELECT af_id FROM appeal_forum
  WHERE complainant_mem_id = ?
    AND af_status = 'PENDING'
    AND " . ($artId > 0 ? "art_id = ?" : "msg_id = ?");
$stmt = $pdo->prepare($sql);
$stmt->execute([$currentMemberId, $artId > 0 ? $artId : $msgId]);

if ($stmt->fetch()) {
  http_response_code(409);
  echo json_encode([
    "success" => false,
    "message" => "你已經對這則內容提出過申訴，請等待審核"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// =====驗證前端回傳的佐證圖片======

$photoEvidence = null;

$evidenceFiles = $_FILES["evidence_images"] ?? null;

if ($evidenceFiles) {
  $fileCount = count($evidenceFiles["name"]);

  if ($fileCount > 5) {
    http_response_code(400);
    echo json_encode([
      "success" => false,
      "message" => "佐證圖片最多只能上傳 5 張"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $uploadDir = __DIR__ . "/../uploads/forum_appeal/";

  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }

  $imagePaths = [];

  for ($i = 0; $i < $fileCount; $i++) {
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

    $maxFileSize = 2 * 1024 * 1024;

    if ($size > $maxFileSize) {
      http_response_code(400);
      echo json_encode([
        "success" => false,
        "message" => "單張圖片不可超過 2MB"
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    $imageInfo = getimagesize($tmpName);

    if ($imageInfo === false) {
      http_response_code(400);
      echo json_encode([
        "success" => false,
        "message" => "上傳的檔案不是有效圖片"
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

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

    $extension = $allowedMimeTypes[$mimeType];

    $newFileName = "forum_appeal_" . ($artId > 0 ? "art{$artId}" : "msg{$msgId}") . "_" . uniqid() . "." . $extension;

    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
      http_response_code(500);
      echo json_encode([
        "success" => false,
        "message" => "圖片儲存失敗"
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    $relativePath = "uploads/forum_appeal/" . $newFileName;
    $imagePaths[] = $relativePath;
  }

  if (!empty($imagePaths)) {
    $photoEvidence = json_encode($imagePaths, JSON_UNESCAPED_UNICODE);
  }
}

try {
  $sql = "
    INSERT INTO appeal_forum (art_id, msg_id, complainant_mem_id, respondent_mem_id, af_content, af_status, create_time, af_evidence)
    VALUES (?, ?, ?, ?, ?, 'PENDING', NOW(), ?)
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $artId > 0 ? $artId : null,
    $msgId > 0 ? $msgId : null,
    $currentMemberId,
    $respondentId,
    $appealContent,
    $photoEvidence
  ]);

  echo json_encode([
    "success" => true,
    "message" => "申訴已成功送出"
  ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    "success" => false,
    "message" => "申訴送出失敗"
  ], JSON_UNESCAPED_UNICODE);
}
?>