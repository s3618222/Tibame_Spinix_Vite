<?php
// forum_appeal_detail_get.php
// 我的申訴詳情頁：取得單筆論壇申訴詳情資料
// 對應前端：myAppealDetail.vue 的論壇分支

session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

header("Content-Type: application/json; charset=utf-8");

$memberId = $_SESSION["MEM_ID"] ?? null;

if (!$memberId) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "請先登入後再查看申訴詳情"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$appealId = (int) ($_GET["appeal_id"] ?? 0);

if ($appealId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "申訴編號無效"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT
        appeal_forum.af_id,
        appeal_forum.art_id,
        appeal_forum.msg_id,
        appeal_forum.af_content,
        appeal_forum.af_status,
        appeal_forum.create_time,
        appeal_forum.responded_at,
        appeal_forum.af_evidence,
        appeal_forum.responded_text,
        appeal_forum.respondent_mem_id,
        complainant.MEM_NAME AS complainant_name,
        respondent.MEM_NAME AS respondent_name
    FROM appeal_forum
    JOIN member AS complainant ON appeal_forum.complainant_mem_id = complainant.MEM_ID
    JOIN member AS respondent ON appeal_forum.respondent_mem_id = respondent.MEM_ID
    WHERE appeal_forum.af_id = ?
      AND appeal_forum.complainant_mem_id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$appealId, $memberId]);
$appeal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appeal) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "找不到這筆論壇申訴資料"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($appeal["art_id"]) {
    $sql = "SELECT title FROM article WHERE art_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$appeal["art_id"]]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    $title = $target ? $target["title"] : "（原文已不存在）";
} else {
    $sql = "SELECT content FROM message WHERE msg_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$appeal["msg_id"]]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($target) {
        $plainContent = strip_tags($target["content"]);
        $title = mb_strlen($plainContent) > 15
            ? mb_substr($plainContent, 0, 15) . "..."
            : $plainContent;
    } else {
        $title = "（原留言已不存在）";
    }
}

$images = [];

if (!empty($appeal["af_evidence"])) {
    $decodedImages = json_decode($appeal["af_evidence"], true);
    if (is_array($decodedImages)) {
        $images = $decodedImages;
    }
}

echo json_encode([
    "success" => true,
    "appeal" => [
        "appealId" => (int) $appeal["af_id"],
        "type" => $appeal["art_id"] ? "論壇文章" : "論壇留言",
        "battleTitle" => $title,
        "targetId" => (int) $appeal["respondent_mem_id"],
        "reporterName" => $appeal["complainant_name"],
        "targetName" => $appeal["respondent_name"],
        "content" => $appeal["af_content"],
        "status" => $appeal["af_status"],
        "createdAt" => $appeal["create_time"],
        "images" => $images,
        "result" => $appeal["responded_text"],
        "resultDate" => $appeal["responded_at"]
    ]
], JSON_UNESCAPED_UNICODE);
?>
