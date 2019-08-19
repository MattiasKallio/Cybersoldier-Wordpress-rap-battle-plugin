<?php
// create custom plugin settings menu

add_action('admin_menu', 'kallios_cybersoldier');

function kallios_cybersoldier() {
	add_menu_page('Cybersoldier', 'Cybersoldier', 'administrator', 'cybersoldier-settings', 'cybersoldier_settings_page',plugins_url('/assets/webbigt_wp_icon.svg', __FILE__));
	add_action( 'admin_init', 'register_cybersoldier_settings' );
}

function register_cybersoldier_settings() {
	//register our settings
	register_setting( 'cybersoldier-settings-group', 'cybersoldier_send_mail_to_opponent');
	register_setting( 'cybersoldier-settings-group', 'cybersoldier_number_of_lines');
	register_setting( 'cybersoldier-settings-group', 'cybersoldier_time_to_end');
	register_setting( 'cybersoldier-settings-group', 'cybersoldier_url_to_infopage');
	register_setting( 'cybersoldier-settings-group', 'cybersoldier_refresh_page_for_opponent');
}

function cybersoldier_settings_page() {
?>
<div class="wrap cybersoldier-admin-content">
<h2><?php echo __( 'Cybersoldier Settings' ,"cybersoldier" ); ?></h2>
<p>Bla bla, nå vettigt text om att skriva in id:n och vad som nu kommer bli här i inställningsväg.</p>

<?php echo __( 'Battle' ,"cybersoldier" ); ?>
Load list from "character info" remember to add icons and character...

<div class="wpaf_leftside">
<form method="post" action="options.php">
    <?php 
    	settings_fields( 'cybersoldier-settings-group' );
    	do_settings_sections( 'cybersoldier-settings-group' );
    	$number_of_lines = get_option('cybersoldier_number_of_lines', 10);
    	$refresh_page_for_opponent = get_option('cybersoldier_refresh_page_for_opponent', true);
    	$time_to_end = get_option('cybersoldier_time_to_end', 24);
    	$cybersoldier_send_mail_to_opponent = get_option('cybersoldier_send_mail_to_opponent', true);
    	$cybersoldier_url_to_infopage = get_option('cybersoldier_url_to_infopage', 'cybersoldier');
    	$checked_or_not = $cybersoldier_send_mail_to_opponent ? "checked" : "";
   	?>
  
    <table>
    	<tr>
    		<td>Send e-mail to opponent when a reply is made in battle.</td>
    		<td>
    			<label class="switch">
  					<input type="checkbox" <?php echo $checked_or_not; ?> id="cybersoldier_send_mail_to_opponent" name="cybersoldier_send_mail_to_opponent">
  					<div class="slider"></div>
				</label>
			</td>
    	</tr>    	
		<tr>
    		<td>Url to the page where the info about soldiers are. ie /cybersolider/</td>
    		<td>
  				<input type="text" value="<?php echo $cybersoldier_url_to_infopage; ?>" id="cybersoldier_url_to_infopage" name="cybersoldier_url_to_infopage">
			</td>
    	</tr>    	
    	<tr>
    		<td>Number of lines in battle, 10 is default (5 lines each)</td>
    		<td>
    			<input type="number" id="cybersoldier_number_of_lines" name="cybersoldier_number_of_lines" value="<?php echo $number_of_lines ?>" />
			</td>
    	</tr>
    	    	    	<tr>
    		<td>Reload battle page for opponent</td>
    		<td>
    			<label class="switch">
  					<input type="checkbox" <?php echo $refresh_page_for_opponent; ?> id="cybersoldier_refresh_page_for_opponent" name="cybersoldier_refresh_page_for_opponent">
  					<div class="slider"></div>
				</label>
			</td>
    	</tr>
    	<tr>
    		<td>How long is a battle going to contiune, in hours</td>
    		<td>
    			<input type="number" id="cybersoldier_time_to_end" name="cybersoldier_time_to_end" value="<?php echo $time_to_end ?>" />
			</td>
    	</tr>    	
    </table>
    
    <?php submit_button(); ?>

</form>
<h3><?php echo __( 'Update character items' ,"cybersoldier" ); ?></h3>
<p><?php echo __( 'Replace the character info file, add new icons to folder and/or the character svg file in character folder... Click button, and magic.' ,"cybersoldier" ); ?></p>
<input type="button" id="set-character-info" value="Update character svg info">
<div class="wait_one_minute">Woohoo<img src="<?php echo plugins_url('/assets/loader.gif', __FILE__)?>"></div>
<div class="out_box"></div>

<h3>Shortcodes</h3>
There are a bunch of shortcodes for different stuff that you might want to have.
<h4>Battles list</h4>
To add a battles list to a page, or maybe in a widget or where ever you want it. Use 
[battles_list]
It can be used in a couple of different ways, you can use the parameters:
user_id
numberposts
content
images
ie: [battles_list numberposts=3 content=false images=true]
<h4>User page</h4>
If a user is logged in the user page can be added to any page. The cybersoldier userpage is also added to the bottom of the ordinary wordpress user page in admin.
[user_page]
<h3>Customizing</h3>
To customize the battle page, you can just copy the battle.php file found in /tempates in the plugin and paste it in a folder named /cybersoldier in you theme.
</div>
</div>
<?php } ?>