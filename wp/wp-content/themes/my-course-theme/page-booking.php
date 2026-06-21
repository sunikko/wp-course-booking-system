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

<div class="container">

<?php if (!is_user_logged_in()): ?>

 <div class="auth-login-box">

        <h3>🔒 Login</h3>

        <?php wp_login_form([
            'label_username' => 'Username',
            'label_password' => 'Password',
            'label_remember' => 'Remember Me',
            'label_log_in' => 'Login'
        ]); ?>

        <p class="auth-register">
            No account?
            <a href="<?php echo wp_registration_url(); ?>" target="_blank">
                Sign up
            </a>
        </p>

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
    $times = ['10:00','11:00','12:00','14:00'];

    $data = [
        '10:00' => [
            'Mon' => [
                [
                    'subject' => 'Science',
                    'teacher' => 'Jane',
                    'weeks' => ['09/03','16/03'],
                    'status' => 'booked'
                ],
                [
                    'subject' => 'Math',
                    'teacher' => 'Tom',
                    'weeks' => ['09/03','16/03'],
                    'status' => 'conflict'
                ]
            ],
            'Tue' => [
                [
                    'subject' => 'English',
                    'teacher' => 'Chris',
                    'weeks' => ['10/03','17/03'],
                    'status' => 'available'
                ]
            ]
        ],
        '11:00' => [
            'Mon' => [
                [
                    'subject' => 'Math',
                    'teacher' => 'Jake',
                    'weeks' => ['09/03','16/03'],
                    'status' => 'available'
                ]
            ],
            'Wed' => [
                [
                    'subject' => 'Science',
                    'teacher' => 'Anna',
                    'weeks' => ['11/03','18/03'],
                    'status' => 'available'
                ],
                [
                    'subject' => 'Math',
                    'teacher' => 'Tom',
                    'weeks' => ['11/03','18/03'],
                    'status' => 'conflict'
                ]
            ]
        ]
    ];
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

                                <div class="class-card <?php echo $class['status']; ?>"

                                    data-time="<?php echo $time; ?>"
                                    data-day="<?php echo $day; ?>"
                                    data-subject="<?php echo $class['subject']; ?>"
                                    data-teacher="<?php echo $class['teacher']; ?>"
                                    data-weeks="<?php echo htmlspecialchars(json_encode($class['weeks'])); ?>"
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

                                                    name="week-<?php echo $time.'-'.$day.'-'.$class['subject']; ?>"

                                                    <?php echo $class['status'] !== 'available' ? 'disabled' : ''; ?>
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
