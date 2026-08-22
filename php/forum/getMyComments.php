<?php
  session_start();
  
  require_once("../common/connect_ckd101g2.php");
  require_once("../common/cors.php");
  
  header("Content-Type: application/json; charset=utf-8");

  $memberId = $_SESSION["MEM_ID"] ?? null;  // 讀出登入時存進去的 MEM_ID

  if (!$memberId) {
      // 沒登入，不該讓他查到留言
      echo json_encode([
          "success" => false,
          "message" => "尚未登入"
      ], JSON_UNESCAPED_UNICODE);
      exit;
  }

  try {
    $sql = "
    SELECT 
      message.*,
      article.title
    FROM message 
    JOIN article ON article.art_id = message.art_id
    WHERE message.mem_id = ?
    ORDER BY message.create_time DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);
    $myArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $myArticles], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "資料庫查詢失敗"]);
    exit();
  }
?>