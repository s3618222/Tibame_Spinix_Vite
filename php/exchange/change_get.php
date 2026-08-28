<?php
session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");


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
`post_contact`,
exchange_post.`city_id`, 
city.`city_name` AS city,
exchange_post.`district_id`,
district.`district_name` as district ,
`is_show`,
DATE(`create_time`) AS `create_time`,
`post_pic1`,
`post_pic2`, 
`post_pic3`, 
`post_pic4`,
`post_pic5`, 
`remove_reason`,
`comm_id`
FROM `exchange_post` 
join `member` on exchange_post.`mem_id` = member.`mem_id`
JOIN `city` on exchange_post.`CITY_ID` = city.`city_id`
JOIN `district` on exchange_post.`DISTRICT_ID` = district.`DISTRICT_ID`";

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
   return $row;
}

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = array_map('FormatExchangRow', $stmt->fetchAll(PDO::FETCH_ASSOC));
header("Content-Type: application/json; charset=utf-8");
echo json_encode($data, JSON_UNESCAPED_UNICODE);
