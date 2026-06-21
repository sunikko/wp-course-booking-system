<div class="auth-bar">

<?php if (is_user_logged_in()): ?>

    <div class="auth-right">

        <span class="auth-user">
            👤 <?php
                $user = wp_get_current_user();
                echo esc_html($user->user_login);
            ?>
        </span>

        <a class="auth-logout"
           href="<?php echo wp_logout_url(get_permalink()); ?>">
            Logout
        </a>

    </div>

<?php endif; ?>

</div>

<?php get_header(); ?>

<!-- ===================== -->
<!-- AUTH MODALS  -->
<!-- ===================== -->
<div id="auth-overlay" class="auth-overlay hidden"></div>

<div id="login-modal" class="auth-modal hidden">
    <div class="auth-box">
        <h2>Login</h2>
        <?php wp_login_form([
            'label_username' => 'Username',
            'label_password' => 'Password',
            'label_log_in' => 'Login',
            'remember' => true,
        ]); ?>
        <p>No account? <a href="<?php echo wp_registration_url(); ?>">Sign up</a></p>
        <button class="close-modal" data-close="login">Close</button>
    </div>
</div>

<div class="container">

<?php if (!is_user_logged_in()): ?>
    <div class="auth-login-box">
        <h3>🔒 Login Required</h3>
        <p><a href="#" class="login-btn">Login</a></p>
        <p>No account? <a href="<?php echo wp_registration_url(); ?>" target="_blank">Sign up</a></p>
    </div>
    </div>
    <?php get_footer(); ?>
    <?php return; ?>
<?php endif; ?>


