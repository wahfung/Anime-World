<?php
/**
 * 登录页面 - Login Page
 * 动漫世界
 */

session_start();
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// 如果已登录，跳转到首页
if (Auth::isLoggedIn()) {
    redirect('index.php');
}

$error = '';

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 验证 CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = '请求无效，请刷新页面重试';
    } else {
        $auth = new Auth();
        $result = $auth->login($username, $password);

        if ($result['success']) {
            setFlash('success', '欢迎回来，' . h($username) . '！');
            // 如果是管理员，跳转到后台
            if ($_SESSION['role'] === 'admin') {
                redirect('admin/index.php');
            }
            redirect('index.php');
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = '登录';
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - 动漫世界</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Noto Sans SC', sans-serif; }
        .anime-gradient { background: linear-gradient(135deg, #FF6B9D 0%, #C44FE0 50%, #7B68EE 100%); }
        .text-anime-gradient {
            background: linear-gradient(135deg, #FF6B9D 0%, #C44FE0 50%, #7B68EE 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .input-focus:focus {
            border-color: #C44FE0;
            box-shadow: 0 0 0 3px rgba(196, 79, 224, 0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center space-x-2">
                <div class="w-12 h-12 anime-gradient rounded-xl flex items-center justify-center">
                    <i class="fas fa-play text-white text-xl"></i>
                </div>
                <span class="text-3xl font-bold text-anime-gradient">动漫世界</span>
            </a>
            <p class="text-gray-500 mt-2">欢迎回来，请登录您的账号</p>
        </div>

        <!-- 登录表单 -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo h($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-user mr-1 text-purple-500"></i>用户名 / 邮箱
                    </label>
                    <input type="text" name="username" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl input-focus transition-all outline-none"
                           placeholder="请输入用户名或邮箱"
                           value="<?php echo h($_POST['username'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-lock mr-1 text-purple-500"></i>密码
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl input-focus transition-all outline-none pr-12"
                               placeholder="请输入密码">
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-gray-600">
                        <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 text-purple-500 focus:ring-purple-500">
                        记住我
                    </label>
                    <a href="#" class="text-purple-600 hover:text-purple-700">忘记密码？</a>
                </div>

                <button type="submit" class="w-full anime-gradient text-white py-3 rounded-xl font-semibold hover:opacity-90 transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-sign-in-alt mr-2"></i>登录
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-500">
                    还没有账号？
                    <a href="register.php" class="text-purple-600 hover:text-purple-700 font-medium">立即注册</a>
                </p>
            </div>

            <!-- 测试账号提示 -->
            <div class="mt-6 p-4 bg-purple-50 rounded-xl">
                <p class="text-sm text-purple-700 font-medium mb-2">
                    <i class="fas fa-info-circle mr-1"></i>测试账号
                </p>
                <div class="text-sm text-purple-600 space-y-1">
                    <p>管理员: admin / password</p>
                    <p>普通用户: test_user / password</p>
                </div>
            </div>
        </div>

        <!-- 返回首页 -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-1"></i>返回首页
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
