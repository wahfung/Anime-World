<?php
/**
 * 动漫编辑/添加 - Anime Edit/Add
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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit = $id > 0;
$pageTitle = $isEdit ? '编辑动漫' : '添加动漫';

// 获取动漫数据（编辑模式）
$anime = [
    'title' => '',
    'original_title' => '',
    'description' => '',
    'category_id' => '',
    'episodes' => 0,
    'status' => 'ongoing',
    'rating' => 0,
    'release_year' => date('Y'),
    'studio' => '',
    'is_featured' => 0
];

if ($isEdit) {
    try {
        $stmt = $conn->prepare("SELECT * FROM animes WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if ($result) {
            $anime = $result;
        } else {
            header('Location: animes.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("[AnimeEdit Error] Fetch: " . $e->getMessage());
    }
}

// 获取分类列表
$categories = [];
try {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[AnimeEdit Error] Categories: " . $e->getMessage());
}

// 处理表单提交
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $originalTitle = trim($_POST['original_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $episodes = intval($_POST['episodes'] ?? 0);
    $status = $_POST['status'] ?? 'ongoing';
    $rating = floatval($_POST['rating'] ?? 0);
    $releaseYear = intval($_POST['release_year'] ?? date('Y'));
    $studio = trim($_POST['studio'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    // 验证
    if (empty($title)) {
        $error = '请输入动漫名称';
    } elseif ($categoryId <= 0) {
        $error = '请选择分类';
    } elseif ($rating < 0 || $rating > 10) {
        $error = '评分必须在0-10之间';
    } else {
        try {
            if ($isEdit) {
                // 更新
                $stmt = $conn->prepare("UPDATE animes SET
                    title = ?, original_title = ?, description = ?, category_id = ?,
                    episodes = ?, status = ?, rating = ?, release_year = ?,
                    studio = ?, is_featured = ?, updated_at = NOW()
                    WHERE id = ?");
                $stmt->execute([
                    $title, $originalTitle, $description, $categoryId,
                    $episodes, $status, $rating, $releaseYear,
                    $studio, $isFeatured, $id
                ]);
                setFlash('success', '动漫更新成功');
            } else {
                // 新增
                $stmt = $conn->prepare("INSERT INTO animes
                    (title, original_title, description, category_id, episodes, status, rating, release_year, studio, is_featured)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $title, $originalTitle, $description, $categoryId,
                    $episodes, $status, $rating, $releaseYear,
                    $studio, $isFeatured
                ]);
                setFlash('success', '动漫添加成功');
            }
            header('Location: animes.php');
            exit;
        } catch (PDOException $e) {
            error_log("[AnimeEdit Error] Save: " . $e->getMessage());
            $error = '保存失败，请稍后重试';
        }
    }

    // 保留表单数据
    $anime = [
        'title' => $title,
        'original_title' => $originalTitle,
        'description' => $description,
        'category_id' => $categoryId,
        'episodes' => $episodes,
        'status' => $status,
        'rating' => $rating,
        'release_year' => $releaseYear,
        'studio' => $studio,
        'is_featured' => $isFeatured
    ];
}

require_once 'header.php';
?>

<div class="max-w-4xl">
    <!-- 返回按钮 -->
    <div class="mb-6">
        <a href="animes.php" class="text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>返回动漫列表
        </a>
    </div>

    <!-- 表单 -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?php echo h($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 动漫名称 -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        动漫名称 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" required maxlength="200"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                           value="<?php echo h($anime['title']); ?>"
                           placeholder="请输入动漫名称">
                </div>

                <!-- 原名 -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        原名（日文/英文）
                    </label>
                    <input type="text" name="original_title" maxlength="200"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                           value="<?php echo h($anime['original_title']); ?>"
                           placeholder="请输入动漫原名">
                </div>

                <!-- 分类 -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        分类 <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all">
                        <option value="">请选择分类</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $anime['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo h($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 集数 -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        集数
                    </label>
                    <input type="number" name="episodes" min="0"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                           value="<?php echo $anime['episodes']; ?>">
                </div>

                <!-- 状态 -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        播放状态
                    </label>
                    <select name="status"
                            class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all">
                        <option value="ongoing" <?php echo $anime['status'] == 'ongoing' ? 'selected' : ''; ?>>连载中</option>
                        <option value="completed" <?php echo $anime['status'] == 'completed' ? 'selected' : ''; ?>>已完结</option>
                        <option value="upcoming" <?php echo $anime['status'] == 'upcoming' ? 'selected' : ''; ?>>即将上映</option>
                    </select>
                </div>

                <!-- 评分 -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        评分（0-10）
                    </label>
                    <input type="number" name="rating" min="0" max="10" step="0.1"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                           value="<?php echo $anime['rating']; ?>">
                </div>

                <!-- 年份 -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        发行年份
                    </label>
                    <input type="number" name="release_year" min="1900" max="2030"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                           value="<?php echo $anime['release_year']; ?>">
                </div>

                <!-- 制作公司 -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        制作公司
                    </label>
                    <input type="text" name="studio" maxlength="100"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                           value="<?php echo h($anime['studio']); ?>"
                           placeholder="如：ufotable, MAPPA">
                </div>
            </div>

            <!-- 简介 -->
            <div class="mt-6">
                <label class="block text-gray-700 font-medium mb-2">
                    作品简介
                </label>
                <textarea name="description" rows="5"
                          class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all resize-none"
                          placeholder="请输入作品简介..."><?php echo h($anime['description']); ?></textarea>
            </div>

            <!-- 推荐 -->
            <div class="mt-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1"
                           class="rounded border-gray-300 text-purple-500 focus:ring-purple-500"
                           <?php echo $anime['is_featured'] ? 'checked' : ''; ?>>
                    <span class="ml-2 text-gray-700">设为推荐动漫（将在首页展示）</span>
                </label>
            </div>

            <!-- 提交按钮 -->
            <div class="mt-8 flex items-center justify-end space-x-4">
                <a href="animes.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">
                    取消
                </a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:opacity-90 transition-all shadow-md">
                    <i class="fas fa-save mr-2"></i><?php echo $isEdit ? '保存修改' : '添加动漫'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
