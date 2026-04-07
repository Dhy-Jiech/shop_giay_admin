<!-- app/Views/admin/layouts/footer.php -->
        </div>
        <!-- Content Area end -->
    </main>
    <script>
        // JS Active menu state
        document.addEventListener('DOMContentLoaded', () => {
            const currentUrl = window.location.href;
            document.querySelectorAll('.nav-item').forEach(link => {
                if (currentUrl.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
