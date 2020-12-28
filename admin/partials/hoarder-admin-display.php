<?php
$dbhandler = new HOARDER_DBhandler;
$pmrequests = new HOARDER_request;
$path =  plugin_dir_url(__FILE__);
$identifier = 'SETTINGS';
if(filter_input(INPUT_POST,'submit_settings'))
{
	$retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
	if (!wp_verify_nonce($retrieved_nonce, 'save_email_settings' ) ) die( __('Failed security check','hoarder') );
	$exclude = array("_wpnonce","_wp_http_referer","submit_settings");
	$post = $pmrequests->sanitize_request($_POST,$identifier,$exclude);
	if($post!=false)
	{
		if(!isset($post['enable_hoarder'])) $post['enable_hoarder'] = 0;
		
		foreach($post as $key=>$value)
		{
			$dbhandler->update_global_option_value($key,$value);
		}
	}
        do_action('hoarder_fetch_rules');
        ?>
<div class="notice notice-success is-dismissible">
        <p><?php _e( 'Saved', 'hoarder' ); ?></p>
    </div>
        <?php
	//wp_redirect( esc_url_raw('admin.php?page=hoarder_rules') );exit;
}
?>

<div class="hoarder">
  <form name="hoarder_settings" id="hoarder_settings" method="post">
    <!-----Dialogue Box Starts----->
    <div class="content">
      <div class="uimheader">
        <?php _e( 'Hoarder Settings','hoarder' ); ?>
      </div>
     
      <div class="uimsubheader">
        <?php
		//Show subheadings or message or notice
		?>
      </div>
      
      <div class="uimrow">
        <div class="uimfield">
          <?php _e( 'Enable Hoarder:','hoarder' ); ?>
        </div>
        <div class="uiminput">
           <input name="enable_hoarder" id="enable_hoarder" type="checkbox" <?php checked($dbhandler->get_global_option_value('enable_hoarder'),'1'); ?> class="hoarder_toggle" value="1" style="display:none;"  onClick="hoarder_show_hide(this,'enable_hoarder_html')" />
          <label for="enable_hoarder"></label>
        </div>
          <div class="uimnote"><?php _e('Enable Hoarder.','hoarder');?></div>
      </div>
       <div class="childfieldsrow" id="enable_hoarder_html" style=" <?php if($dbhandler->get_global_option_value('enable_hoarder',0)==1){echo 'display:block;';} else { echo 'display:none;';} ?>">
       <div class="uimrow">
            <div class="uimfield">
              <?php _e( 'API Key','hoarder' ); ?>
            </div>
            <div class="uiminput">
             <input type="text" name="hoarder_api_key" id="hoarder_api_key" value="<?php echo $dbhandler->get_global_option_value('hoarder_api_key','');?>" />
             <div id="hoarder_response"></div>
            </div>
           <div class="uimnote"><span onclick="verify_hoarder()">Click to verify</span></div>
        </div>

            
       </div>
      
     
      <div class="buttonarea"> <a href="admin.php?page=hoarder_settings">
        <div class="cancel">&#8592; &nbsp;
          <?php _e('Cancel','hoarder');?>
        </div>
        </a>
        <?php wp_nonce_field('save_email_settings'); ?>
        <input type="submit" value="<?php _e('Save','hoarder');?>" name="submit_settings" id="submit_settings" />
        <div class="all_error_text" style="display:none;"></div>
      </div>
    </div>
  </form>
</div>