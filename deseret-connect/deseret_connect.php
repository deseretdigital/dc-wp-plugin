<?php
/**
 * Plugin Name: Deseret Connect
 * Plugin URI: http://wp.deseretconnect.com
 * Description: Consume content from Deseret Connect
 * Version: 1.0.3
 * Author: Deseret Connect / Deseret Digital Media
 * Author URI:  http://deseretconnect.com
 *
 * @package deseret_connect
 */
define('DESERET_CONNECT_OPTIONS', 'deseret_connect_opts');
define('DESERET_CONNECT_VERSION', '1.0.3');
/**/
/* Set up the plugin. */
add_action('plugins_loaded', 'deseret_connect_setup');  
//add cron intervals
add_filter('cron_schedules', 'deseret_connect_intervals');
//Actions for Cron job
add_action('deseret_connect_cron', 'deseret_connect_cron_hook');

add_action('wp_head','deseret_connect_head');
remove_action( 'wp_head', 'rel_canonical' );
function deseret_connect_head()
{
  if(!is_singular()){
    return;
  }

  $postId = get_the_id();

  $canonical = get_post_meta($postId, '_dc_syn_canonical', true);
  $standout = get_post_meta($postId, '_dc_syn_standout', true);
  $syndication_source = get_post_meta($postId, '_dc_syn_syndication_source', true);
  $description = get_post_meta($postId, '_dc_syn_description', true);
  $authors = get_post_meta($postId, '_dc_syn_authors', true);

  if($canonical){
    echo $canonical ."\n";
  }else{
    rel_canonical();
  }

  if($standout){
    echo $standout ."\n";
  }

  if($description){
    echo $description . "\n";
  }

  if($authors){
    echo $authors . "\n";
  }
}
/* Create table when admin active this plugin*/
register_activation_hook(__FILE__,'deseret_connect_activation');
register_deactivation_hook(__FILE__, 'deseret_connect_deactivation');

function deseret_connect_activation()
{
	$deseret_connect_opts = get_option(DESERET_CONNECT_OPTIONS);
	if(!empty($deseret_connect_opts)){
	   $deseret_connect_opts['version'] = DESERET_CONNECT_VERSION;
	   update_option(DESERET_CONNECT_OPTIONS, $deseret_connect_opts); 	
	}else{
	   $opts = array(
		'version' => DESERET_CONNECT_VERSION		
	  );
	  // add the configuration options
	  add_option(DESERET_CONNECT_OPTIONS, $opts);   	
	}	
	
	
	//test if cron active
	if (!(wp_next_scheduled('deseret_connect_cron')))
	wp_schedule_event(time(), 'deseret_connect_intervals', 'deseret_connect_cron');
	
}

function deseret_connect_deactivation(){
    wp_clear_scheduled_hook('deseret_connect_cron');	
}

function deseret_connect_cron_hook(){
    global $wpdb;
    $deseret_connect_opts = get_option(DESERET_CONNECT_OPTIONS);
    $url = $deseret_connect_opts['url'];
    $apiKey = $deseret_connect_opts['api_key'];
    $pending = $deseret_connect_opts['pending'];
    $author_name = $deseret_connect_opts['author_name'];
    $post_type = $deseret_connect_opts['post_type'];

    require_once DESERET_CONNECT_INC . 'deseret_connect_client.php';
    $client = new DeseretConnect_Client($wpdb);
    $requests = $client->getRequests($url, $apiKey, $pending, $author_name, $post_type);	
}

function deseret_connect_unix_cron(){
  deseret_connect_cron_hook();	
}

function deseret_connect_intervals($schedules){
   $intervals['deseret_connect_intervals']=array('interval' => '5', 'display' => 'deseret_connect');
   $schedules=array_merge($intervals,$schedules);
   return $schedules;	
}

/* 
 * Set up the deseret_connect plugin and load files at appropriate time. 
*/
function deseret_connect_setup(){
   $deseret_connect_opts = get_option(DESERET_CONNECT_OPTIONS);	
   /* Set constant path for the plugin directory */
   define('DESERET_CONNECT_DIR', plugin_dir_path(__FILE__));
   define('DESERET_CONNECT_ADMIN', DESERET_CONNECT_DIR.'/admin/');
   define('DESERET_CONNECT_INC', DESERET_CONNECT_DIR.'/include/');

   /* Set constant path for the plugin url */
   define('DESERET_CONNECT_URL', plugin_dir_url(__FILE__));
   define('DESERET_CONNECT_CSS', DESERET_CONNECT_URL.'css/');
   define('DESERET_CONNECT_JS', DESERET_CONNECT_URL.'js/');
   
   if($deseret_connect_opts['unix_cron'] == 'true'){
       wp_clear_scheduled_hook('deseret_connect_cron');
   }else{
	   //test if cron active
	   if (!(wp_next_scheduled('deseret_connect_cron'))){
	     wp_schedule_event(time(), 'deseret_connect_intervals', 'deseret_connect_cron');   
     }
   }      

   if(is_admin())
      require_once(DESERET_CONNECT_ADMIN.'admin.php');

   
   //$cron = wp_get_schedules();
   //error_log( "CRON jobs: " . print_r( $cron, true ) );
}



add_filter( 'the_author', 'dc_author_name' );
add_filter( 'get_the_author_display_name', 'dc_author_name' );
function dc_author_name( $name ) {
  global $post;
  $author = get_post_meta( $post->ID, '_dc_author', true );
  if ( $author ){
    $name = $author;
  }
  return $name;
}

?>
