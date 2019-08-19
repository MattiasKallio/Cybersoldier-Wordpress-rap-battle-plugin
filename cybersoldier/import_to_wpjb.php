<?php
// create custom plugin import to wpjb page menu

add_action('admin_menu', 'kallios_wpjb_af_import2');

function kallios_wpjb_af_import2() {
	add_submenu_page('kallio-wpjb-af-import-settings','Move to WPJB', 'Move to WPJB', 'administrator', 'kallio-wpjb-af-import-import-to-wpjb', 'kallios_wpjb_af_import_to_wpjoboard_page',plugins_url('/assets/icon.png', __FILE__));
}

// function register_kallios_wpjb_af_import_settings() {
// 	//register our settings
// 	register_setting( 'kallio-wpjb-af-import-settings-group', 'kallios_wpjb_af_import_ids');
// 	register_setting( 'kallio-wpjb-af-import-settings-group', 'kallios_wpjb_af_import_url');
// }

function kallios_wpjb_af_import_to_wpjoboard_page() {
?>
<div class="wrap kallio-wpjb-af-import">
	<h2>Kallios import från Arbetsförmedlingen</h2>
	<h3>Importera till WPJB</h3>
	<p>Någon skön lista.</p>
	<div class="wpaf_leftside">
		<h3>Flytta till WPJB</h3>
		<p>Hämta alla jobb från temp-databasen och lista dem med lite lagom mycket text och någon kryssruta så att det ser tjusigt ut.</p>
		<input type="button" id="get-jobs" value="Hämta lista med jobb">
		<input type="button" id="save-to-wpjb" value="Spara markerade i WPJB"><br />
		<div class="wait_one_minute">Väntar och väntar...<br />Det kan ta en stund.<br /><img src="<?php echo plugins_url('/assets/loader.gif', __FILE__)?>"></div>
		<div class="out_box"></div>
	</div>
</div>
<?php } ?>