<?php
/**
 * Plugin Name: Import Plugin
 * Description: Wholegrain Starter plugin for developer test
 * Version: 0.1
 * Author: Wholegrain Digital
 * Text domain: wgd
*/

namespace WGD;

class ImportPlugin
{
    public static function init(): void
    {
        self::register();
    }

    // A function to register the post type and taxonomy
    public static function register(): void
    {
        global $pagenow;
        
        // Register Custom Post Types - Action Hook
        add_action( "init", array( static::class, 'register_custom_post_type_n_taxonomy' ) );

        // Adding Meta Boxes - Action Hook
        add_action( "add_meta_boxes", array( static::class, 'add_customer_meta_box' ) );
        add_action( "save_post", array( static::class, 'save_customer_meta_box_data' ) );

        /**
         * Show 'insert posts' button on backend
         */
        if( $pagenow == "edit.php" && $_GET['post_type'] == "wgd_customer" && !get_posts( array( 'post_type' => 'wgd_customer', 'numberposts' => 1 ) ) ) {
            add_action( "admin_notices", array( static::class, "showing_admin_notice" ));
        }

        /**
         * Create and insert posts from CSV files
         */
        add_action( "admin_init", array( static::class, "import_customers_from_csv" ));

    }

    // Register Custom Post Type "wgd_customer"
    public static function register_custom_post_type_n_taxonomy() {

        $labels = array(
            'name'                  => _x( 'Customers Detail', 'Post type general name', 'wgd' ),
            'singular_name'         => _x( 'Customer Detail', 'Post type singular name', 'wgd' ),
            'menu_name'             => _x( 'Customers Detail', 'Admin Menu text', 'wgd' ),
            'name_admin_bar'        => _x( 'Customer Detail', 'Add New on Toolbar', 'wgd' ),
            'add_new'               => __( 'Add New', 'wgd' ),
            'add_new_item'          => __( 'Add New Customer', 'wgd' ),
            'new_item'              => __( 'New Customer', 'wgd' ),
            'edit_item'             => __( 'Edit Customer', 'wgd' ),
            'view_item'             => __( 'View Customer', 'wgd' ),
            'all_items'             => __( 'All Customers', 'wgd' ),
            'search_items'          => __( 'Search Customers', 'wgd' ),
            'parent_item_colon'     => __( 'Parent Customers:', 'wgd' ),
            'not_found'             => __( 'No Customers found.', 'wgd' ),
            'not_found_in_trash'    => __( 'No Customers found in Trash.', 'wgd' ),
            'featured_image'        => _x( 'Customer Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'wgd' ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'wgd' ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'wgd' ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'wgd' ),
            'archives'              => _x( 'Customer archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'wgd' ),
            'insert_into_item'      => _x( 'Insert into Customer', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'wgd' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this Customer', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'wgd' ),
            'filter_items_list'     => _x( 'Filter Customers list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'wgd' ),
            'items_list_navigation' => _x( 'Customers list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'wgd' ),
            'items_list'            => _x( 'Customers list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'wgd' ),
        );
     
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'customer' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-id',
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
            'show_in_rest'       => true,
            // 'taxonomies'         => array( "category" ), // This declaration could be lot easier than registering Custom Taxonomy
        );

        // Registering Custom Post Type
        register_post_type( "wgd_customer", $args );

        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name'              => _x( 'Categories', 'taxonomy general name', 'wgd' ),
            'singular_name'     => _x( 'Category', 'taxonomy singular name', 'wgd' ),
            'search_items'      => __( 'Search Categories', 'wgd' ),
            'all_items'         => __( 'All Categories', 'wgd' ),
            'parent_item'       => __( 'Parent Category', 'wgd' ),
            'parent_item_colon' => __( 'Parent Category:', 'wgd' ),
            'edit_item'         => __( 'Edit Category', 'wgd' ),
            'update_item'       => __( 'Update Category', 'wgd' ),
            'add_new_item'      => __( 'Add New Category', 'wgd' ),
            'new_item_name'     => __( 'New Category Name', 'wgd' ),
            'menu_name'         => __( 'Category', 'wgd' ),
        );
    
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'customer_category' ),
        );
    
        // Register Custom Taxonomy
        register_taxonomy( 'customer_category', array( 'wgd_customer' ), $args );
    }

    public static function add_customer_meta_box() {
        add_meta_box( 'wgd_meta_box_id', __( "Customer's Address", "wgd" ), array( static::class, 'custom_meta_boxes_fields_callback' ), 'wgd_customer', 'advanced', 'default' );
    }

    public static function custom_meta_boxes_fields_callback( $post ) {

        wp_nonce_field( 'wgd_meta_data_gen', "wgd_meta_data_gen_nonce" );

        $data = get_post_meta( $post->ID, '_wgd_customer_address', true );

        echo '<label for="wgd_customer_address">' . __( 'Address:', 'wgd' ) . '</label><input type="text" class="widefat" size="25" name="wgd_customer_address" id="wgd_customer_address" value="' . esc_attr( $data ) . '" />';

    }

    public static function save_customer_meta_box_data( $post_id ) {

        if( ! isset( $_POST[ 'wgd_meta_data_gen_nonce' ] ) ) return $post_id;
        $nonce = $_POST[ 'wgd_meta_data_gen_nonce' ];

        if( ! wp_verify_nonce( $nonce, 'wgd_meta_data_gen' ) ) return $post_id;
        if( defined( "DOING_AUTOSAVE" ) && DOING_AUTOSAVE ) return $post_id;
        if( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;

        $data = isset( $_POST['wgd_customer_address'] ) ? sanitize_text_field( $_POST['wgd_customer_address'] ) : '';

        // Update Meta Field
        update_post_meta( $post_id, '_wgd_customer_address', $data );

    }

    public static function showing_admin_notice() {
        echo "<div class='updated'>";
        echo "<p>";
        echo "To insert the customers into the database, click the button to the right.";
        echo "<a class='button button-primary' style='margin:0.25em 1em' href='{$_SERVER["REQUEST_URI"]}&insert_customers'>Insert Customers</a>";
        echo "</p>";
        echo "</div>";
    }

    public static function import_customers_from_csv() {
            global $wpdb;

            if ( ! isset( $_GET["insert_customers"] ) ) {
                return;
            }

            // Change these to whatever you set
            $wgd = array(
                "custom-field" => "insert_customer_attachment",
                "custom-post-type" => "wgd_customer"
            );

            // Get the data from all those CSVs!
            $posts = function() {
                $data = array();
                $errors = array();

                // Get array of CSV files
                // $files = glob(  __DIR__ . '/locations.csv' );
                $files = glob( plugin_dir_path( __FILE__ ) . 'locations.csv' );

                foreach ( $files as $file ) {

                    // Attempt to change permissions if not readable
                    if ( ! is_readable( $file ) ) {
                        chmod( $file, 0744 );
                    }

                    // Check if file is writable, then open it in 'read only' mode
                    if ( is_readable( $file ) && $_file = fopen( $file, "r" ) ) {

                        // To sum this part up, all it really does is go row by
                        //  row, column by column, saving all the data
                        $post = array();

                        // Get first row in CSV, which is of course the headers
                        $header = fgetcsv( $_file );

                        while ( $row = fgetcsv( $_file ) ) {

                            foreach ( $header as $i => $key ) {
                                $post[$key] = $row[$i];
                            }

                            $data[] = $post;
                        }

                        fclose( $_file );

                    } else {
                        $errors[] = "File '$file' could not be opened. Check the file's permissions to make sure it's readable by your server.";
                    }
                }

                if ( ! empty( $errors ) ) {
                    // ... do stuff with the errors
                }

                return $data;
            };

            $post_exists = function( $title ) use ( $wpdb, $wgd ) {

                // Get an array of all posts within our custom post type
                $posts = $wpdb->get_col( "SELECT post_title FROM {$wpdb->posts} WHERE post_type = '{$wgd["custom-post-type"]}'" );
            
                // Check if the passed title exists in array
                return in_array( $title, $posts );
            };

            foreach ( $posts() as $post ) {

                // If the post exists, skip this post and go to the next one
                if ( $post_exists( $post["customer"] ) ) {
                    continue;
                }
            
                // Insert the post into the database
                $post["id"] = wp_insert_post( array(
                    "post_title" => $post["customer"],
                    "post_content" => $post["description"],
                    "post_type" => $wgd["custom-post-type"],
                    "post_status" => "publish"
                ));

                if( $post["id"] ) {
                    update_post_meta( $post["id"], '_wgd_customer_address', $post['address'] );

                    $cat = term_exists( $post['category'], 'customer_category' );
 
                    if ( ! $cat ) {
                        $cat = wp_insert_term( $post['category'], 'customer_category', array('description'=> '') );
                    }

                    wp_set_post_terms( $post["id"], array( $cat->term_id ), 'customer_category', false );
                }
                            
            }

            // Remove import notice
            remove_action( "admin_notices", array( static::class, "showing_admin_notice" ));
        }

}

\WGD\ImportPlugin::init();
