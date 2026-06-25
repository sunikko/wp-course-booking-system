<?php

/**
 * Template Name: Home Page
 */
get_header(); ?>

<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Unlock Your Potential with Our Classes</h1>
        <p>Browse schedules and book your preferred sessions with a single click.</p>
        <div class="hero-buttons">
            <a href="<?php echo home_url('/booking/'); ?>" class="btn-cta">📅 Book a Class</a>
            <?php if (!is_user_logged_in()): ?>
                <a href="<?php echo esc_url(home_url('/register')); ?>" class="btn-outline">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="features-section container">
    <h2 class="section-title">How It Works</h2>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">👤</div>
            <h3>1. Create an Account</h3>
            <p>Sign up in under a minute to activate your personal booking dashboard.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>2. Check Availability</h3>
            <p>View real-time course schedules and remaining capacity instantly.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">✅</div>
            <h3>3. Book Instantly</h3>
            <p>Secure your spot with one click. Our smart system prevents any schedule conflicts.</p>
        </div>
    </div>
</div>

<?php get_footer(); ?>