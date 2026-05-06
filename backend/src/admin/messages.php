<?php
/**
 * 留言管理 - Message Management
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

$db = new Database();
$conn = $db->getConnection();

// 处理删除
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', '留言已删除');
    } catch (PDOException $e) {
        error_log("[Messages Error] Delete: " . $e->getMessage());
        setFlash('error', '删除失败');
    }
    header('Location: messages.php');
    exit;
}

// 处理回复
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'])) {
    $replyId = intval($_POST['reply_id']);
    $reply = trim($_POST['reply'] ?? '');

    if (!empty($reply)) {
        try {
            $stmt = $conn->prepare("UPDATE messages SET reply = ?, replied_by = ?, replied_at = NOW() WHERE id = ?");
            $stmt->execute([$reply, $_SESSION['user_id'], $replyId]);
            setFlash('success', '回复成功');
        } catch (PDOException $e) {
            error_log("[Messages Error] Reply: " . $e->getMessage());
            setFlash('error', '回复失败');
        }
    }
    header('Location: messages.php');
    exit;
}

// 处理状态切换
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];
    if (in_array($status, ['approved', 'hidden', 'pending'])) {
        try {
            $stmt = $conn->prepare("UPDATE messages SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            setFlash('success', '状态已更新');
        } catch (PDOException $e) {
            error_log("[Messages Error] Status: " . $e->getMessage());
            setFlash('error', '操作失败');
        }
    }
    header('Location: messages.php');
    exit;
}

// 现在加载页面头部
$pageTitle = '留言管理';
require_once 'header.php';

// 分页
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;
$filterStatus = isset($_GET['filter']) ? $_GET['filter'] : '';

// 构建查询
$where = '';
$params = [];
if ($filterStatus && in_array($filterStatus, ['approved', 'hidden', 'pending'])) {
    $where = 'WHERE m.status = ?';
    $params[] = $filterStatus;
}

// 获取总数
$total = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM messages m {$where}");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
} catch (PDOException $e) {
    error_log("[Messages Error] Count: " . $e->getMessage());
}

$totalPages = ceil($total / $perPage);

// 获取留言列表
$messages = [];
try {
    $sql = "SELECT m.*, u.username, ru.username as replied_by_name
            FROM messages m
            LEFT JOIN users u ON m.user_id = u.id
            LEFT JOIN users ru ON m.replied_by = ru.id
            {$where}
            ORDER BY m.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Messages Error] List: " . $e->getMessage());
}
?>

<!-- 操作栏 -->
<div class="bg-white rounded-xl shadow-md p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center space-x-2">
            <span class="text-gray-500">筛选：</span>
            <a href="messages.php" class="px-3 py-1 rounded-full text-sm <?php echo !$filterStatus ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                全部
            </a>
            <a href="messages.php?filter=approved" class="px-3 py-1 rounded-full text-sm <?php echo $filterStatus == 'approved' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                已通过
            </a>
            <a href="messages.php?filter=pending" class="px-3 py-1 rounded-full text-sm <?php echo $filterStatus == 'pending' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                待审核
            </a>
            <a href="messages.php?filter=hidden" class="px-3 py-1 rounded-full text-sm <?php echo $filterStatus == 'hidden' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                已隐藏
            </a>
        </div>
        <div class="text-gray-500 text-sm">
            共 <?php echo $total; ?> 条留言
        </div>
    </div>
</div>

<!-- 留言列表 -->
<?php if (empty($messages)): ?>
<div class="bg-white rounded-xl shadow-md p-12 text-center">
    <i class="fas fa-comments text-4xl text-gray-300 mb-3"></i>
    <p class="text-gray-500">暂无留言数据</p>
</div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($messages as $message): ?>
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-400 rounded-full flex items-center justify-center">
                    <span class="text-white font-medium"><?php echo mb_substr($message['username'], 0, 1); ?></span>
                </div>
                <div>
                    <div class="font-medium text-gray-800"><?php echo h($message['username']); ?></div>
                    <div class="text-sm text-gray-400"><?php echo formatDate($message['created_at']); ?></div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <!-- 状态标签 -->
                <span class="px-2 py-1 text-xs rounded-full <?php
                    echo $message['status'] == 'approved' ? 'bg-green-100 text-green-700' :
                        ($message['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600');
                ?>">
                    <?php echo $message['status'] == 'approved' ? '已通过' : ($message['status'] == 'pending' ? '待审核' : '已隐藏'); ?>
                </span>

                <!-- 操作按钮 -->
                <div class="flex items-center space-x-1">
                    <?php if ($message['status'] != 'approved'): ?>
                    <a href="messages.php?id=<?php echo $message['id']; ?>&status=approved" class="text-green-500 hover:text-green-700 p-1" title="通过">
                        <i class="fas fa-check"></i>
                    </a>
                    <?php endif; ?>
                    <?php if ($message['status'] != 'hidden'): ?>
                    <a href="messages.php?id=<?php echo $message['id']; ?>&status=hidden" class="text-yellow-500 hover:text-yellow-700 p-1" title="隐藏">
                        <i class="fas fa-eye-slash"></i>
                    </a>
                    <?php endif; ?>
                    <a href="messages.php?delete=<?php echo $message['id']; ?>" onclick="return confirmDelete('确定要删除这条留言吗？')" class="text-red-500 hover:text-red-700 p-1" title="删除">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 留言内容 -->
        <div class="text-gray-600 mb-4 pl-13">
            <?php echo nl2br(h($message['content'])); ?>
        </div>

        <!-- 已有回复 -->
        <?php if ($message['reply']): ?>
        <div class="bg-purple-50 rounded-lg p-4 ml-13">
            <div class="flex items-center mb-2">
                <span class="bg-purple-500 text-white text-xs px-2 py-1 rounded-full mr-2">
                    <i class="fas fa-crown mr-1"></i>管理员回复
                </span>
                <span class="text-sm text-gray-400">
                    <?php echo h($message['replied_by_name']); ?> · <?php echo formatDate($message['replied_at']); ?>
                </span>
            </div>
            <p class="text-gray-700"><?php echo nl2br(h($message['reply'])); ?></p>
        </div>
        <?php else: ?>
        <!-- 回复表单 -->
        <div class="ml-13 mt-4">
            <form method="POST" class="flex space-x-2">
                <input type="hidden" name="reply_id" value="<?php echo $message['id']; ?>">
                <input type="text" name="reply" placeholder="输入回复内容..."
                       class="flex-1 px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 outline-none">
                <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                    <i class="fas fa-reply mr-1"></i>回复
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- 分页 -->
<?php if ($totalPages > 1): ?>
<div class="mt-6 flex justify-center">
    <div class="flex items-center space-x-2">
        <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-50">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
           class="px-3 py-1 rounded <?php echo $i == $page ? 'bg-purple-500 text-white' : 'bg-white border border-gray-200 hover:bg-gray-50'; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="px-3 py-1 bg-white border border-gray-200 rounded hover:bg-gray-50">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
