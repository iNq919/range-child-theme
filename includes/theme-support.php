<?php

const NEWSLETTER_FORM_ID = 581;
const NEWSLETTER_EMAIL_FIELD = 'newsletter_email';
const CFDB7_TABLE = 'db7_forms';

/**
 * Allow SVG upload
 */
add_filter('upload_mimes', function (array $mimes): array {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

/**
 * Fix SVG preview in admin
 */
add_action('admin_head', function (): void {
    echo '<style>
        img[src$=".svg"] {
            width: 100%;
            height: auto;
        }
    </style>';
});

/**
* New product badge shortcode
*/
function is_product_new_badge( WC_Product $product, int $days = 14 ): string
{
    $date_created = $product->get_date_created();
    if ( ! $date_created ) {
        return '';
    }

    if ( current_time( 'timestamp' ) - $date_created->getTimestamp() > DAY_IN_SECONDS * $days ) {
        return '';
    }

    return '<span class="product-badge new-badge">' .
        esc_html__( 'Nowość', 'range' ) .
    '</span>';
}

function shortcode_new_badge(): string
{
    global $product;

    if ( ! $product instanceof WC_Product ) {
        return '';
    }

    return is_product_new_badge( $product, 14 );
}

add_shortcode( 'new_badge', 'shortcode_new_badge' );

function enqueue_menu_shortcode_script() {
    $shortcode_html = do_shortcode('[elementor-template id="1144"]');

    wp_register_script('menu-shortcode-insert', false);
    wp_enqueue_script('menu-shortcode-insert');

    wp_add_inline_script('menu-shortcode-insert', '
      document.addEventListener("DOMContentLoaded", function() {
        var menuWrapper = document.querySelector(".e-n-menu-wrapper");
        var menuHeading = document.querySelector(".e-n-menu-heading");
        if (!menuWrapper || !menuHeading) return;

        var shortcodeDiv = document.createElement("div");
        shortcodeDiv.className = "menu-shortcode-wrapper";
        shortcodeDiv.innerHTML = ' . json_encode($shortcode_html) . ';

        menuWrapper.insertBefore(shortcodeDiv, menuHeading.nextSibling);
      });
    ');
}
add_action('wp_enqueue_scripts', 'enqueue_menu_shortcode_script');

/**
* Disable Gutenberg editor
*/
add_filter( 'use_block_editor_for_post', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );
add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'global-styles' );
    wp_dequeue_style( 'classic-theme-styles' );
}, 20 );

add_filter(
    'wpcf7_validate_email*',
    'prevent_duplicate_newsletter_email',
    20,
    2
);

function prevent_duplicate_newsletter_email($result, $tag)
{
    if ($tag->name !== NEWSLETTER_EMAIL_FIELD) {
        return $result;
    }

    $submission = WPCF7_Submission::get_instance();
    if (!$submission) {
        return $result;
    }

    $posted_data = $submission->get_posted_data();
    if (empty($posted_data[NEWSLETTER_EMAIL_FIELD])) {
        return $result;
    }

    $email = sanitize_email($posted_data[NEWSLETTER_EMAIL_FIELD]);
    if (!is_email($email)) {
        return $result;
    }

    global $wpdb;

    $table = $wpdb->prefix . CFDB7_TABLE;

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "
            SELECT 1
            FROM {$table}
            WHERE form_post_id = %d
            AND form_value LIKE %s
            LIMIT 1
            ",
            NEWSLETTER_FORM_ID,
            '%' . $wpdb->esc_like($email) . '%'
        )
    );

    if ($exists) {
        $result->invalidate(
            $tag,
            __('Ten adres e-mail jest już zapisany do newslettera.', 'range')
        );
    }

    return $result;
}