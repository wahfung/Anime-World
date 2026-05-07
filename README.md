# 动漫世界 - Anime World

一个以动漫为主题的动态网站，内容积极向上，提供动漫资讯浏览、用户互动等功能。

## 🛠 技术栈

- **Frontend**: PHP 8.2 + Tailwind CSS (CDN)
- **Backend**: PHP 8.2 + Apache
- **Database**: MySQL 8.0

## 🚀 启动指南 (How to Run)

### 环境要求

- PHP 8.2 或更高版本
- Apache Web 服务器
- MySQL 8.0 或更高版本

### 安装步骤

1. 配置 Web 服务器，将 `backend/src/` 目录作为网站根目录
2. 创建 MySQL 数据库，并执行 `mysql/init.sql` 初始化脚本
3. 修改 `backend/src/config/database.php` 中的数据库连接配置
4. 启动 Apache 和 MySQL 服务
5. 访问网站

## 🔗 服务地址 (Services)

| 服务     | 地址                                              |
| -------- | ------------------------------------------------- |
| 前台网站 | http://localhost/ (根据你的 Web 服务器配置)       |
| 后台管理 | http://localhost/admin/ (根据你的 Web 服务器配置) |

## 🧪 测试账号

| 角色     | 用户名      | 密码     |
| -------- | ----------- | -------- |
| 管理员   | admin       | password |
| 普通用户 | test_user   | password |
| 普通用户 | anime_lover | password |

## 📄 页面说明

### 前台页面（6个以上）

1. **首页** (`index.php`) - 网站首页，展示推荐动漫、分类导航、最新更新
2. **动漫列表** (`anime_list.php`) - 动漫浏览页面，支持分类筛选、状态筛选、搜索、排序
3. **动漫详情** (`anime_detail.php`) - 动漫详细信息页面，支持收藏功能
4. **登录页面** (`login.php`) - 用户登录
5. **注册页面** (`register.php`) - 用户注册
6. **留言板** (`guestbook.php`) - 用户留言互动

### 后台管理页面（5个）

1. **仪表盘** (`admin/index.php`) - 数据统计概览
2. **动漫管理** (`admin/animes.php`) - 动漫的增删改查
3. **用户管理** (`admin/users.php`) - 用户管理
4. **留言管理** (`admin/messages.php`) - 留言审核与回复
5. **分类管理** (`admin/categories.php`) - 动漫分类管理

## ✨ 功能特点

- 🎨 现代化UI设计，使用 Tailwind CSS 构建
- 📱 响应式布局，支持PC和移动端
- 🔐 完整的用户认证系统（登录/注册/登出）
- 💬 留言板功能，支持管理员回复
- ❤️ 动漫收藏功能
- 🔍 动漫搜索、分类筛选、状态筛选
- 📊 后台数据统计
- 🛡️ 安全防护（CSRF Token、密码加密、SQL注入防护）

## 🗂 项目结构

```
repo/
├── backend/
│   └── src/                # PHP 源代码
│       ├── config/         # 配置文件
│       ├── includes/       # 公共模块
│       ├── api/            # API 接口
│       ├── admin/          # 后台管理
│       └── *.php           # 前台页面
└── mysql/
    └── init.sql            # 数据库初始化脚本
```
