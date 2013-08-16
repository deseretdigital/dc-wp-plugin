<?php
function deseret_connect_meta_boxen(){
   $deseret_connect_opts = get_option(DESERET_CONNECT_OPTIONS);
   $pending = $deseret_connect_opts['pending']; 
   $author_name = $deseret_connect_opts['author_name']; 
   $api_key = $deseret_connect_opts['api_key']; 
   $url = $deseret_connect_opts['url']; 
?>	
   <p><input type="checkbox" value="true" name="pending" <?php if($pending == 'true'){echo 'checked="checked"'; }?> />
   <label>Mark new articles as pending?</label></p>
   <p><input type="checkbox" value="true" name="author_name" <?php if($author_name == 'true'){echo 'checked="checked"'; }?> />
   <label>Include author name in article body?</label></p>
  <p><label>API Key</label><br /><input type="text" name="api_key" class="api_key" value="<?php echo $api_key; ?>" /> </p>
  <p><label>URL </label><br /><input type="text" name="url" class="url" value="<?php echo $url; ?>" /></p>
<?php   	
}
?>