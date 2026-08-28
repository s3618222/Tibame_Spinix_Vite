<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

$post_id = $_GET['id'] ?? '';

// function getComments($pdo, $params = [],  $includeContact = false) {
//    $sql = "SELECT 
//    `comm_id`,
//    `post_id`,
//    exchange_comment.`mem_id`,
//    member.`mem_name`,
//    member.`MEM_PHOTO`,
//    `content`,
//    DATE(`create_time`) AS `create_time`,
//    `is_show`,
//    `remove_reason`,
//    `is_choose`,
//    `comm_contact` 
//    FROM `exchange_comment` 
//    JOIN member on exchange_comment.`mem_id` = member.`mem_id`  ";

//    $stmt = $pdo->prepare($sql);
//    $stmt->excute($params);
//    return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }

// $rows = getComments($pdo);
// header("Content-Type: application/json; charset=utf-8");
// echo json_encode($rows, JSON_UNESCAPED_UNICODE);


$sql = "SELECT 
   `comm_id`,
   `post_id`,
   exchange_comment.`mem_id`,
   member.`mem_name`,
   member.`MEM_PHOTO`,
   `content`,
   DATE(`create_time`) AS `create_time`,
   `is_show`,
   `remove_reason`,
   `is_choose`,
   `comm_contact` 
   FROM `exchange_comment` 
   JOIN member on exchange_comment.`mem_id` = member.`mem_id`
   WHERE post_id = ?
   ORDER BY `create_time` DESC
   ";

$stmt = $pdo->prepare($sql);
$stmt->execute([$post_id]);
$comments  = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comments) {
   echo json_encode([
      'success' => false,
      'message' => '取得留言失敗'
   ], JSON_UNESCAPED_UNICODE);
}

echo json_encode([
   'success' => true,
   'data' => $comments
], JSON_UNESCAPED_UNICODE);
