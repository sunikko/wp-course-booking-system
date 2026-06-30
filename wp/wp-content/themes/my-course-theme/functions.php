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
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('booking_nonce')
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
            'nonce' => wp_create_nonce('booking_nonce')
        ));
    }
}

add_action('wp_enqueue_scripts', 'my_theme_assets');
add_action('wp_ajax_submit_booking', 'submit_booking');

/**
 * Atomic capacity decrease
 */
function decrease_course_capacity($course_id)
{
    global $wpdb;
    $result = $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->postmeta} 
         SET meta_value = meta_value - 1 
         WHERE post_id = %d 
         AND meta_key = 'capacity' 
         AND meta_value > 0",
        $course_id
    ));
    return $result > 0;
}

/**
 * Atomic capacity increase
 */
function increase_course_capacity($course_id)
{
    global $wpdb;
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->postmeta} 
         SET meta_value = meta_value + 1 
         WHERE post_id = %d 
         AND meta_key = 'capacity'",
        $course_id
    ));
}



/**
 * Get booking page data
 */
function get_booking_page_data()
{
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $day_offsets = ['Mon' => 0, 'Tue' => 1, 'Wed' => 2, 'Thu' => 3, 'Fri' => 4, 'Sat' => 5, 'Sun' => 6];

    $now = current_time('timestamp');
    $today_numeric = intval(date('w', $now));

    $days_to_subtract = ($today_numeric === 0) ? 6 : ($today_numeric - 1);
    $this_monday_ts = strtotime("-{$days_to_subtract} days", strtotime(date('Y-m-d 00:00:00', $now)));
    $next_monday_ts = strtotime('+7 days', $this_monday_ts);

    $weeks_to_show = [
        date('d/m', $this_monday_ts),
        date('d/m', $next_monday_ts)
    ];

    $courses = new WP_Query([
        'post_type' => 'course',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    $current_user_id = get_current_user_id();
    $my_booked_course_ids = [];
    $my_booked_dates_by_course = [];
    $my_all_bookings = [];

    $all_booked_query = new WP_Query([
        'post_type'      => 'booking',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            'relation' => 'AND',
            ['key' => 'user_id', 'value' => (string)$current_user_id, 'compare' => '='],
            ['key' => 'status', 'value' => 'confirmed', 'compare' => '='],
        ],
    ]);

    if ($all_booked_query->have_posts()) {
        while ($all_booked_query->have_posts()) {
            $all_booked_query->the_post();
            $b_post_id = get_the_ID();
            $b_course_id = get_field('course_id', $b_post_id);
            $b_date = get_field('booking_date', $b_post_id);

            // Handle different ACF return formats
            if (is_object($b_course_id) && isset($b_course_id->ID)) {
                $final_course_id = intval($b_course_id->ID);
            } elseif (is_array($b_course_id)) {
                if (isset($b_course_id['ID'])) {
                    $final_course_id = intval($b_course_id['ID']);
                } elseif (!empty($b_course_id)) {
                    $final_course_id = intval($b_course_id[0]);
                } else {
                    $final_course_id = 0;
                }
            } else {
                $final_course_id = intval($b_course_id);
            }

            if ($final_course_id > 0) {
                $my_booked_course_ids[] = $final_course_id;

                if (!empty($b_date)) {
                    $b_ymd = date('Y-m-d', strtotime($b_date));
                    $b_day = date('D', strtotime($b_date));
                    $my_booked_dates_by_course[$final_course_id][] = $b_ymd;

                    $c_schedule = get_field('schedule', $final_course_id);
                    if ($c_schedule) {
                        $b_time = date('H:i', strtotime($c_schedule));
                        $key = trim($b_day) . '-' . trim($b_time);
                        $my_all_bookings[$key] = [
                            'course_id' => $final_course_id,
                            'date'      => $b_ymd,
                        ];
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    $my_booked_course_ids = array_unique($my_booked_course_ids);
    $data = [];
    $detected_times = [];

    while ($courses->have_posts()) {
        $courses->the_post();
        $course_id = get_the_ID();

        $schedule_raw = get_field('schedule', $course_id);
        if (!$schedule_raw) continue;

        $timestamp = strtotime($schedule_raw);
        if (!$timestamp) continue;

        $day = date('D', $timestamp);
        $time = date('H:i', $timestamp);

        $c_today = intval(date('w', $timestamp));
        $c_sub = ($c_today === 0) ? 6 : ($c_today - 1);
        $course_monday = date('d/m', strtotime("-{$c_sub} days", strtotime(date('Y-m-d 00:00:00', $timestamp))));

        if (!in_array($course_monday, $weeks_to_show)) {
            continue;
        }

        if (!in_array($time, $detected_times)) {
            $detected_times[] = $time;
        }

        $offset = $day_offsets[$day] ?? 0;
        $mapped_weeks = [
            date('d/m', $this_monday_ts) => date('Y-m-d', strtotime("+{$offset} days", $this_monday_ts)),
            date('d/m', $next_monday_ts) => date('Y-m-d', strtotime("+{$offset} days", $next_monday_ts)),
        ];

        $slot_key = trim($day) . '-' . trim($time);
        $status = 'available';
        $booked_date_str = '';
        $capacity = intval(get_field('capacity', $course_id));

        if (!empty($my_all_bookings) && isset($my_all_bookings[$slot_key])) {
            $booked_info = $my_all_bookings[$slot_key];
            if (intval($booked_info['course_id']) === intval($course_id)) {
                $status = 'booked';
                $booked_date_str = 'Booked';
            } else {
                $status = 'disabled';
                $booked_date_str = 'Time Conflict';
            }
        } elseif (in_array((int)$course_id, array_map('intval', $my_booked_course_ids))) {
            $status = 'course-conflict';
            $booked_date_str = 'Already Booked This Course';
        } elseif ($capacity <= 0) {
            $status = 'fully-booked';
            $booked_date_str = 'Fully Booked';
        }

        $price = get_field('price', $course_id);

        $data[$time][$day][] = [
            'course_id'       => $course_id,
            'subject'         => get_the_title(),
            'teacher'         => 'Instructor',
            'weeks'           => $weeks_to_show,
            'mapped_weeks'    => $mapped_weeks,
            'status'          => $status,
            'capacity'        => $capacity,
            'price'           => $price,
            'booked_date_str' => $booked_date_str,
        ];
    }
    wp_reset_postdata();

    sort($detected_times);
    $times = !empty($detected_times) ? $detected_times : ['10:00', '11:00', '12:00', '14:00'];

    return [
        'days'                    => $days,
        'weeks_to_show'           => $weeks_to_show,
        'data'                    => $data,
        'times'                   => $times,
        'my_booked_dates_by_course' => $my_booked_dates_by_course,
    ];
}


function submit_booking()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Login required']);
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'booking_nonce')) {
        wp_send_json_error(['message' => 'Invalid request']);
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

        // Duplicate check using booking date only
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

        // Atomic capacity decrease
        if (!decrease_course_capacity($course_id)) {
            $errors[] = "$subject - $teacher: No capacity left";
            wp_delete_post($booking_id, true);
            continue;
        }

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

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'booking_nonce')) {
        wp_send_json_error(['message' => 'Invalid request']);
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
        increase_course_capacity($course_id);
    }

    // 2. Update booking status to 'cancelled'
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
