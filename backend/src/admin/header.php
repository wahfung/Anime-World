<?php
/**
 * 后台管理头部模板 - Admin Header
 * 注意：调用此文件前必须先完成所有重定向操作
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// 权限检查（不重定向，由调用页面处理）
if (!Auth::isAdmin()) {
    die('无权访问');
}

$currentUser = Auth::getCurrentUser();
$flash = getFlash();

// 当前页面
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle ?? '后台管理'); ?> - 动漫世界管理后台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Noto Sans SC', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Noto Sans SC', sans-serif; }
        .sidebar-link {
            transition: all 0.2s ease;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.1), transparent);
            border-left: 3px solid #8B5CF6;
        }
        .sidebar-link.active {
            color: #8B5CF6;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex">
    <!-- 侧边栏 -->
    <aside class="w-64 bg-white shadow-xl fixed h-full z-20">
        <!-- Logo -->
        <div class="h-16 flex items-center justify-center border-b border-gray-100">
            <a href="index.php" class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-play text-white"></i>
                </div>
                <span class="text-xl font-bold text-gray-800">管理后台</span>
            </a>
        </div>

        <!-- 导航菜单 -->
        <nav class="py-6">
            <div class="px-4 mb-4">
                <p class="text-xs text-gray-400 uppercase tracking-wider">主菜单</p>
            </div>

            <a href="index.php" class="sidebar-link flex items-center px-6 py-3 text-gray-600 <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                <span>仪表盘</span>
            </a>

            <a href="animes.php" class="sidebar-link flex items-center px-6 py-3 text-gray-600 <?php echo $currentPage === 'animes' || $currentPage === 'anime_edit' ? 'active' : ''; ?>">
                <i class="fas fa-film w-5 mr-3"></i>
                <span>动漫管理</span>
            </a>

            <a href="users.php" class="sidebar-link flex items-center px-6 py-3 text-gray-600 <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users w-5 mr-3"></i>
                <span>用户管理</span>
            </a>

            <a href="messages.php" class="sidebar-link flex items-center px-6 py-3 text-gray-600 <?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
                <i class="fas fa-comments w-5 mr-3"></i>
                <span>留言管理</span>
            </a>

            <a href="categories.php" class="sidebar-link flex items-center px-6 py-3 text-gray-600 <?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
                <i class="fas fa-folder w-5 mr-3"></i>
                <span>分类管理</span>
            </a>

            <div class="px-4 my-4">
                <p class="text-xs text-gray-400 uppercase tracking-wider">其他</p>
            </div>

            <a href="../index.php" class="sidebar-link flex items-center px-6 py-3 text-gray-600">
                <i class="fas fa-home w-5 mr-3"></i>
                <span>返回前台</span>
            </a>

            <a href="../logout.php" class="sidebar-link flex items-center px-6 py-3 text-gray-600">
                <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                <span>退出登录</span>
            </a>
        </nav>
    </aside>

    <!-- 主内容区 -->
    <div class="flex-1 ml-64">
        <!-- 顶部栏 -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 sticky top-0 z-10">
            <h1 class="text-lg font-semibold text-gray-800">
                <?php echo h($pageTitle ?? '后台管理'); ?>
            </h1>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium"><?php echo mb_substr($currentUser['username'], 0, 1); ?></span>
                    </div>
                    <span class="text-gray-700"><?php echo h($currentUser['username']); ?></span>
                    <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">管理员</span>
                </div>
            </div>
        </header>

        <!-- Flash 消息 -->
        <?php if ($flash): ?>
        <div class="mx-6 mt-4">
            <div class="<?php echo $flash['type'] === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo h($flash['message']); ?>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- 页面内容 -->
        <main class="p-6">
