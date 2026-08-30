<?php
// 後台「陀螺圖庫管理」：取得所有零件列表（含上架狀態）
// 對應前端頁面：beybladeManage.vue
//
// ⚠️ 技術債：目前尚未加上管理員登入驗證，待補上（同論壇管理後台的做法）

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php"); // 請自行核對實際檔名與路徑

header("Content-Type: application/json; charset=utf-8");

try {
    // 一次撈全部零件，不做 SQL 分頁，交給前端既有的篩選/分頁 computed 處理
    $sql = "
        SELECT
            beyblade_id,
            category,
            name,
            attack,
            defense,
            stamina,
            weight,
            pic,
            is_show
        FROM beyblade
        ORDER BY beyblade_id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $parts
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "取得零件列表失敗",
        "error" => $e->getMessage() // 除錯用，測試通過後記得移除或改回固定訊息
    ], JSON_UNESCAPED_UNICODE);
}