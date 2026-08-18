<?php
  
  if( str_contains($_SERVER["HTTP_HOST"], "127.0.0.1") || str_contains($_SERVER["HTTP_HOST"], "localhost") ){
    // localhost本機端開發
    $db_host = '127.0.0.1';
    $db_port = 8889;
    $db_dbname = 'spinix';

    $db_user = 'root';
    $db_password = 'root';
  }else{
    // remote
    $db_host = '127.0.0.1';               // 資料庫主機(ip)
    $db_port = 3306;                      // 資料庫 port number
    $db_dbname = 'tibamefe_ckd101g2'; // 資料庫名稱 (需更改)

    $db_user = 'tibamefe_since2021';      // 可連線資料庫的帳號
    $db_password = 'vwRBSb.j&K#E';        // 該帳號的密碼
  }
  

  $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_dbname;charset=utf8mb4";

  try {
    $pdo = new PDO($dsn, $db_user, $db_password);
    echo '<p style="color: green;">資料庫連線成功。</p>';
  } catch (PDOException $e) {
    echo '<p style="color: red;">資料庫連線錯誤：' . $e->getMessage() . '。</p>';
    exit();
  }
?>