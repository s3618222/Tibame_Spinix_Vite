<?php
  //串接AI，將前端傳來的約戰設定條件，提供給AI生成文案，再將取得的文案回傳給前端

  require_once("../common/cors.php");
  require_once("../common/funcs.php");

  header("Content-Type: application/json; charset=UTF-8");


  //限制REQUEST METHOD必須為POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
      http_response_code(405);

      echo json_encode([
          "success" => false,
          "message" => "請使用 POST 請求"
      ], JSON_UNESCAPED_UNICODE);

      exit();
  }

  //接收前端回傳的約戰條件資料
  $mode = $_POST["mode"] ?? "";
  $level = $_POST["level"] ?? "";
  $target = $_POST["target"] ?? "";

  //若回傳的資料不完整，就不往下執行
  if ( $mode === "" || $level === "" || $target === "") {
      http_response_code(400);

      echo json_encode([
          "success" => false,
          "message" => "缺少生成文案所需的對戰條件"
      ], JSON_UNESCAPED_UNICODE);

      exit();
  }


  //Gemini API設定
  $apiKey = getenv("GEMINI_KEY");

  $model = "gemini-3.6-flash";

  $apiUrl =
    "https://generativelanguage.googleapis.com/v1beta/models/"
    . $model
    . ":generateContent?key="
    . $apiKey;

  //準備提供給AI的文案生成prompt
  $prompt = "
    你是 Spinix 戰鬥陀螺交流平台的約戰文案助手。

    請根據玩家提供的對戰條件，
    產生一段自然、有趣、適合發布在玩家社群中的邀約說明。

    對戰條件：
    - 對戰模式：{$mode}
    - 玩家程度：{$level}
    - 適合對象：{$target}

    撰寫規則：
    1. 使用繁體中文。
    2. 語氣自然、友善、有活力。
    3. 不要太正式，盡量有趣一點，但也不要過度浮誇。
    4. 不要自行加入日期、時間、地點或聯絡方式。
    5. 不要編造額外規則、獎品或活動內容。
    6. 文案控制在約 30～60 個中文字。
    7. 只輸出邀約說明本身，不需要標題或其他解釋。
  ";

  //建立要發送給Gemini API的請求資料
  $requestData = ["contents" => [
        ["parts" => [
            ["text" => $prompt]
          ]
        ]
      ]
  ];

  //使用curl 呼叫 Gemini API
  /*
    curl_init()    → 建立連線
    curl_setopt... → 設定請求
    curl_exec()    → 正式送出
  */
  $ch = curl_init($apiUrl); //建立與Gemini API的連線物件

  curl_setopt_array($ch, [ //設定連線規則

      CURLOPT_POST => true, //用POST請求

      CURLOPT_RETURNTRANSFER => true, //設定讓API回傳的內容可以被存進變數中

      CURLOPT_HTTPHEADER => [
          "Content-Type: application/json"
      ],

      //要傳送給Gemini的資料內容
      CURLOPT_POSTFIELDS => json_encode(
          $requestData,
          JSON_UNESCAPED_UNICODE
      ),

      // 本機 MAMP 測試用
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => false
  ]);


  // 執行請求
  $response = curl_exec($ch);

  // HTTP 狀態碼
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  // cURL 錯誤
  $curlError = curl_error($ch);

  curl_close($ch);

  //檢查curl是否連線成功
  if ($response === false) {
      http_response_code(500);

      echo json_encode([
          "success" => false,
          "message" => "無法連線至文案服務",
          "error" => $curlError
      ], JSON_UNESCAPED_UNICODE);

      exit();
  }


  //將 Gemini 回傳 JSON 轉成 PHP 陣列
  $result = json_decode($response, true);

  // Gemini API 本身回傳錯誤時
  if ($httpCode < 200 || $httpCode >= 300) {
      http_response_code(500);

      echo json_encode([
          "success" => false,
          "message" => "文案產生失敗",
          "error" => $result
      ], JSON_UNESCAPED_UNICODE);

      exit();
  }

  //從回傳結果中取得文字內容
  $generatedText = $result["candidates"][0]["content"]["parts"][0]["text"] ?? "";

  //確認真的有取得文案
  if ($generatedText === "") {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "沒有取得文案內容"
    ], JSON_UNESCAPED_UNICODE);

    exit();
  }

  //將文案回傳給前端
  echo json_encode([
    "success" => true,
    "description" => trim($generatedText)
  ], JSON_UNESCAPED_UNICODE);

?>