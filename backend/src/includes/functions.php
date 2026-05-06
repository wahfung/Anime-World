<?php
/**
 * 通用函数库
 * 动漫网站 - Common Functions
 */

/**
 * 安全输出 HTML
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 生成 CSRF Token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 验证 CSRF Token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 重定向
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * 设置 Flash 消息
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * 获取并清除 Flash 消息
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * 格式化日期
 */
function formatDate($date, $format = 'Y-m-d H:i') {
    return date($format, strtotime($date));
}

/**
 * 截断文本
 */
function truncate($text, $length = 100) {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/**
 * 生成评分星星 HTML
 */
function renderStars($rating) {
    $rating = (float)$rating;
    $fullStars = (int)floor($rating / 2);
    $halfStar = (fmod($rating, 2) >= 0.5) ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;

    $html = '';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star text-yellow-400"></i>';
    }
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt text-yellow-400"></i>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star text-yellow-400"></i>';
    }
    return $html;
}

/**
 * 获取动漫状态中文名
 */
function getStatusName($status) {
    $statusMap = [
        'ongoing' => '连载中',
        'completed' => '已完结',
        'upcoming' => '即将上映'
    ];
    return $statusMap[$status] ?? $status;
}

/**
 * 获取状态标签颜色
 */
function getStatusColor($status) {
    $colorMap = [
        'ongoing' => 'bg-green-100 text-green-800',
        'completed' => 'bg-blue-100 text-blue-800',
        'upcoming' => 'bg-yellow-100 text-yellow-800'
    ];
    return $colorMap[$status] ?? 'bg-gray-100 text-gray-800';
}

/**
 * 分类图标映射
 */
function getCategoryIcon($icon) {
    $iconMap = [
        'fire' => 'fa-fire',
        'heart' => 'fa-heart',
        'compass' => 'fa-compass',
        'star' => 'fa-star',
        'school' => 'fa-graduation-cap',
        'rocket' => 'fa-rocket'
    ];
    return $iconMap[$icon] ?? 'fa-folder';
}

/**
 * 格式化浏览量
 */
function formatViews($views) {
    if ($views >= 10000) {
        return round($views / 10000, 1) . '万';
    }
    return number_format($views);
}

/**
 * JSON 响应
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 验证是否为 AJAX 请求
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * 获取动漫封面图 URL
 * 优先使用本地图片，其次使用预设的在线图片，最后使用占位图
 */
function getCoverImage($coverImage, $title = 'Anime') {
    $localPath = __DIR__ . '/../assets/images/' . $coverImage;

    if ($coverImage && file_exists($localPath)) {
        return 'assets/images/' . $coverImage;
    }

    // 预设的动漫封面图片映射（使用公开可用的图片资源）
    $coverMap = [
        'kimetsu.jpg' => 'https://cdn.myanimelist.net/images/anime/1286/99889l.jpg',
        'aot.jpg' => 'https://cdn.myanimelist.net/images/anime/10/47347l.jpg',
        'natsume.jpg' => 'https://media.themoviedb.org/t/p/w300_and_h450_face/4IAX5fgsyj9SnV2sqEh5JfuOvUo.jpg',
        'jjk.jpg' => 'https://cdn.myanimelist.net/images/anime/1171/109222l.jpg',
        'spy.jpg' => 'https://cdn.myanimelist.net/images/anime/1441/122795l.jpg',
        'violet.jpg' => 'https://cdn.myanimelist.net/images/anime/1795/95088l.jpg',
        'onepiece.jpg' => 'https://cdn.myanimelist.net/images/anime/1244/138851l.jpg',
        'fate.jpg' => 'https://cdn.myanimelist.net/images/anime/2/73249l.jpg',
        'oregairu.jpg' => 'https://cdn.myanimelist.net/images/anime/11/49459l.jpg',
        'kaguya.jpg' => 'https://cdn.myanimelist.net/images/anime/1295/106551l.jpg',
        'steinsgate.jpg' => 'https://cdn.myanimelist.net/images/anime/1935/127974l.jpg',
        '86.jpg' => 'https://cdn.myanimelist.net/images/anime/1987/117507l.jpg',
    ];

    if ($coverImage && isset($coverMap[$coverImage])) {
        return $coverMap[$coverImage];
    }

    $encodedTitle = urlencode($title);
    return "https://placehold.co/300x400/6366f1/ffffff?text={$encodedTitle}";
}
