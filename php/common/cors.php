<?php
  // 允許的域名列表
  $allowed_origins = [
    "http://127.0.0.1:5173",
    "http://localhost:5173"
  ];

  // 抓到請求的來源網域
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

  if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
  }
?>