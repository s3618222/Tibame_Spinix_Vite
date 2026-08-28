<?php
  // 後台「論壇管理」：取得單篇文章完整內容 + 底下所有留言（各自獨立的待審申訴判斷）
  // 對應前端頁面：forumManageDetail.vue
  //
  // ⚠️ 技術債：目前尚未加上管理員登入/角色驗證，待同學的後台登入驗證 API 完成後補上

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php"); // 請自行核對實際檔名與路徑

  header("Content-Type: application/json; charset=utf-8");

  // 從網址參數取得要查詢的文章 id
  $articleId = (int) ($_GET["art_id"] ?? 0);

  if ($articleId <= 0) {
      http_response_code(400);
      echo json_encode([
          "success" => false,
          "message" => "文章編號無效"
      ], JSON_UNESCAPED_UNICODE);
      exit;
  }

  try {
    // 第一段：查文章本身
    // has_pending_appeal 只比對這篇文章本身的申訴，不用跨到留言層級
    $sql = "
        SELECT
            article.art_id,
            article.title,
            article.category,
            article.content,
            article.create_time,
            article.is_show,
            article.delete_type,
            article.remove_reason,
            member.MEM_NAME AS author_name,
            member.MEM_PHOTO AS author_photo,
            EXISTS (
                SELECT 1 FROM appeal_forum
                WHERE appeal_forum.art_id = article.art_id
                  AND appeal_forum.af_status = 'PENDING'
            ) AS has_pending_appeal,
            (
                SELECT af_content FROM appeal_forum
                WHERE appeal_forum.art_id = article.art_id
                AND appeal_forum.af_status = 'PENDING'
                ORDER BY create_time DESC
                LIMIT 1
            ) AS report_reason
        FROM article
        JOIN member ON article.mem_id = member.MEM_ID
        WHERE article.art_id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "找不到這篇文章"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 第二段：查這篇文章底下所有留言
    // 每則留言各自獨立判斷 has_pending_appeal，比對 appeal_forum.msg_id
    $sql = "
        SELECT
            message.msg_id,
            message.content,
            message.pic,
            message.create_time,
            message.is_show,
            message.delete_type,
            message.remove_reason,
            member.MEM_NAME AS commenter_name,
            member.MEM_PHOTO AS commenter_photo,
            EXISTS (
                SELECT 1 FROM appeal_forum
                WHERE appeal_forum.msg_id = message.msg_id
                  AND appeal_forum.af_status = 'PENDING'
            ) AS has_pending_appeal
        FROM message
        JOIN member ON message.mem_id = member.MEM_ID
        WHERE message.art_id = ?
        ORDER BY message.create_time ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$articleId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => [
            "article" => $article,
            "comments" => $comments
        ]
    ], JSON_UNESCAPED_UNICODE);

  } catch (PDOException $e) {
      http_response_code(500);
      echo json_encode([
          "success" => false,
          "message" => "取得文章詳情失敗",
          "error" => $e->getMessage() // 除錯用，測試通過後記得移除或改回固定訊息
      ], JSON_UNESCAPED_UNICODE);
  }
?>