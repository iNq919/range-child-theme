<!DOCTYPE html>

<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <title><?php wp_title('-', true, 'right'); ?></title>
    <link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/fonts/ClashGrotesk.woff2" as="font"
        type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/fonts/Inter.woff2" as="font"
        type="font/woff2" crossorigin>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	if ( hello_elementor_display_header_footer() ) {
		if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
			get_template_part( 'template-parts/dynamic-header' );
		} else {
			get_template_part( 'template-parts/header' );
		}
	}
}