<?php get_header(); ?>

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

    // =========================
    // DUMMY DATA (STATE INCLUDED)
    // =========================
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

                <!-- TIME -->
                <div class="time-label">
                    <?php echo $time; ?>
                </div>

                <!-- DAYS -->
                <?php foreach ($days as $day): ?>

                    <div class="day-cell">

                        <?php if (isset($data[$time][$day])): ?>

                            <?php foreach ($data[$time][$day] as $class): ?>

                                <div class="class-card <?php echo $class['status']; ?>">

                                    <!-- TITLE -->
                                    <div class="class-header">
                                        <?php echo $class['subject']; ?>
                                    </div>

                                    <div class="class-teacher">
                                        <?php echo $class['teacher']; ?>
                                    </div>

                                    <!-- STATUS -->
                                    <?php if ($class['status'] === 'booked'): ?>
                                        <div class="badge booked">BOOKED</div>
                                    <?php elseif ($class['status'] === 'conflict'): ?>
                                        <div class="badge conflict">CONFLICT</div>
                                    <?php endif; ?>

                                    <!-- WEEKS -->
                                    <div class="week-options">

                                        <?php foreach ($class['weeks'] as $week): ?>

                                            <label class="week-option <?php echo $class['status'] !== 'available' ? 'disabled' : ''; ?>">

                                                <input type="radio"
                                                    name="<?php echo $time.'-'.$day.'-'.$class['subject']; ?>"
                                                    <?php echo $class['status'] !== 'available' ? 'disabled' : ''; ?>>

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

<!-- ===================== -->
<!-- LIVE UI SCRIPT -->
<!-- ===================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const cards = document.querySelectorAll('.class-card');
    const panel = document.querySelector('.selected-list');

    function updatePanel() {

        panel.innerHTML = '';

        document.querySelectorAll('.class-card.selected').forEach(card => {

            const title = card.querySelector('.class-header').innerText;
            const teacher = card.querySelector('.class-teacher').innerText;

            const div = document.createElement('div');
            div.className = 'selected-item';

            div.innerHTML = `
                <span>${title} - ${teacher}</span>
                <button class="remove-btn">Remove</button>
            `;

            panel.appendChild(div);

        });
    }

    cards.forEach(card => {

        if (card.classList.contains('booked') || card.classList.contains('conflict')) {
            return;
        }

        card.addEventListener('click', function() {

            this.classList.toggle('selected');
            updatePanel();

        });

    });

});
</script>

<?php get_footer(); ?>
