        </main>
        <!-- End Main Content Area -->

        <footer class="py-3 px-4 border-top border-secondary text-center text-muted fs-7 bg-dark bg-opacity-50 mt-auto">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div>
                    &copy; <?php echo date('Y'); ?> <strong class="text-gold" style="color: var(--gg-gold);">Glamour Gems</strong>. All Rights Reserved.
                </div>
                <div>
                    Powered by High Precision Jewelry Commerce Engine
                </div>
            </div>
        </footer>
    </div>
    <!-- End Content Wrapper -->
</div>
<!-- End Outer Wrapper -->

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Sidebar Toggle & Interactive Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggleBtn && sidebar) {
        sidebarToggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

</body>
</html>
