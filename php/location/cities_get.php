<?php

/**
 * API：取得所有縣市資料
 *
 * Method: GET
 * Path: /php/location/cities.php
 *
 * Request:
 * 無需參數
 *
 * Success Response:
 * [
 *   {
 *     "CITY_ID": 1,
 *     "CITY_NAME": "臺北市"
 *   }
 * ]
 *
 * Error:
 * HTTP 403 - 不允許的 Request Method
 */

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");


if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $sql = "SELECT CITY_ID, CITY_NAME FROM city ORDER BY CITY_ID ASC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  header("Content-Type: application/json; charset=utf-8");

  echo json_encode($data, JSON_UNESCAPED_UNICODE);

  exit();
}

// 拒絕get以外的其他request method
http_response_code(403);

$arr = [
  "error" => "denied"
];

echo json_encode($arr, JSON_UNESCAPED_UNICODE);
