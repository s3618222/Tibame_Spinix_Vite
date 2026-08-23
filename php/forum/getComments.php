<?php
  session_start();

  require_once("../common/connect_ckd101g2.php");
  require_once("../common/cors.php");

  header("Content-Type: application/json; charset=utf-8");
  
  $currentMemberId = $_SESSION["MEM_ID"] ?? null;

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
      WHERE art_id = ?
      AND (message.is_show = 1 OR message.mem_id = ?)
      ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $currentMemberId]);  // 記得加中括號
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $comments], JSON_UNESCAPED_UNICODE);
  }catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "資料庫連接失敗"], JSON_UNESCAPED_UNICODE);
    exit();
  }
?>