<?php
  session_start();

  require_once("../common/connect_ckd101g2.php");
  require_once("../common/cors.php");

  header("Content-Type: application/json; charset=utf-8");
  
  $id = $_GET["id"] ?? null; //沒有抓到id的話，就帶入空值，才不會報錯整個程式碼死掉
  $currentMemberId = $_SESSION["MEM_ID"] ?? null;  //沒抓到mem_id的話，帶入null(訪客)

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

    // 找不到文章，或是有人直接在網址列隨便輸入id的情況
    if(!$article){
      echo json_encode(["success" => false, "message" => "找不到這篇文章"], JSON_UNESCAPED_UNICODE);

      exit();
    }

    // 文章已下架：訪客，或是登入者不是發文者本人進來該文章
    // 資料庫撈出來的資料可能是字串，要用(int)轉成數值再做判斷
    if( (int) $article['is_show'] === 0 
    && ( $currentMemberId === null || (int) $currentMemberId !== (int) $article['mem_id']) ){
      
      echo json_encode(["success" => false, "message" => "找不到這篇文章"], JSON_UNESCAPED_UNICODE);

      exit();
    }


    echo json_encode(["success" => true, "data" => $article], JSON_UNESCAPED_UNICODE);

  }catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["success" =>false, "message" => "資料庫查詢失敗"], JSON_UNESCAPED_UNICODE);
    exit();
  }
?>