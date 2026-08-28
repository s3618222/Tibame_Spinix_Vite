<?php
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

function getComments($pdo, $params = [],  $includeContact = false) {
   $sql = "SELECT 
   `comm_id`, 
   `post_id`, 
   `mem_id`,
   `content`,
   `create_time`,
   `is_show`,
   `remove_reason`,
   `is_choose`";

   if ($includeContact){
      $sql .= " ,`comm_contact` ";
   }

   $sql .= " FROM `exchange_comment`";
}
