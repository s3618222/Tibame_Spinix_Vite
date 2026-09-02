<?php
session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

header("Content-Type: application/json; charset=utf-8");

//REQUEST METHOD 必須為 POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
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
      "message" => "請先登入"
   ], JSON_UNESCAPED_UNICODE);

   exit;
}

$post_id = (int)($_POST['post_id'] ?? 0);
$comm_id = (int)($_POST['comm_id'] ?? 0);
$appealContent = trim($_POST['description'] ?? '');

if (($post_id <= 0 && $comm_id <= 0) || ($post_id > 0 && $comm_id > 0)) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '檢舉目標無效'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if ($appealContent === '') {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '請輸入檢舉內容'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if ($post_id > 0) {
   $sql = "SELECT mem_id FROM exchange_post WHERE post_id = ?";
   $stmt = $pdo->prepare($sql);
   $stmt->execute([$post_id]);
} else {
   $sql = "SELECT mem_id FROM exchange_comment WHERE comm_id = ?";
   $stmt = $pdo->prepare($sql);
   $stmt->execute([$comm_id]);
}

$traget = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$traget) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '檢舉失敗'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$responseID = (int)$traget['mem_id'];
if ($responseID === (int)$memberId) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '檢舉無效'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

$sql = "SELECT 
ae_id FROM appeal_exchange
WHERE complainant_mem_id = ?
AND ae_status = 'PENDING'
AND " . ($post_id > 0 ? "post_id = ?" : "comm_id = ?");

$stmt = $pdo->prepare($sql);
$stmt->execute([$memberId, ($post_id > 0 ? $post_id : $comm_id)]);

if ($stmt->fetch()) {
   http_response_code(409);
   echo json_encode([
      'success' => false,
      'message' => '你已提出過申訴，請等待管理員審核'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

// ===== 驗證前端回傳的佐證圖片 ======

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

   $uploadDir = __DIR__ . "/../uploads/exchange_appeal/";

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

      $newFileName = "exchange_appeal_" . ($post_id > 0 ? "post{$post_id}" : "comm{$comm_id}") . "_" . uniqid() . "." . $extension;

      $targetPath = $uploadDir . $newFileName;

      if (!move_uploaded_file($tmpName, $targetPath)) {
         http_response_code(500);
         echo json_encode([
            "success" => false,
            "message" => "圖片儲存失敗"
         ], JSON_UNESCAPED_UNICODE);
         exit;
      }

      $relativePath = "uploads/exchange_appeal/" . $newFileName;
      $imagePaths[] = $relativePath;
   }

   if (!empty($imagePaths)) {
      $photoEvidence = json_encode($imagePaths, JSON_UNESCAPED_UNICODE);
   }
}

try {
   $sql = "INSERT INTO `appeal_exchange`(
   `post_id`,
   `comm_id`,
   `complainant_mem_id`,
   `respondent_mem_id`,
   `ae_content`,
   `ae_evidence`) 
   VALUES (?,?,?,?,?,?)";

   $stmt = $pdo->prepare($sql);
   $stmt->execute([
      $post_id > 0 ? $post_id : null,
      $comm_id > 0 ? $comm_id : null,
      $memberId,
      $responseID,
      $appealContent,
      $photoEvidence
   ]);

   echo json_encode([
      'success' => true,
      'message' => '申訴已成功送出'
   ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
   http_response_code(500);
   echo json_encode([
      "success" => false,
      "message" => "申訴送出失敗"
   ], JSON_UNESCAPED_UNICODE);
}
