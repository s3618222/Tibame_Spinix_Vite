<?php
// 後台「論壇管理」：管理員對留言執行 REMOVE（下架）／RESTORE（恢復上架）
// 對應前端頁面：forumManageDetail.vue
//

// 註：恢復上架（RESTORE）不需要填寫原因，與 adminUpdateArticleStatus.php
// 保持對稱——申訴處理面板只有「確認違規」才要求填原因，
// 恢復上架不用，因此不需要額外的歷史紀錄表存放 RESTORE 原因

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("../common/admin_guard.php");

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 管理員 ID，目前先用佔位數字頂著，理由同 adminUpdateArticleStatus.php
$adminId = $_SESSION["admin_id"] ?? 1;

$commentId = (int) ($_POST["msg_id"] ?? 0);
$action = $_POST["action"] ?? "";
$reason = trim($_POST["reason"] ?? "");

if ($commentId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "留言編號無效"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action !== "REMOVE" && $action !== "RESTORE") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "管理處置類型不正確"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 只有 REMOVE 需要必填原因；RESTORE 不用填
if ($action === "REMOVE" && $reason === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "請填寫下架原因"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 先查目前狀態 + 是否有 PENDING 申訴
    $sql = "
        SELECT
            message.is_show,
            EXISTS (
                SELECT 1 FROM appeal_forum
                WHERE appeal_forum.msg_id = message.msg_id
                  AND appeal_forum.af_status = 'PENDING'
            ) AS has_pending_appeal
        FROM message
        WHERE message.msg_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "找不到這則留言"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ((int) $comment["has_pending_appeal"] === 1) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "此留言有待審申訴，請至申訴管理處理"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $currentIsShow = (int) $comment["is_show"];

    if ($action === "REMOVE" && $currentIsShow === 0) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "此留言目前已經是下架狀態"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === "RESTORE" && $currentIsShow === 1) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "此留言目前已經是上架狀態"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 通過驗證，執行更新
    if ($action === "REMOVE") {
        $sql = "
            UPDATE message
            SET is_show = 0, delete_type = 'admin_removed', remove_reason = ?
            WHERE msg_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reason, $commentId]);

        // 通知留言者，留言已被下架
        $sql = "
            SELECT message.mem_id, article.title
            FROM message
            JOIN article ON message.art_id = article.art_id
            WHERE message.msg_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commentId]);
        $commentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $notificationContent = "您在文章「" . $commentInfo["title"] . "」底下的留言已被管理員下架，原因：" . $reason;

        $sql = "INSERT INTO notification (mem_id, content, is_read, create_time) VALUES (?, ?, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commentInfo["mem_id"], $notificationContent]);
    } else {
        $sql = "
            UPDATE message
            SET is_show = 1, delete_type = NULL, remove_reason = NULL
            WHERE msg_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commentId]);

        // 通知留言者，留言已恢復上架
        $sql = "
            SELECT message.mem_id, article.title
            FROM message
            JOIN article ON message.art_id = article.art_id
            WHERE message.msg_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commentId]);
        $commentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $notificationContent = "您在文章「" . $commentInfo["title"] . "」底下的留言已恢復上架";

        $sql = "INSERT INTO notification (mem_id, content, is_read, create_time) VALUES (?, ?, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commentInfo["mem_id"], $notificationContent]);
    }

    echo json_encode([
        "success" => true,
        "message" => $action === "REMOVE" ? "留言已成功下架" : "留言已成功恢復上架"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "處置失敗"
    ], JSON_UNESCAPED_UNICODE);
}
?>