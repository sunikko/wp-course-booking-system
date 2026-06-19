<?php

function my_theme_setup()
{
    add_theme_support('title-tag');
}

add_action('after_setup_theme', 'my_theme_setup');
