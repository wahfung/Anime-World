-- 动漫网站数据库初始化脚本
-- 使用 UTF8 编码

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    avatar VARCHAR(255) DEFAULT 'default.png',
    role ENUM('user', 'admin') DEFAULT 'user',
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 动漫分类表
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 动漫表
CREATE TABLE IF NOT EXISTS animes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    original_title VARCHAR(200),
    cover_image VARCHAR(255),
    description TEXT,
    category_id INT,
    episodes INT DEFAULT 0,
    status ENUM('ongoing', 'completed', 'upcoming') DEFAULT 'ongoing',
    rating DECIMAL(3,1) DEFAULT 0.0,
    release_year INT,
    studio VARCHAR(100),
    views INT DEFAULT 0,
    is_featured TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 留言板表
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content TEXT NOT NULL,
    reply TEXT,
    replied_by INT,
    status ENUM('pending', 'approved', 'hidden') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 收藏表
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    anime_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, anime_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (anime_id) REFERENCES animes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 系统日志表
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 初始化种子数据
-- ============================================

-- 插入管理员账号 (密码: admin123)
INSERT INTO users (username, password, email, role, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@anime.com', 'admin', 1),
('test_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test@anime.com', 'user', 1),
('anime_lover', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lover@anime.com', 'user', 1);

-- 插入动漫分类
INSERT INTO categories (name, description, icon) VALUES
('热血', '充满激情和战斗的动漫作品', 'fire'),
('治愈', '温暖治愈心灵的动漫作品', 'heart'),
('冒险', '充满冒险和探索的动漫作品', 'compass'),
('奇幻', '魔法和奇幻世界的动漫作品', 'star'),
('校园', '发生在校园里的青春故事', 'school'),
('科幻', '科技和未来题材的动漫作品', 'rocket');

-- 插入动漫数据
INSERT INTO animes (title, original_title, cover_image, description, category_id, episodes, status, rating, release_year, studio, views, is_featured) VALUES
('鬼灭之刃', 'Demon Slayer', 'kimetsu.jpg', '故事讲述了少年炭治郎为了让变成鬼的妹妹祢豆子变回人类，踏上了斩鬼之旅的故事。作品传递了家人之间的羁绊、不屈不挠的精神以及对正义的追求。', 1, 44, 'ongoing', 9.5, 2019, 'ufotable', 150000, 1),
('进击的巨人', 'Attack on Titan', 'aot.jpg', '在被巨人支配的世界，人类被迫生活在巨大的围墙内。少年艾伦立志要消灭所有巨人，为人类夺回自由。这是一部关于自由与牺牲的史诗级作品。', 1, 87, 'completed', 9.8, 2013, 'MAPPA', 200000, 1),
('夏目友人帐', 'Natsume Yuujinchou', 'natsume.jpg', '能看见妖怪的少年夏目贵志，继承了祖母留下的友人帐，开始了将名字归还给妖怪的温暖故事。这是一部关于羁绊与成长的治愈系作品。', 2, 74, 'completed', 9.2, 2008, 'Brain Base', 80000, 1),
('咒术回战', 'Jujutsu Kaisen', 'jjk.jpg', '高中生虎杖悠仁为了拯救同学，吞下了特级咒物两面宿傩的手指，从此踏入了咒术的世界。热血与黑暗并存的精彩故事。', 1, 47, 'ongoing', 9.3, 2020, 'MAPPA', 180000, 1),
('间谍过家家', 'SPY×FAMILY', 'spy.jpg', '为了完成任务，间谍黄昏组建了临时家庭，却意外收获了温暖。这是一部充满欢笑与感动的作品，讲述了一个特殊家庭的日常。', 2, 37, 'ongoing', 9.0, 2022, 'WIT Studio', 120000, 1),
('紫罗兰永恒花园', 'Violet Evergarden', 'violet.jpg', '战争结束后，少女薇尔莉特成为了自动手记人偶，帮助人们将心意传达给重要的人。这是一部关于爱与理解的感人之作。', 2, 13, 'completed', 9.4, 2018, 'Kyoto Animation', 95000, 1),
('海贼王', 'ONE PIECE', 'onepiece.jpg', '少年路飞立志成为海贼王，与伙伴们踏上寻找传说宝藏的冒险之旅。这是一部关于梦想、友情和自由的传奇故事。', 3, 1100, 'ongoing', 9.6, 1999, 'Toei Animation', 300000, 1),
('命运之夜', 'Fate/stay night', 'fate.jpg', '围绕着能实现任何愿望的圣杯，七位御主召唤英灵展开生死之战。这是一部关于正义与理想的史诗级奇幻作品。', 4, 26, 'completed', 8.9, 2006, 'ufotable', 110000, 0),
('我的青春恋爱物语果然有问题', 'Oregairu', 'oregairu.jpg', '性格扭曲的高中生比企谷八幡被强制加入服务社，开始了他独特的青春故事。这是一部深刻探讨青春与人际关系的作品。', 5, 38, 'completed', 8.8, 2013, 'feel.', 75000, 0),
('辉夜大小姐想让我告白', 'Kaguya-sama', 'kaguya.jpg', '两位天才学生因为自尊心都不愿先告白，展开了一场智慧与心理的恋爱战争。这是一部充满笑料的浪漫喜剧。', 5, 37, 'completed', 9.1, 2019, 'A-1 Pictures', 130000, 1),
('STEINS;GATE 命运石之门', 'Steins;Gate', 'steinsgate.jpg', '大学生冈部伦太郎偶然发明了能发送短信到过去的装置，却因此卷入了时间线的漩涡。这是一部关于时间与牺牲的科幻神作。', 6, 24, 'completed', 9.7, 2011, 'White Fox', 140000, 1),
('86-不存在的战区', '86 Eighty Six', '86.jpg', '在这个将某些人视为非人的国家，被称为86的少年少女们在战场上挣扎求存。这是一部关于歧视与人性的深刻作品。', 6, 23, 'completed', 9.0, 2021, 'A-1 Pictures', 85000, 0);

-- 插入留言数据
INSERT INTO messages (user_id, content, status, created_at) VALUES
(2, '这个网站太棒了！收录的动漫都是我喜欢的类型，希望能增加更多治愈系动漫推荐！', 'approved', '2024-01-15 10:30:00'),
(3, '作为一个老二次元，看到这么用心的动漫网站真的很感动。特别喜欢夏目友人帐的推荐，每次看都很治愈。', 'approved', '2024-01-16 14:20:00'),
(2, '建议增加一个动漫讨论区，这样大家可以分享观后感！', 'approved', '2024-01-17 09:15:00'),
(3, '鬼灭之刃真的太好看了，期待第四季！', 'approved', '2024-01-18 16:45:00'),
(2, '网站界面设计得很漂亮，动漫信息也很全面，五星好评！', 'approved', '2024-01-19 11:30:00');

-- 插入收藏数据
INSERT INTO favorites (user_id, anime_id) VALUES
(2, 1), (2, 3), (2, 5), (2, 6),
(3, 1), (3, 2), (3, 7), (3, 11);

-- 插入系统日志
INSERT INTO system_logs (user_id, action, description, ip_address) VALUES
(1, 'LOGIN', '管理员登录系统', '127.0.0.1'),
(1, 'CREATE_ANIME', '添加动漫：鬼灭之刃', '127.0.0.1'),
(2, 'REGISTER', '新用户注册', '192.168.1.100'),
(3, 'REGISTER', '新用户注册', '192.168.1.101');
