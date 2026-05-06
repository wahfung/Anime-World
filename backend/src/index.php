<?php
/**
 * 首页 - Homepage
 * 动漫世界
 */

$pageTitle = '首页';
require_once 'includes/header.php';
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// 获取推荐动漫
$featuredAnimes = [];
try {
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name FROM animes a LEFT JOIN categories c ON a.category_id = c.id WHERE a.is_featured = 1 ORDER BY a.views DESC LIMIT 6");
    $stmt->execute();
    $featuredAnimes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Index Error] Failed to fetch featured animes: " . $e->getMessage());
}

// 获取分类列表
$categories = [];
try {
    $stmt = $conn->prepare("SELECT c.*, COUNT(a.id) as anime_count FROM categories c LEFT JOIN animes a ON c.id = a.category_id GROUP BY c.id");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Index Error] Failed to fetch categories: " . $e->getMessage());
}

// 获取最新动漫
$latestAnimes = [];
try {
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name FROM animes a LEFT JOIN categories c ON a.category_id = c.id ORDER BY a.created_at DESC LIMIT 8");
    $stmt->execute();
    $latestAnimes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Index Error] Failed to fetch latest animes: " . $e->getMessage());
}

// 获取统计数据
$stats = ['animes' => 0, 'users' => 0, 'views' => 0];
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM animes");
    $stats['animes'] = $stmt->fetch()['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stats['users'] = $stmt->fetch()['count'];

    $stmt = $conn->query("SELECT SUM(views) as total FROM animes");
    $stats['views'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    error_log("[Index Error] Failed to fetch stats: " . $e->getMessage());
}
?>

<!-- Hero Section -->
<section class="relative overflow-hidden">
    <div class="anime-gradient py-20 px-4">
        <div class="max-w-7xl mx-auto text-center text-white">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 fade-in">
                探索动漫的无限魅力
            </h1>
            <p class="text-xl md:text-2xl opacity-90 mb-8 fade-in">
                发现精彩动漫，感受热血与感动，传递积极向上的力量
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 fade-in">
                <a href="anime_list.php"
                    class="bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-purple-50 transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-compass mr-2"></i>浏览动漫
                </a>
                <?php if (!Auth::isLoggedIn()): ?>
                    <a href="register.php"
                        class="bg-white/20 backdrop-blur-sm text-white px-8 py-3 rounded-full font-semibold hover:bg-white/30 transition-all border border-white/30">
                        <i class="fas fa-user-plus mr-2"></i>加入我们
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- 装饰元素 -->
    <div class="absolute top-10 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl"></div>
    <div class="absolute bottom-10 right-10 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
</section>

<!-- 统计数据 -->
<section class="py-12 bg-white shadow-lg -mt-8 relative z-10 mx-4 lg:mx-auto max-w-6xl rounded-2xl">
    <div class="grid grid-cols-3 gap-8 px-8">
        <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-anime-gradient">
                <?php echo number_format($stats['animes']); ?></div>
            <div class="text-gray-500 mt-1">精选动漫</div>
        </div>
        <div class="text-center border-x border-gray-100">
            <div class="text-3xl md:text-4xl font-bold text-anime-gradient">
                <?php echo number_format($stats['users']); ?></div>
            <div class="text-gray-500 mt-1">注册用户</div>
        </div>
        <div class="text-center">
            <div class="text-3xl md:text-4xl font-bold text-anime-gradient"><?php echo formatViews($stats['views']); ?>
            </div>
            <div class="text-gray-500 mt-1">累计浏览</div>
        </div>
    </div>
</section>

<!-- 分类导航 -->
<section class="py-16 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">动漫分类</h2>
            <p class="text-gray-500">选择你喜欢的类型，发现更多精彩</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($categories as $category): ?>
                <a href="anime_list.php?category=<?php echo $category['id']; ?>"
                    class="bg-white rounded-2xl p-6 text-center card-hover shadow-md hover:shadow-xl">
                    <div
                        class="w-14 h-14 mx-auto bg-gradient-to-r from-purple-100 to-pink-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas <?php echo getCategoryIcon($category['icon']); ?> text-2xl text-purple-500"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800"><?php echo h($category['name']); ?></h3>
                    <p class="text-sm text-gray-400 mt-1"><?php echo $category['anime_count']; ?> 部作品</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 推荐动漫 -->
<section class="py-16 px-4 bg-white/50">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">精选推荐</h2>
                <p class="text-gray-500">编辑精心挑选的优质作品</p>
            </div>
            <a href="anime_list.php" class="text-purple-600 hover:text-purple-700 font-medium">
                查看更多 <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($featuredAnimes as $anime): ?>
                <a href="anime_detail.php?id=<?php echo $anime['id']; ?>"
                    class="bg-white rounded-2xl overflow-hidden card-hover shadow-lg group">
                    <div class="relative h-48 overflow-hidden">
                        <img src="<?php echo h(getCoverImage($anime['cover_image'], $anime['title'])); ?>"
                            alt="<?php echo h($anime['title']); ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            onerror="this.src='https://placehold.co/300x400/6366f1/ffffff?text=No+Image'">
                        <div class="absolute top-3 left-3">
                            <span
                                class="bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs px-3 py-1 rounded-full">
                                <?php echo h($anime['category_name']); ?>
                            </span>
                        </div>
                        <div class="absolute top-3 right-3">
                            <span class="<?php echo getStatusColor($anime['status']); ?> text-xs px-3 py-1 rounded-full">
                                <?php echo getStatusName($anime['status']); ?>
                            </span>
                        </div>
                        <div
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="bg-white text-purple-600 px-6 py-2 rounded-full font-medium">
                                <i class="fas fa-play mr-2"></i>查看详情
                            </span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-800 mb-2 group-hover:text-purple-600 transition-colors">
                            <?php echo h($anime['title']); ?>
                        </h3>
                        <p class="text-gray-500 text-sm mb-3 line-clamp-2">
                            <?php echo h(truncate($anime['description'], 80)); ?>
                        </p>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-1">
                                <?php echo renderStars($anime['rating']); ?>
                                <span class="text-gray-600 ml-1"><?php echo $anime['rating']; ?></span>
                            </div>
                            <div class="text-gray-400">
                                <i class="fas fa-eye mr-1"></i><?php echo formatViews($anime['views']); ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 最新更新 -->
<section class="py-16 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">最新更新</h2>
                <p class="text-gray-500">发现最新收录的动漫作品</p>
            </div>
            <a href="anime_list.php?sort=latest" class="text-purple-600 hover:text-purple-700 font-medium">
                查看更多 <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($latestAnimes as $anime): ?>
                <a href="anime_detail.php?id=<?php echo $anime['id']; ?>"
                    class="bg-white rounded-xl overflow-hidden card-hover shadow-md group">
                    <div class="relative h-32 overflow-hidden">
                        <img src="<?php echo h(getCoverImage($anime['cover_image'], $anime['title'])); ?>"
                            alt="<?php echo h($anime['title']); ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            onerror="this.src='https://placehold.co/300x400/6366f1/ffffff?text=No+Image'">
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 truncate group-hover:text-purple-600 transition-colors">
                            <?php echo h($anime['title']); ?>
                        </h3>
                        <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                            <span><?php echo $anime['episodes']; ?> 集</span>
                            <span><?php echo getStatusName($anime['status']); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 号召行动 -->
<section class="py-16 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="anime-gradient rounded-3xl p-8 md:p-12 text-center text-white relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">加入动漫世界大家庭</h2>
                <p class="text-lg opacity-90 mb-8">
                    注册成为会员，收藏喜欢的动漫，参与社区讨论
                </p>
                <?php if (!Auth::isLoggedIn()): ?>
                    <a href="register.php"
                        class="inline-block bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-purple-50 transition-all shadow-lg">
                        <i class="fas fa-rocket mr-2"></i>立即注册
                    </a>
                <?php else: ?>
                    <a href="guestbook.php"
                        class="inline-block bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-purple-50 transition-all shadow-lg">
                        <i class="fas fa-pen mr-2"></i>留言互动
                    </a>
                <?php endif; ?>
            </div>
            <!-- 装饰 -->
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2">
            </div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2">
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>