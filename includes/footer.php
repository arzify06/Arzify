<?php
/**
 * Desire Travel - Global Footer Template
 */
?>
        </main>

        <!-- Global Footer -->
        <footer class="mt-auto py-3 px-4 bg-white border-top text-center text-muted small">
            <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div>
                    <strong><?= htmlspecialchars(APP_NAME) ?></strong> &copy; <?= date('Y') ?>. <?= _e('copyright') ?>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light text-secondary border">v<?= htmlspecialchars(APP_VERSION) ?></span>
                    <a href="https://github.com" target="_blank" class="text-decoration-none text-muted">
                        <i class="bi bi-github"></i> GitHub
                    </a>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap 5 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>

</body>
</html>
