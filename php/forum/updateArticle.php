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

  $articleId = $_POST["art_id"] ?? null;

  if ($articleId === null) {
      echo json_encode(["success" => false, "message" => "缺少文章 id"], JSON_UNESCAPED_UNICODE);
      exit();
  }

  try{
    $checkSql = "SELECT mem_id FROM article WHERE art_id = ?";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    // 文章存在驗證
    if (!$article) {
        echo json_encode(["success" => false, "message" => "找不到這篇文章"], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // 身分驗證
    if ((int) $article["mem_id"] !== (int) $memberId) {
        echo json_encode(["success" => false, "message" => "您沒有權限編輯這篇文章"], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $title = $_POST["title"] ?? null;
    $category = $_POST["category"] ?? null;
    $content = $_POST["content"] ?? null;

    //修改內容表單驗證，要在sql update指令之前
    if(
      $title === null || trim($title) === "" ||
      $category === null || trim($category) === "" ||
      $content === null || trim($content) === ""
    ){
      echo json_encode(["success" => false, "message" => "標題、分類、內容皆為必填"], JSON_UNESCAPED_UNICODE);
      exit();
    }

    if (mb_strlen($title, "UTF-8") > 100) {
      echo json_encode(["success" => false, "message" => "標題不可超過 100 字"], JSON_UNESCAPED_UNICODE);
      exit;
    }

    // 修改內容update指令
    $updateSql = "
      UPDATE article
      SET title = ?, category = ?, content = ?
      WHERE art_id = ?
    ";
    $stmt2 = $pdo->prepare($updateSql);
    $stmt2->execute([$title, $category, $content, $articleId]);

    echo json_encode(["success" => true, "message" => "編輯成功"], JSON_UNESCAPED_UNICODE);

  }catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "資料庫查詢失敗"], JSON_UNESCAPED_UNICODE);
    exit();
  }

  


?>