<?php
/**
 * 分类管理 - Category Management
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

$error = '';
$editCategory = null;

// 处理删除
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        // 检查是否有关联动漫
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM animes WHERE category_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetch()['count'];

        if ($count > 0) {
            setFlash('error', "该分类下有 {$count} 部动漫，无法删除");
        } else {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', '分类已删除');
        }
    } catch (PDOException $e) {
        error_log("[Categories Error] Delete: " . $e->getMessage());
        setFlash('error', '删除失败');
    }
    header('Location: categories.php');
    exit;
}

// 处理编辑加载
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $editCategory = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("[Categories Error] Edit load: " . $e->getMessage());
    }
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'folder');

    if (empty($name)) {
        $error = '请输入分类名称';
    } else {
        try {
            if ($id > 0) {
                // 更新
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?");
                $stmt->execute([$name, $description, $icon, $id]);
                setFlash('success', '分类更新成功');
            } else {
                // 新增
                $stmt = $conn->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $icon]);
                setFlash('success', '分类添加成功');
            }
            header('Location: categories.php');
            exit;
        } catch (PDOException $e) {
            error_log("[Categories Error] Save: " . $e->getMessage());
            $error = '保存失败';
        }
    }
}

// 获取分类列表
$categories = [];
try {
    $stmt = $conn->query("SELECT c.*, COUNT(a.id) as anime_count FROM categories c LEFT JOIN animes a ON c.id = a.category_id GROUP BY c.id ORDER BY c.id");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("[Categories Error] List: " . $e->getMessage());
}

$icons = ['fire', 'heart', 'compass', 'star', 'school', 'rocket', 'magic', 'ghost', 'robot', 'gamepad'];

// 现在加载页面头部
$pageTitle = '分类管理';
require_once 'header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- 添加/编辑表单 -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-<?php echo $editCategory ? 'edit' : 'plus'; ?> mr-2 text-purple-500"></i>
                <?php echo $editCategory ? '编辑分类' : '添加分类'; ?>
            </h2>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo h($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">
                        分类名称 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required maxlength="50"
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all"
                           value="<?php echo h($editCategory['name'] ?? ''); ?>"
                           placeholder="如：热血、治愈">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">
                        分类描述
                    </label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-400 focus:ring-2 focus:ring-purple-100 outline-none transition-all resize-none"
                              placeholder="简短描述该分类..."><?php echo h($editCategory['description'] ?? ''); ?></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">
                        分类图标
                    </label>
                    <div class="grid grid-cols-5 gap-2">
                        <?php foreach ($icons as $icon): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="icon" value="<?php echo $icon; ?>" class="hidden peer"
                                   <?php echo ($editCategory['icon'] ?? 'fire') == $icon ? 'checked' : ''; ?>>
                            <div class="w-full aspect-square flex items-center justify-center rounded-lg border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-purple-300 transition-all">
                                <i class="fas fa-<?php echo $icon; ?> text-gray-500 peer-checked:text-purple-500"></i>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <?php if ($editCategory): ?>
                    <a href="categories.php" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-center text-gray-600 hover:bg-gray-50 transition-colors">
                        取消
                    </a>
                    <?php endif; ?>
                    <button type="submit" class="flex-1 bg-gradient-to-r from-purple-500 to-pink-500 text-white px-4 py-2 rounded-lg hover:opacity-90 transition-all">
                        <i class="fas fa-save mr-2"></i><?php echo $editCategory ? '保存修改' : '添加分类'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 分类列表 -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">
                    <i class="fas fa-folder mr-2 text-purple-500"></i>分类列表
                    <span class="text-gray-400 font-normal ml-2">(<?php echo count($categories); ?> 个分类)</span>
                </h2>
            </div>

            <?php if (empty($categories)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
                <p>暂无分类</p>
            </div>
            <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($categories as $category): ?>
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-100 to-pink-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-<?php echo h($category['icon']); ?> text-purple-500 text-xl"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-800"><?php echo h($category['name']); ?></div>
                            <div class="text-sm text-gray-400">
                                <?php echo $category['description'] ? h(truncate($category['description'], 40)) : '暂无描述'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 text-sm rounded-full">
                            <?php echo $category['anime_count']; ?> 部动漫
                        </span>
                        <div class="flex items-center space-x-2">
                            <a href="categories.php?edit=<?php echo $category['id']; ?>" class="text-blue-500 hover:text-blue-700" title="编辑">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($category['anime_count'] == 0): ?>
                            <a href="categories.php?delete=<?php echo $category['id']; ?>" onclick="return confirmDelete('确定要删除分类【<?php echo h($category['name']); ?>】吗？')" class="text-red-500 hover:text-red-700" title="删除">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-gray-300 cursor-not-allowed" title="该分类下有动漫，无法删除">
                                <i class="fas fa-trash"></i>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
