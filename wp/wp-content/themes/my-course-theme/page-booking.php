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

                                                <input type="radio"
                                                    name="week-<?php echo $time.'-'.$day.'-'.$class['subject']; ?>"
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
<!-- JS (STATE-BASED SYSTEM) -->
<!-- ===================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    let selected = [];

    const cards = document.querySelectorAll('.class-card');
    const panel = document.querySelector('.selected-list');

    function getId(card) {
        return `${card.dataset.time}-${card.dataset.day}-${card.dataset.subject}`;
    }

    function renderPanel() {

        panel.innerHTML = '';

        selected.forEach(item => {

            const div = document.createElement('div');
            div.className = 'selected-item';

            div.innerHTML = `
                <span>${item.subject} - ${item.teacher} (${item.time}, ${item.day})</span>
                <button class="remove-btn" data-id="${item.id}">Remove</button>
            `;

            panel.appendChild(div);
        });
    }

    function addItem(card) {

        selected.push({
            id: getId(card),
            time: card.dataset.time,
            day: card.dataset.day,
            subject: card.dataset.subject,
            teacher: card.dataset.teacher
        });
    }

    function removeItem(card) {

        const id = getId(card);
        selected = selected.filter(i => i.id !== id);
    }

    cards.forEach(card => {

        if (card.classList.contains('booked') || card.classList.contains('conflict')) {
            return;
        }

        card.addEventListener('click', function () {

            const id = getId(this);
            const exists = selected.find(i => i.id === id);

            if (exists) {
                this.classList.remove('selected');
                removeItem(this);
            } else {
                this.classList.add('selected');
                addItem(this);
            }

            renderPanel();
        });

    });

});
</script>

<?php get_footer(); ?>
