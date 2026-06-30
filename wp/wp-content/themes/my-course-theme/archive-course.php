<?php get_header(); ?>

<div class="archive-header">
    <h1>Find Your Perfect Course</h1>
    <p>Discover and book amazing courses taught by expert instructors</p>
</div>

<div class="archive-container">
    <div class="archive-filters">
        <div class="filter-group">
            <label for="filter-subject">Subject:</label>
            <select id="filter-subject" name="subject">
                <option value="all">All Subjects</option>
                <!-- JavaScript will populate the options -->
            </select>
        </div>

        <div class="filter-group">
            <label for="sort-by">Sort By:</label>
            <select id="sort-by" name="sort">
                <option value="date">Newest First</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="capacity">Availability</option>
            </select>
        </div>
    </div>

    <div class="courses-grid" id="courses-grid">
        <?php
        // Force a new query to get ALL courses
        $custom_query = new WP_Query(array(
            'post_type' => 'course',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'suppress_filters' => true
        ));

        if ($custom_query->have_posts()) : ?>
            <?php while ($custom_query->have_posts()) : $custom_query->the_post(); ?>
                <?php
                $course_id = get_the_ID();
                $price = get_field('price', $course_id);
                $capacity = get_field('capacity', $course_id);
                $schedule = get_field('schedule', $course_id);

                // Extract subject from title (assuming format "Subject - Teacher")
                $title_parts = explode(' - ', get_the_title());
                $subject = $title_parts[0] ?? get_the_title();
                $teacher = $title_parts[1] ?? 'Instructor';

                // Determine capacity status
                if ($capacity <= 0) {
                    $capacity_class = 'full';
                    $capacity_text = 'Fully Booked';
                } elseif ($capacity <= 2) {
                    $capacity_class = 'limited';
                    $capacity_text = "Only {$capacity} left!";
                } else {
                    $capacity_class = 'available';
                    $capacity_text = "{$capacity} spots available";
                }
                ?>

                <article class="course-card" data-course-id="<?php echo esc_attr($course_id); ?>" data-price="<?php echo esc_attr($price); ?>" data-capacity="<?php echo esc_attr($capacity); ?>" data-subject="<?php echo esc_attr($subject); ?>">
                    <div class="course-card-content">
                        <span class="course-subject"><?php echo esc_html($subject); ?></span>

                        <h2>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h2>

                        <div class="course-meta">
                            <div class="course-meta-item">
                                <span class="icon">👨‍🏫</span>
                                <span><?php echo esc_html($teacher); ?></span>
                            </div>
                            <?php if ($schedule) : ?>
                                <div class="course-meta-item">
                                    <span class="icon">📅</span>
                                    <span><?php echo esc_html(date('D, M j, g:i A', strtotime($schedule))); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="course-price">
                            <span class="currency">$</span><?php echo esc_html(number_format($price)); ?>
                        </div>

                        <div class="course-card-footer">
                            <div class="capacity-badge <?php echo esc_attr($capacity_class); ?>">
                                <?php echo esc_html($capacity_text); ?>
                            </div>

                            <a href="<?php echo esc_url(home_url('/booking/')); ?>" class="btn-book-now" <?php echo ($capacity <= 0) ? 'disabled' : ''; ?>>
                                <?php echo ($capacity <= 0) ? 'Sold Out' : 'Book Now'; ?>
                            </a>
                        </div>
                    </div>
                </article>

            <?php endwhile; ?>
            <?php wp_reset_postdata(); // Important! Reset the query 
            ?>
        <?php else : ?>
            <div class="no-courses">
                <p>😕 No courses found at the moment.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-cta">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>