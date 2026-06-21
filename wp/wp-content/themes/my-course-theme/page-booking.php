<?php get_header(); ?>

<div style="max-width:1000px; margin:0 auto; font-family:Arial;">

<h1>Course Booking Timetable</h1>

<?php
$courses = get_posts([
    'post_type' => 'course',
    'numberposts' => -1
]);
?>

<?php foreach ($courses as $course): ?>

    <div style="margin-bottom:40px; border:1px solid #ddd; padding:20px; border-radius:8px;">

        <h2 style="margin-bottom:10px;">
            <?php echo $course->post_title; ?>
        </h2>

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="text-align:left; padding:10px;">Date</th>
                    <th style="text-align:left;">Time</th>
                    <th style="text-align:left;">Capacity</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

            <?php
            $sessions = get_posts([
                'post_type' => 'session',
                'numberposts' => -1,
                'meta_key' => 'course_id',
                'meta_value' => $course->ID
            ]);
            ?>

            <?php foreach ($sessions as $s): ?>

                <?php $cap = get_field('capacity', $s->ID); ?>

                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:10px;">
                        <?php echo get_field('date', $s->ID); ?>
                    </td>

                    <td>
                        <?php echo get_field('time', $s->ID); ?>
                    </td>

                    <td>
                        <?php echo $cap; ?>
                    </td>

                    <td>
                        <?php if ($cap > 0): ?>
                            <form method="POST">
                                <input type="hidden" name="session_id" value="<?php echo $s->ID; ?>">
                                <button style="padding:6px 12px; background:#28a745; color:white; border:none;">
                                    Book
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color:red;">Full</span>
                        <?php endif; ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>
        </table>

    </div>

<?php endforeach; ?>

</div>

<?php get_footer(); ?>
