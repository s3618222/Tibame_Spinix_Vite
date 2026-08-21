<?php
  require_once("../common/connect_ckd101g2.php");
  require_once("../common/cors.php");

  header('Content-Type: application/json');

  try {
    $sql = "SELECT * FROM article WHERE is_show = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $articles]);

  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "資料庫查詢失敗"]);
    exit();
  }
?>