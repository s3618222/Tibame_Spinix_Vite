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
