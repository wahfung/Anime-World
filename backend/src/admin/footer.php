        </main>

        <!-- 页脚 -->
        <footer class="bg-white border-t border-gray-100 py-4 px-6">
            <div class="text-center text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> 动漫世界管理后台 · 传递正能量，分享好动漫
            </div>
        </footer>
    </div>

    <!-- Toast 容器 -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <script>
        // Toast 通知
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };

            toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2`;
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i><span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // 确认删除
        function confirmDelete(message = '确定要删除吗？此操作不可恢复。') {
            return confirm(message);
        }
    </script>
</body>
</html>
