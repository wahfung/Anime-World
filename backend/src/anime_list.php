<?php
/**
 * 动漫列表页面 - Anime List Page
 * 动漫世界
 */

$pageTitle = '动漫列表';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// 获取筛选参数
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// 获取分类列表
$categories = [];
try {
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY id");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[AnimeList Error] Failed to fetch categories: " . $e->getMessage());
}

// 构建查询
$where = [];
$params = [];

if ($categoryId > 0) {
    $where[] = "a.category_id = ?";
    $params[] = $categoryId;
}

if (in_array($status, ['ongoing', 'completed', 'upcoming'])) {
    $where[] = "a.status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(a.title LIKE ? OR a.original_title LIKE ? OR a.description LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 排序
// 名称排序: 数字(0-9) -> 英文(A-Z) -> 中文(按拼音)
$orderBy = match ($sort) {
    'rating' => 'a.rating DESC',
    'views' => 'a.views DESC',
    'title' => 'CASE
                    WHEN a.title REGEXP "^[0-9]" THEN 0
                    WHEN a.title REGEXP "^[A-Za-z]" THEN 1
                    ELSE 2
                END ASC,
                CONVERT(a.title USING gbk) ASC',
    default => 'a.created_at DESC'
};

// 获取总数
$total = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM animes a {$whereClause}");
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
} catch (PDOException $e) {
    error_log("[AnimeList Error] Failed to count animes: " . $e->getMessage());
}

$totalPages = ceil($total / $perPage);

// 获取动漫列表
$animes = [];
try {
    $sql = "SELECT a.*, c.name as category_name
            FROM animes a
            LEFT JOIN categories c ON a.category_id = c.id
            {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $animes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[AnimeList Error] Failed to fetch animes: " . $e->getMessage());
}

// 当前分类名称
$currentCategory = null;
if ($categoryId > 0) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categoryId) {
            $currentCategory = $cat;
            break;
        }
    }
}
?>

