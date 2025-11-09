<?php
/*
Plugin Name: FAQ Accordion
Description: Creates a CPT for FAQs and a shortcode to display FAQs in accordion format.
Version: 1.1
Author: Henrique Ferreira
*/

function faq_cpt() {
    // CPT
    $args = array(
        'public'    => true,
        'label'     => 'FAQs',
        'menu_icon' => 'dashicons-editor-help',
        'supports'  => array( 'title', 'editor' ),
    );
    register_post_type( 'faq', $args );

    // Taxonomy: FAQ Category
    $labels = array(
        'name'              => 'FAQ Categories',
        'singular_name'     => 'FAQ Category',
        'search_items'      => 'Search FAQ Categories',
        'all_items'         => 'All FAQ Categories',
        'parent_item'       => 'Parent FAQ Category',
        'parent_item_colon' => 'Parent FAQ Category:',
        'edit_item'         => 'Edit FAQ Category',
        'update_item'       => 'Update FAQ Category',
        'add_new_item'      => 'Add New FAQ Category',
        'new_item_name'     => 'New FAQ Category Name',
        'menu_name'         => 'FAQ Categories',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'faq-category'),
    );

    register_taxonomy('faq_category', array('faq'), $args);
}
add_action( 'init', 'faq_cpt' );


function faq_accordion_shortcode($atts) {
    $atts = shortcode_atts( 
        array(
            'faq_category' => '',  // default category is empty, meaning all FAQs will be shown
        ), 
        $atts, 
        'faq_accordion' 
    );

    $args = array(
        'post_type' => 'faq',
        'posts_per_page' => -1,
    );

    // If a category is specified in the shortcode, add it to the query
    if (!empty($atts['faq_category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'faq_category',
                'field'    => 'slug',
                'terms'    => $atts['faq_category'],
            ),
        );
    }

    $query = new WP_Query( $args );
    $output = '';
    if ( $query->have_posts() ) {
        $output .= '<div class="faq-accordion">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $output .= '<h2 class="faq-question">' . get_the_title() . '</h2>';
            $output .= '<div class="faq-answer">' . get_the_content() . '</div>';
        }
        $output .= '</div>';
    }
    wp_reset_postdata();
    return $output;
}
add_shortcode( 'faq_accordion', 'faq_accordion_shortcode' );

function faq_enqueue_scripts() {
    wp_enqueue_style( 'faq', plugin_dir_url( __FILE__ ) . 'faq.css' );
    wp_enqueue_script( 'faq', plugin_dir_url( __FILE__ ) . 'faq.js', array(), '1.2.0', true );
}
add_action( 'wp_enqueue_scripts', 'faq_enqueue_scripts' );

function faq_accordion_print_styles() {
    $custom_css = get_option( 'faq_custom_css' );

    echo '<style type="text/css">
        ' . $custom_css . '
    </style>';
}
add_action( 'wp_head', 'faq_accordion_print_styles' );



function faq_accordion_options_page(){
    add_submenu_page(
        'edit.php?post_type=faq',
        'FAQ Accordion Options', 
        'Settings', 
        'manage_options', 
        'faq-accordion', 
        'faq_accordion_options_page_render'
    );
}
add_action('admin_menu', 'faq_accordion_options_page');

function faq_accordion_options_page_render(){
    ?>
    <div>
    <h2>FAQ Accordion Options</h2>
    <pre>[faq_accordion faq_category="category-slug"]</pre>
    <form method="post" action="options.php">
    <?php 
    settings_fields( 'faq-settings-group' );
    do_settings_sections( 'faq-settings-group' );
    ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Custom CSS</th>
        <td><textarea name="faq_custom_css" rows="10" cols="50"><?php echo esc_attr( get_option('faq_custom_css') ); ?></textarea></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

    </form>
    </div>
    <?php
}

function faq_plugin_settings() {
	register_setting( 'faq-settings-group', 'faq_custom_css' );
}
add_action( 'admin_init', 'faq_plugin_settings' );
?>
