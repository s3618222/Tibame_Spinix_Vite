<?php
session_start();
require_once("../common/cors.php");
require_once("../common/connect_ckd101g2.php");
require_once("../common/admin_guard.php");

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$target = $data['target'] ?? ''; // 被下架的目標
$id = (int)($data['id'] ?? 0); // 被下架目標的id
$action = $data['action'] ?? ''; // 是否顯示
$reason = $data['remove_reason'] ?? null; // 下架原因

if (!in_array($target, ['post', 'comment'])) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '未提供有效的目標'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if ($id <= 0) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '未提供有效的id'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if (!in_array($action, ['show', 'hide'])) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '未提供有效的參數'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if ($action === 'hide' && empty(trim($reason ?? ''))) {
   http_response_code(400);
   echo json_encode([
      'success' => false,
      'message' => '請輸入下架原因'
   ], JSON_UNESCAPED_UNICODE);
   exit;
}

if ($target === 'post' && $action === 'hide') {
   $check = "SELECT status FROM `exchange_post` WHERE post_id = ?";
   $checkStmt = $pdo->prepare($check);
   $checkStmt->execute([$id]);
   $post = $checkStmt->fetch(PDO::FETCH_ASSOC);

   if (!$post) {
      http_response_code(400);
      echo json_encode([
         'success' => false,
         'message' => '找不到此交換文章'
      ], JSON_UNESCAPED_UNICODE);
      exit;
   }
   if (in_array($post['status'], ['exchanging', 'pending'])) {
      http_response_code(400);
      echo json_encode([
         'success' => false,
         'message' => '此文章正在交換流程中，無法下架'
      ],JSON_UNESCAPED_UNICODE);
      exit;
   }
}

$tableMap = [
   'post' => ['table' => 'exchange_post', 'idColumn' => 'post_id'],
   'comment' => ['table' => 'exchange_comment', 'idColumn' => 'comm_id']
];

$table = $tableMap[$target]['table'];
$idColumn = $tableMap[$target]['idColumn'];

$isShow = ($action === 'show') ? 1 : 0;
$remove_reason = ($action === 'show') ? null : $reason;

$sql = "UPDATE `{$table}` SET `is_show`= ?,`remove_reason`=? WHERE `{$idColumn}` = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$isShow, $remove_reason, $id]);
echo json_encode([
   'success' => true,
   'message' => $action === 'show' ? '已恢復上架' : '已下架'
], JSON_UNESCAPED_UNICODE);
