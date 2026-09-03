<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
session_start();

$memId = $_SESSION["MEM_ID"];

$sql = "SELECT 
   exchange_comment.`comm_id`,
   exchange_comment.`post_id`,
   exchange_comment.`mem_id`,
   member.`MEM_NAME` AS `name`,
   member.`MEM_PHOTO` AS `headshot`,
   exchange_comment.`content`,
   DATE(exchange_comment.`create_time`) AS `create_time`,
   exchange_comment.`is_show` AS `comment_is_show`,
   exchange_comment.`remove_reason`,
   exchange_comment.`is_choose`,
   exchange_comment.`comm_contact`,
   exchange_post.`title`,
   exchange_post.`status` AS `post_status`,
   exchange_post.`is_show` AS `post_is_show`,
   exchange_post.`type`,
   exchange_post.`condition`,
   exchange_post.`post_pic1`,
   exchange_post.`CITY_ID` AS `city_id`,
   city.`CITY_NAME` AS `city`,
   exchange_post.`DISTRICT_ID` AS `district_id`,
   district.`DISTRICT_NAME` AS `district`,
   DATE(exchange_post.`create_time`) AS `post_create_time`,
   poster.`MEM_NAME` AS `poster_name`,
   poster.`MEM_PHOTO` AS `poster_photo`
   FROM `exchange_comment`
   JOIN `member` on exchange_comment.`mem_id` = member.`mem_id`
   JOIN `exchange_post` on exchange_comment.`post_id` = exchange_post.`post_id`
   JOIN `member` AS `poster` on exchange_post.`mem_id` = poster.`mem_id`
   JOIN `city` on exchange_post.`CITY_ID` = city.`CITY_ID`
   JOIN `district` on exchange_post.`DISTRICT_ID` = district.`DISTRICT_ID`
   WHERE exchange_comment.`mem_id` = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$memId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);


function FormatExchangRow(array $row): array {
   $boolFieles = ['comment_is_show', 'post_is_show', 'is_choose'];
   $stringFields = ['post_id', 'mem_id'];

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

   return $row;
}

$comments = array_map('FormatExchangRow', $comments);

echo json_encode([
   'success' => true,
   'data' => $comments
], JSON_UNESCAPED_UNICODE);
