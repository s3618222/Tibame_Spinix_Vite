<?php
// 後台「論壇管理」：管理員對文章執行 REMOVE（下架）／RESTORE（恢復上架）
// 對應前端頁面：forumManageDetail.vue


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

// 管理員 ID，目前先用佔位數字頂著。
// ⚠️ 這裡不對應 member 表的 MEM_ID，因為管理員資料表是另一張獨立的表，
// 目前欄位名稱未知，待同學確認後才能正確記錄「是哪個管理員操作」。
$adminId = $_SESSION["admin_id"] ?? 1;

$articleId = (int) ($_POST["art_id"] ?? 0);
$action = $_POST["action"] ?? "";
$reason = trim($_POST["reason"] ?? "");

if ($articleId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "文章編號無效"
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
    // 不管 REMOVE 還是 RESTORE，只要有待審申訴，一律擋下，
    // 引導管理員去申訴管理處理（論壇管理與申訴管理權責切開的核心規則）
    $sql = "
        SELECT
            article.is_show,
            EXISTS (
                SELECT 1 FROM appeal_forum
                WHERE appeal_forum.art_id = article.art_id
                  AND appeal_forum.af_status = 'PENDING'
            ) AS has_pending_appeal
        FROM article
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

    if ((int) $article["has_pending_appeal"] === 1) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "此文章有待審申訴，請至申訴管理處理"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $currentIsShow = (int) $article["is_show"];

    if ($action === "REMOVE" && $currentIsShow === 0) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "此文章目前已經是下架狀態"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === "RESTORE" && $currentIsShow === 1) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "此文章目前已經是上架狀態"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 通過驗證，執行更新
    if ($action === "REMOVE") {
        $sql = "
            UPDATE article
            SET is_show = 0, delete_type = 'admin_removed', remove_reason = ?
            WHERE art_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reason, $articleId]);

        // 通知作者文章已被下架
        $sql = "SELECT mem_id, title FROM article WHERE art_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$articleId]);
        $articleInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $notificationContent = "您的文章「" . $articleInfo["title"] . "」已被管理員下架，原因：" . $reason;

        $sql = "INSERT INTO notification (mem_id, content, is_read, create_time) VALUES (?, ?, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$articleInfo["mem_id"], $notificationContent]);

    } else {
        // RESTORE 時，把 delete_type/remove_reason 一併清空，
        // 避免舊的下架原因殘留、誤導成「這篇還帶著下架紀錄」
        $sql = "
            UPDATE article
            SET is_show = 1, delete_type = NULL, remove_reason = NULL
            WHERE art_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$articleId]);

        // 通知作者文章已恢復上架
        $sql = "SELECT mem_id, title FROM article WHERE art_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$articleId]);
        $articleInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $notificationContent = "您的文章「" . $articleInfo["title"] . "」已恢復上架";

        $sql = "INSERT INTO notification (mem_id, content, is_read, create_time) VALUES (?, ?, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$articleInfo["mem_id"], $notificationContent]);
    }

    echo json_encode([
        "success" => true,
        "message" => $action === "REMOVE" ? "文章已成功下架" : "文章已成功恢復上架"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "處置失敗"
    ], JSON_UNESCAPED_UNICODE);
}
?>