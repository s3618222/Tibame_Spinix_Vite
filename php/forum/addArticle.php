<?php
  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");
  require_once("./forum_access_check.php");

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

  $title = $_POST["title"] ?? null;
  $category = $_POST["category"] ?? null;
  $content = $_POST["content"] ?? null;

  if(
    $title === null || trim($title) === "" ||
    $category === null || trim($category) === "" ||
    $content === null || trim($content) === ""
  ){
    echo json_encode(["success" => false, "message" => "標題、分類、內容皆為必填"], JSON_UNESCAPED_UNICODE);
    exit();
  }
  // 檢查論壇功能是否被限制使用
  $access = checkForumAccess($pdo, $memberId);

  if (!$access["allowed"]) {
      http_response_code(403);
      $message = $access["status"] === "PERMA-RESTRICT"
          ? "您的帳號已被永久限制使用論壇功能"
          : "您的論壇功能目前暫時受限，將於 " . $access["suspendUntil"] . " 恢復";

      echo json_encode([
          "success" => false,
          "message" => $message
      ], JSON_UNESCAPED_UNICODE);
      exit;
  }
  
  if (mb_strlen($title, "UTF-8") > 100) {
    echo json_encode(["success" => false, "message" => "標題不可超過 100 字"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  try{
    $sql = "
      INSERT INTO article (title, category, content, mem_id, create_time, is_show)
      VALUES (?, ?, ?, ?, NOW(), 1)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $category, $content, $memberId]);

    $newArtId = $pdo->lastInsertId();

    echo json_encode(["success" => true, "data" => ["art_id" => $newArtId]], JSON_UNESCAPED_UNICODE);
  }catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "資料庫寫入失敗"], JSON_UNESCAPED_UNICODE);
    exit();
  }
?>