<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

session_start();
$post_id = $_GET['id'] ?? '';

$memberId = $_SESSION["MEM_ID"] ?? null;
$isAdmin = isset($_SESSION["ADMIN_ID"]);


$sql = "SELECT 
   exchange_comment.`comm_id`,
   exchange_comment.`post_id`,
   exchange_comment.`mem_id`,
   member.`mem_name` AS `name`,
   member.`MEM_PHOTO` AS `headshot`,
   `content`,
   DATE(exchange_comment.`create_time`) AS `create_time`,
   exchange_comment.`is_show`,
   exchange_comment.`remove_reason`,
   `is_choose`,
   `comm_contact` AS `contact`,
   exchange_post.`status` AS `post_status`
   FROM `exchange_comment` 
   JOIN member on exchange_comment.`mem_id` = member.`mem_id`
   JOIN exchange_post on exchange_comment.`post_id` = exchange_post.`post_id`
   WHERE exchange_comment.`post_id` = ?
   ";

$stmt = $pdo->prepare($sql);
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

function FormatExchangRow(array $row, ?string $memberId, bool $isAdmin): array {
   $boolFieles = ['is_show', 'is_choose'];
   $stringFields = ['post_id', 'mem_id', 'comm_id'];

   foreach ($boolFieles as $field) {
      if (array_key_exists($field, $row)) {
         $row[$field] = $row[$field] === null ? null : (bool)$row[$field];
      }
   }

   foreach ($stringFields as $field) {
      if (array_key_exists($field, $row)) {
         $row[$field] = $row[$field] === null ? null : (string) $row[$field];
      }
   }

   $isAgreed = !empty($row['is_choose']) && ($row['post_status'] === 'exchanging');
   if (!$isAgreed) {
      unset($row['contact']);
   }

   unset($row['post_status']);

   if ($row['is_show'] === false) {
      $isCommentOwner = $memberId !== null && $row['mem_id'] === $memberId;
      if (!$isCommentOwner && !$isAdmin) {
         $row['content'] = '該留言已違反社群公約，已被下架';
         $row['remove_reason'] = null;
         unset($row['contact']);
      }
   }

   return $row;
}

$comments = array_map(function ($row) use ($memberId, $isAdmin) {
   return FormatExchangRow($row, $memberId, $isAdmin);
}, $comments);

echo json_encode([
   'success' => true,
   'data' => $comments
], JSON_UNESCAPED_UNICODE);