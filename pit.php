<?php
/**
* Plugin Name: Plug In Testing 
*Plugin URI: https://plugintest.com/
*Description: This is a testing plugin.
*Version: 1.0.0
*Requires PHP: 7.4
*Author: Tanmoy Roy
*Author URI: 
*License: GPLv2 or later
*Text Domain: pit
 */

 register_activation_hook(__FILE__,'form_data_active');
 register_deactivation_hook(__FILE__,'form_data_deactive');

 function form_data_active(){
    global $wpdb;
    $table_name = $wpdb->prefix . "plugin_test";
    $table_product = $wpdb->prefix . "product";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name tinytext NOT NULL,
    email text NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";
    $sql_product_table = "CREATE TABLE $table_product (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        pro_title varchar(255) DEFAULT NULL,
        price varchar(255) DEFAULT NULL,
        pro_image varchar(255) DEFAULT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    $wpdb->query($sql_product_table);
 }

 function form_data_deactive(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_test';
    $sql        = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query( $sql );
    delete_option( 'wp_install_uninstall_config' );
 }

 function contact_form(){
    ob_start();
    ?>
    <form id="plugintest_form">
        <table>
            <tr>
                <td>Name</td>
                <td><input type="text" name="name" placeholder="Your Name Here"></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><input type="email" name="email" placeholder="Your Email Address Here"></td>
            </tr>
            <tr>
                <td><button type="button" id="sub_butt" >Submit</button></td>
            </tr>
        </table>
    </form>
    <?php
    return ob_get_clean();
 }
 add_shortcode("contact_form","contact_form");
// include JS
 function pit_form_script(){
    wp_enqueue_script('jquery');
    wp_enqueue_script('pit-plug-script', plugins_url('js/pit_script.js', __FILE__), array(), '1.0.0', 'true');
    // wp_localize_script('my-ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
 }

 add_action("wp_enqueue_scripts","pit_form_script");

 function pit_script_activate(){
    ?>
        <script>
            jQuery(document).ready(function() {
                jQuery("#sub_butt").click(function() {
                    var link = '<?php echo admin_url('admin-ajax.php'); ?>';
                    var form = jQuery("#plugintest_form").serialize();
                    jQuery.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        data: {
                            action: 'contact_us',
                            contact_us: form,
                        },
                        type: 'post',
                        success: function(result) {
                            alert(result);
                            $('#plugintest_form')[0].reset();
                        }
                    });
                });
            });
        </script>
    <?php
 }
 add_action("wp_footer","pit_script_activate");

 function pit_form_submit(){
    $arr=[];
    global $wpdb;
    wp_parse_str($_POST['contact_us'], $arr);
    $thename = $arr['name'];
    $theemail = $arr['email'];
    $table_name = $wpdb->prefix . "plugin_test";
    $sql = "INSERT INTO `$table_name`(`name`, `email`) VALUES ('$thename','$theemail')";
    $wpdb->query($sql);
    echo "done";
 }
add_action('wp_ajax_contact_us', 'pit_form_submit');
add_action('wp_ajax_nopriv_contact_us', 'pit_form_submit');
function add_my_name(){
    echo "I want to show my name Tanmoy Roy.";
}
add_action("wp_pit_testing_hook","add_my_name");


// The main Menu
add_action('admin_menu', 'pit_data_menu');
function pit_data_menu() {
    add_menu_page('Plugin Test','Plugin Test',10,__FILE__,'pit_data_list');
    add_submenu_page(__FILE__, 'Add Product', 'Add Product', 10, 'add-product', 'pit_add_product');
    add_submenu_page(__FILE__, 'Product List', 'Product List', 10, 'list-product', 'pit_list_product');
    add_submenu_page(__FILE__, 'Settings', 'Settings', 10, 'the-setting', 'pit_the_settings');
}
function pit_data_list() {
    include('admin/pit_form_data.php');
}

//adding sub menu
function pit_add_product() {
    include('admin/pit_add_product.php');
}
function pit_list_product() {
    include('admin/pit_list_product.php');
}
function pit_the_settings() {
    echo '<h1>Settings Page</h1>';
}


//media open
function enqueue_media_uploader() {
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'enqueue_media_uploader');

add_action('wp_ajax_product_add', 'pit_product_add');
add_action('wp_ajax_nopriv_product_add', 'pit_product_add');
function pit_product_add() {
    global $wpdb;
    $prod=[];
    wp_parse_str($_POST['product_add'],$prod);
    $title = $prod['title'];
    $price = $prod['price'];
    $img_url = $prod['img_url'];

    $sql_product_add = "INSERT INTO `wp_product`(`pro_title`, `price`, `pro_image`) VALUES ('$title','$price','$img_url')";
    $wpdb->query($sql_product_add);
    echo "Your Product Is Added.";
}

add_action("product_loop","product_loop");
function product_loop() {
    include('view/product_loop.php');
}

//create a unique id for this plugin
function pit_uniqid() {
    echo $_COOKIE['custom_cookie']; 
}
add_action('pit_uniqid', 'pit_uniqid');

function set_custom_cookie() {
    $cookie_name = 'custom_cookie';
    $cookie_value = rand(10,1000);
    $expiration = time() + 86400; // Cookie will expire in 24 hours (86400 seconds).


    if(isset($_COOKIE['custom_cookie'])){
        
    }else{
        // Set the cookie
        setcookie($cookie_name, $cookie_value, $expiration, COOKIEPATH, COOKIE_DOMAIN);
    }

    
}
add_action('init', 'set_custom_cookie');

add_action('pit_login_function', 'pit_login_function');
function pit_login_function() {
    include('view/pit_login_function.php');
}

add_action('wp_ajax_login_function', 'ajax_login_function');
add_action('wp_ajax_nopriv_login_function', 'ajax_login_function');
function ajax_login_function(){
    $arr_log=[];
    global $wpdb;
    wp_parse_str($_POST['login_function'], $arr_log);
    $username = $arr_log['username'];
    $password = $arr_log['password'];

    $credentials = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => true
    );
    
    $user = wp_signon($credentials);
    
    if (is_wp_error($user)) {
        // Login failed, handle the error
        echo "No";
    } else {
        // Login successful, $user contains the user data
        // You can perform actions or redirect the user to a specific page here
        echo "Ok";
    }
}

?>
