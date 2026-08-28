<?php
// 刊登二手交換API

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

//先從PHP Session中取得當前登入會員的MEM_ID
$memberId = $_SESSION["MEM_ID"] ?? null;
if (!$memberId) {
   echo json_encode(["success" => false, "message" => "請先登入"], JSON_UNESCAPED_UNICODE);
   exit;
}

if (!isset($_FILES["photos"]) || empty($_FILES["photos"]["name"][0])) {
   http_response_code(400);
   echo json_encode([
      "success" => false,
      "message" => "請至少上傳一張物品照片"
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

// ✅ 改動 2：設定上傳限制
$maxFileSize = 2 * 1024 * 1024; // 單張最大 2MB
$maxPhotoCount = 5; // 最多幾張，跟你 Vue 元件的 MAX_PHOTOS 對齊
$allowedMimeTypes = [
   "image/jpeg" => "jpg",
   "image/png"  => "png"
];

$photoCount = count($_FILES["photos"]["name"]);

// 檢查是否超過最大張數
if ($photoCount > $maxPhotoCount) {
   http_response_code(400);
   echo json_encode([
      "success" => false,
      "message" => "最多只能上傳 {$maxPhotoCount} 張照片"
   ], JSON_UNESCAPED_UNICODE);
   exit;
}


$uploadDir = __DIR__ . "/../uploads/articles/";

// 防呆：確定資料夾存在，不存在就自動建立
if (!is_dir($uploadDir)) {
   mkdir($uploadDir, 0755, true);
}

// ✅ 改動 3：用迴圈逐一驗證、搬移每一張照片
$uploadedFilePaths = []; // 記錄這次實際搬移成功的檔案，方便失敗時可以刪除、回滾
$exchangeImages = []; // 最後要寫進資料庫的圖片路徑陣列

for ($i = 0; $i < $photoCount; $i++) {

   // 先確認這張圖片上傳成功
   if ($_FILES["photos"]["error"][$i] !== UPLOAD_ERR_OK) {
      http_response_code(400);
      echo json_encode([
         "success" => false,
         "message" => "第 " . ($i + 1) . " 張照片上傳失敗"
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }

   // 檢查檔案大小
   if ($_FILES["photos"]["size"][$i] > $maxFileSize) {
      http_response_code(400);
      echo json_encode([
         "success" => false,
         "message" => "第 " . ($i + 1) . " 張照片大小不可超過 2MB"
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }

   // 讀取圖片內容，確認是否為真正的圖片檔案
   $imageInfo = getimagesize($_FILES["photos"]["tmp_name"][$i]);

   if ($imageInfo === false) {
      http_response_code(400);
      echo json_encode([
         "success" => false,
         "message" => "第 " . ($i + 1) . " 張檔案不是有效圖片"
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }

   // 取得實際圖片 MIME type
   $mimeType = $imageInfo["mime"];

   // 檢查格式是否為允許的 JPG / PNG
   if (!isset($allowedMimeTypes[$mimeType])) {
      http_response_code(400);
      echo json_encode([
         "success" => false,
         "message" => "第 " . ($i + 1) . " 張照片僅支援 JPG、PNG 格式"
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }

   // 取得副檔名，組成新檔名（避免重複覆蓋）
   $extension = $allowedMimeTypes[$mimeType];
   $newFileName = "exchange_" . bin2hex(random_bytes(8)) . "." . $extension;
   $targetPath = $uploadDir . $newFileName;

   // 搬移暫存檔案到正式資料夾
   if (!move_uploaded_file($_FILES["photos"]["tmp_name"][$i], $targetPath)) {
      http_response_code(500);
      echo json_encode([
         "success" => false,
         "message" => "第 " . ($i + 1) . " 張照片儲存失敗"
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }

   $uploadedFilePaths[] = $targetPath;
   $exchangeImages[] = "uploads/articles/" . $newFileName; // 存進資料庫用的相對路徑
}





$sql = "INSERT INTO `exchange_post`( `type`, `title`, `description`, `want_item`, `condition`,  `post_pic1`, `post_pic2`, `post_pic3`, `post_pic4`, `post_pic5`,`mem_id`, `CITY_ID`, `DISTRICT_ID`, `post_contact`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $pdo->prepare($sql);


$type = $_POST['type'] ?? '';
$title = trim($_POST['title'] ?? '');
$condition = $_POST['condition'] ?? '';
$city_id = $_POST['CITY_ID'] ?? '';
$district_id = $_POST['DISTRICT_ID'] ?? '';
$post_contact = trim($_POST['post_contact']  ?? '');
$want_item = trim($_POST['want_item'] ?? '');
$description = trim($_POST['description'] ?? '');
$post_pic1 = $exchangeImages[0] ?? null;
$post_pic2 = $exchangeImages[1] ?? null;
$post_pic3 = $exchangeImages[2] ?? null;
$post_pic4 = $exchangeImages[3] ?? null;
$post_pic5 = $exchangeImages[4] ?? null;

$stmt->execute([$type, $title, $description, $want_item, $condition,  $post_pic1, $post_pic2, $post_pic3, $post_pic4, $post_pic5, $memberId, $city_id, $district_id, $post_contact]);

$newPostId = $pdo->lastInsertId(); // 拿到剛新增這筆資料的 ID

echo json_encode([
   "success" => true,
   "message" => "刊登成功",
   "post_id" => $newPostId,
   "photos" => $exchangeImages
], JSON_UNESCAPED_UNICODE);
exit;
