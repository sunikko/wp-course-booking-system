<?php get_header(); ?>

<h1><?php the_title(); ?></h1>

<div>
    <?php the_content(); ?>
</div>

<p>
    Price:
    <?php echo get_field('price'); ?>
</p>

<p>
    Capacity:
    <?php echo get_field('capacity'); ?>
</p>

<p>
    Schedule:
    <?php echo get_field('schedule'); ?>
</p>

<?php get_footer(); ?>
