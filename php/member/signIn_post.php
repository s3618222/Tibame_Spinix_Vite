<?php
  // 會員登入驗證api

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  // 設定登入API僅能以POST方式串接
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //取得從前端FormData傳入的會員帳號與密碼
  $account = trim($_POST["account"] ?? "");
  $password = $_POST["password"] ?? "";

  //向資料庫查詢前端傳送的帳號資料是否存在於會員資料表中
  $sql = "
    SELECT 
      MEM_ID, 
      MEM_ACCOUNT, 
      MEM_PASSWORD, 
      MEM_NAME, 
      MEM_PHOTO
    FROM member
    WHERE MEM_ACCOUNT = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$account]); //將前面儲存使用者輸入的帳號資訊變數放入sql指令中的"?"處

  $member = $stmt->fetch(PDO::FETCH_ASSOC);

  //驗證使用者輸入的密碼
  $isPasswordCorrect = false; //先直接預設密碼驗證失敗，只有當前一步驟的帳號真的存在時，才進入密碼比對
  if ($member) {
    //用password_verify 將使用者輸入的密碼明碼與資料庫中儲存的hash值進行比對
    $isPasswordCorrect = password_verify($password, $member["MEM_PASSWORD"]); 
  }

  //判斷帳號與密碼是否皆存在、正確；找不到帳號或密碼錯誤時，迴船登入失敗
  if (!$member || !$isPasswordCorrect) {

    echo json_encode([
        "success" => false,
        "message" => "帳號或密碼錯誤"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //通過帳號與密碼驗證後，建立登入Session，保存會員ID，方便後續網站可以記住當前登入者資訊
  session_regenerate_id(true);
  $_SESSION["MEM_ID"] = $member["MEM_ID"];

  
  echo json_encode([
    "success" => true,
    "message" => "登入成功",
    "memberName" => $member["MEM_NAME"],
    "memberPhoto" => $member["MEM_PHOTO"]
  ], JSON_UNESCAPED_UNICODE);

?>