<?php
/**
 * Plugin Name: Book Library
 * Description: Registers a "Book" custom post type with custom meta fields.
 * Version: 1.0
 * Author: Nikhil Awasthi
 */

 
if (!defined('ABSPATH')) exit;// Don't allow direct access

// 1. Register Custom Post Type
function bl_register_book_cpt() {
    register_post_type('book', [
        'labels' => [
            'name' => 'Books',
            'singular_name' => 'Book'
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-book',
        'supports' => ['title', 'editor'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'bl_register_book_cpt');



// 2. Add Meta Boxes
function bl_add_book_meta_boxes() {
    add_meta_box('bl_book_meta', 'Book Details', 'bl_render_book_meta_box', 'book', 'normal', 'default');
}
add_action('add_meta_boxes', 'bl_add_book_meta_boxes');

function bl_render_book_meta_box($post) {
    // Nonce for security
    wp_nonce_field('bl_save_book_meta', 'bl_book_meta_nonce');

    $author = get_post_meta($post->ID, '_bl_author_name', true);
    $year = get_post_meta($post->ID, '_bl_published_year', true);

    ?>
    <p>
        <label for="bl_author_name">Author Name:</label><br>
        <input type="text" name="bl_author_name" id="bl_author_name" value="<?php echo esc_attr($author); ?>" size="30" />
    </p>
    <p>
        <label for="bl_published_year">Published Year:</label><br>
        <input type="number" name="bl_published_year" id="bl_published_year" value="<?php echo esc_attr($year); ?>" size="4" />
    </p>
    <?php
}

// 3. Save Meta Fields Safely
function bl_save_book_meta($post_id) {
    if (!isset($_POST['bl_book_meta_nonce']) || !wp_verify_nonce($_POST['bl_book_meta_nonce'], 'bl_save_book_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['bl_author_name'])) {
        update_post_meta($post_id, '_bl_author_name', sanitize_text_field($_POST['bl_author_name']));
    }

    if (isset($_POST['bl_published_year'])) {
        update_post_meta($post_id, '_bl_published_year', intval($_POST['bl_published_year']));
    }
}
add_action('save_post', 'bl_save_book_meta');





// 4. Show Meta Fields in Admin Columns
function bl_add_custom_columns($columns) {
    $columns['author_name'] = 'Author';
    $columns['published_year'] = 'Year';
    return $columns;
}
add_filter('manage_book_posts_columns', 'bl_add_custom_columns');

function bl_fill_custom_columns($column, $post_id) {
    if ($column == 'author_name') {
        echo esc_html(get_post_meta($post_id, '_bl_author_name', true));
    }
    if ($column == 'published_year') {
        echo esc_html(get_post_meta($post_id, '_bl_published_year', true));
    }
}
add_action('manage_book_posts_custom_column', 'bl_fill_custom_columns', 10, 2);


