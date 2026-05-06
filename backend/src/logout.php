<?php
/**
 * 登出处理 - Logout Handler
 * 动漫世界
 */

session_start();
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->logout();

setFlash('success', '您已成功退出登录');
redirect('index.php');
