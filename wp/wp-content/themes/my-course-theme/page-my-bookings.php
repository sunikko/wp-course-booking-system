<?php

/**
 * Template Name: My Bookings
 */

get_header();
?>

<div class="container my-bookings-page">

    <?php if (!is_user_logged_in()): ?>
        <div class="auth-login-box">
            <h3>🔒 Login Required</h3>
            <p><a href="#" class="login-btn">Login</a></p>
        </div>
        <?php get_footer(); ?>
        <?php return; ?>
    <?php endif; ?>

    <div class="page-header">
        <h1>📅 My Bookings</h1>
        <a href="<?php echo home_url('/booking/'); ?>" class="back-link">← Back to Booking</a>
    </div>

    <?php
    $user_id = get_current_user_id();

    $my_bookings = new WP_Query([
        'post_type' => 'booking',
        'meta_query' => [
            [
                'key' => 'user_id',
                'value' => $user_id,
                'compare' => '='
            ],
            [
                'key' => 'status',
                'value' => 'confirmed',
                'compare' => '='
            ]
        ],
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => 'booking_week',
        'order' => 'ASC'
    ]);

    if ($my_bookings->have_posts()):
    ?>
        <div class="bookings-table-wrapper">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($my_bookings->have_posts()):
                        $my_bookings->the_post();
                        $booking_id = get_the_ID();
                        $course_id = get_post_meta($booking_id, 'course_id', true);
                        $status = get_post_meta($booking_id, 'status', true);

                        $booking_day = get_post_meta($booking_id, 'booking_day', true);
                        $booking_time = get_post_meta($booking_id, 'booking_time', true);
                        $booking_week = get_post_meta($booking_id, 'booking_week', true);

                        $course_title = $course_id ? get_the_title($course_id) : 'Deleted Course';

                        $display_date = $booking_week . ' (' . $booking_day . ')';
                        $display_time = $booking_time ?: 'N/A';
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html($course_title); ?></strong></td>
                            <td><?php echo esc_html($display_date); ?></td>
                            <td><?php echo esc_html($display_time); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status; ?>">
                                    <?php echo $status === 'confirmed' ? '✅ Confirmed' : '❌ Cancelled'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($status === 'confirmed'): ?>
                                    <button class="cancel-booking-btn" data-booking-id="<?php echo $booking_id; ?>">
                                        ❌ Cancel
                                    </button>
                                <?php else: ?>
                                    <span class="cancelled-label">Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="bookings-summary">
            <p>Total: <?php echo $my_bookings->found_posts; ?> confirmed bookings</p>
        </div>
    <?php else: ?>
        <div class="no-bookings">
            <p>😅 You have no confirmed bookings yet.</p>
            <p><a href="<?php echo home_url('/booking/'); ?>" class="btn btn-primary">Browse Courses</a></p>
        </div>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>

</div>

<?php get_footer(); ?>