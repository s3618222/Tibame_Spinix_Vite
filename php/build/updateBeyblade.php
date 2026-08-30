<?php
// 後台「陀螺圖庫管理」：編輯零件
// 對應前端頁面：beybladeForm.vue（編輯模式）

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("../common/admin_guard.php");
require_once("../common/funcs.php");

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
$name = trim($_POST["name"] ?? "");
$category = $_POST["category"] ?? "";
$attack = (int) ($_POST["attack"] ?? -1);
$defense = (int) ($_POST["defense"] ?? -1);
$stamina = (int) ($_POST["stamina"] ?? -1);
$weight = (int) ($_POST["weight"] ?? -1);
$isShow = (int) ($_POST["is_show"] ?? 0);

if ($beybladeId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "零件編號無效"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($name === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "請填寫零件名稱"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($category, ["Blade", "Ratchet", "Bit"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "零件類別不正確"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stats = ["attack" => $attack, "defense" => $defense, "stamina" => $stamina, "weight" => $weight];
foreach ($stats as $label => $value) {
    if ($value < 0 || $value > 100) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "數值必須介於 0 到 100 之間"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

try {
    // 先查這筆零件目前的 pic，確認零件真的存在，也順便拿到「沒換圖時該保留的舊路徑」
    $sql = "SELECT pic FROM beyblade WHERE beyblade_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$beybladeId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "找不到這個零件"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 預設沿用舊圖片路徑，只有真的收到新檔案才覆蓋
    $picPath = $existing["pic"];

    if (isset($_FILES["pic"]) && $_FILES["pic"]["error"] === UPLOAD_ERR_OK) {
        $fileName = time() . "_" . $_FILES["pic"]["name"];
        $targetPath = "../uploads/beyblade/" . $fileName;

        if (!move_uploaded_file($_FILES["pic"]["tmp_name"], $targetPath)) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "圖片上傳失敗"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $picPath = "uploads/beyblade/" . $fileName;
    }

    $sql = "
        UPDATE beyblade
        SET category = ?, name = ?, attack = ?, defense = ?, stamina = ?, weight = ?, pic = ?, is_show = ?
        WHERE beyblade_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category, $name, $attack, $defense, $stamina, $weight, $picPath, $isShow, $beybladeId]);

    echo json_encode([
        "success" => true,
        "message" => "零件已成功更新"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "更新零件失敗"
    ], JSON_UNESCAPED_UNICODE);
}
?>