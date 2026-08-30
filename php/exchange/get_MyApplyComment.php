<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
session_start();

$memId = $_SESSION["MEM_ID"];

$sql = "SELECT 
`comm_id`,
exchange_comment.`post_id`,
exchange_comment.`mem_id`,
member.`MEM_NAME` AS `name`,
member.`MEM_PHOTO` AS `headshot`,
`content`,
DATE(`create_time`) AS `create_time`,
`is_show`,
`remove_reason`,
`is_choose`,
`comm_contact` FROM `exchange_comment` 
JOIN member on exchange_comment.`mem_id` = member.`mem_id`
WHERE exchange_comment.`mem_id` = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$memId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);


function FormatExchangRow(array $row): array {
   $boolFieles = ['is_show', 'is_choose'];
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
