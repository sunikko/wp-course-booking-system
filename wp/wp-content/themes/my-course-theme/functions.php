<?php

function my_theme_setup()
{
    add_theme_support('title-tag');
}

add_action('after_setup_theme', 'my_theme_setup');


function register_courses_post_type()
{
    register_post_type('course', [
        'labels' => [
            'name' => 'Courses',
            'singular_name' => 'Course'
        ],
        'public' => true,
        'has_archive' => 'courses',
        'rewrite' => [
            'slug' => 'courses'
        ],
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-welcome-learn-more'
    ]);
}

add_action('init', 'register_courses_post_type');



function my_theme_assets()
{
    // ===== CSS =====
    wp_enqueue_style(
        'my-global-style',
        get_template_directory_uri() . '/assets/css/style.css',
        array(),
        '1.0.0'
    );
    if (is_front_page()) {
        wp_enqueue_style(
            'my-home-style',
            get_template_directory_uri() . '/assets/css/home.css',
            array(),
            '1.0.0'
        );
    }
    wp_enqueue_style(
        'booking-style',
        get_template_directory_uri() . '/assets/css/booking.css',
        array(),
        '1.0'
    );

    // ===== JS =====
    if (is_page('booking')) {
        wp_enqueue_script(
            'booking-js',
            get_template_directory_uri() . '/assets/js/booking.js',
            array(),
            '1.0',
            true // footer load (IMPORTANT)
        );

        // ===== wpData =====
        wp_localize_script('booking-js', 'wpData', array(
            'isLoggedIn' => is_user_logged_in(),
            'ajaxUrl' => admin_url('admin-ajax.php')
        ));
    }

    if (is_page('my-bookings')) { // slug 'my-bookings' page only
        // ===== CSS =====
        wp_enqueue_style(
            'my-bookings-style',
            get_template_directory_uri() . '/assets/css/my-bookings.css',
            array(),
            '1.0'
        );
        // ===== JS =====
        wp_enqueue_script(
            'my-bookings-js',
            get_template_directory_uri() . '/assets/js/my-bookings.js',
            array(),
            '1.0',
            true
        );
        // ===== wpData =====
        wp_localize_script('my-bookings-js', 'wpData', array(
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'loginUrl'  => wp_login_url(),
        ));
    }
}

add_action('wp_enqueue_scripts', 'my_theme_assets');
add_action('wp_ajax_submit_booking', 'submit_booking');


function submit_booking()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Login required']);
    }

    $user_id = get_current_user_id();
    $selected = isset($_POST['selected']) ? json_decode(stripslashes($_POST['selected']), true) : [];

    if (empty($selected)) {
        wp_send_json_error(['message' => 'No bookings selected']);
    }

    $errors = [];
    $success = [];

    foreach ($selected as $item) {
        $course_id = isset($item['course_id']) ? intval($item['course_id']) : 0;
        $booking_date = isset($item['booking_date']) ? sanitize_text_field($item['booking_date']) : '';
        $subject = sanitize_text_field($item['subject']);
        $teacher = sanitize_text_field($item['teacher']);

        if (!$course_id) {
            $errors[] = 'Course ID missing';
            continue;
        }

        if (empty($booking_date)) {
            $errors[] = "$subject - $teacher: Booking date missing";
            continue;
        }

        // Capacity check
        $capacity = intval(get_field('capacity', $course_id));
        if ($capacity <= 0) {
            $errors[] = "$subject - $teacher: No capacity left";
            continue;
        }

        // Duplicate check using booking_date only
        $existing = new WP_Query([
            'post_type'      => 'booking',
            'posts_per_page' => 1,
            'meta_query'     => [
                'relation' => 'AND',
                ['key' => 'course_id', 'value' => $course_id],
                ['key' => 'booking_date', 'value' => $booking_date],
                ['key' => 'user_id', 'value' => $user_id],
                ['key' => 'status', 'value' => 'confirmed', 'compare' => '='],
            ],
            'post_status' => 'publish',
        ]);

        if ($existing->have_posts()) {
            $errors[] = "$subject - $teacher: Already booked this date";
            continue;
        }

        // Save booking
        $booking_id = wp_insert_post([
            'post_title'  => $subject . ' - ' . $teacher,
            'post_type'   => 'booking',
            'post_status' => 'publish',
        ]);

        if (is_wp_error($booking_id) || !$booking_id) {
            $errors[] = "$subject - $teacher: Failed to save";
            continue;
        }

        // Save meta fields
        update_field('course_id', $course_id, $booking_id);
        update_field('booking_date', $booking_date, $booking_id);
        update_field('booked_at', current_time('mysql'), $booking_id);
        update_field('user_id', $user_id, $booking_id);
        update_field('status', 'confirmed', $booking_id);

        // Decrease capacity
        update_field('capacity', $capacity - 1, $course_id);

        $success[] = "$subject - $teacher booked successfully!";
    }

    if (!empty($errors)) {
        wp_send_json_error(['message' => implode(' ', $errors)]);
    } else {
        wp_send_json_success(['message' => 'All bookings saved!', 'booked' => $success]);
    }
}

