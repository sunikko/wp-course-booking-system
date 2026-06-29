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

    // Query updated to order by the new 'booking_date' meta key
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
        'meta_key' => 'booking_date',
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
                        $booking_date = get_post_meta($booking_id, 'booking_date', true);

                        $course_title = $course_id ? get_the_title($course_id) : 'Deleted Course';

                        // Extract time dynamically from the course schedule
                        $schedule_raw = get_field('schedule', $course_id);
                        $display_time = 'N/A';
                        if ($schedule_raw) {
                            $display_time = date('H:i', strtotime($schedule_raw));
                        }

                        // Format the new Y-m-d booking_date
                        $display_date = 'N/A';
                        if ($booking_date) {
                            $display_date = date('d/m/Y (D)', strtotime($booking_date));
                        }
                    ?>
                        <tr>
                            <td data-label="Course"><strong><?php echo esc_html($course_title); ?></strong></td>
                            <td data-label="Date"><?php echo esc_html($display_date); ?></td>
                            <td data-label="Time"><?php echo esc_html($display_time); ?></td>
                            <td data-label="Status">
                                <span class="status-badge <?php echo esc_attr($status); ?>">
                                    <?php echo $status === 'confirmed' ? '✅ Confirmed' : '❌ Cancelled'; ?>
                                </span>
                            </td>
                            <td data-label="Action">
                                <?php if ($status === 'confirmed'): ?>
                                    <button class="cancel-booking-btn" data-booking-id="<?php echo esc_attr($booking_id); ?>">
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