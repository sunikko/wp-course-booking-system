</main> <?php if (!is_user_logged_in()): ?>
    <div id="auth-overlay" class="auth-overlay hidden"></div>
    <div id="login-modal" class="auth-modal hidden">
        <div class="auth-box">
            <h2>Login</h2>
            <div class="login-tip">
                <p><strong>Test Account:</strong> ID: <code>Test77</code>, PW: <code>Test77Password</code></p>
            </div>
            <?php wp_login_form([
                'label_username' => 'Username',
                'label_password' => 'Password',
                'label_log_in' => 'Login',
                'remember' => true,
                'redirect' => home_url('/booking/')
            ]); ?>
            <p>No account? <a href="<?php echo esc_url(home_url('/register')); ?>">Sign Up</a></p>
            <button class="close-modal">Close</button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loginModal = document.getElementById("login-modal");
            const overlay = document.getElementById("auth-overlay");
            const loginBtns = document.querySelectorAll(".login-btn");
            const closeBtns = document.querySelectorAll(".close-modal");

            if (loginModal && overlay) {
                function openModal(e) {
                    if (e) e.preventDefault();
                    loginModal.classList.remove("hidden");
                    overlay.classList.remove("hidden");
                }

                function closeModal() {
                    loginModal.classList.add("hidden");
                    overlay.classList.add("hidden");
                }

                loginBtns.forEach(btn => btn.addEventListener("click", openModal));
                closeBtns.forEach(btn => btn.addEventListener("click", closeModal));
                overlay.addEventListener("click", closeModal);
            }
        });
    </script>
<?php endif; ?>

<footer class="site-footer">
    <div class="footer-container">
        <p>&copy; <?php echo date('Y'); ?> EduBook Booking System. All rights reserved.</p>
    </div>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const navToggle = document.querySelector('.mobile-nav-toggle');
        const navWrapper = document.querySelector('.nav-wrapper');

        if (navToggle && navWrapper) {
            navToggle.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                navWrapper.classList.toggle('active');
            });
        }
    });
</script>

<?php wp_footer(); ?>
</body>

</html>