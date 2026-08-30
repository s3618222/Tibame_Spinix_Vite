<?php
// 後台「陀螺圖庫管理」：取得單筆零件資料
// 對應前端頁面：beybladeForm.vue（編輯模式，依 id 抓資料填入表單）
//
// ⚠️ 技術債：目前尚未加上管理員登入驗證，待補上

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");

header("Content-Type: application/json; charset=utf-8");

$beybladeId = (int) ($_GET["beyblade_id"] ?? 0);

if ($beybladeId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "零件編號無效"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
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
        WHERE beyblade_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$beybladeId]);
    $part = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$part) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "找不到這個零件"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $part
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "取得零件資料失敗",
        "error" => $e->getMessage() // 除錯用，測試通過後記得移除或改回固定訊息
    ], JSON_UNESCAPED_UNICODE);
}
?>