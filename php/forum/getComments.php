<?php
  require_once("../common/connect_ckd101g2.php");
  require_once("../common/cors.php");

  header('Content-Type: application/json');

  $id = $_GET["id"] ?? null;

  try{
    $sql = "
      SELECT 
        message.*, 
        member.MEM_NAME AS commenter_name,
        member.MEM_PHOTO AS commenter_photo,
        member.BATTLE_WINS AS commenter_battle_wins
      FROM message 
      JOIN member ON message.mem_id = member.MEM_ID
      WHERE art_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);  // 記得加中括號
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $comments]);
  }catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "資料庫連接失敗"]);
    exit();
  }
?>