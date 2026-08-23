<?php
  require_once("../common/connect_ckd101g2.php");
  require_once("../common/cors.php");

  header("Content-Type: application/json; charset=utf-8");
  
  try {
    $sql = "
    SELECT 
      article.*,
      member.MEM_NAME AS author_name,
      member.MEM_PHOTO AS author_photo,
      COUNT(message.msg_id) AS comment_count,
      MAX(message.create_time) AS last_comment_time 
    FROM article 
    JOIN member ON article.mem_id = member.MEM_ID
    LEFT JOIN message ON message.art_id = article.art_id
    WHERE article.is_show = 1
    GROUP BY article.art_id, member.MEM_NAME, member.MEM_PHOTO
    ORDER BY article.create_time DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $articles], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "資料庫查詢失敗"], JSON_UNESCAPED_UNICODE);
    exit();
  }
?>