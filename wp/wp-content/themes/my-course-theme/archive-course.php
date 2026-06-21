<?php get_header(); ?>

<h1>Courses</h1>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>

        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <h2>
                <a href="<?php the_permalink(); ?>">
                    <?php the_title(); ?>
                </a>
            </h2>

            <p>
                Price: <?php echo get_field('price'); ?>
            </p>

            <p>
                Capacity: <?php echo get_field('capacity'); ?>
            </p>

        </div>

    <?php endwhile; ?>
<?php else: ?>
    <p>No courses found</p>
<?php endif; ?>

<?php get_footer(); ?>
