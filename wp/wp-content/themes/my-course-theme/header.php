<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right');
            bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <header class="site-header">
        <div class="header-container">
            <!-- 1. Logo -->
            <div class="site-branding">
                <a href="<?php echo home_url('/'); ?>" class="site-logo">🎓 EduBook</a>
            </div>

            <!-- 2. Mobile Nav Toggle -->
            <button class="mobile-nav-toggle" aria-controls="primary-navigation" aria-expanded="false">
                <span class="sr-only">Menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- 3. Main Navigation & Auth -->
            <div class="nav-wrapper">
                <nav class="site-navigation">
                    <ul class="nav-links">
                        <li><a href="<?php echo home_url('/'); ?>">Home</a></li>
                        <li><a href="<?php echo home_url('/booking/'); ?>">Booking</a></li>
                        <?php if (is_user_logged_in()): ?>
                            <li><a href="<?php echo home_url('/my-bookings/'); ?>">My Bookings</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="header-auth">
                    <?php if (is_user_logged_in()):
                        $current_user = wp_get_current_user();
                    ?>
                        <span class="user-greeting">👤 Hi, <?php echo esc_html($current_user->user_login); ?></span>
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn-nav btn-logout">Logout</a>
                    <?php else: ?>
                        <a href="#" class="btn-nav login-btn">Login</a>
                        <a href="<?php echo esc_url(home_url('/register')); ?>" class="btn-nav btn-signup">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Wrapper (Closed in footer.php) -->
    <main class="site-content">