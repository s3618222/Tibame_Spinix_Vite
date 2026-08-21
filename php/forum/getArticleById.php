<?php
  require_once("../common/connect_ckd101g2.php");
  require_once("../common/cors.php");

  header('Content-Type: application/json');

  $id = $_GET["id"] ?? null; //沒有抓到id的話，就帶入空值，才不會報錯整個程式碼死掉

  try{
    $sql = "
      SELECT 
        article.*, 
        member.MEM_NAME AS author_name,
        member.MEM_PHOTO AS author_photo,
        member.BATTLE_WINS AS author_battle_wins
      FROM article 
      JOIN member ON article.mem_id = member.MEM_ID
      WHERE article.art_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $article]);

  }catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["success" =>false, "message" => "資料庫查詢失敗"]);
    exit();
  }
?>