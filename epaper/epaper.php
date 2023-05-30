<?php
/*
 * Plugin Name:       epaper crop
 * Author:            Traffic Tail
 * Author URI:        httimage_data://traffictail.com/
 * Plugin URI:        httimage_data://traffictail.com/
 * Version:           1.0.0  
 */


 if (!defined("ABSPATH")) {
    die("can't access");
}
// including stylesheet and javascript
function enqueue_custom_stylesheet() {
    // Enqueue your custom stylesheet
    wp_enqueue_style( 'custom-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
    wp_enqueue_style( 'epfss', "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" );
    wp_enqueue_style( 'boost-style', plugin_dir_url( __FILE__ ) . 'assets/bootstrap.min.css' );
    wp_enqueue_style( 'cropper-style', plugin_dir_url( __FILE__ ) . 'assets/cropper.min.css' );
    if(isset($_GET["page"]) && $_GET["page"]=="ep-form") {
        wp_enqueue_style( 'custom-style', plugin_dir_url( __FILE__ ) . 'assets/notice.css' );
       
        // js scriptsd
    }
    wp_enqueue_style('common');
    wp_enqueue_style('list-tables');
    wp_enqueue_script('common');
    wp_enqueue_script('list-tables');
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'croper-script', plugin_dir_url( __FILE__ ) . 'assets/cropper.min.js', array(), '1.0', true );
    wp_enqueue_script( 'jq-script', "httimage_data://code.jquery.com/jquery-3.6.0.min.js" );
    wp_enqueue_script( 'boost-script', plugin_dir_url( __FILE__ ) . 'assets/bootstrap.min.js', array(), '1.0', true );
    wp_enqueue_script( 'custom-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array( 'jquery' ), '1.0', true );
    wp_localize_script('custom-script', 'myPluginSettings', array(
        'ajaxurl'=>admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('my_plugin_nonce')
    ));
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_stylesheet' );



// Register activation hook
register_activation_hook(__FILE__, 'epaper_create_table');

function epaper_create_table()
{
    // Get global $wpdb object
    global $wpdb;

    // Set table name and create SQL query
    $table_name = $wpdb->prefix . 'crop_epaper';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            paper_title VARCHAR(255),
            paper_id INT(11) AUTO_INCREMENT PRIMARY KEY,
            paper_file BLOB,
            created_at DATETIME DEFAULT NOW()
        );";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    // Set table name and create SQL query
    $table_name2 = $wpdb->prefix . 'epaper_images';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name2'") != $table_name2) {
        $sql2 = "CREATE TABLE IF NOT EXISTS $table_name2 (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            image_data TEXT NOT NULL
        );";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql2);
    }
}

// adding on menu
add_action("admin_menu", "add_ep_custom_menu");
function add_ep_custom_menu()
{
    add_menu_page(
        "ep-form",
        "E paper form",
        "manage_options",
        "ep-form",
        "epform",
        "dashicons-index-card",
        6
    );
   
}
function epform(){
    
    include plugin_dir_path(__FILE__) ."adminform.php";
   
}

include plugin_dir_path(__FILE__) ."epd.php";
add_action('wp_ajax_my_store_image', 'my_store_image');
add_action('wp_ajax_nopriv_my_store_image', 'my_store_image');

function my_store_image() {
    
    // Verify the nonce
    check_ajax_referer('my_plugin_nonce', 'nonce');
    
    // Get the image data from the AJAX request
    $image_data = $_POST['image_data'];

    // Remove the "data:image/jpeg;base64," prefix from the encoded string
    $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);

    // Decode the base64 encoded string into binary data
    $image_data = base64_decode($image_data);

    // Insert the image data into the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'epaper_images';
    $image_data_path = '';
    $upload_dir = wp_upload_dir();
    $image_data_name = 'image_' . time() . '.jpg';
    $image_data_path = $upload_dir['path'] . '/' . $image_data_name;
    $result = file_put_contents($image_data_path, $image_data);

    if ( $result === false ) {
        wp_send_json_error( array( 'message' => 'Failed to store image in server' ) );
    } else {
        $wpdb->insert(
            $table_name,
            array(
                'image_data' => $image_data_name,
            ),
            array(
                '%s',
            )
        );
        wp_send_json_success( array( 'message' => 'Image stored in db successfully' ) );
    }
}