// Register Booking CPT
function register_booking_post_type()
{
    register_post_type('booking', [
        'labels' => [
            'name'               => 'Bookings',
            'singular_name'      => 'Booking',
            'add_new'            => 'Add New Booking',
            'add_new_item'       => 'Add New Booking',
            'edit_item'          => 'Edit Booking',
            'new_item'           => 'New Booking',
            'view_item'          => 'View Booking',
            'search_items'       => 'Search Bookings',
            'not_found'          => 'No bookings found',
            'not_found_in_trash' => 'No bookings found in Trash',
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'supports'      => ['title', 'custom-fields'],
        'menu_icon'     => 'dashicons-calendar-alt',
        'capability_type' => 'post',
    ]);
}
add_action('init', 'register_booking_post_type');


function handle_cancel_booking()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in to cancel a booking.']);
    }

    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

    if ($booking_id <= 0) {
        wp_send_json_error(['message' => 'Invalid booking ID.']);
    }

    $current_user_id = get_current_user_id();
    $booked_user_id = intval(get_post_meta($booking_id, 'user_id', true));

    if ($current_user_id !== $booked_user_id) {
        wp_send_json_error(['message' => 'You do not have permission to cancel this booking.']);
    }

    $current_status = get_post_meta($booking_id, 'status', true);

    if ($current_status === 'cancelled') {
        wp_send_json_error(['message' => 'This booking is already cancelled.']);
    }

    // 1. Restore the course capacity
    $course_id = get_post_meta($booking_id, 'course_id', true);
    if ($course_id) {
        $capacity = intval(get_field('capacity', $course_id));
        update_field('capacity', $capacity + 1, $course_id);
    }

    // 2. Update booking status to 'cancelled'
    update_post_meta($booking_id, 'status', 'cancelled');
    update_field('status', 'cancelled', $booking_id);

    wp_send_json_success(['message' => 'Booking cancelled successfully.']);
}
add_action('wp_ajax_cancel_booking', 'handle_cancel_booking');


