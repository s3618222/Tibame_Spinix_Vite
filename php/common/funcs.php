<?php
function getBaseUrl() {
  $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
   $host = $_SERVER['HTTP_HOST']; // 例如 localhost:8888 或 tibamef2e.com
  return $protocol . $host;
}

function getUploadBaseUrl() {
  $host = $_SERVER['HTTP_HOST'];

  if (strpos($host, 'localhost') !== false) {
      // 本機環境
      return getBaseUrl() . '/Spinix/php/uploads/';
  } else {
      // 正式站環境
      return getBaseUrl() . '/ckd101/g2/php/uploads/';
  }
}


// 讀取 .env 檔案、設定環境變數 (Gemini API key)
function read_env() {

    $lines = file(
        __DIR__ . "/../../.env",
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    foreach ($lines as $line) {

        // 跳過註解
        if (str_starts_with(trim($line), "#")) {
            continue;
        }

        // 只切第一個 =
        [$name, $value] = explode("=", $line, 2);

        putenv(
            trim($name) . "=" . trim($value)
        );
    }
}

// 載入 .env
read_env();
