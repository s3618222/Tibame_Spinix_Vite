<?php
// 與前端 src/assets/js/utils/phpBaseUrl.js 對應的後端版本
// 判斷邏輯必須跟前端完全一致，否則前端呼叫 API 用的網址，
// 跟後端組出來存進資料庫、回傳給 TinyMCE 的圖片網址會對不上
$phpBaseUrl =
  in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1'])
    ? "http://localhost:8888/Spinix/php"
    : "/ckd101/g2/php";
?>