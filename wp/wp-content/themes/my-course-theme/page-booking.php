<?php get_header(); ?>

<div class="container">

    <h1>Class Booking Timetable</h1>

    <div class="selection-panel">
        <h2>Selected Bookings:</h2>

        <div class="selected-item">
            <span>Science (Jane's Class) - 09/03/2026</span>
            <button class="remove-btn">Remove</button>
        </div>

        <div class="action-buttons">
            <button class="btn btn-primary">Book Selected</button>
            <button class="btn btn-secondary">Cancel All</button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
            </tr>
        </thead>

        <tbody>

        <?php
        $times = ['10:00','11:00','14:00','16:00'];
        $days = ['Mon','Tue','Wed','Thu','Fri','Sat'];

        foreach ($times as $time): ?>
            <tr>

                <td>
                    <div class="time-label"><?php echo $time; ?></div>
                </td>

                <?php foreach ($days as $day): ?>

                    <?php $type = rand(0,2); ?>

                    <td>

                        <?php if ($type == 0): ?>

                            <div class="no-class">No class</div>

                        <?php elseif ($type == 1): ?>

                            <div class="class-card">
                                <div class="class-header">Science</div>
                                <div class="class-teacher">Jane's Class</div>
                                <div class="status-message booked">BOOKED</div>
                            </div>

                        <?php else: ?>

                            <div class="class-card">
                                <div class="class-header">Math</div>
                                <div class="class-teacher">Tom's Class</div>
                                <div class="status-message conflict">CONFLICT</div>
                            </div>

                        <?php endif; ?>

                    </td>

                <?php endforeach; ?>

            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

</div>

<?php get_footer(); ?>
