<?php
session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

function getExchange($pdo, $postId = null, $params = [], $includeContact = false) {
   $sql = "SELECT 
   `post_id`,
   exchange_post.`mem_id`,
   member.`mem_name`,
   member.`mem_photo`,
   `type`, 
   `title`,
   `description`, 
   `want_item`, 
   `condition`, 
   `status`, 
   exchange_post.`CITY_ID` AS city_id, 
   city.`CITY_NAME` AS city,
   exchange_post.`DISTRICT_ID` AS district_id,
   district.`DISTRICT_NAME` AS district ,
   `is_show`,
   DATE(`create_time`) AS `create_time`,
   `post_pic1`,
   `post_pic2`, 
   `post_pic3`, 
   `post_pic4`,
   `post_pic5`, 
   `remove_reason`,
   exchange_post.`comm_id`";

   if ($includeContact) {
      $sql .= " , `post_contact` ";
   }

   $sql .= "FROM `exchange_post` 
   join `member` on exchange_post.`mem_id` = member.`mem_id`
   JOIN `city` on exchange_post.`CITY_ID` = city.`CITY_ID`
   JOIN `district` on exchange_post.`DISTRICT_ID` = district.`DISTRICT_ID`";

   if (!empty($postId)) {
      $sql .= "  WHERE post_id = ?";
      $params[] = $postId;
   }

   $stmt = $pdo->prepare($sql);
   $stmt->execute($params);
   return array_map('FormatExchangRow', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

function FormatExchangRow(array $row): array {
   $boolFieles = ['is_show'];
   $stringFields = ['post_id'];

   // 轉布林值
   foreach ($boolFieles as $field) {
      if (array_key_exists($field, $row)) {
         $row[$field] = $row[$field] === null ? null : (bool)$row[$field];
      }
   }

   // 轉字串
   foreach ($stringFields as $field) {
      if (array_key_exists($field, $row)) {
         $row[$field] = $row[$field] === null ? null : (string) $row[$field];
      }
   }

   if (empty($row['remove_reason'])) {
      unset($row['remove_reson']);
   }
   return $row;
}


if (!defined('IS_INCLUDED_AS_LIBRARY')) {
   $rows = getExchange($pdo);
   header("Content-Type: application/json; charset=utf-8");
   echo json_encode($rows, JSON_UNESCAPED_UNICODE);
}
