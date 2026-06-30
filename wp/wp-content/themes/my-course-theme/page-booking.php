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

<div class="container booking-container">
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
    $booking_data = get_booking_page_data();
    $days = $booking_data['days'];
    $weeks_to_show = $booking_data['weeks_to_show'];
    $data = $booking_data['data'];
    $times = $booking_data['times'];
    $my_booked_dates_by_course = $booking_data['my_booked_dates_by_course'];
    ?>

    <div class="calendar-wrapper">
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

    <!-- Mobile-Specific Calendar View -->
    <div class="calendar-mobile">
        <?php
        $has_classes_for_day = false;
        foreach ($days as $day):
            $day_has_content = false;
            ob_start(); // Start output buffering to check if any classes are outputted for the day
        ?>
            <?php foreach ($times as $time): ?>
                <?php if (isset($data[$time][$day])): ?>
                    <?php
                    $day_has_content = true;
                    foreach ($data[$time][$day] as $class):
                    ?>
                        <div class="mobile-class-card-wrapper">
                            <div class="mobile-time-label"><?php echo $time; ?></div>
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
                                            <input type="radio" class="week-radio <?php echo $is_this_date_booked ? 'is-checked' : ''; ?>" data-time="<?php echo $time; ?>" data-day="<?php echo $day; ?>" data-subject="<?php echo $class['subject']; ?>" data-teacher="<?php echo $class['teacher']; ?>" data-week="<?php echo $display_date; ?>" data-course-id="<?php echo $class['course_id'] ?? 0; ?>" data-booking-date="<?php echo $actual_date; ?>" name="week-<?php echo $time . '-' . $day . '-' . $class['subject'] . '-mobile'; ?>" <?php if ($is_this_date_booked): ?> checked style="cursor: pointer; accent-color: #28a745;" <?php elseif ($class['status'] !== 'available'): ?> disabled <?php endif; ?>>
                                            <span><?php echo $display_date; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php
            $day_output = ob_get_clean(); // Get buffered output
            if ($day_has_content):
                $has_classes_for_day = true;
            ?>
                <div class="mobile-day-block">
                    <h3 class="mobile-day-header"><?php echo $day; ?></h3>
                    <div class="mobile-day-content">
                        <?php echo $day_output; // Output the content for the day 
                        ?>
                    </div>
                </div>
        <?php
            endif;
        endforeach;
        ?>
    </div>
</div>
<?php get_footer(); ?>