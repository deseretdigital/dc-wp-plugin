<?php
/* Admin functions to set and save settings of the
 * @package deseret_connect
*/
require_once('pages.php');
require_once('meta_box.php');
/* Initialize the theme admin functions */
if( current_user_can( 'manage_options' )) {
add_action('init', 'deseret_connect_admin_init');
}

function deseret_connect_admin_init(){

    add_action('admin_menu', 'deseret_connect_settings_init');
    add_action('admin_init', 'deseret_connect_actions_handler');
    add_action('admin_init', 'deseret_connect_admin_style');
    add_action('admin_init', 'deseret_connect_admin_script');

}

function deseret_connect_settings_init(){
   global $deseret_connect;
   add_menu_page('deseret_connect', 'Deseret Connect', 0, 'deseret_connect-config', 'deseret_connect_configuration_page' );
}

function deseret_connect_admin_style(){
   $plugin_data = get_plugin_data( DESERET_CONNECT_DIR . 'deseret_connect.php' );
   wp_enqueue_style( 'deseret_connect-admin', DESERET_CONNECT_CSS . 'style.css', false, $plugin_data['Version'], 'screen' );
}

function deseret_connect_admin_script(){}
function deseret_connect_actions_handler(){
    global $wpdb;

    if(isset($_GET['connect'])){
	   deseret_connect_connect($_GET['connect']);
	}

	if(isset($_GET['disconnect'])){
	   deseret_connect_disconnect($_GET['disconnect']);
	}

    if(isset($_GET['action']) && $_GET['action']=='run-now'){
      deseret_connect_cron_hook();
  	  $redirect = admin_url( 'admin.php?page=deseret_connect-config&success=true' );
      wp_redirect($redirect);
   }

   if(isset($_POST['settings'])){
	  $deseret_connect_opts = get_option(DESERET_CONNECT_OPTIONS);
	  $deseret_connect_opts['pending'] = $_POST['pending'];
    $deseret_connect_opts['api_key'] = $_POST['api_key'];
    $deseret_connect_opts['state_id'] = $_POST['state_id'];
    $deseret_connect_opts['author_name'] = $_POST['author_name'];
      $deseret_connect_opts['post_type'] = $_POST['post_type'];
      $deseret_connect_opts['include_canonical'] = $_POST['include_canonical'];
      $deseret_connect_opts['feature_image'] = $_POST['feature_image'];
      $deseret_connect_opts['enable_logging'] = $_POST['enable_logging'];
	  update_option(DESERET_CONNECT_OPTIONS, $deseret_connect_opts);
	  wp_redirect(admin_url('admin.php?page=deseret_connect-config&updated=true'));
   }

}

function deseret_connect_error_message(){
   echo '<div class="error">
		<p>Error</p>
  </div>';
}
function deseret_connect_success_message(){
  echo '<div class="updated fade">
		<p>This campaign has done successfully.</p>
  </div>';
}
function deseret_connect_update_message(){
   echo '<div class="updated fade">
		<p>Settings Updated</p>
  </div>';
}
function deseret_connect_create_message(){
   echo '<div class="updated fade">
		<p>Campaign Created</p>
  </div>';
}
?>
