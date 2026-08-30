<?php
// 後台「論壇管理」：取得所有文章列表(含上架/已下架/使用者自刪三種狀態、待審申訴提醒)
// 對應前端頁面：forumManage.vue

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("../common/admin_guard.php");

header("Content-Type: application/json; charset=utf-8");

try {
    // 這支是查詢類 API，一次撈全部文章、不做 SQL 分頁，分頁邏輯交給前端
    // 既有的 filteredArticles/paginatedArticles computed 處理。

    // WHERE 條件刻意不過濾 delete_type，三種狀態(上架中/已下架/使用者已刪除)
    // 都要出現在列表裡，讓管理員有全知視角，這是討論後的明確決定。

    // has_pending_appeal：只要「這篇文章本身」或「這篇文章底下任何一則留言」
    // 存在至少一筆 PENDING 申訴，就回傳 true。用 OR 把「檢舉文章」跟「檢舉
    // 該文章底下的留言」兩種情況都涵蓋進來，避免列表頁漏掉留言層級的申訴。
    $sql = "
        SELECT
            article.art_id,
            article.category,
            article.title,
            article.create_time,
            article.is_show,
            article.delete_type,
            member.MEM_NAME AS author_name,
            EXISTS (
                SELECT 1 FROM appeal_forum
                WHERE appeal_forum.af_status = 'PENDING'
                  AND (
                    appeal_forum.art_id = article.art_id
                    OR appeal_forum.msg_id IN (
                        SELECT msg_id FROM message WHERE message.art_id = article.art_id
                    )
                  )
            ) AS has_pending_appeal
        FROM article
        JOIN member ON article.mem_id = member.MEM_ID
        ORDER BY article.create_time DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $articles
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "取得文章列表失敗"
    ], JSON_UNESCAPED_UNICODE);
}
?>