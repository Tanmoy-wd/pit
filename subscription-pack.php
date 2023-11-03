<?php
/**
*Plugin Name: Subscription Package Plugin
*Plugin URI: https://logregplugin.com/
*Description: This is a testing plugin.
*Version: 1.0.0
*Requires PHP: 7.4
*Author: Tanmoy Roy
*Author URI: 
*License: GPLv2 or later
*Text Domain: spp
 */
register_activation_hook(__FILE__,'form_data_active');
register_deactivation_hook(__FILE__,'form_data_deactive');

function form_data_active() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscribers';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
function form_data_deactive() {
   
}
// when post uploaded notification
function my_custom_function_on_post_publish($ID) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'new';
    $post = get_post($ID);
    $post_title = $post->post_title;

    $sql = "SELECT * FROM `wp_newsletter_subscribers`";
    $result = $wpdb->get_results($sql);
    foreach($result as $res){
    $sql = "INSERT INTO $table_name (`name`) VALUES ('$post_title')";
    $wpdb->query($sql);

    // mail send code
    $to = $res->email; 
    $subject = 'The subject';
    $body = 'The email body content';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail( $to, $subject, $body, $headers );
    }

    
}
add_action('publish_post', 'my_custom_function_on_post_publish');

function spp_form_script(){
    wp_enqueue_script('jquery');
    wp_enqueue_script('spp-plug-script', plugins_url('js/spp_script.js', __FILE__), array(), '1.0.0', 'true');
 }

 add_action("wp_enqueue_scripts","spp_form_script");


 // form shortcode
function ssp_subscribe_form() {

    ?>

    <form id="ssp_subscribe_form">
        <div class="row">
            <div class="col-md-6">
                <input type="email" name="email" id="theemail" class="form-control"> 
            </div>
            <div class="col-md-12">
                <button type="button" id="spp_btn" class="btn btn-success">Subscribe</button>
            </div>
        </div>
    </form>

    <?php
}
add_shortcode('ssp_subscribe_form', 'ssp_subscribe_form');


// form submit ajax function
function subscriber_add() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscribers';
    $sub=[];
    wp_parse_str($_POST['subscriber_add'], $sub);
    $theemail = $sub['email'];
    $sql_subscribed = "INSERT INTO `$table_name`(`email`) VALUES ('$theemail')";
    $wpdb->query($sql_subscribed);
    $response = 'You Are Subscribed';
    wp_send_json($response);
    wp_die();
}
add_action('wp_ajax_subscriber_add', 'subscriber_add');
add_action('wp_ajax_nopriv_subscriber_add', 'subscriber_add');

// menues and pages 
add_action('admin_menu', 'spp_menu_listing');
function spp_menu_listing() {
    add_menu_page('Subscribers List','Subscribers List',10,__FILE__,'subscribers_list');
    // add_submenu_page(__FILE__, 'Add Product', 'Add Product', 10, 'add-product', 'pit_add_product');
}
function subscribers_list() {
    include('admin/spp_package_list.php');
}
?>