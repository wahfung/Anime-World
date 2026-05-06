<?php
/**
 * 用户管理 - User Management
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

// 处理状态切换
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    // 不能禁用自己
    if ($id == $_SESSION['user_id']) {
        setFlash('error', '不能禁用自己的账号');
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET status = IF(status = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', '用户状态已更新');
        } catch (PDOException $e) {
            error_log("[Users Error] Toggle: " . $e->getMessage());
            setFlash('error', '操作失败');
        }
    }
    header('Location: users.php');
    exit;
}

// 处理删除
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id == $_SESSION['user_id']) {
        setFlash('error', '不能删除自己的账号');
    } else {
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', '用户已删除');
        } catch (PDOException $e) {
            error_log("[Users Error] Delete: " . $e->getMessage());
            setFlash('error', '删除失败');
        }
    }
    header('Location: users.php');
    exit;
}

// 分页和搜索
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

// 构建查询
$where = [];
$params = [];

if ($search) {
    $where[] = "(username LIKE ? OR email LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($role && in_array($role, ['user', 'admin'])) {
    $where[] = "role = ?";
    $params[] = $role;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 获取总数
$total = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users {$whereClause}");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
} catch (PDOException $e) {
    error_log("[Users Error] Count: " . $e->getMessage());
}

$totalPages = ceil($total / $perPage);

// 获取用户列表
$users = [];
try {
    $sql = "SELECT * FROM users {$whereClause} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Users Error] List: " . $e->getMessage());
}

// 现在加载页面头部
$pageTitle = '用户管理';
require_once 'header.php';
?>

<!-- 操作栏 -->
<div class="bg-white rounded-xl shadow-md p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <span class="text-gray-500">用户总数: <?php echo $total; ?></span>
        </div>

        <form method="GET" class="flex items-center space-x-2">
            <select name="role" class="px-3 py-2 border border-gray-200 rounded-lg outline-none focus:border-purple-400">
                <option value="">全部角色</option>
                <option value="user" <?php echo $role == 'user' ? 'selected' : ''; ?>>普通用户</option>
                <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>管理员</option>
            </select>
            <input type="text" name="search" value="<?php echo h($search); ?>"
                   class="px-3 py-2 border border-gray-200 rounded-lg outline-none focus:border-purple-400"
                   placeholder="搜索用户名/邮箱...">
            <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<!-- 数据表格 -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用户信息</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">角色</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">注册时间</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                        <p>暂无用户数据</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $user['id']; ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-indigo-400 rounded-full flex items-center justify-center mr-3">
                                <span class="text-white font-medium"><?php echo mb_substr($user['username'], 0, 1); ?></span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900"><?php echo h($user['username']); ?></div>
                                <div class="text-xs text-gray-400"><?php echo h($user['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'; ?>">
                            <?php echo $user['role'] === 'admin' ? '管理员' : '普通用户'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full <?php echo $user['status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo $user['status'] ? '正常' : '已禁用'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo formatDate($user['created_at']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="users.php?toggle=<?php echo $user['id']; ?>"
                           class="<?php echo $user['status'] ? 'text-yellow-500 hover:text-yellow-700' : 'text-green-500 hover:text-green-700'; ?> mr-3"
                           title="<?php echo $user['status'] ? '禁用' : '启用'; ?>">
                            <i class="fas <?php echo $user['status'] ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                        </a>
                        <a href="users.php?delete=<?php echo $user['id']; ?>"
                           onclick="return confirmDelete('确定要删除用户【<?php echo h($user['username']); ?>】吗？')"
                           class="text-red-500 hover:text-red-700" title="删除">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php else: ?>
                        <span class="text-gray-400" title="当前账号">
                            <i class="fas fa-user-shield"></i>
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 分页 -->
    <?php if ($totalPages > 1): ?>
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                共 <?php echo $total; ?> 条记录，第 <?php echo $page; ?>/<?php echo $totalPages; ?> 页
            </div>
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
    </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
