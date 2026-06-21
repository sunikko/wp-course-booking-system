<?php get_header(); ?>

<h1><?php the_title(); ?></h1>

<div>
    <p>Price: <?php echo get_field('price'); ?></p>
    <p>Capacity: <?php echo get_field('capacity'); ?></p>
    <p>Schedule: <?php echo get_field('schedule'); ?></p>
</div>

<hr>

<!--  BOOKING BUTTON -->
<form method="POST" action="">
    <input type="hidden" name="course_id" value="<?php echo get_the_ID(); ?>">

    <button type="submit" name="book_course">
        Book this course
    </button>
</form>

<?php get_footer(); ?>
