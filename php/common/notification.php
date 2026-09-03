<?php
// 共用會員通知模組
// 用途:統一處理「寫入 notification 表」的邏輯,
// 讓各個情境(新增留言、留言被選中、交換完成...等)都呼叫同一支函式,
// 避免各自複製貼上 SQL,以後要調整欄位或格式只需要改這裡。

/**
 * 建立一筆會員通知
 *
 * @param PDO    $pdo     資料庫連線物件
 * @param int    $memId   要通知的會員 ID(收件者)
 * @param string $content 通知內容文字
 * @return int            新增的 notification_id
 * @throws PDOException   若寫入失敗則向外拋出例外,由呼叫端決定如何處理
 */
function createNotification(PDO $pdo, int $memId, string $content): int {
   $sql = "INSERT INTO notification (mem_id, content, create_time) VALUES (?, ?, NOW())";
   $stmt = $pdo->prepare($sql);
   $stmt->execute([$memId, $content]);

   return (int) $pdo->lastInsertId();
}
