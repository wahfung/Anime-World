<?php
/**
 * 动漫详情页面 - Anime Detail Page
 * 动漫世界
 */

require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: anime_list.php');
    exit;
}

// 获取动漫详情
$anime = null;
try {
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name, c.icon as category_icon
                            FROM animes a
                            LEFT JOIN categories c ON a.category_id = c.id
                            WHERE a.id = ?");
    $stmt->execute([$id]);
    $anime = $stmt->fetch();
} catch (PDOException $e) {
    error_log("[AnimeDetail Error] Failed to fetch anime: " . $e->getMessage());
}

if (!$anime) {
    header('Location: anime_list.php');
    exit;
}

// 增加浏览量
try {
    $stmt = $conn->prepare("UPDATE animes SET views = views + 1 WHERE id = ?");
    $stmt->execute([$id]);
    $anime['views']++;
} catch (PDOException $e) {
    error_log("[AnimeDetail Error] Failed to update views: " . $e->getMessage());
}

// 获取相关动漫
$relatedAnimes = [];
try {
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name
                            FROM animes a
                            LEFT JOIN categories c ON a.category_id = c.id
                            WHERE a.category_id = ? AND a.id != ?
                            ORDER BY a.rating DESC
                            LIMIT 4");
    $stmt->execute([$anime['category_id'], $id]);
    $relatedAnimes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[AnimeDetail Error] Failed to fetch related animes: " . $e->getMessage());
}

$pageTitle = $anime['title'];
require_once 'includes/header.php';

// 检查是否已收藏
$isFavorited = false;
if (Auth::isLoggedIn()) {
    try {
        $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND anime_id = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);
        $isFavorited = $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("[AnimeDetail Error] Failed to check favorite: " . $e->getMessage());
    }
}
?>

