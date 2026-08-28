<?php

/**
 * API：依照 city_id 取得該縣市的行政區列表
 * GET /php/location/districts.php?city_id=1
 *
 *
 * Request:
 * city_id: int
 *
 * Response:
 * [
 *   {
 *     "DISTRICT_ID": 1,
 *     "DISTRICT_NAME": "中正區"
 *   }
 * ]
 */


require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");



if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $city_id = $_GET["city_id"] ?? null; //抓取網址請求中的city_id參數，再依指定city_id，找出其底下的行政區

  //若前端請求未附上city_id就回傳錯誤
  if ($city_id === null) {
    http_response_code(400);

    echo json_encode(["error" => "缺少 city_id"], JSON_UNESCAPED_UNICODE);

    exit();
  }

  $sql = "SELECT DISTRICT_ID, DISTRICT_NAME FROM district WHERE CITY_ID = ? ORDER BY DISTRICT_ID ASC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$city_id]); //將city_id變數帶入上方sql指令中的 CITY_ID = ? 中
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit();
}

// 不是 GET 的 request 一律拒絕
http_response_code(403);
echo json_encode(["error" => "denied"], JSON_UNESCAPED_UNICODE);
