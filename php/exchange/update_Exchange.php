<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("../common/funcs.php");

session_start();
$memberId = $_SESSION["MEM_ID"] ?? null;

$postId = (int)($_POST['post_id'] ?? 0);
$title = $_POST['title'] ?? '';
$type = $_POST['type'] ?? '';
$condition = $_POST['condition'] ?? '';
$description = $_POST['description'] ?? '';
$wantItem = $_POST['want_item'] ?? '';
$existingPhotos = json_decode($_POST['existing_photos'] ?? '[]', true);
if (!is_array($existingPhotos)) {
   $existingPhotos = [];
}

// // 確認文章存在，且是本人的
$ownerStmt = $pdo->prepare("SELECT mem_id, post_pic1, post_pic2, post_pic3, post_pic4, post_pic5 FROM exchange_post WHERE post_id = ?");
$ownerStmt->execute([$postId]);
$oldRow = $ownerStmt->fetch(PDO::FETCH_ASSOC);

if (!$oldRow) {
   echo json_encode(['success' => false, 'message' => '找不到此商品'], JSON_UNESCAPED_UNICODE);
   exit;
}
if ((int)$oldRow['mem_id'] !== (int)$memberId) {
   echo json_encode(['success' => false, 'message' => '無權限修改此商品'], JSON_UNESCAPED_UNICODE);
   exit;
}

$getBaseUrl = getUploadBaseUrl();
$maxFileSize = 2 * 1024 * 1024;
$maxPhotoCount = 5;
$allowedMimeTypes = [
   "image/jpeg" => "jpg",
   "image/png"  => "png"
];

$newPhotoCount = isset($_FILES['photos']) ? count(array_filter($_FILES['photos']['name'])) : 0;
$totalCount = count($existingPhotos) + $newPhotoCount;

if ($totalCount === 0) {
   echo json_encode(['success' => false, 'message' => '請至少上傳一張物品照片'], JSON_UNESCAPED_UNICODE);
   exit;
}
if ($totalCount > $maxPhotoCount) {
   echo json_encode(['success' => false, 'message' => "最多只能有 {$maxPhotoCount} 張照片"], JSON_UNESCAPED_UNICODE);
   exit;
}

$uploadedPaths = [];
if ($newPhotoCount > 0) {
   $uploadDir = __DIR__ . "/../uploads/articles/";

   foreach ($_FILES['photos']['name'] as $index => $originalName) {
      if (empty($originalName)) continue;

      $tmpName = $_FILES['photos']['tmp_name'][$index];
      $fileSize = $_FILES['photos']['size'][$index];
      $imageType = @exif_imagetype($tmpName);
      $mimeTypeMap = [
         IMAGETYPE_JPEG => 'image/jpeg',
         IMAGETYPE_PNG  => 'image/png',
      ];
      $mimeType = $mimeTypeMap[$imageType] ?? null;

      if (!$mimeType) {
         echo json_encode(['success' => false, 'message' => "「{$originalName}」不是有效的圖片檔案"], JSON_UNESCAPED_UNICODE);
         exit;
      }

      if ($fileSize > $maxFileSize) {
         echo json_encode(['success' => false, 'message' => "「{$originalName}」超過 2MB 限制"], JSON_UNESCAPED_UNICODE);
         exit;
      }
      if (!isset($allowedMimeTypes[$mimeType])) {
         echo json_encode(['success' => false, 'message' => "「{$originalName}」不是允許的圖片格式"], JSON_UNESCAPED_UNICODE);
         exit;
      }

      $extension = $allowedMimeTypes[$mimeType];
      $newFileName = "exchange_" . bin2hex(random_bytes(8)) . "." . $extension;
      $targetPath = $uploadDir . $newFileName;

      if (move_uploaded_file($tmpName, $targetPath)) {
         $uploadedPaths[] = $getBaseUrl . "articles/" . $newFileName;
      }
   }
}

$allPhotos = array_merge($existingPhotos, $uploadedPaths);

$pic1 = $allPhotos[0] ?? null;
$pic2 = $allPhotos[1] ?? null;
$pic3 = $allPhotos[2] ?? null;
$pic4 = $allPhotos[3] ?? null;
$pic5 = $allPhotos[4] ?? null;

// 刪除被移除的舊圖實體檔案
$originalPhotos = array_filter([
   $oldRow['post_pic1'],
   $oldRow['post_pic2'],
   $oldRow['post_pic3'],
   $oldRow['post_pic4'],
   $oldRow['post_pic5']
]);
$removedPhotos = array_diff($originalPhotos, $existingPhotos);

foreach ($removedPhotos as $removedUrl) {
   $localPath = str_replace($getBaseUrl, '../../', $removedUrl);
   if (file_exists($localPath)) {
      unlink($localPath);
   }
}

$sql = "UPDATE exchange_post SET 
   `title` = ?, 
   `type` = ?, 
   `condition` = ?, 
   `description` = ?, 
   `want_item` = ?,
   `post_pic1` = ?, 
   `post_pic2` = ?, 
   `post_pic3` = ?, 
   `post_pic4` = ?,  
   `post_pic5` = ?
   WHERE post_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$title, $type, $condition, $description, $wantItem, $pic1, $pic2, $pic3, $pic4, $pic5, $postId]);

echo json_encode([
   'success' => true,
   'message' => '更新成功',
   'data' => ['images' => $allPhotos]
], JSON_UNESCAPED_UNICODE);