<!-- 详情头部 -->
<section class="anime-gradient py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <nav class="text-white/70 text-sm mb-4">
            <a href="index.php" class="hover:text-white">首页</a>
            <span class="mx-2">/</span>
            <a href="anime_list.php" class="hover:text-white">动漫列表</a>
            <span class="mx-2">/</span>
            <a href="anime_list.php?category=<?php echo $anime['category_id']; ?>"
                class="hover:text-white"><?php echo h($anime['category_name']); ?></a>
            <span class="mx-2">/</span>
            <span class="text-white"><?php echo h($anime['title']); ?></span>
        </nav>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="md:flex">
            <!-- 封面 -->
            <div class="md:w-1/3 lg:w-1/4">
                <div class="aspect-[3/4] overflow-hidden rounded-xl shadow-lg relative group">
                    <img src="<?php echo h(getCoverImage($anime['cover_image'], $anime['title'])); ?>"
                        alt="<?php echo h($anime['title']); ?>"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        onerror="this.src='https://placehold.co/300x400/6366f1/ffffff?text=No+Image'">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                </div>
            </div>

            <!-- 信息 -->
            <div class="md:w-2/3 lg:w-3/4 p-6 md:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
                            <?php echo h($anime['title']); ?>
                        </h1>
                        <?php if ($anime['original_title']): ?>
                            <p class="text-gray-500"><?php echo h($anime['original_title']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span
                            class="<?php echo getStatusColor($anime['status']); ?> px-4 py-2 rounded-full text-sm font-medium">
                            <?php echo getStatusName($anime['status']); ?>
                        </span>
                        <?php if (Auth::isLoggedIn()): ?>
                            <button onclick="toggleFavorite(<?php echo $anime['id']; ?>)" id="favoriteBtn"
                                class="px-4 py-2 rounded-full border-2 transition-all <?php echo $isFavorited ? 'bg-pink-500 border-pink-500 text-white' : 'border-gray-300 text-gray-500 hover:border-pink-500 hover:text-pink-500'; ?>">
                                <i class="<?php echo $isFavorited ? 'fas' : 'far'; ?> fa-heart mr-1"></i>
                                <span><?php echo $isFavorited ? '已收藏' : '收藏'; ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 评分 -->
                <div class="flex items-center space-x-6 mb-6">
                    <div class="flex items-center">
                        <div class="text-4xl font-bold text-anime-gradient mr-3"><?php echo $anime['rating']; ?></div>
                        <div>
                            <div class="flex items-center text-yellow-400">
                                <?php echo renderStars($anime['rating']); ?>
                            </div>
                            <p class="text-gray-400 text-sm"><?php echo formatViews($anime['views']); ?> 次浏览</p>
                        </div>
                    </div>
                </div>

                <!-- 信息标签 -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-purple-50 rounded-xl p-4 text-center">
                        <div class="text-purple-500 mb-1">
                            <i class="fas <?php echo getCategoryIcon($anime['category_icon']); ?> text-xl"></i>
                        </div>
                        <div class="text-sm text-gray-500">分类</div>
                        <div class="font-semibold text-gray-800"><?php echo h($anime['category_name']); ?></div>
                    </div>
                    <div class="bg-pink-50 rounded-xl p-4 text-center">
                        <div class="text-pink-500 mb-1">
                            <i class="fas fa-list-ol text-xl"></i>
                        </div>
                        <div class="text-sm text-gray-500">集数</div>
                        <div class="font-semibold text-gray-800"><?php echo $anime['episodes']; ?> 集</div>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-4 text-center">
                        <div class="text-indigo-500 mb-1">
                            <i class="fas fa-calendar text-xl"></i>
                        </div>
                        <div class="text-sm text-gray-500">年份</div>
                        <div class="font-semibold text-gray-800"><?php echo $anime['release_year'] ?: '未知'; ?></div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4 text-center">
                        <div class="text-blue-500 mb-1">
                            <i class="fas fa-building text-xl"></i>
                        </div>
                        <div class="text-sm text-gray-500">制作</div>
                        <div class="font-semibold text-gray-800 truncate"><?php echo h($anime['studio'] ?: '未知'); ?>
                        </div>
                    </div>
                </div>

                <!-- 简介 -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fas fa-align-left mr-2 text-purple-500"></i>作品简介
                    </h2>
                    <p class="text-gray-600 leading-relaxed">
                        <?php echo nl2br(h($anime['description'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 相关推荐 -->
    <?php if (!empty($relatedAnimes)): ?>
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-th-large mr-2 text-purple-500"></i>相关推荐
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($relatedAnimes as $related): ?>
                    <a href="anime_detail.php?id=<?php echo $related['id']; ?>"
                        class="bg-white rounded-xl overflow-hidden card-hover shadow-md group">
                        <div class="relative h-36 overflow-hidden">
                            <img src="<?php echo h(getCoverImage($related['cover_image'], $related['title'])); ?>"
                                alt="<?php echo h($related['title']); ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                onerror="this.src='https://placehold.co/300x400/6366f1/ffffff?text=No+Image'">
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 truncate group-hover:text-purple-600 transition-colors">
                                <?php echo h($related['title']); ?>
                            </h3>
                            <div class="flex items-center justify-between mt-2 text-sm text-gray-400">
                                <span>
                                    <i class="fas fa-star text-yellow-400 mr-1"></i><?php echo $related['rating']; ?>
                                </span>
                                <span><?php echo $related['episodes']; ?> 集</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleFavorite(animeId) {
        fetch('api/favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ anime_id: animeId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const btn = document.getElementById('favoriteBtn');
                    const icon = btn.querySelector('i');
                    const text = btn.querySelector('span');

                    if (data.favorited) {
                        btn.classList.remove('border-gray-300', 'text-gray-500');
                        btn.classList.add('bg-pink-500', 'border-pink-500', 'text-white');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        text.textContent = '已收藏';
                    } else {
                        btn.classList.add('border-gray-300', 'text-gray-500');
                        btn.classList.remove('bg-pink-500', 'border-pink-500', 'text-white');
                        icon.classList.add('far');
                        icon.classList.remove('fas');
                        text.textContent = '收藏';
                    }
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('操作失败，请重试', 'error');
            });
    }
</script>

<?php require_once 'includes/footer.php'; ?>