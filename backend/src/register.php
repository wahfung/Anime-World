<?php
/**
 * 注册页面 - Register Page
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
$success = false;

// 处理注册请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 验证 CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = '请求无效，请刷新页面重试';
    } elseif ($password !== $confirmPassword) {
        $error = '两次输入的密码不一致';
    } else {
        $auth = new Auth();
        $result = $auth->register($username, $email, $password);

        if ($result['success']) {
            $success = true;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = '注册';
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - 动漫世界</title>
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
            <p class="text-gray-500 mt-2">创建账号，开启动漫之旅</p>
        </div>

        <!-- 注册表单 -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($success): ?>
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl text-green-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">注册成功！</h3>
                <p class="text-gray-500 mb-6">欢迎加入动漫世界大家庭</p>
                <a href="login.php" class="inline-block anime-gradient text-white px-8 py-3 rounded-xl font-semibold hover:opacity-90 transition-all">
                    <i class="fas fa-sign-in-alt mr-2"></i>立即登录
                </a>
            </div>
            <?php else: ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo h($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-user mr-1 text-purple-500"></i>用户名
                    </label>
                    <input type="text" name="username" required minlength="3" maxlength="20"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl input-focus transition-all outline-none"
                           placeholder="3-20个字符"
                           value="<?php echo h($_POST['username'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-envelope mr-1 text-purple-500"></i>邮箱
                    </label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl input-focus transition-all outline-none"
                           placeholder="请输入邮箱地址"
                           value="<?php echo h($_POST['email'] ?? ''); ?>">
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-lock mr-1 text-purple-500"></i>密码
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required minlength="6"
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl input-focus transition-all outline-none pr-12"
                               placeholder="至少6个字符">
                        <button type="button" onclick="togglePassword('password', 'toggleIcon1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <!-- 密码强度指示器 -->
                    <div class="mt-2 flex space-x-1" id="passwordStrength">
                        <div class="h-1 flex-1 rounded bg-gray-200"></div>
                        <div class="h-1 flex-1 rounded bg-gray-200"></div>
                        <div class="h-1 flex-1 rounded bg-gray-200"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-lock mr-1 text-purple-500"></i>确认密码
                    </label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirmPassword" required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl input-focus transition-all outline-none pr-12"
                               placeholder="请再次输入密码">
                        <button type="button" onclick="togglePassword('confirmPassword', 'toggleIcon2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                    <p class="text-sm text-red-500 mt-1 hidden" id="passwordMatch">
                        <i class="fas fa-exclamation-circle mr-1"></i>两次密码不一致
                    </p>
                </div>

                <div class="flex items-start">
                    <input type="checkbox" name="agree" required class="mt-1 mr-2 rounded border-gray-300 text-purple-500 focus:ring-purple-500">
                    <label class="text-sm text-gray-600">
                        我已阅读并同意
                        <a href="#" class="text-purple-600 hover:text-purple-700">用户协议</a>
                        和
                        <a href="#" class="text-purple-600 hover:text-purple-700">隐私政策</a>
                    </label>
                </div>

                <button type="submit" class="w-full anime-gradient text-white py-3 rounded-xl font-semibold hover:opacity-90 transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-user-plus mr-2"></i>注册
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-500">
                    已有账号？
                    <a href="login.php" class="text-purple-600 hover:text-purple-700 font-medium">立即登录</a>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- 返回首页 -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-1"></i>返回首页
            </a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
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

        // 密码强度检测
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const bars = document.querySelectorAll('#passwordStrength div');
            let strength = 0;

            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/) || password.match(/[^a-zA-Z0-9]/)) strength++;

            bars.forEach((bar, index) => {
                if (index < strength) {
                    bar.classList.remove('bg-gray-200');
                    bar.classList.add(strength === 1 ? 'bg-red-400' : strength === 2 ? 'bg-yellow-400' : 'bg-green-400');
                } else {
                    bar.classList.add('bg-gray-200');
                    bar.classList.remove('bg-red-400', 'bg-yellow-400', 'bg-green-400');
                }
            });
        });

        // 确认密码验证
        document.getElementById('confirmPassword').addEventListener('input', function(e) {
            const password = document.getElementById('password').value;
            const confirm = e.target.value;
            const hint = document.getElementById('passwordMatch');

            if (confirm && password !== confirm) {
                hint.classList.remove('hidden');
            } else {
                hint.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
