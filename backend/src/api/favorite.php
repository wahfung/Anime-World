<?php
/**
 * 收藏 API - Favorite API
 * 动漫世界
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// 检查是否登录
if (!Auth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => '请先登录'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不支持的请求方法'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 获取请求数据
$input = json_decode(file_get_contents('php://input'), true);
$animeId = isset($input['anime_id']) ? intval($input['anime_id']) : 0;

if ($animeId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '无效的动漫ID'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $userId = $_SESSION['user_id'];

    // 检查动漫是否存在
    $stmt = $conn->prepare("SELECT id FROM animes WHERE id = ?");
    $stmt->execute([$animeId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '动漫不存在'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 检查是否已收藏
    $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND anime_id = ?");
    $stmt->execute([$userId, $animeId]);
    $favorite = $stmt->fetch();

    if ($favorite) {
        // 取消收藏
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND anime_id = ?");
        $stmt->execute([$userId, $animeId]);

        echo json_encode([
            'success' => true,
            'favorited' => false,
            'message' => '已取消收藏'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // 添加收藏
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, anime_id) VALUES (?, ?)");
        $stmt->execute([$userId, $animeId]);

        echo json_encode([
            'success' => true,
            'favorited' => true,
            'message' => '收藏成功'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    error_log("[Favorite API Error] " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '操作失败，请稍后重试'], JSON_UNESCAPED_UNICODE);
}
