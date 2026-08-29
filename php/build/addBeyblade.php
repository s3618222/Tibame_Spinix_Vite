<?php
// 後台「陀螺圖庫管理」：新增零件
// 對應前端頁面：beybladeForm.vue（新增模式）
//
// ⚠️ 技術債：目前尚未加上管理員登入驗證，待補上（同論壇管理後台的做法，
// 之後統一處理所有 API 的驗證）

require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php"); // 請自行核對實際檔名與路徑
require_once("../common/funcs.php"); // 圖片上傳網址組合，比照 addComment.php 的做法

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "僅允許 POST 請求"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$name = trim($_POST["name"] ?? "");
$category = $_POST["category"] ?? "";
$attack = (int) ($_POST["attack"] ?? -1);
$defense = (int) ($_POST["defense"] ?? -1);
$stamina = (int) ($_POST["stamina"] ?? -1);
$weight = (int) ($_POST["weight"] ?? -1);
$isShow = (int) ($_POST["is_show"] ?? 0);

// 必填防呆：名稱不能空白
if ($name === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "請填寫零件名稱"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// category 是資料庫 enum('Blade', 'Ratchet', 'Bit')，前端送過來的必須是這三個值之一，
// 不能直接信任前端傳什麼就存什麼（防止前端邏輯出錯或有人繞過前端直接打 API）
if (!in_array($category, ["Blade", "Ratchet", "Bit"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "零件類別不正確"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 四維數值防呆：資料庫沒有限制範圍，但前端滑桿是 0~100，這裡比照前端規則守一次，
// 避免有人繞過前端直接傳負數或超大數字進資料庫
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

// 新增零件一定要有圖片，不像編輯零件可以保留原圖不換
if (!isset($_FILES["pic"]) || $_FILES["pic"]["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "請上傳零件圖片"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

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

// 存進資料庫的路徑格式：uploads/beyblade/xxx.png，對應前端 getBeybladeImageUrl()
// 用 pic.startsWith("uploads/beyblade/") 判斷是不是動態上傳圖片的邏輯
$picPath = "uploads/beyblade/" . $fileName;

try {
    $sql = "
        INSERT INTO beyblade (category, name, attack, defense, stamina, weight, pic, is_show)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$category, $name, $attack, $defense, $stamina, $weight, $picPath, $isShow]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "零件已成功新增",
        "beyblade_id" => $newId
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "新增零件失敗",
        "error" => $e->getMessage() // 除錯用，測試通過後記得移除或改回固定訊息
    ], JSON_UNESCAPED_UNICODE);
}
?>