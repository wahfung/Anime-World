<?php
/**
 * 动漫管理 - Anime Management
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

// 处理删除操作
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM animes WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', '动漫删除成功');
        header('Location: animes.php');
        exit;
    } catch (PDOException $e) {
        error_log("[Animes Error] Delete: " . $e->getMessage());
        setFlash('error', '删除失败');
    }
}

// 分页和筛选
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;

// 获取分类列表
$categories = [];
try {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Animes Error] Categories: " . $e->getMessage());
}

// 构建查询
$where = [];
$params = [];

if ($search) {
    $where[] = "(a.title LIKE ? OR a.original_title LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($categoryId > 0) {
    $where[] = "a.category_id = ?";
    $params[] = $categoryId;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 获取总数
$total = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM animes a {$whereClause}");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
} catch (PDOException $e) {
    error_log("[Animes Error] Count: " . $e->getMessage());
}

$totalPages = ceil($total / $perPage);

// 获取动漫列表
$animes = [];
try {
    $sql = "SELECT a.*, c.name as category_name
            FROM animes a
            LEFT JOIN categories c ON a.category_id = c.id
            {$whereClause}
            ORDER BY a.id DESC
            LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $animes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Animes Error] List: " . $e->getMessage());
}

// 现在加载页面头部
$pageTitle = '动漫管理';
require_once 'header.php';
?>

<!-- 操作栏 -->
<div class="bg-white rounded-xl shadow-md p-4 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="anime_edit.php" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-4 py-2 rounded-lg hover:opacity-90 transition-all shadow-md">
                <i class="fas fa-plus mr-2"></i>添加动漫
            </a>
        </div>

        <form method="GET" class="flex items-center space-x-2">
            <select name="category" class="px-3 py-2 border border-gray-200 rounded-lg outline-none focus:border-purple-400">
                <option value="">全部分类</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                    <?php echo h($cat['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="search" value="<?php echo h($search); ?>"
                   class="px-3 py-2 border border-gray-200 rounded-lg outline-none focus:border-purple-400"
                   placeholder="搜索动漫名称...">
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">动漫名称</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">分类</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">集数</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">评分</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">浏览量</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">推荐</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($animes)): ?>
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-film text-4xl text-gray-300 mb-3"></i>
                        <p>暂无动漫数据</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($animes as $anime): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $anime['id']; ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-14 rounded-lg overflow-hidden mr-3 flex-shrink-0">
                                <img src="<?php echo h(getCoverImage($anime['cover_image'], $anime['title'])); ?>"
                                     alt="<?php echo h($anime['title']); ?>"
                                     class="w-full h-full object-cover"
                                     onerror="this.src='https://placehold.co/40x56/6366f1/ffffff?text=N'">
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900"><?php echo h($anime['title']); ?></div>
                                <?php if ($anime['original_title']): ?>
                                <div class="text-xs text-gray-400"><?php echo h($anime['original_title']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700">
                            <?php echo h($anime['category_name']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $anime['episodes']; ?> 集</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full <?php echo getStatusColor($anime['status']); ?>">
                            <?php echo getStatusName($anime['status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                            <span class="text-sm text-gray-700"><?php echo $anime['rating']; ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatViews($anime['views']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($anime['is_featured']): ?>
                        <span class="text-green-500"><i class="fas fa-check-circle"></i></span>
                        <?php else: ?>
                        <span class="text-gray-300"><i class="fas fa-times-circle"></i></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="anime_edit.php?id=<?php echo $anime['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-3" title="编辑">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="animes.php?delete=<?php echo $anime['id']; ?>" onclick="return confirmDelete('确定要删除动漫【<?php echo h($anime['title']); ?>】吗？')" class="text-red-500 hover:text-red-700" title="删除">
                            <i class="fas fa-trash"></i>
                        </a>
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
