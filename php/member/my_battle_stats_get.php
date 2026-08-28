<?php
  //會員中心「個人資料」- 取得當前會員的約戰數據統計資料API
  // 1.約戰總場次 2.競技場次 3.競技勝率

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  $memberId = $_SESSION["MEM_ID"] ?? null;

  if (!$memberId) {
    http_response_code(401);

    echo json_encode([
      "success" => false,
      "message" => "請先登入後再查看約戰統計"
    ], JSON_UNESCAPED_UNICODE);

    exit;
  }

  //查詢當前會員約戰統計
  /*
    TOTAL_BATTLES：不管是約戰發起人/參加人都算
    COMPETITIVE_TOTAL：只計算競技模式，且勝者資料已完成回填的場次
    COMPETITVE_WINS： WINNER 0 為發起人獲勝 / 1 為參加人獲勝
  */

    $sql = "
      SELECT
        /* 約戰總場次 */
        COUNT(battle_record.BATTLE_ID) AS TOTAL_BATTLES,

        /* 競技模式總場次：計算競技模式，且已經有 WINNER 結果的場次。 */
        SUM(
          CASE
            WHEN battle_record.BATTLE_MODE = 'COMPETITIVE'
              AND battle_record.WINNER IS NOT NULL
              THEN 1

            ELSE 0
          END
        ) AS COMPETITIVE_TOTAL,


        /* 
          當前會員的競技勝場數
            -會員是發起人：WINNER = 0 → 發起人獲勝
            -會員是參加人：WINNER = 1 → 參加人獲勝
        */
        SUM(
          CASE
            WHEN battle_record.BATTLE_MODE = 'COMPETITIVE'
              AND battle_record.WINNER IS NOT NULL
              AND battle_record.INITIATOR_ID = ?
              AND battle_record.WINNER = 0
              THEN 1

            WHEN battle_record.BATTLE_MODE = 'COMPETITIVE'
              AND battle_record.WINNER IS NOT NULL
              AND battle_record.PARTICIPANT_ID = ?
              AND battle_record.WINNER = 1
              THEN 1

            ELSE 0
          END
        ) AS COMPETITIVE_WINS

      FROM battle_record

      /* 抓目前登入會員所有參與過的約戰；作為發起人或參加人都算 */
      WHERE (
        battle_record.INITIATOR_ID = ?
        OR battle_record.PARTICIPANT_ID = ?
      )

      /* 只統計已確認的歷史約戰 */
      AND battle_record.BATTLE_STATUS = 'CONFIRMED'
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $memberId, // 判斷發起人勝場
      $memberId, // 判斷參加人勝場
      $memberId, // 查會員作為發起人的紀錄
      $memberId  // 查會員作為參加人的紀錄
    ]);

    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    //整理回傳的資料類型
    $totalBattles = (int)($stats["TOTAL_BATTLES"] ?? 0);
    $competitiveTotal = (int)($stats["COMPETITIVE_TOTAL"] ?? 0);
    $competitiveWins = (int)($stats["COMPETITIVE_WINS"] ?? 0);

    //計算競技模式勝率
    if ($competitiveTotal > 0) {

      $winRate = round($competitiveWins / $competitiveTotal * 100) . "%";

    } else {
      // 沒有競技紀錄時，不顯示 (避免顯示0%，看起來像全敗的意思)
      $winRate = null;
    }

    echo json_encode([
      "success" => true,

      "stats" => [
        "totalBattles" => $totalBattles,
        "competitiveTotal" => $competitiveTotal,
        "winRate" => $winRate
      ]

    ], JSON_UNESCAPED_UNICODE);
?>