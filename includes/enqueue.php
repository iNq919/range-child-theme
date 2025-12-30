<?php

const THEME_VERSION = '1.0.0';

function theme_enqueue_assets(): void {
    wp_enqueue_style('hello-elementor', get_parent_theme_file_uri('style.css'), [], THEME_VERSION);
    wp_enqueue_style('range-child-theme', get_stylesheet_directory_uri() . '/assets/css/style.css', ['hello-elementor'], THEME_VERSION);
    wp_enqueue_script('theme-script', get_stylesheet_directory_uri() . '/assets/js/script.js', [], THEME_VERSION, true);
}

add_action('wp_enqueue_scripts', 'theme_enqueue_assets');