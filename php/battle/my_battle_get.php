<?php
  // 「我的約戰」中，取得當前會員發起或參加的約戰紀錄API

  session_start();

  require_once("../common/cors.php");
  require_once("../common/connect_ckd101g2.php");

  // 先同步已截止的 MATCHING 約戰狀態
  require_once("./battle_status_sync.php");

  header("Content-Type: application/json; charset=utf-8");

  //從Session中，取得當前登入會員的MEM_ID
  $memberId = $_SESSION["MEM_ID"] ?? null;

  //如果Session中沒有MEM_ID，即尚未登入，不往下執行
  if (!$memberId) {
    echo json_encode([
      "success" => false,
      "message" => "請先登入後再查看我的約戰"
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  //接著準備sql的查詢指令，需查詢目前登入會員的所有相關約戰資料
  $sql = "
    SELECT
      battle_record.BATTLE_ID,
      battle_record.BATTLE_TITLE,
      battle_record.BATTLE_IMG,
      battle_record.BATTLE_MODE,
      battle_record.BATTLE_TARGET,
      battle_record.BATTLE_DATE,
      battle_record.BATTLE_STATUS,

      battle_record.INITIATOR_ID,
      battle_record.PARTICIPANT_ID,

      battle_record.BATTLE_LOC,
      battle_record.INI_CONTACT,
      battle_record.PAR_CONTACT,

      battle_record.WINNER,

      city.CITY_NAME,
      district.DISTRICT_NAME,

      initiator.MEM_NAME AS INITIATOR_NAME,
      initiator.MEM_PHOTO AS INITIATOR_PHOTO,

      participant.MEM_NAME AS PARTICIPANT_NAME,
      participant.MEM_PHOTO AS PARTICIPANT_PHOTO,

      /*
        判斷目前會員在這場約戰中的角色：
        - INITIATOR：我發起的約戰
        - PARTICIPANT：我參加的約戰
      */
      CASE
        WHEN battle_record.INITIATOR_ID = ?
          THEN 'INITIATOR'

        WHEN battle_record.PARTICIPANT_ID = ?
          THEN 'PARTICIPANT'
      END AS MY_ROLE,

      /*
        接著找出對手的會員ID：
        - 當前會員為發起人，對手就是參加人
        - 當前會員為參加人，對手就是發起人
      */
      CASE
        WHEN battle_record.INITIATOR_ID = ?
          THEN battle_record.PARTICIPANT_ID

        WHEN battle_record.PARTICIPANT_ID = ?
          THEN battle_record.INITIATOR_ID
      END AS OPPONENT_ID,

      /*
        找出對手名稱、頭像、聯絡方式
      */
      CASE
        WHEN battle_record.INITIATOR_ID = ?
          THEN participant.MEM_NAME

        WHEN battle_record.PARTICIPANT_ID = ?
          THEN initiator.MEM_NAME
      END AS OPPONENT_NAME,

      CASE
        WHEN battle_record.INITIATOR_ID = ?
          THEN participant.MEM_PHOTO

        WHEN battle_record.PARTICIPANT_ID = ?
          THEN initiator.MEM_PHOTO
      END AS OPPONENT_PHOTO,

      CASE
        WHEN battle_record.INITIATOR_ID = ?
          THEN battle_record.PAR_CONTACT

        WHEN battle_record.PARTICIPANT_ID = ?
          THEN battle_record.INI_CONTACT
      END AS OPPONENT_CONTACT,

      /*
        目前會員完的約戰評價資訊。

        如果會員是發起人：
        要評的是參加人，所以看 TO_PAR_COMMENTED_AT。

        如果會員是參加人：
        要評的是發起人，所以看 TO_INI_COMMENTED_AT。
      */
      CASE
        WHEN battle_record.INITIATOR_ID = ?
          THEN battle_record.TO_PAR_COMMENTED_AT

        WHEN battle_record.PARTICIPANT_ID = ?
          THEN battle_record.TO_INI_COMMENTED_AT
      END AS MY_REVIEWED_AT,

      /*
        判斷目前會員是否已經評價過對方：
        1 = 已評價
        0 = 尚未評價
      */
      CASE
        WHEN battle_record.INITIATOR_ID = ?
          AND battle_record.TO_PAR_COMMENTED_AT IS NOT NULL
            THEN 1

        WHEN battle_record.PARTICIPANT_ID = ?
          AND battle_record.TO_INI_COMMENTED_AT IS NOT NULL
            THEN 1

        ELSE 0
      END AS HAS_REVIEWED
    
    FROM battle_record

    /* 取得約戰縣市與行政區名稱 */
    JOIN city
      ON battle_record.CITY_ID = city.CITY_ID
    
    JOIN district
      ON battle_record.DISTRICT_ID = district.DISTRICT_ID

    /* 取得約戰發起人與參加人會員資料 */
    JOIN member AS initiator
      ON battle_record.INITIATOR_ID = initiator.MEM_ID
    
    /*
      使用 LEFT JOIN，因為 MATCHING 狀態的約戰可能還沒有參加人，
      PARTICIPANT_ID 此時會是 NULL。
    */
    LEFT JOIN member AS participant
      ON battle_record.PARTICIPANT_ID = participant.MEM_ID

    WHERE (
      battle_record.INITIATOR_ID = ?
      OR battle_record.PARTICIPANT_ID = ?
    )

    /*
    被下架的紀錄不顯示
    */
    AND battle_record.IS_SHOW = 1
    
    /*
    「我的約戰」回傳資料排序順位：依使用者當前最需注意的事情決定優先順位

    1.PENDING + 我是發起人
      → 接受/拒絕對方申請加入，屬於需立即決策的操作。

    2. CONFIRMED + 約戰時間尚未到
      → 已配對成功、即將進行的約戰。
      → 優先提醒使用者近期行程，越接近現在的約戰要排越前面。 (ASC)

    3. CONFIRMED + 約戰時間已過，但有尚未完成的操作 (屬約戰結束後，需補完成的待辦）
      → 尚未評價對方；
      → 或競技模式下，我是發起人但尚未回填勝者。

    4. 等待中的約戰
      → PENDING + 我是參加人：已提出申請，等待發起人確認。
      → MATCHING：我發起的邀約仍在等待其他會員加入。

    5. CONFIRMED + 我的相關操作都已完成
      → 無待辦需求，視為歷史紀錄。 (DSEC)

    6. FAILED / CANCELLED
      → 已過期或已取消，放在最後作為歷史紀錄。 (DSEC)

    日期排序方向：
    - 即將發生 / 等待中的約戰：BATTLE_DATE ASC
      → 最近要發生的排前面。
    - 已經發生的待辦 / 已完成 / 已失效紀錄：BATTLE_DATE DESC
      → 最近發生的紀錄排前面。
  */
    ORDER BY
      /* 第一層：先決定每筆約戰所屬的UX優先順位 */
      CASE
        /* 1. 發起人需要處理參加申請 */
        WHEN battle_record.BATTLE_STATUS = 'PENDING'
          AND MY_ROLE = 'INITIATOR'
          THEN 1

        /* 2. 已確認，而且約戰尚未開始 */
        WHEN battle_record.BATTLE_STATUS = 'CONFIRMED'
          AND battle_record.BATTLE_DATE > NOW()
          THEN 2

        /* 3. 約戰時間已過，但目前會員仍有待辦 (尚未評價/未回填勝者) */
        WHEN battle_record.BATTLE_STATUS = 'CONFIRMED'
          AND battle_record.BATTLE_DATE <= NOW()
          AND (
            HAS_REVIEWED = 0

            OR (
              battle_record.BATTLE_MODE = 'COMPETITIVE'
              AND MY_ROLE = 'INITIATOR'
              AND battle_record.WINNER IS NULL
            )
          )
          THEN 3


        /* 4. 目前處於等待狀態：
          - 參加人已送出申請，等待發起人確認
          - 發起人的邀約仍等待其他會員加入
        */
        WHEN battle_record.BATTLE_STATUS = 'PENDING'
          AND MY_ROLE = 'PARTICIPANT'
          THEN 4

        WHEN battle_record.BATTLE_STATUS = 'MATCHING'
          THEN 4


        /* 5. 已完成所有相關操作的 CONFIRMED 約戰 */
        WHEN battle_record.BATTLE_STATUS = 'CONFIRMED'
          THEN 5


        /* 6. 已過期 / 已取消 */
        WHEN battle_record.BATTLE_STATUS IN ('FAILED', 'CANCELLED')
          THEN 6

        ELSE 7

      END ASC,

      /* 第二層：尚未發生、仍在等待的約戰使用 ASC；越接近現在的日期越優先，例如：明天 → 後天 → 下週 */
      CASE
        WHEN battle_record.BATTLE_STATUS = 'PENDING'
          AND MY_ROLE = 'INITIATOR'
          THEN battle_record.BATTLE_DATE

        WHEN battle_record.BATTLE_STATUS = 'CONFIRMED'
          AND battle_record.BATTLE_DATE > NOW()
          THEN battle_record.BATTLE_DATE

        WHEN battle_record.BATTLE_STATUS = 'PENDING'
          AND MY_ROLE = 'PARTICIPANT'
          THEN battle_record.BATTLE_DATE

        WHEN battle_record.BATTLE_STATUS = 'MATCHING'
          THEN battle_record.BATTLE_DATE
      END ASC,


      /* 第三層：已發生的紀錄使用 DESC；讓最近完成 / 尚待補處理的紀錄排在較前 */
      CASE
        WHEN battle_record.BATTLE_STATUS = 'CONFIRMED'
          AND battle_record.BATTLE_DATE <= NOW()
          THEN battle_record.BATTLE_DATE

        WHEN battle_record.BATTLE_STATUS IN ('FAILED', 'CANCELLED')
          THEN battle_record.BATTLE_DATE
      END DESC,


      /* 當日期相同時：新建立的 BATTLE_ID 較大，因此放前面。 */
      battle_record.BATTLE_ID DESC
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $memberId,
    $memberId,

    $memberId,
    $memberId,

    $memberId,
    $memberId,

    $memberId,
    $memberId,

    $memberId,
    $memberId,

    $memberId,
    $memberId,

    $memberId,
    $memberId,

    $memberId,
    $memberId
  ]);

  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  //將從資料庫取回的資料 $data 轉為JSON回傳給前端
  echo json_encode (
    $data,
    JSON_UNESCAPED_UNICODE //讓中文可正常顯示
  );



?>