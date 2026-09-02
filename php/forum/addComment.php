<?php
  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");
  require_once("../common/funcs.php");
  require_once("./forum_access_check.php");

  header("Content-Type: application/json; charset=utf-8");

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);
      echo json_encode(["success" => false, "message" => "僅允許 POST 請求"], JSON_UNESCAPED_UNICODE);
      exit;
  }

  $memberId = $_SESSION["MEM_ID"] ?? null;

  if (!$memberId) {
      echo json_encode(["success" => false, "message" => "尚未登入"], JSON_UNESCAPED_UNICODE);
      exit;
  }

  $articleId = $_POST["art_id"] ?? null;
  $content = $_POST["content"] ?? null;
  $pic = null;

  // 阻止留言內容空白或是輸入空白鍵的資料寫入
  if($content === null || trim($content) === ""){
    echo json_encode(["success" => false, "message" => "留言內容不可為空"], JSON_UNESCAPED_UNICODE);
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

  // 圖片上傳選填，$_FILES["image"]["error"] 是 PHP 自動記錄「這次上傳有沒有出錯」的狀態碼，UPLOAD_ERR_OK 是 PHP 內建常數，代表「完全沒問題」
  if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
    $fileName = time() . "_" . $_FILES["image"]["name"];
    $targetPath = "../uploads/messages/" . $fileName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
      $pic = getUploadBaseUrl() . "messages/" . $fileName;
    }
  }

  try{
    // 寫入留言
    $insertSql = "INSERT INTO message (mem_id, art_id, content, create_time, pic, is_show) 
    VALUES (?, ?, ?, NOW(), ?, 1)";
    $stmt = $pdo ->prepare($insertSql);
    $stmt->execute([$memberId, $articleId, $content, $pic]);

    $newMsgId = $pdo->lastInsertId(); //拿到新增那筆的 msg_id

    // 通知文章作者有新留言
    $sql = "SELECT mem_id, title FROM article WHERE art_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$articleId]);
    $articleInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // 排除自己留言給自己文章的情況
    if ($articleInfo && (int) $articleInfo["mem_id"] !== (int) $memberId) {
      $notificationContent = "您的文章「" . $articleInfo["title"] . "」有新留言";

      $sql = "INSERT INTO notification (mem_id, content, is_read, create_time) VALUES (?, ?, 0, NOW())";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$articleInfo["mem_id"], $notificationContent]);
    }

    //反查完整資料
    $selectSql = "
    SELECT 
        message.*,
        member.MEM_NAME AS commenter_name,
        member.MEM_PHOTO AS commenter_photo,
        member.BATTLE_WINS AS commenter_battle_wins
    FROM message
    JOIN member ON message.mem_id = member.MEM_ID
    WHERE message.msg_id = ?";

    $stmt2 = $pdo->prepare($selectSql);
    $stmt2->execute([$newMsgId]);
    $newComment = $stmt2->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $newComment], JSON_UNESCAPED_UNICODE);
  }catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "資料庫寫入失敗"], JSON_UNESCAPED_UNICODE);
    exit();
  }