<div class="container">

    <h1 class="page-title">Class Booking Timetable</h1>

    <!-- ===================== -->
    <!-- SELECTED PANEL -->
    <!-- ===================== -->
    <div class="selection-panel">

        <h2>Selected Bookings</h2>

        <div class="selected-list"></div>

        <div class="action-buttons">
            <button class="btn btn-primary">Book Selected</button>
            <button class="btn btn-secondary">Clear</button>
        </div>

    </div>

    <?php
    $days = ['Mon','Tue','Wed','Thu','Fri','Sat'];

    $now = current_time('timestamp');
    $today_numeric = intval(date('w', $now));

    $days_to_subtract = ($today_numeric === 0) ? 6 : ($today_numeric - 1);
    $this_monday_ts = strtotime("-{$days_to_subtract} days", strtotime(date('Y-m-d 00:00:00', $now)));

    $weeks_to_show = [
        date('d/m', $this_monday_ts),
        date('d/m', strtotime('+7 days', $this_monday_ts))
    ];

    $courses = new WP_Query([
        'post_type' => 'course',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    $current_user_id = get_current_user_id();
    $my_all_bookings = [];
    $my_booked_course_ids = [];

    $all_booked_query = new WP_Query([
        'post_type'      => 'booking',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            ['key' => 'user_id', 'value' => (string)$current_user_id, 'compare' => '=']
        ]
    ]);

    if ($all_booked_query->have_posts()) {
        while ($all_booked_query->have_posts()) {
            $all_booked_query->the_post();
            $b_post_id = get_the_ID();

            $b_course_id = get_field('course_id', $b_post_id);
            $b_day       = get_field('booking_day', $b_post_id);
            $b_time      = get_field('booking_time', $b_post_id);
            $b_week      = get_field('booking_week', $b_post_id);

            $final_course_id = 0;
            if (is_object($b_course_id) && isset($b_course_id->ID)) {
                $final_course_id = intval($b_course_id->ID);
            } elseif (is_array($b_course_id) && isset($b_course_id['ID'])) {
                $final_course_id = intval($b_course_id['ID']);
            } elseif (is_array($b_course_id) && !empty($b_course_id)) {
                $final_course_id = intval($b_course_id[0]);
            } else {
                $final_course_id = intval($b_course_id);
            }

            if ($final_course_id > 0) {
                $my_booked_course_ids[] = $final_course_id;
            }

            if (!empty($b_day) && !empty($b_time)) {
                $key = trim($b_day) . '-' . trim($b_time);
                $my_all_bookings[$key] = [
                    'course_id' => $final_course_id,
                    'week'      => $b_week ? $b_week : ''
                ];
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

        $day  = date('D', $timestamp);
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

        $slot_key = trim($day) . '-' . trim($time);
        $status = 'available';
        $booked_date_str = '';

        /* Check conflict status */
        if (!empty($my_all_bookings) && isset($my_all_bookings[$slot_key])) {
            $booked_info = $my_all_bookings[$slot_key];

            if (intval($booked_info['course_id']) === intval($course_id)) {
                $status = 'booked';
                $booked_date_str = "Booked (" . $booked_info['week'] . ")";
            } else {
                $status = 'disabled';
                $booked_date_str = "Time Conflict";
            }
        } elseif (in_array((int)$course_id, array_map('intval', $my_booked_course_ids))) {
            $status = 'course-conflict';
            $booked_date_str = "Already Booked This Course";
        }

        $capacity = intval(get_field('capacity', $course_id));
        $price = get_field('price', $course_id);

        $data[$time][$day][] = [
            'course_id'       => $course_id,
            'subject'         => get_the_title(),
            'teacher'         => 'Instructor',
            'weeks'           => $weeks_to_show,
            'status'          => $status,
            'capacity'        => $capacity,
            'price'           => $price,
            'booked_date_str' => $booked_date_str,
        ];
    }
    wp_reset_postdata();

    sort($detected_times);
    $times = !empty($detected_times) ? $detected_times : ['10:00', '11:00', '12:00', '14:00'];
    ?>

    <!-- ===================== -->
    <!-- CALENDAR -->
    <!-- ===================== -->
    <div class="calendar">

        <!-- HEADER -->
        <div class="calendar-header">
            <div class="time-col">Time</div>

            <?php foreach ($days as $day): ?>
                <div class="day-header"><?php echo $day; ?></div>
            <?php endforeach; ?>
        </div>

        <!-- BODY -->
        <?php foreach ($times as $time): ?>

            <div class="time-row">

                <div class="time-label">
                    <?php echo $time; ?>
                </div>

                <?php foreach ($days as $day): ?>

                    <div class="day-cell">

                        <?php if (isset($data[$time][$day])): ?>

                            <?php foreach ($data[$time][$day] as $class): ?>
                                <?php
                                    $card_style = '';
                                    if ($class['status'] === 'booked') {
                                        $card_style = 'border: 2px solid #28a745; background-color: #f4fbf6;';
                                    } elseif ($class['status'] === 'course-conflict') {
                                        $card_style = 'border: 2px solid #ff9800; background-color: #fff9e6; opacity: 0.7;';
                                    } elseif ($class['status'] === 'disabled') {
                                        $card_style = 'border: 1px solid #eeeeee; background-color: #eeeeee; opacity: 0.5;';
                                    }
                                ?>
                                <div class="class-card <?php echo $class['status']; ?>"

                                    data-time="<?php echo $time; ?>"
                                    data-day="<?php echo $day; ?>"
                                    data-subject="<?php echo $class['subject']; ?>"
                                    data-teacher="<?php echo $class['teacher']; ?>"
                                    data-course-id="<?php echo $class['course_id'] ?? 0; ?>"
                                    data-weeks="<?php echo htmlspecialchars(json_encode($class['weeks'])); ?>"

                                    style="<?php echo $card_style; ?>"
                                >

                                    <div class="class-header">
                                        <?php echo $class['subject']; ?>
                                    </div>

                                    <div class="class-teacher">
                                        <?php echo $class['teacher']; ?>
                                    </div>

                                    <?php if ($class['status'] === 'booked'): ?>
                                        <div class="badge booked">BOOKED</div>
                                    <?php elseif ($class['status'] === 'conflict'): ?>
                                        <div class="badge conflict">CONFLICT</div>
                                    <?php endif; ?>

                                    <div class="week-options">

                                        <?php foreach ($class['weeks'] as $week): ?>

                                            <label class="week-option <?php echo $class['status'] !== 'available' ? 'disabled' : ''; ?>">

                                                <input
                                                    type="radio"
                                                    class="week-radio"

                                                    data-time="<?php echo $time; ?>"
                                                    data-day="<?php echo $day; ?>"
                                                    data-subject="<?php echo $class['subject']; ?>"
                                                    data-teacher="<?php echo $class['teacher']; ?>"
                                                    data-week="<?php echo $week; ?>"
                                                    data-course-id="<?php echo $class['course_id'] ?? 0; ?>"

                                                    name="week-<?php echo $time.'-'.$day.'-'.$class['subject']; ?>"

                                                    <?php if ($class['status'] === 'booked'): ?>
                                                        checked
                                                        style="pointer-events: none; cursor: not-allowed; accent-color: #28a745;"
                                                    <?php elseif ($class['status'] === 'disabled'): ?>
                                                        disabled
                                                    <?php endif; ?>
                                                >

                                                <span><?php echo $week; ?></span>

                                            </label>

                                        <?php endforeach; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="no-class">No class</div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php get_footer(); ?>
