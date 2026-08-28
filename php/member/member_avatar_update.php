<?php
  // 更新會員頭像API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //限制REQUEST METHOD 須為 POST
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
      "message" => "請先登入會員"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //確認前端有傳入頭像資訊
  if ( !isset($_FILES["avatar"]) || $_FILES["avatar"]["error"] === UPLOAD_ERR_NO_FILE) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "請選擇要上傳的會員頭像"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }
    
  // 確認PHP有成功接收到頭像圖片
  if ($_FILES["avatar"]["error"] !== UPLOAD_ERR_OK) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "會員頭像上傳失敗"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //限制圖片大小最大為2MB
  $maxFileSize = 2 * 1024 * 1024;

  if ($_FILES["avatar"]["size"] > $maxFileSize) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "會員頭像大小不可超過 2MB"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //確認上傳檔案為真的圖片，不是文字檔偽裝成圖片檔名
  $imageInfo = getimagesize(
    $_FILES["avatar"]["tmp_name"]
  );

  if ($imageInfo === false) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "上傳的檔案不是有效圖片"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  $mimeType = $imageInfo["mime"];

  //設定允許上傳的圖片格式
  /*
    -左邊是圖片真正的 MIME Type
    -右邊是最後儲存時使用的副檔名
  */
  $allowedMimeTypes = [
    "image/jpeg" => "jpg",
    "image/png" => "png"
  ];

  // 如果圖片格式不在白名單內，就拒絕上傳
  if (!isset($allowedMimeTypes[$mimeType])) {

    http_response_code(400);

    echo json_encode([
      "success" => false,
      "message" => "會員頭像僅支援 JPG、PNG 格式"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 取得最後儲存的圖片副檔名
  $extension = $allowedMimeTypes[$mimeType];

  //產生新的圖片檔名 - 不直接使用原始檔名，避免與資料庫其他檔名重複
  $newFileName = "member_" . bin2hex(random_bytes(8)) . "." . $extension;

  //設定上傳頭像的存放資料夾路徑 (php/uploads/member/)
  $uploadDir = __DIR__ . "/../uploads/member/";

  //當 uploads/member不存在時，就自動建立資料夾
  if (!is_dir($uploadDir)) {
    mkdir(
      $uploadDir,
      0755,
      true
    );
  }

  // 組合頭像的完整實體路徑
  $targetPath = $uploadDir . $newFileName;

  // 將圖片放入目標資料夾位置
  if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetPath)) {

    http_response_code(500);

    echo json_encode([
      "success" => false,
      "message" => "會員頭像儲存失敗"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  // 準備將實際寫入資料庫的頭像相對路徑
  $newAvatarPath = "uploads/member/" . $newFileName;

  // 當會員上傳新頭像時，須清除舊的頭像，因此要先取得目前的舊頭像路徑
  $sql = "
    SELECT
      MEM_PHOTO
    FROM member
    WHERE MEM_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$memberId]);

  $member = $stmt->fetch(PDO::FETCH_ASSOC);

  //防呆：如果資料庫找不到目前Session中的會員ID時，就把剛剛先存入資料夾的新圖片刪除
  if (!$member) {

    if (file_exists($targetPath)) {
      unlink($targetPath); //刪除圖片
    }

    http_response_code(404);

    echo json_encode([
      "success" => false,
      "message" => "找不到會員資料"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //存取在還沒更新前，目前會員的舊頭像
  $oldAvatarPath = $member["MEM_PHOTO"];

  //接著更新資料庫中的頭像資料
  $sql = "
    UPDATE member
    SET
      MEM_PHOTO = ?
    WHERE MEM_ID = ?
  ";

  try {
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
      $newAvatarPath,
      $memberId
    ]);

  } catch (PDOException $e) {
    /*
      因為圖片已經存到 uploads/member/，
      如果資料庫更新失敗時，需把剛上傳的新圖片刪掉。
    */
    if (file_exists($targetPath)) {
      unlink($targetPath);
    }

    http_response_code(500);

    echo json_encode([
      "success" => false,
      "message" => "會員頭像更新失敗，請稍後再試"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //成功更新後，刪除目前會員的舊頭像 
  /*
    當頭像路徑是： uploads/member/xxx.jpg
    才代表是會員之前「自行上傳」的圖片。
    如果是純檔名：e.g. spinix_member_default.png 或 member_01.png
    代表這是放在 public 裡的預設靜態圖片，不能刪除!!!
  */

  if ($oldAvatarPath && str_starts_with($oldAvatarPath, "uploads/member/")) {
    //當舊頭像是會員自行動態上傳的圖片時，就可以刪除掉

    $oldAvatarFilePath = __DIR__ . "/../" . $oldAvatarPath;

    // 確認檔案真的存在後再刪除
    if (file_exists($oldAvatarFilePath)) {
      unlink($oldAvatarFilePath);
    }
  }

  //回傳更新成功結果
  echo json_encode([
    "success" => true,
    "message" => "會員頭像更新成功",
    "photo" => $newAvatarPath
  ], JSON_UNESCAPED_UNICODE);

?>