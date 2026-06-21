<?php

function my_theme_setup()
{
    add_theme_support('title-tag');
}

add_action('after_setup_theme', 'my_theme_setup');


function register_courses_post_type()
{
    register_post_type('course', [
        'labels' => [
            'name' => 'Courses',
            'singular_name' => 'Course'
        ],
        'public' => true,
        'has_archive' => 'courses',
        'rewrite' => [
            'slug' => 'courses'
        ],
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-welcome-learn-more'
    ]);
}

add_action('init', 'register_courses_post_type');

add_action('init', function () {

    if (isset($_POST['book_course'])) {

        $course_id = $_POST['course_id'];

        $capacity = get_field('capacity', $course_id);

        if ($capacity > 0) {
            update_field('capacity', $capacity - 1, $course_id);
        }

        wp_redirect(get_permalink($course_id));
        exit;
    }
});


function my_theme_assets() {
    wp_enqueue_style(
        'booking-style',
        get_template_directory_uri() . '/assets/css/booking.css',
        array(),
        '1.0'
    );
}

add_action('wp_enqueue_scripts', 'my_theme_assets');


add_action('wp_enqueue_scripts', function () {

    wp_enqueue_script(
        'booking-js',
        get_template_directory_uri() . '/assets/js/booking.js',
        array(),
        '1.0',
        true // footer load (IMPORTANT)
    );

});
