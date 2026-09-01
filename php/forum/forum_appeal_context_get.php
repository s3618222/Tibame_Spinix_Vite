<?php
// forum_appeal_context_get.php
// 論壇申訴：取得被檢舉文章/留言的初始資料（被檢舉人姓名、標題/內容），供申訴表單預填
// 對應前端：complaintForm.vue 的 initForumSource()

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

header("Content-Type: application/json; charset=utf-8");

session_start();

$currentMemberId = $_SESSION["MEM_ID"] ?? null;

if (!$currentMemberId) {
  http_response_code(401);
  echo json_encode([
    "success" => false,
    "message" => "尚未登入"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$artId = (int) ($_GET["art_id"] ?? 0);
$msgId = (int) ($_GET["msg_id"] ?? 0);

// 兩個都沒有，或兩個都有，都算不合法的請求（只能檢舉文章或留言其中一種）
if (($artId <= 0 && $msgId <= 0) || ($artId > 0 && $msgId > 0)) {
  http_response_code(400);
  echo json_encode([
    "success" => false,
    "message" => "檢舉目標無效"
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  if ($artId > 0) {
    // 檢舉文章：被檢舉人 = 文章作者
    $sql = "
        SELECT
            article.title,
            article.mem_id AS respondent_mem_id,
            member.MEM_NAME AS respondent_name
        FROM article
        JOIN member ON article.mem_id = member.MEM_ID
        WHERE article.art_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$artId]);
  } else {
    // 檢舉留言：被檢舉人 = 留言者
    $sql = "
        SELECT
            message.content AS title,
            message.mem_id AS respondent_mem_id,
            member.MEM_NAME AS respondent_name
        FROM message
        JOIN member ON message.mem_id = member.MEM_ID
        WHERE message.msg_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$msgId]);
  }

  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$result) {
    http_response_code(404);
    echo json_encode([
      "success" => false,
      "message" => "找不到檢舉目標"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if ($msgId > 0) {
    // 留言沒有獨立標題欄位，用留言內容當標題，剝除 HTML 標籤後截斷成 15 字 + 省略號
    //strip_tags()是 PHP 內建的函式，剝掉 HTML 標籤取純文字
    $plainContent = strip_tags($result["title"]);

    if (mb_strlen($plainContent) > 15) {
      $title = mb_substr($plainContent, 0, 15) . "...";
    } else {
      $title = $plainContent;
    }
  } else {
    // 文章標題本身已有長度限制（100 字），不需要額外截斷
    $title = $result["title"];
  }

  // 不能檢舉自己
  if ((int) $result["respondent_mem_id"] === (int) $currentMemberId) {
    http_response_code(400);
    echo json_encode([
      "success" => false,
      "message" => "無法檢舉自己發表的內容"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  echo json_encode([
    "success" => true,
    "title" => $title,
    "respondentName" => $result["respondent_name"],
    "respondentMemId" => $result["respondent_mem_id"]
  ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
      "success" => false,
      "message" => "取得申訴資料失敗"
  ], JSON_UNESCAPED_UNICODE);
}
?>