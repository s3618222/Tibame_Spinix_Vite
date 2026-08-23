<?php
  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  header("Content-Type: application/json; charset=utf-8");

  //查詢約戰發起人的會員資訊(姓名、頭像、約戰總場次、歷史評分、評價、競技模式勝率)
  //查發起人獲得的最新5筆評價

  $hostId = $_GET["host_id"] ?? null; //取得前端request網址上附帶的發起人id資訊

  //如果前端沒附上阿發起人id資訊，就回報錯誤資訊，且不繼續往下執行
  if (!$hostId) {
    http_response_code(400);

    echo json_encode([
        "message" => "缺少 host_id"
    ], JSON_UNESCAPED_UNICODE);

    exit();
  }


  //第一部分：查詢發起人的會員基本資料與約戰統計資料
  /* 
    會員基本資料 + 歷史約戰統計
    1. 姓名、頭像
    2. 已確認的約戰總場次
    3. 收到的平均評分
    4. 參與競技模式總場次
    5. 競技模式勝場數 (計算競技勝率)
   */
  $sqlHost = "
  SELECT
    member.MEM_ID,
    member.MEM_NAME,
    member.MEM_PHOTO,

    /* 計算此發起人參加過、狀態為CONFIRMED的約戰總場次 */
    COUNT(battle_record.BATTLE_ID) AS TOTAL_BATTLES,

    /* 
      計算該會員收到的平均評分：
      - 當會員是發起人，評分需查 TO_INI_STARS
      - 當會員是參加人，評分改查 TO_PAR_STARS
    */
    AVG(
        CASE
            WHEN battle_record.INITIATOR_ID = member.MEM_ID
                  THEN battle_record.TO_INI_STARS

            WHEN battle_record.PARTICIPANT_ID = member.MEM_ID
                  THEN battle_record.TO_PAR_STARS
            END
      ) AS AVERAGE_RATING,

    /*
      計算競技模式總場次。
      休閒模式不加入計算，或WINNER尚未回填的競技場次也不計入。
    */
    SUM(
        CASE
            WHEN battle_record.BATTLE_MODE = 'COMPETITIVE'
                  AND battle_record.WINNER IS NOT NULL
                THEN 1

            ELSE 0
        END
    ) AS COMPETITIVE_TOTAL,

    /*
      計算該會員的競技勝場：
      -當會員是發起人，WINNER = 0 → 代表會員獲勝
      -當會員是參加人，WINNER = 1 → 代表會員獲勝
    */
    SUM(
        CASE
            WHEN battle_record.BATTLE_MODE = 'COMPETITIVE'
              AND battle_record.WINNER IS NOT NULL
              AND battle_record.INITIATOR_ID = member.MEM_ID
              AND battle_record.WINNER = 0
                THEN 1

            WHEN battle_record.BATTLE_MODE = 'COMPETITIVE'
                  AND battle_record.WINNER IS NOT NULL
                  AND battle_record.PARTICIPANT_ID = member.MEM_ID
                  AND battle_record.WINNER = 1
                THEN 1

            ELSE 0
        END
    ) AS COMPETITIVE_WINS

    FROM member

    /*
      將會員與約戰紀錄串接。
      會員無論是發起人或參加人，都算參與過該場約戰。

      BATTLE_STATUS 放在 JOIN 裡，
      是因為這樣即使該會員沒有任何 CONFIRMED 紀錄，
      LEFT JOIN 仍然可以保留會員本身的資料。
    */
    LEFT JOIN battle_record
        ON (
            member.MEM_ID = battle_record.INITIATOR_ID
            OR member.MEM_ID = battle_record.PARTICIPANT_ID
        )
        AND battle_record.BATTLE_STATUS = 'CONFIRMED'

    /* 只查詢網址傳入的指定會員 */
    WHERE member.MEM_ID = ?

    /* 將同一會員的多筆 battle_record 聚合成一筆統計結果 */
    GROUP BY
        member.MEM_ID,
        member.MEM_NAME,
        member.MEM_PHOTO
