<?php
/**
 * 后台仪表盘 - Admin Dashboard
 * 动漫世界
 */

session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// 检查管理员权限
if (!Auth::isAdmin()) {
    setFlash('error', '您没有权限访问后台管理');
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// 获取统计数据
$stats = [
    'animes' => 0,
    'users' => 0,
    'messages' => 0,
    'views' => 0
];

try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM animes");
    $stats['animes'] = $stmt->fetch()['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stats['users'] = $stmt->fetch()['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM messages");
    $stats['messages'] = $stmt->fetch()['count'];

    $stmt = $conn->query("SELECT SUM(views) as total FROM animes");
    $stats['views'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    error_log("[Dashboard Error] Stats: " . $e->getMessage());
}

// 获取最新动漫
$latestAnimes = [];
try {
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name FROM animes a LEFT JOIN categories c ON a.category_id = c.id ORDER BY a.created_at DESC LIMIT 5");
    $stmt->execute();
    $latestAnimes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Dashboard Error] Latest animes: " . $e->getMessage());
}

// 获取最新留言
$latestMessages = [];
try {
    $stmt = $conn->prepare("SELECT m.*, u.username FROM messages m LEFT JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC LIMIT 5");
    $stmt->execute();
    $latestMessages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Dashboard Error] Latest messages: " . $e->getMessage());
}

// 获取最新用户
$latestUsers = [];
try {
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $latestUsers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Dashboard Error] Latest users: " . $e->getMessage());
}

// 获取热门动漫
$popularAnimes = [];
try {
    $stmt = $conn->prepare("SELECT title, views FROM animes ORDER BY views DESC LIMIT 5");
    $stmt->execute();
    $popularAnimes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Dashboard Error] Popular animes: " . $e->getMessage());
}

// 现在加载页面头部
$pageTitle = '仪表盘';
require_once 'header.php';
?>

<!-- 统计卡片 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">动漫总数</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['animes']); ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-film text-purple-500 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-500"><i class="fas fa-arrow-up mr-1"></i>12%</span>
            <span class="text-gray-400 ml-2">较上月</span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">注册用户</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['users']); ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-500 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-500"><i class="fas fa-arrow-up mr-1"></i>8%</span>
            <span class="text-gray-400 ml-2">较上月</span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">留言总数</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats['messages']); ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-comments text-green-500 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-500"><i class="fas fa-arrow-up mr-1"></i>24%</span>
            <span class="text-gray-400 ml-2">较上月</span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">总浏览量</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo formatViews($stats['views']); ?></p>
            </div>
            <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                <i class="fas fa-eye text-pink-500 text-xl"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-500"><i class="fas fa-arrow-up mr-1"></i>18%</span>
            <span class="text-gray-400 ml-2">较上月</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- 最新动漫 -->
    <div class="bg-white rounded-xl shadow-md">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-semibold text-gray-800">
                <i class="fas fa-film mr-2 text-purple-500"></i>最新动漫
            </h2>
            <a href="animes.php" class="text-purple-500 hover:text-purple-600 text-sm">
                查看全部 <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="p-6">
            <?php if (empty($latestAnimes)): ?>
            <p class="text-gray-500 text-center py-4">暂无数据</p>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($latestAnimes as $anime): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-200 to-pink-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-play text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800"><?php echo h($anime['title']); ?></p>
                            <p class="text-sm text-gray-400"><?php echo h($anime['category_name']); ?></p>
                        </div>
                    </div>
                    <span class="text-sm text-gray-400"><?php echo formatDate($anime['created_at'], 'm-d H:i'); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 最新留言 -->
    <div class="bg-white rounded-xl shadow-md">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-semibold text-gray-800">
                <i class="fas fa-comments mr-2 text-green-500"></i>最新留言
            </h2>
            <a href="messages.php" class="text-purple-500 hover:text-purple-600 text-sm">
                查看全部 <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="p-6">
            <?php if (empty($latestMessages)): ?>
            <p class="text-gray-500 text-center py-4">暂无数据</p>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($latestMessages as $message): ?>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-purple-400 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-xs"><?php echo mb_substr($message['username'], 0, 1); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-800 text-sm"><?php echo h($message['username']); ?></p>
                            <span class="text-xs text-gray-400"><?php echo formatDate($message['created_at'], 'm-d H:i'); ?></span>
                        </div>
                        <p class="text-sm text-gray-500 truncate"><?php echo h(truncate($message['content'], 50)); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 热门动漫排行 -->
    <div class="bg-white rounded-xl shadow-md">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">
                <i class="fas fa-fire mr-2 text-orange-500"></i>热门动漫排行
            </h2>
        </div>
        <div class="p-6">
            <?php if (empty($popularAnimes)): ?>
            <p class="text-gray-500 text-center py-4">暂无数据</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($popularAnimes as $index => $anime): ?>
                <div class="flex items-center">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 <?php echo $index < 3 ? 'bg-gradient-to-r from-orange-400 to-pink-400 text-white' : 'bg-gray-100 text-gray-500'; ?>">
                        <?php echo $index + 1; ?>
                    </span>
                    <div class="flex-1 flex items-center justify-between">
                        <span class="text-gray-700"><?php echo h($anime['title']); ?></span>
                        <span class="text-sm text-gray-400">
                            <i class="fas fa-eye mr-1"></i><?php echo formatViews($anime['views']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 最新注册用户 -->
    <div class="bg-white rounded-xl shadow-md">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-semibold text-gray-800">
                <i class="fas fa-user-plus mr-2 text-blue-500"></i>最新注册用户
            </h2>
            <a href="users.php" class="text-purple-500 hover:text-purple-600 text-sm">
                查看全部 <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="p-6">
            <?php if (empty($latestUsers)): ?>
            <p class="text-gray-500 text-center py-4">暂无数据</p>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($latestUsers as $user): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-indigo-400 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium"><?php echo mb_substr($user['username'], 0, 1); ?></span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800"><?php echo h($user['username']); ?></p>
                            <p class="text-sm text-gray-400"><?php echo h($user['email']); ?></p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'; ?>">
                        <?php echo $user['role'] === 'admin' ? '管理员' : '用户'; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