<!-- 页面头部 -->
<section class="anime-gradient py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">
            <?php if ($currentCategory): ?>
                <i class="fas <?php echo getCategoryIcon($currentCategory['icon']); ?> mr-2"></i>
                <?php echo h($currentCategory['name']); ?>动漫
            <?php elseif ($search): ?>
                <i class="fas fa-search mr-2"></i>搜索结果
            <?php else: ?>
                <i class="fas fa-film mr-2"></i>动漫列表
            <?php endif; ?>
        </h1>
        <p class="text-white/80">
            <?php if ($search): ?>
                找到 <?php echo $total; ?> 个与 "<?php echo h($search); ?>" 相关的结果
            <?php else: ?>
                共收录 <?php echo $total; ?> 部精彩动漫作品
            <?php endif; ?>
        </p>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- 侧边栏筛选 -->
        <aside class="lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-24">
                <!-- 搜索框 -->
                <form method="GET" class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-search mr-1 text-purple-500"></i>搜索动漫
                    </label>
                    <div class="flex">
                        <input type="text" name="search"
                            class="flex-1 px-4 py-2 border border-gray-200 rounded-l-xl outline-none focus:border-purple-400 w-full lg:max-w-[180px]"
                            placeholder="输入动漫名称..." value="<?php echo h($search); ?>">
                        <button type="submit"
                            class="bg-purple-500 text-white px-4 rounded-r-xl hover:bg-purple-600 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- 分类筛选 -->
                <div class="mb-6">
                    <h3 class="text-gray-700 font-medium mb-3">
                        <i class="fas fa-folder mr-1 text-purple-500"></i>动漫分类
                    </h3>
                    <div class="space-y-2">
                        <a href="anime_list.php<?php echo $status ? "?status={$status}" : ''; ?>"
                            class="block px-3 py-2 rounded-lg transition-colors <?php echo !$categoryId ? 'bg-purple-100 text-purple-700' : 'hover:bg-gray-100 text-gray-600'; ?>">
                            全部分类
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="anime_list.php?category=<?php echo $cat['id']; ?><?php echo $status ? "&status={$status}" : ''; ?>"
                                class="block px-3 py-2 rounded-lg transition-colors <?php echo $categoryId == $cat['id'] ? 'bg-purple-100 text-purple-700' : 'hover:bg-gray-100 text-gray-600'; ?>">
                                <i class="fas <?php echo getCategoryIcon($cat['icon']); ?> mr-2 w-4"></i>
                                <?php echo h($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 状态筛选 -->
                <div class="mb-6">
                    <h3 class="text-gray-700 font-medium mb-3">
                        <i class="fas fa-broadcast-tower mr-1 text-purple-500"></i>播放状态
                    </h3>
                    <div class="space-y-2">
                        <a href="anime_list.php<?php echo $categoryId ? "?category={$categoryId}" : ''; ?>"
                            class="block px-3 py-2 rounded-lg transition-colors <?php echo !$status ? 'bg-purple-100 text-purple-700' : 'hover:bg-gray-100 text-gray-600'; ?>">
                            全部状态
                        </a>
                        <a href="anime_list.php?status=ongoing<?php echo $categoryId ? "&category={$categoryId}" : ''; ?>"
                            class="block px-3 py-2 rounded-lg transition-colors <?php echo $status == 'ongoing' ? 'bg-purple-100 text-purple-700' : 'hover:bg-gray-100 text-gray-600'; ?>">
                            <span class="w-2 h-2 bg-green-500 rounded-full inline-block mr-2"></span>连载中
                        </a>
                        <a href="anime_list.php?status=completed<?php echo $categoryId ? "&category={$categoryId}" : ''; ?>"
                            class="block px-3 py-2 rounded-lg transition-colors <?php echo $status == 'completed' ? 'bg-purple-100 text-purple-700' : 'hover:bg-gray-100 text-gray-600'; ?>">
                            <span class="w-2 h-2 bg-blue-500 rounded-full inline-block mr-2"></span>已完结
                        </a>
                        <a href="anime_list.php?status=upcoming<?php echo $categoryId ? "&category={$categoryId}" : ''; ?>"
                            class="block px-3 py-2 rounded-lg transition-colors <?php echo $status == 'upcoming' ? 'bg-purple-100 text-purple-700' : 'hover:bg-gray-100 text-gray-600'; ?>">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full inline-block mr-2"></span>即将上映
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- 主内容区 -->
        <div class="flex-1">
            <!-- 排序选项 -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-500">排序：</span>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'latest'])); ?>"
                        class="px-3 py-1 rounded-full text-sm transition-colors <?php echo $sort == 'latest' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                        最新
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'rating'])); ?>"
                        class="px-3 py-1 rounded-full text-sm transition-colors <?php echo $sort == 'rating' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                        评分
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'views'])); ?>"
                        class="px-3 py-1 rounded-full text-sm transition-colors <?php echo $sort == 'views' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                        热门
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'title'])); ?>"
                        class="px-3 py-1 rounded-full text-sm transition-colors <?php echo $sort == 'title' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">
                        名称
                    </a>
                </div>
                <div class="text-gray-500 text-sm">
                    共 <?php echo $total; ?> 部 · 第 <?php echo $page; ?>/<?php echo max(1, $totalPages); ?> 页
                </div>
            </div>

            <!-- 动漫网格 -->
            <?php if (empty($animes)): ?>
                <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">暂无动漫</h3>
                    <p class="text-gray-500">
                        <?php if ($search): ?>
                            没有找到与 "<?php echo h($search); ?>" 相关的动漫
                        <?php else: ?>
                            该分类下暂无动漫，敬请期待
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($animes as $anime): ?>
                        <a href="anime_detail.php?id=<?php echo $anime['id']; ?>"
                            class="bg-white rounded-xl overflow-hidden card-hover shadow-md group">
                            <div class="relative h-64 overflow-hidden">
                                <img src="<?php echo h(getCoverImage($anime['cover_image'], $anime['title'])); ?>"
                                    alt="<?php echo h($anime['title']); ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                    onerror="this.src='https://placehold.co/300x400/6366f1/ffffff?text=No+Image'">
                                <div class="absolute top-2 left-2 z-10">
                                    <span
                                        class="bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs px-2 py-1 rounded-full shadow-md">
                                        <?php echo h($anime['category_name']); ?>
                                    </span>
                                </div>
                                <div class="absolute top-2 right-2 z-10">
                                    <span
                                        class="<?php echo getStatusColor($anime['status']); ?> text-xs px-2 py-1 rounded-full shadow-md">
                                        <?php echo getStatusName($anime['status']); ?>
                                    </span>
                                </div>
                                <div
                                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center z-20">
                                    <span class="bg-white text-purple-600 px-4 py-1 rounded-full text-sm font-medium">
                                        查看详情
                                    </span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3
                                    class="font-semibold text-gray-800 truncate group-hover:text-purple-600 transition-colors mb-2">
                                    <?php echo h($anime['title']); ?>
                                </h3>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center text-yellow-500">
                                        <i class="fas fa-star mr-1"></i>
                                        <span class="text-gray-600"><?php echo $anime['rating']; ?></span>
                                    </div>
                                    <div class="text-gray-400">
                                        <?php echo $anime['episodes']; ?> 集
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- 分页 -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-8 flex justify-center">
                        <nav class="flex items-center space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                                    class="px-4 py-2 bg-white rounded-lg shadow hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                                ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                                    class="px-4 py-2 rounded-lg shadow transition-colors <?php echo $i == $page ? 'bg-purple-500 text-white' : 'bg-white hover:bg-gray-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                                    class="px-4 py-2 bg-white rounded-lg shadow hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>