";

  $stmtHost = $pdo->prepare($sqlHost);
  $stmtHost->execute([$hostId]); //將發起人id資訊，帶入上方sql指令裡的?處
  $host = $stmtHost->fetch(PDO::FETCH_ASSOC);

  //取得資料庫回傳的發起人資料後，計算其競技模式勝率
  if ($host) {
    $competitiveTotal = (int)$host["COMPETITIVE_TOTAL"]; //參加過的競技模式總場次
    $competitiveWins = (int)$host["COMPETITIVE_WINS"]; //累積的競技模式總勝場

    if ($competitiveTotal > 0) {
        $host["WIN_RATE"] = round($competitiveWins / $competitiveTotal * 100); //計算勝率，四捨五入至小數點第 1 位
    } else {
        $host["WIN_RATE"] = null;
    }
  } else {
    //如果沒有該位會員資訊
    http_response_code(404);
    echo json_encode([
        "message" => "找不到會員資料"
    ], JSON_UNESCAPED_UNICODE);

    exit();
  }

  /*
    第二部分：取得發起人收到的最新5筆約戰評價資訊
    1. 約戰 ID
    2. 約戰標題
    3. 收到的星等
    4. 收到的評論
    5. 評論時間
    6. 評論者名稱
    該會員可能是約戰中的發起人或參加人，所以要用 CASE 判斷實際要讀哪一組評論欄位。
  */
  
  $sqlReviews = "
    SELECT
      battle_record.BATTLE_ID,
      battle_record.BATTLE_TITLE,

      /*
        發起人收到的評分：
        作為發起人時 → TO_INI_STARS
        做為參加人時 → TO_PAR_STARS
      */

      CASE
          WHEN battle_record.INITIATOR_ID = ?
              THEN battle_record.TO_INI_STARS

          WHEN battle_record.PARTICIPANT_ID = ?
              THEN battle_record.TO_PAR_STARS
      END AS RATING,

      /*
        該會員收到的評論內容
      */
      CASE
          WHEN battle_record.INITIATOR_ID = ?
              THEN battle_record.TO_INI_COMMENT

          WHEN battle_record.PARTICIPANT_ID = ?
              THEN battle_record.TO_PAR_COMMENT
      END AS COMMENT,

      /*
        取得評論的建立時間，
        讓評論可以依照新到舊排序
      */
      CASE
          WHEN battle_record.INITIATOR_ID = ?
              THEN battle_record.TO_INI_COMMENTED_AT

          WHEN battle_record.PARTICIPANT_ID = ?
              THEN battle_record.TO_PAR_COMMENTED_AT
      END AS COMMENTED_AT,

      /* 留下評論的會員名稱 */
      reviewer.MEM_NAME AS REVIEWER_NAME

    FROM battle_record

      /*
        找出「另一方會員」作為評論者。
        被查詢會員是發起人時 → 評論者就是參加人
        是參加人時 → 評論者就是發起人
      */

    JOIN member AS reviewer
        ON reviewer.MEM_ID =
            CASE
                WHEN battle_record.INITIATOR_ID = ?
                    THEN battle_record.PARTICIPANT_ID

                WHEN battle_record.PARTICIPANT_ID = ?
                    THEN battle_record.INITIATOR_ID
            END

      /* 只看已確認的歷史約戰 */
      WHERE battle_record.BATTLE_STATUS = 'CONFIRMED'

      /* 指定會員必須參與過這場約戰 */
      AND (
          battle_record.INITIATOR_ID = ?
          OR battle_record.PARTICIPANT_ID = ?
      )

      /*
        只留下真正已經有評論時間的紀錄，
        避免尚未評價的場次佔掉 5 筆名額。
      */
      AND (
          CASE
              WHEN battle_record.INITIATOR_ID = ?
                  THEN battle_record.TO_INI_COMMENTED_AT

              WHEN battle_record.PARTICIPANT_ID = ?
                  THEN battle_record.TO_PAR_COMMENTED_AT
          END
      ) IS NOT NULL

      /* 最新評論放最前面 */
      ORDER BY COMMENTED_AT DESC

      /* 燈箱最多顯示最新 5 筆 */
      LIMIT 5
  ";

  $stmtReviews = $pdo->prepare($sqlReviews);

  $stmtReviews->execute([
      $hostId,
      $hostId,
      $hostId,
      $hostId,
      $hostId,
      $hostId,
      $hostId,
      $hostId,
      $hostId,
      $hostId,
      $hostId,
      $hostId
  ]);

  $reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);
  
  //將「會員摘要」與「最新評論」組成同一份 JSON
  $response = [
    "host" => $host,
    "reviews" => $reviews
  ];

  echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE
  );

?>