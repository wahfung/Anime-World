    </main>

    <!-- 页脚 -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo & 简介 -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 anime-gradient rounded-xl flex items-center justify-center">
                            <i class="fas fa-play text-white text-lg"></i>
                        </div>
                        <span class="text-2xl font-bold">动漫世界</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        分享优质动漫内容，传递积极向上的价值观。<br>
                        让每一个热爱动漫的人都能找到属于自己的感动。
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition-colors">
                            <i class="fab fa-weixin"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition-colors">
                            <i class="fab fa-weibo"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition-colors">
                            <i class="fab fa-qq"></i>
                        </a>
                    </div>
                </div>

                <!-- 快速链接 -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">快速链接</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors">首页</a></li>
                        <li><a href="anime_list.php" class="text-gray-400 hover:text-white transition-colors">动漫列表</a></li>
                        <li><a href="guestbook.php" class="text-gray-400 hover:text-white transition-colors">留言板</a></li>
                    </ul>
                </div>

                <!-- 分类导航 -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">动漫分类</h3>
                    <ul class="space-y-2">
                        <li><a href="anime_list.php?category=1" class="text-gray-400 hover:text-white transition-colors">热血</a></li>
                        <li><a href="anime_list.php?category=2" class="text-gray-400 hover:text-white transition-colors">治愈</a></li>
                        <li><a href="anime_list.php?category=3" class="text-gray-400 hover:text-white transition-colors">冒险</a></li>
                        <li><a href="anime_list.php?category=4" class="text-gray-400 hover:text-white transition-colors">奇幻</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> 动漫世界. 传递正能量，分享好动漫.
                </p>
                <p class="text-gray-500 text-sm mt-2 md:mt-0">
                    <i class="fas fa-heart text-red-500"></i> 热爱动漫，传递温暖
                </p>
            </div>
        </div>
    </footer>

    <!-- Toast 通知容器 -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <script>
        // Toast 通知函数
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle',
                warning: 'fa-exclamation-triangle'
            };

            toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2 fade-in`;
            toast.innerHTML = `<i class="fas ${icons[type]}"></i><span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // 确认删除对话框
        function confirmDelete(message = '确定要删除吗？此操作不可恢复。') {
            return confirm(message);
        }

        // 表单加载状态
        function setLoading(button, loading) {
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<div class="loading-spinner mx-auto"></div>';
            } else {
                button.disabled = false;
            }
        }
    </script>
</body>
</html>
