<?php

/**
 * WC product attributes
 */
function wc_product_attributes_shortcode(array $atts): string
{
    $atts = shortcode_atts(
        ['product_id' => 0],
        $atts,
        'wc_product_attributes'
    );

    $product_id = absint($atts['product_id']) ?: get_the_ID();
    if (!$product_id) {
        return '';
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        return '';
    }

    $items = [];

    foreach ($product->get_attributes() as $attribute) {
        $data = method_exists($attribute, 'get_data') ? $attribute->get_data() : [];

        if (empty($data['visible'])) {
            continue;
        }

        $label = wc_attribute_label($attribute->get_name());

        $values = $attribute->is_taxonomy()
            ? wc_get_product_terms($product_id, $attribute->get_name(), ['fields' => 'names'])
            : $attribute->get_options();

        if (empty($values)) {
            continue;
        }

        $items[] = sprintf(
            '<li class="wc-attribute-item">%s: %s</li>',
            esc_html($label),
            esc_html(implode(', ', $values))
        );
    }

    if (empty($items)) {
        return '';
    }

    return sprintf(
        '<ul class="wc-attributes-list">%s</ul>',
        implode('', $items)
    );
}

add_shortcode('wc_product_attributes', 'wc_product_attributes_shortcode');

/**
 * WC product categories
 */
function wc_product_categories_shortcode( array $atts ): string
{
    $product_id = absint( $atts['product_id'] ?? get_the_ID() );
    if ( ! $product_id ) {
        return '';
    }

    $categories = get_the_terms( $product_id, 'product_cat' );
    if ( empty( $categories ) || is_wp_error( $categories ) ) {
        return '';
    }

    $category = reset( $categories );

    return sprintf(
        '<a class="wc-category-link" href="%s">%s</a>',
        esc_url( get_term_link( $category ) ),
        esc_html( $category->name )
    );
}

add_shortcode( 'wc_product_categories', 'wc_product_categories_shortcode' );

add_action('elementor/init', function () {

    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }

    class Pro_WC_Categories_Menu_Widget extends \Elementor\Widget_Base
    {
        public function get_name()
        {
            return 'pro_wc_categories_menu';
        }

        public function get_title()
        {
            return __('Menu kategorii produktów', 'range');
        }

        public function get_icon()
        {
            return 'eicon-products';
        }

        public function get_categories()
        {
            return ['general'];
        }

        protected function register_controls()
        {
            $this->start_controls_section(
                'content_section',
                [
                    'label' => __('Ustawienia', 'range'),
                ]
            );

            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent' => 0,
            ]);

            $options = [];

            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $options[$term->term_id] = $term->name;
                }
            }

            $this->add_control(
                'parent_categories',
                [
                    'label' => __('Wybierz kategorie nadrzędne', 'range'),
                    'type' => \Elementor\Controls_Manager::SELECT2,
                    'options' => $options,
                    'multiple' => true,
                    'label_block' => true,
                ]
            );

            $this->end_controls_section();
        }

        private function get_category_image_html(int $term_id): string
        {
            $thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);

            if ($thumbnail_id) {
                $image = wp_get_attachment_image($thumbnail_id, 'medium', false, [
                    'alt' => get_term($term_id)->name,
                    'class' => 'cat-image',
                ]);
                if ($image) {
                    return '<div class="image-wrapper">' . $image . '</div>';
                }
            }

            return '';
        }

        private function render_subcategories(int $parent_id): string
        {
            $subcats = get_terms([
                'taxonomy' => 'product_cat',
                'parent' => $parent_id,
                'hide_empty' => false,
            ]);

            if (empty($subcats) || is_wp_error($subcats)) {
                return '';
            }

            $output = '<ul class="subcategories">';
            foreach ($subcats as $subcat) {
                $link = get_term_link($subcat);
                if (is_wp_error($link)) {
                    continue;
                }
                $output .= sprintf(
                    '<li><a href="%s">%s</a></li>',
                    esc_url($link),
                    esc_html($subcat->name)
                );
            }
            $output .= '</ul>';

            return $output;
        }

        private function render_category_block(int $term_id): string
        {
            $term = get_term($term_id, 'product_cat');

            if (!$term || is_wp_error($term)) {
                return '';
            }

            $link = get_term_link($term);
            if (is_wp_error($link)) {
                $link = '#';
            }

            $image_html = $this->get_category_image_html($term_id);
            $subcategories_html = $this->render_subcategories($term_id);

            return sprintf(
                '<div class="column">
                 <a href="%1$s" class="category-link">%2$s</a>
                 <div class="content"><h4 class="category-name">%3$s</h4>%4$s</div></div>',
                esc_url($link),
                $image_html,
                esc_html($term->name),
                $subcategories_html
            );
        }

        protected function render()
        {
            $settings = $this->get_settings_for_display();

            $parent_categories = $settings['parent_categories'] ?? [];

            if (!is_array($parent_categories)) {
                $parent_categories = [];
            }

            if (empty($parent_categories)) {
                echo '<p>' . __('Nie wybrano kategorii nadrzędnych.', 'range') . '</p>';
                return;
            }

            echo '<div class="categories-menu-widget">';
            foreach ($parent_categories as $parent_id) {
                echo $this->render_category_block((int)$parent_id);
            }
            echo '</div>';
        }
    }

    add_action('elementor/widgets/widgets_registered', function () {
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Pro_WC_Categories_Menu_Widget());
    });
});