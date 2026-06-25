<?php get_header(); ?>
<div class="container">
    <?php if (!is_user_logged_in()): ?>
        <div class="auth-login-box">
            <h3>🔒 Login Required</h3>
            <p><a href="#" class="login-btn">Login</a></p>
            <p>No account? <a href="<?php echo esc_url(home_url('/register')); ?>" target="_blank">Sign Up</a></p>
        </div>
</div>
<?php get_footer(); ?>
<?php return; ?>
<?php endif; ?>

<div class="container">
    <h1 class="page-title">Class Booking Timetable</h1>

    <div class="selection-panel">
        <h2>Selected Bookings</h2>
        <div class="selected-list"></div>
        <div class="action-buttons">
            <button class="btn btn-primary">Book Selected</button>
            <button class="btn btn-secondary">Clear</button>
        </div>
    </div>

    <?php
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
    $my_all_bookings = [];
    $my_booked_course_ids = [];
    $my_booked_dates_by_course = [];

    $all_booked_query = new WP_Query([
        'post_type'      => 'booking',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            'relation' => 'AND',
            ['key' => 'user_id', 'value' => (string)$current_user_id, 'compare' => '='],
            ['key' => 'status', 'value' => 'confirmed', 'compare' => '=']
        ]
    ]);

    if ($all_booked_query->have_posts()) {
        while ($all_booked_query->have_posts()) {
            $all_booked_query->the_post();
            $b_post_id = get_the_ID();
            $b_course_id = get_field('course_id', $b_post_id);
            $b_date = get_field('booking_date', $b_post_id);

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
                            'date'      => $b_ymd
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

    // --- Course Data Loop ---
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

        $offset = $day_offsets[$day] ?? 0;
        $mapped_weeks = [
            date('d/m', $this_monday_ts) => date('Y-m-d', strtotime("+{$offset} days", $this_monday_ts)),
            date('d/m', $next_monday_ts) => date('Y-m-d', strtotime("+{$offset} days", $next_monday_ts))
        ];

        $slot_key = trim($day) . '-' . trim($time);
        $status = 'available';
        $booked_date_str = '';
        $capacity = intval(get_field('capacity', $course_id));

        // PRIORITY 1 & 2: Check personal bookings and conflicts first!
        if (!empty($my_all_bookings) && isset($my_all_bookings[$slot_key])) {
            $booked_info = $my_all_bookings[$slot_key];
            if (intval($booked_info['course_id']) === intval($course_id)) {
                $status = 'booked';
                $booked_date_str = "Booked";
            } else {
                $status = 'disabled';
                $booked_date_str = "Time Conflict";
            }
        } elseif (in_array((int)$course_id, array_map('intval', $my_booked_course_ids))) {
            $status = 'course-conflict';
            $booked_date_str = "Already Booked This Course";
        }
        // PRIORITY 3: Only check global capacity if it's not already booked/conflicted by you
        elseif ($capacity <= 0) {
            $status = 'fully-booked';
            $booked_date_str = "Fully Booked";
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
    ?>

    <div class="calendar">
        <div class="calendar-header">
            <div class="time-col">Time</div>
            <?php foreach ($days as $day): ?>
                <div class="day-header"><?php echo $day; ?></div>
            <?php endforeach; ?>
        </div>

        <?php foreach ($times as $time): ?>
            <div class="time-row">
                <div class="time-label"><?php echo $time; ?></div>

                <?php foreach ($days as $day): ?>
                    <div class="day-cell">
                        <?php if (isset($data[$time][$day])): ?>
                            <?php foreach ($data[$time][$day] as $class): ?>
                                <div class="class-card <?php echo $class['status']; ?> subject-<?php echo sanitize_title($class['subject']); ?>"
                                    data-time="<?php echo $time; ?>"
                                    data-day="<?php echo $day; ?>"
                                    data-subject="<?php echo $class['subject']; ?>"
                                    data-teacher="<?php echo $class['teacher']; ?>"
                                    data-course-id="<?php echo $class['course_id'] ?? 0; ?>"
                                    data-weeks="<?php echo htmlspecialchars(json_encode($class['weeks'])); ?>">

                                    <div class="class-header"><?php echo $class['subject']; ?></div>
                                    <div class="class-teacher"><?php echo $class['teacher']; ?></div>

                                    <?php if ($class['status'] === 'booked'): ?>
                                        <div class="badge booked">BOOKED</div>
                                    <?php elseif ($class['status'] === 'fully-booked'): ?>
                                        <div class="badge conflict" style="background-color: #dc3545; border-color: #dc3545; color: white;">FULL</div>
                                    <?php elseif ($class['status'] === 'course-conflict' || $class['status'] === 'disabled'): ?>
                                        <div class="badge conflict">CONFLICT</div>
                                    <?php endif; ?>

                                    <div class="week-options">
                                        <?php foreach ($class['weeks'] as $week): ?>
                                            <?php
                                            $actual_date = $class['mapped_weeks'][$week];
                                            $display_date = date('d/m', strtotime($actual_date));

                                            $is_this_date_booked = isset($my_booked_dates_by_course[$class['course_id']]) && in_array($actual_date, $my_booked_dates_by_course[$class['course_id']]);
                                            ?>

                                            <label class="week-option <?php echo ($class['status'] !== 'available' && !$is_this_date_booked) ? 'disabled' : ''; ?>">
                                                <input
                                                    type="radio"
                                                    class="week-radio <?php echo $is_this_date_booked ? 'is-checked' : ''; ?>"
                                                    data-time="<?php echo $time; ?>"
                                                    data-day="<?php echo $day; ?>"
                                                    data-subject="<?php echo $class['subject']; ?>"
                                                    data-teacher="<?php echo $class['teacher']; ?>"
                                                    data-week="<?php echo $display_date; ?>"
                                                    data-course-id="<?php echo $class['course_id'] ?? 0; ?>"
                                                    data-booking-date="<?php echo $actual_date; ?>"
                                                    name="week-<?php echo $time . '-' . $day . '-' . $class['subject']; ?>"

                                                    <?php if ($is_this_date_booked): ?>
                                                    checked
                                                    style="cursor: pointer; accent-color: #28a745;"
                                                    <?php elseif ($class['status'] !== 'available'): ?>
                                                    disabled
                                                    <?php endif; ?>>
                                                <span><?php echo $display_date; ?></span>
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