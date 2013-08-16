<?php
	
	require_once(dirname(__FILE__) . '/../../../wp-config.php');
	require_once( dirname(__FILE__) . '/deseret_connect.php' );
	                                     
	nocache_headers();
	
	$deseret_connect_opts = get_option(DESERET_CONNECT_OPTIONS);
	if($deseret_connect_opts['unix_cron'] == 'true') {
		deseret_connect_unix_cron();		
	}
?>