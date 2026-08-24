<?php
  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(["success" => false, "message" => "僅允許 POST 請求"], JSON_UNESCAPED_UNICODE);
      exit();
  }

  $memberId = $_SESSION["MEM_ID"] ?? null;

  if (!$memberId) {
      echo json_encode(["success" => false, "message" => "尚未登入"], JSON_UNESCAPED_UNICODE);
      exit();
  }

  if(!isset($_FILES["file"])){
    echo json_encode(["error" => "未收到檔案"]);
    exit();
  }

  $fileName = time() . "_" . $_FILES["file"]["name"];  // 改掉原始檔名
  $targetPath = "../uploads/articles/" . $fileName;

  //判斷檔案有沒有上傳成功
  //第一個參數：來源路徑；第二個參數：目標路徑
  if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetPath)){
    $imageUrl = "http://localhost:8888/Spinix/php/uploads/articles/" . $fileName;
    echo json_encode(["location" => $imageUrl]);
  }else{
    echo json_encode(["error" => "檔案上傳失敗"]);
  }
?>