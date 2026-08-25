<?php
session_start();

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

$sql = "SELECT `post_id`, `type`, `title`, `description`, `want_item`, `condition`, `is_exchanged`, `is_show`, `create_time`, `post_pic1`, `post_pic2`, `post_pic3`, `post_pic4`, `post_pic5`, `remove_reason`, `mem_id`, `comm_id`, `CITY_ID`, `DISTRICT_ID`, `post_contact` FROM `exchange_post` WHERE 1";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($data, JSON_UNESCAPED_UNICODE);
