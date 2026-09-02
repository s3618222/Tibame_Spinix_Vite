<?php
// 查詢當前會員論壇功能權限 API
// 提供讓前端在會員準備要發文/留言前進行檢查

session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("./forum_access_check.php");

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "僅允許 GET 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$memberId = $_SESSION["MEM_ID"] ?? null;

if (!$memberId) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "請先登入會員"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $forumAccess = checkForumAccess($pdo, $memberId);

    echo json_encode([
        "success" => true,
        "allowed" => $forumAccess["allowed"],
        "status" => $forumAccess["status"],
        "suspendUntil" => $forumAccess["suspendUntil"]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "取得論壇功能權限失敗"
    ], JSON_UNESCAPED_UNICODE);
}
?>