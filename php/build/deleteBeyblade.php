<?php
// 後台「陀螺圖庫管理」：真正刪除零件（非軟刪除）
// 對應前端頁面：beybladeForm.vue
//
// 設計決策：跟論壇文章/留言不同，零件是管理員自建維護的素材資料，不是使用者
// 產生的內容，且已確認沒有任何其他資料表以外鍵引用 beyblade_id，因此走真正
// 的 DELETE，不做軟刪除。若之後零件被其他功能（例如收藏、配置紀錄）引用，
// 這個決策需要重新評估。

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php"); // 請自行核對實際檔名與路徑
require_once("../common/admin_guard.php");

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$beybladeId = (int) ($_POST["beyblade_id"] ?? 0);

if ($beybladeId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "零件編號無效"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 先確認零件真的存在，同時拿到 pic 路徑，方便之後決定要不要一併刪除實體圖片檔案
    $sql = "SELECT pic FROM beyblade WHERE beyblade_id = ?";
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

    $sql = "DELETE FROM beyblade WHERE beyblade_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$beybladeId]);

    echo json_encode([
        "success" => true,
        "message" => "零件已成功刪除"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "刪除零件失敗"
    ], JSON_UNESCAPED_UNICODE);
}
?>