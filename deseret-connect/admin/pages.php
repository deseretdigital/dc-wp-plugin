<?php
function deseret_connect_configuration_settings(){
  global $deseret_connect;
  add_meta_box( 
     'deseret_connect-meta-boxen'
    ,__( 'Settings', 'deseret_connect' )
    ,'deseret_connect_meta_boxen'
    ,$deseret_connect->settings
    ,'pending'
    ,'high'
  );
}

function deseret_connect_configuration_page(){
global $deseret_connect;
  deseret_connect_configuration_settings();
	$plugin_data = get_plugin_data( DESERET_CONNECT_DIR . 'deseret_connect.php' ); ?>


	<div class="wrap">
		
        <?php if ( function_exists( 'screen_icon' ) ) screen_icon(); ?>
        <?php if ( isset( $_GET['updated'] ) && 'true' == esc_attr( $_GET['updated'] ) ) deseret_connect_update_message(); ?>
		<h2><?php _e( 'General Settings', 'deseret_connect' ); ?></h2>
       
        <form id="settings" method="post">		
		<div id="poststuff">			               
          <?php settings_fields( 'deseret_connect-settings-group' ); ?>
				<div class="metabox-holder">
	
					<div class="post-box-container column-1 cron"><?php do_meta_boxes( $deseret_connect->settings, 'pending', $plugin_data ); ?></div>
							            
		       	</div>						
		</div><!-- #poststuff -->
     <br class="clear">
        <input class="button button-primary" type="submit" value="<?php _e('Save'); ?>" name="settings" />
        </form>
       <!--<a href="<?php echo $_SERVER['REQUEST_URI'] ?>&action=run-now">Run Now</a>-->
	</div><!-- .wrap -->  	
<?php	
}
?>