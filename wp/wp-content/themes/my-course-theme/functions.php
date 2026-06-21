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
