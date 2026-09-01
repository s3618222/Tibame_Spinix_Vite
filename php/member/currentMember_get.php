<?php
  /*
  API：登入狀態確認api，用來確認目前是否已登入
  
  功能： 取得當前登入中的會員資訊

  使用情境：只要頁面上的操作需確認「目前是否有會員登入」，或需取得登入者的基本資料，就可串接此api

  METHOD：GET

  Response（目前測試階段）：
  {
    "success": true,
    "memberId": 1
  }

  若尚未登入：
  {
    "success": true,
    "memberId": null
  }
  */

  session_start();
  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //先檢查目前 Session 中是否已有登入會員的 MEM_ID
  $memberId = $_SESSION["MEM_ID"] ?? null;


  // Session中若沒有MEM_ID，即代表目前無會員登入
  if(!$memberId) {
    echo json_encode([
        "success" => true, //指這隻api有沒有成功執行
        "isLoggedIn" => false, //指目前是否有會員登入
        "member" => null
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  /* 
  若有存在MEM_ID，接續利用MEM_ID查詢目前登入會員在跨分頁中會用到的共用基本資料：
    MEM_ID → 會員ID
    MEM_ACCOUNT → 會員帳號（Email）
    MEM_NAME → 會員名稱
    MEM_PHOTO → 會員頭像
  */

  $sql = "
    SELECT
      MEM_ID,
      MEM_ACCOUNT,
      MEM_NAME,
      MEM_PHOTO,
      MEM_GENDER,
      MEM_BIRTH
    FROM member
    WHERE MEM_ID = ?
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([$memberId]);
  $member = $stmt->fetch(PDO::FETCH_ASSOC);

  //防呆：當Session中有MEM_ID，但資料庫找不到對應會員時，視為登入失敗
  if (!$member) {

    echo json_encode([
        "success" => false,
        "isLoggedIn" => false,
        "message" => "會員資料不存在"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //傳出目前登入會員的共用基本資料 
  // 之後串接API後，可以使用 e.g. data.member.name取得登入會員的姓名 )
  echo json_encode([
    "success" => true,
    "isLoggedIn" => true,
    "member" => [
        "id" => $member["MEM_ID"],
        "account" => $member["MEM_ACCOUNT"],
        "name" => $member["MEM_NAME"],
        "photo" => $member["MEM_PHOTO"],
        "gender" => $member["MEM_GENDER"], // MALE / FEMALE
        "birth" => $member["MEM_BIRTH"]    // "Y-m-d"
    ]
  ], JSON_UNESCAPED_UNICODE);
?>