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

  $messageId = $_POST["msg_id"] ?? null;

  if ($messageId === null) {
      echo json_encode(["success" => false, "message" => "缺少留言 id"], JSON_UNESCAPED_UNICODE);
      exit();
  }

  try {
    $checkSql = "SELECT mem_id FROM message WHERE msg_id = ?";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([$messageId]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        echo json_encode(["success" => false, "message" => "找不到這則留言"], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ((int) $message["mem_id"] !== (int) $memberId) {
        echo json_encode(["success" => false, "message" => "您沒有權限刪除這則留言"], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $updateSql = "
      UPDATE message
      SET is_show = 0, delete_type = 'self_deleted'
      WHERE msg_id = ? AND is_show = 1
    ";
    $stmt2 = $pdo->prepare($updateSql);
    $stmt2->execute([$messageId]);

    // 防止被管理員下架的文章再由使用者端重複刪除，修改資料庫欄位
    // rowCount()是計算這次 UPDATE／DELETE 實際影響了幾筆資料列，0代表 WHERE 條件沒有配到任何東西
    if($stmt2->rowCount() === 0){
      echo json_encode(["success" => false, "message" => "這則留言已經是下架狀態，無法重複刪除"], JSON_UNESCAPED_UNICODE);
      exit();
    }

    echo json_encode(["success" => true, "message" => "刪除成功"], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {
      http_response_code(500);
      echo json_encode(["success" => false, "message" => "資料庫查詢失敗"], JSON_UNESCAPED_UNICODE);
      exit();
  }
?>