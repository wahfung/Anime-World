<?php
/**
 * 通用头部模板 - 前台
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$currentUser = Auth::getCurrentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle ?? '动漫世界'); ?> - 动漫世界</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Noto Sans SC', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fdf4ff',
                            100: '#fae8ff',
                            200: '#f5d0fe',
                            300: '#f0abfc',
                            400: '#e879f9',
                            500: '#d946ef',
                            600: '#c026d3',
                            700: '#a21caf',
                            800: '#86198f',
                            900: '#701a75',
                        },
                        anime: {
                            pink: '#FF6B9D',
                            purple: '#C44FE0',
                            blue: '#7B68EE',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Noto Sans SC', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .nav-link {
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #FF6B9D, #C44FE0);
            transition: width 0.3s ease;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        .anime-gradient {
            background: linear-gradient(135deg, #FF6B9D 0%, #C44FE0 50%, #7B68EE 100%);
        }
        .text-anime-gradient {
            background: linear-gradient(135deg, #FF6B9D 0%, #C44FE0 50%, #7B68EE 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #C44FE0;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 min-h-screen">
    <!-- 导航栏 -->
    <nav class="bg-white/90 backdrop-blur-md shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2">
                        <div class="w-10 h-10 anime-gradient rounded-xl flex items-center justify-center">
                            <i class="fas fa-play text-white text-lg"></i>
                        </div>
                        <span class="text-2xl font-bold text-anime-gradient">动漫世界</span>
                    </a>
                    <div class="hidden md:flex ml-10 space-x-8">
                        <a href="index.php" class="nav-link text-gray-700 hover:text-purple-600 font-medium transition-colors">
                            <i class="fas fa-home mr-1"></i>首页
                        </a>
                        <a href="anime_list.php" class="nav-link text-gray-700 hover:text-purple-600 font-medium transition-colors">
                            <i class="fas fa-film mr-1"></i>动漫列表
                        </a>
                        <a href="guestbook.php" class="nav-link text-gray-700 hover:text-purple-600 font-medium transition-colors">
                            <i class="fas fa-comments mr-1"></i>留言板
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($currentUser): ?>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2 bg-purple-50 px-4 py-2 rounded-full">
                                <div class="w-8 h-8 bg-gradient-to-r from-pink-400 to-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium"><?php echo mb_substr($currentUser['username'], 0, 1); ?></span>
                                </div>
                                <span class="text-gray-700 font-medium"><?php echo h($currentUser['username']); ?></span>
                            </div>
                            <?php if (Auth::isAdmin()): ?>
                                <a href="admin/index.php" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-4 py-2 rounded-full hover:from-purple-600 hover:to-pink-600 transition-all shadow-md hover:shadow-lg">
                                    <i class="fas fa-cog mr-1"></i>管理后台
                                </a>
                            <?php endif; ?>
                            <a href="logout.php" class="text-gray-500 hover:text-red-500 transition-colors" title="退出登录">
                                <i class="fas fa-sign-out-alt text-lg"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">
                            <i class="fas fa-sign-in-alt mr-1"></i>登录
                        </a>
                        <a href="register.php" class="anime-gradient text-white px-5 py-2 rounded-full hover:opacity-90 transition-all shadow-md hover:shadow-lg font-medium">
                            <i class="fas fa-user-plus mr-1"></i>注册
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- 移动端菜单 -->
        <div class="md:hidden border-t border-gray-100">
            <div class="px-4 py-3 space-y-2">
                <a href="index.php" class="block text-gray-700 hover:text-purple-600 py-2">首页</a>
                <a href="anime_list.php" class="block text-gray-700 hover:text-purple-600 py-2">动漫列表</a>
                <a href="guestbook.php" class="block text-gray-700 hover:text-purple-600 py-2">留言板</a>
            </div>
        </div>
    </nav>

    <!-- Flash 消息 -->
    <?php if ($flash): ?>
    <div id="flash-message" class="fixed top-20 right-4 z-50 max-w-md fade-in">
        <div class="<?php echo $flash['type'] === 'success' ? 'bg-green-500' : ($flash['type'] === 'error' ? 'bg-red-500' : 'bg-blue-500'); ?> text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
            <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : ($flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'); ?>"></i>
            <span><?php echo h($flash['message']); ?></span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-white/80 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const flash = document.getElementById('flash-message');
            if (flash) flash.remove();
        }, 5000);
    </script>
    <?php endif; ?>

    <!-- 主内容区 -->
    <main class="flex-1">
