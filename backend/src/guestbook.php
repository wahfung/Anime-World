<?php
/**
 * 留言板页面 - Guestbook Page
 * 动漫世界
 */

$pageTitle = '留言板';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

$error = '';
$success = '';

// 处理留言提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::isLoggedIn()) {
    $content = trim($_POST['content'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = '请求无效，请刷新页面重试';
    } elseif (empty($content)) {
        $error = '请输入留言内容';
    } elseif (mb_strlen($content) > 500) {
        $error = '留言内容不能超过500字';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO messages (user_id, content) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $content]);
            $success = '留言发表成功！';

            // 记录日志
            $logStmt = $conn->prepare("INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, 'POST_MESSAGE', '发表留言', ?)");
            $logStmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
        } catch (PDOException $e) {
            error_log("[Guestbook Error] Failed to post message: " . $e->getMessage());
            $error = '留言失败，请稍后重试';
        }
    }
}

// 获取留言列表
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$total = 0;
$messages = [];

try {
    // 获取总数
    $stmt = $conn->query("SELECT COUNT(*) as total FROM messages WHERE status = 'approved'");
    $total = $stmt->fetch()['total'];

    // 获取留言
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.avatar,
               ru.username as replied_by_name
        FROM messages m
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN users ru ON m.replied_by = ru.id
        WHERE m.status = 'approved'
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Guestbook Error] Failed to fetch messages: " . $e->getMessage());
}

$totalPages = ceil($total / $perPage);
$csrfToken = generateCsrfToken();
?>

<!-- 页面头部 -->
<section class="anime-gradient py-12 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">
            <i class="fas fa-comments mr-2"></i>留言板
        </h1>
        <p class="text-white/80">
            分享你对动漫的热爱，与志同道合的朋友交流心得
        </p>
    </div>
</section>

<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- 发表留言 -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-pen mr-2 text-purple-500"></i>发表留言
        </h2>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo h($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo h($success); ?>
        </div>
        <?php endif; ?>

        <?php if (Auth::isLoggedIn()): ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-gradient-to-r from-pink-400 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold"><?php echo mb_substr($_SESSION['username'], 0, 1); ?></span>
                </div>
                <div class="flex-1">
                    <textarea name="content" rows="4" maxlength="500" required
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all resize-none"
                              placeholder="写下你的想法，分享你对动漫的热爱..."></textarea>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-sm text-gray-400">
                            <i class="fas fa-info-circle mr-1"></i>请文明发言，最多500字
                        </span>
                        <button type="submit" class="anime-gradient text-white px-6 py-2 rounded-full font-medium hover:opacity-90 transition-all shadow-md">
                            <i class="fas fa-paper-plane mr-2"></i>发表留言
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php else: ?>
        <div class="bg-purple-50 rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-lock text-2xl text-purple-500"></i>
            </div>
            <p class="text-gray-600 mb-4">登录后即可发表留言</p>
            <a href="login.php" class="inline-block anime-gradient text-white px-6 py-2 rounded-full font-medium hover:opacity-90 transition-all">
                <i class="fas fa-sign-in-alt mr-2"></i>立即登录
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- 留言统计 -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">
            <i class="fas fa-list mr-2 text-purple-500"></i>全部留言
            <span class="text-base font-normal text-gray-500 ml-2">(<?php echo $total; ?> 条)</span>
        </h2>
    </div>

    <!-- 留言列表 -->
    <?php if (empty($messages)): ?>
    <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-comment-slash text-3xl text-gray-400"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">暂无留言</h3>
        <p class="text-gray-500">成为第一个留言的人吧！</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($messages as $message): ?>
        <div class="bg-white rounded-2xl shadow-md p-6 card-hover">
            <div class="flex items-start space-x-4">
                <!-- 头像 -->
                <div class="w-12 h-12 bg-gradient-to-r from-pink-400 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold"><?php echo mb_substr($message['username'], 0, 1); ?></span>
                </div>

                <div class="flex-1 min-w-0">
                    <!-- 用户信息 -->
                    <div class="flex items-center flex-wrap gap-2 mb-2">
                        <span class="font-semibold text-gray-800"><?php echo h($message['username']); ?></span>
                        <span class="text-sm text-gray-400">
                            <i class="far fa-clock mr-1"></i>
                            <?php echo formatDate($message['created_at']); ?>
                        </span>
                    </div>

                    <!-- 留言内容 -->
                    <p class="text-gray-600 leading-relaxed mb-3">
                        <?php echo nl2br(h($message['content'])); ?>
                    </p>

                    <!-- 管理员回复 -->
                    <?php if ($message['reply']): ?>
                    <div class="bg-purple-50 rounded-xl p-4 mt-3">
                        <div class="flex items-center mb-2">
                            <span class="bg-purple-500 text-white text-xs px-2 py-1 rounded-full mr-2">
                                <i class="fas fa-crown mr-1"></i>管理员回复
                            </span>
                            <span class="text-sm text-gray-400">
                                <?php echo h($message['replied_by_name']); ?> ·
                                <?php echo formatDate($message['replied_at']); ?>
                            </span>
                        </div>
                        <p class="text-gray-700"><?php echo nl2br(h($message['reply'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-8 flex justify-center">
        <nav class="flex items-center space-x-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white rounded-lg shadow hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
            <a href="?page=<?php echo $i; ?>" class="px-4 py-2 rounded-lg shadow transition-colors <?php echo $i == $page ? 'bg-purple-500 text-white' : 'bg-white hover:bg-gray-50'; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white rounded-lg shadow hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
