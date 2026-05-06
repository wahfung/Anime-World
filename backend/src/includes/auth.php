<?php
/**
 * 认证处理模块
 * 动漫网站 - Authentication Module
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * 用户注册
     */
    public function register($username, $email, $password) {
        // 输入验证
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => '所有字段都是必填的'];
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            return ['success' => false, 'message' => '用户名长度应在3-20个字符之间'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => '请输入有效的邮箱地址'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => '密码长度至少6个字符'];
        }

        try {
            // 检查用户名是否已存在
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => '用户名已被使用'];
            }

            // 检查邮箱是否已存在
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => '邮箱已被注册'];
            }

            // 创建新用户
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);

            $userId = $this->conn->lastInsertId();
            $this->logAction($userId, 'REGISTER', '新用户注册');

            return ['success' => true, 'message' => '注册成功！请登录'];

        } catch (PDOException $e) {
            error_log("[Auth Error] Register failed: " . $e->getMessage());
            return ['success' => false, 'message' => '注册失败，请稍后重试'];
        }
    }

    /**
     * 用户登录
     */
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => '请输入用户名和密码'];
        }

        try {
            $stmt = $this->conn->prepare("SELECT id, username, email, password, role, status, avatar FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => '用户名或密码错误'];
            }

            if ($user['status'] != 1) {
                return ['success' => false, 'message' => '账号已被禁用，请联系管理员'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => '用户名或密码错误'];
            }

            // 登录成功，设置 session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['logged_in'] = true;

            $this->logAction($user['id'], 'LOGIN', '用户登录');

            return [
                'success' => true,
                'message' => '登录成功',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ];

        } catch (PDOException $e) {
            error_log("[Auth Error] Login failed: " . $e->getMessage());
            return ['success' => false, 'message' => '登录失败，请稍后重试'];
        }
    }

    /**
     * 用户登出
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logAction($_SESSION['user_id'], 'LOGOUT', '用户登出');
        }
        session_destroy();
        return ['success' => true, 'message' => '已退出登录'];
    }

    /**
     * 检查是否已登录
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * 检查是否是管理员
     */
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /**
     * 获取当前用户信息
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'],
            'avatar' => $_SESSION['avatar'] ?? 'default.png'
        ];
    }

    /**
     * 记录系统日志
     */
    private function logAction($userId, $action, $description) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $this->conn->prepare("INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $action, $description, $ip]);
        } catch (PDOException $e) {
            error_log("[Log Error] Failed to log action: " . $e->getMessage());
        }
    }
}