function edubook_custom_registration_form()
{
    if (is_user_logged_in()) {
        return '<p>You are already logged in.</p>';
    }

    $message = '';

    if (isset($_POST['edubook_register_nonce']) && wp_verify_nonce($_POST['edubook_register_nonce'], 'edubook_register_action')) {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        $errors = new WP_Error();

        if (empty($username) || empty($email) || empty($password)) {
            $errors->add('field', 'All fields are required.');
        }
        if (username_exists($username)) {
            $errors->add('user_name', 'Sorry, that username already exists!');
        }
        if (!is_email($email)) {
            $errors->add('email_invalid', 'Email is not valid.');
        }
        if (email_exists($email)) {
            $errors->add('email', 'Email is already in use.');
        }

        if (empty($errors->get_error_codes())) {
            $user_id = wp_create_user($username, $password, $email);
            if (!is_wp_error($user_id)) {
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                wp_redirect(home_url('/'));
                exit;
            } else {
                $message = '<p style="color:red;">Error creating user.</p>';
            }
        } else {
            $message = '<p style="color:red;">' . $errors->get_error_message() . '</p>';
        }
    }

    ob_start();
    echo $message;
?>
    <form method="POST" action="">
        <p>
            <label for="username">Username</label><br />
            <input type="text" name="username" id="username" required />
        </p>
        <p>
            <label for="email">Email</label><br />
            <input type="email" name="email" id="email" required />
        </p>
        <p>
            <label for="password">Password</label><br />
            <input type="password" name="password" id="password" required />
        </p>
        <?php wp_nonce_field('edubook_register_action', 'edubook_register_nonce'); ?>
        <p>
            <input type="submit" value="Sign Up" />
        </p>
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('edubook_register', 'edubook_custom_registration_form');


function remove_admin_bar_for_students()
{
    if (!current_user_can('manage_options')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'remove_admin_bar_for_students');

function edubook_trigger_demo_data_generation()
{
    if (isset($_GET['generate_demo']) && $_GET['generate_demo'] === 'now') {
        $now = current_time('timestamp');
        $today_numeric = intval(date('w', $now));
        $days_to_subtract = ($today_numeric === 0) ? 6 : ($today_numeric - 1);
        $this_monday_ts = strtotime("-{$days_to_subtract} days", strtotime(date('Y-m-d 00:00:00', $now)));

        $existing = new WP_Query([
            'post_type' => 'course',
            'posts_per_page' => -1,
        ]);
        while ($existing->have_posts()) {
            $existing->the_post();
            wp_delete_post(get_the_ID(), true);
        }
        wp_reset_postdata();

        $subjects = [
            'Science'     => ['teacher' => 'Dr. Kim', 'price' => 100, 'capacity' => 5],
            'Math'        => ['teacher' => 'Prof. Lee', 'price' => 120, 'capacity' => 4],
            'English'     => ['teacher' => 'Ms. Park', 'price' => 90, 'capacity' => 6],
            'Art'         => ['teacher' => 'Mr. Jung', 'price' => 80, 'capacity' => 8],
            'Music'       => ['teacher' => 'Ms. Choi', 'price' => 110, 'capacity' => 3],
            'History'     => ['teacher' => 'Dr. Yoon', 'price' => 95, 'capacity' => 5],
            'Programming' => ['teacher' => 'Mr. Kang', 'price' => 150, 'capacity' => 4],
        ];
        $subject_keys = array_keys($subjects);

        $times = ['10:00', '11:00', '12:00', '14:00', '15:00', '16:00'];

        $weeks_offsets = [0, 7];
        $total_created = 0;

        foreach ($weeks_offsets as $week_offset) {
            for ($day_offset = 0; $day_offset <= 5; $day_offset++) {

                $target_day_ts = strtotime("+ " . ($week_offset + $day_offset) . " days", $this_monday_ts);
                $date_str = date('Y-m-d', $target_day_ts);

                foreach ($times as $time) {
                    if (rand(1, 10) > 4) {

                        $classes_in_this_slot = rand(1, 2);

                        for ($i = 0; $i < $classes_in_this_slot; $i++) {
                            $random_subject = $subject_keys[array_rand($subject_keys)];
                            $info = $subjects[$random_subject];

                            $post_id = wp_insert_post([
                                'post_title'  => $random_subject . ' – ' . $info['teacher'],
                                'post_type'   => 'course',
                                'post_status' => 'publish'
                            ]);

                            if ($post_id) {
                                update_field('schedule', $date_str . ' ' . $time . ':00', $post_id);
                                update_field('capacity', $info['capacity'], $post_id);
                                update_field('price', $info['price'], $post_id);

                                $total_created++;
                            }
                        }
                    }
                }
            }
        }

        echo "✅  {$total_created} courses created";
        wp_die("✅ 성공!");
    }
}
add_action('init', 'edubook_trigger_demo_data_generation');
