<!DOCTYPE html>
<html>

<head>
    <?php wp_head(); ?>
</head>

<body>

    <header>
        <h1><?php bloginfo('name'); ?></h1>
        <?php if (is_user_logged_in()): ?>
            <a href="<?php echo home_url('/my-bookings/'); ?>" class="nav-link">
                📅 My Bookings
            </a>
        <?php endif; ?>
    </header>