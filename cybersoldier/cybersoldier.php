<?php
/**
 * Plugin Name: Cybersoldier
 * Plugin URI: http://cybersoldier.com/wp-plugin
 * Description: A rap battle plugin for Wordpress.
 * Version: 0.3
 * Author: Mattias Kallio
 * Author URI: http://webbigt.se
 * Text Domain: kallio-wpjb-af-import
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
global $plugin_path;
$plugin_path = WP_PLUGIN_DIR . '/cybersoldier/';
require_once ($plugin_path.'settings.php');
require_once ($plugin_path.'functions.php');
require_once ($plugin_path.'adminajaxcalls.php');
require_once ($plugin_path.'ajaxcalls.php');
require_once ($plugin_path.'import_to_wpjb.php');
require_once ($plugin_path.'views/shortcodes.php');

function admin_scripts_cs() {
	$plugins_url = plugins_url ( 'cybersoldier' );
	wp_enqueue_script( 'cs_admin_scripts', $plugins_url . '/js/cybersoldier-admin.js' );
}
add_action( 'admin_enqueue_scripts', 'admin_scripts_cs' );

/**
 * Adding stuff to profile page
 */

add_action ( 'show_user_profile', 'add_2_user_profile' );
add_action ( 'edit_user_profile', 'add_2_user_profile' );
function add_2_user_profile() {
	echo do_shortcode ( "[user_page]" );
}

/* Filter the single_template with our custom function*/
add_filter('single_template', 'battle_template');

function battle_template($single) {
	global $wp_query, $post, $plugin_path;

	/* Checks for single template by post type */
	if ($post->post_type == "battle"){
		$template_path = get_template_directory();
		if(file_exists($template_path.'/cybersoldier/battle.php'))
			return $template_path.'/cybersoldier/battle.php';
		else if(file_exists($plugin_path.'templates/battle.php'))
			return $plugin_path.'templates/battle.php';
	}
	return $single;
}

/**
 * Battle post type
 */
function cybersoldier_add_battle() {
	
	$battle = register_post_type ( 'battle', [ 
			'labels' => [ 
					'name' => __( 'Battles',"cybersoldier" ),
					'singular_name' => __( 'Battle' ,"cybersoldier" ) 
			],
			'public' => true,
			'has_archive' => true,
			'custom-fields' => true,
			'capability_type' => 'battle',
			'capabilities' => array (
					'publish_posts' => 'publish_battles',
					'edit_posts' => 'edit_battles',
					'edit_others_posts' => 'edit_others_battles',
					'read_private_posts' => 'read_private_battles',
					'edit_post' => 'edit_battle',
					'delete_posts' => 'delete_battle',
					'read_post' => 'read_battle' 
			),
			'map_meta_cap' => true 
	] );
}

add_action ( 'init', 'cybersoldier_add_battle' );

/**
 * Add metabox for battles
 */
add_action( 'add_meta_boxes', 'cybersoldier_metaboxes' );
function cybersoldier_metaboxes(){
	add_meta_box('battle_invite', __( 'Invite User' ,"cybersoldier" ), 'battle_invite_metabox', 'battle', 'side', 'default');
}
function battle_invite_metabox() {
	global $post;
	// Noncename needed to verify where the data originated
	//echo '<input type="hidden" name="battelinvite_noncename" id="battelinvite_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	wp_nonce_field( 'battelinvite_nonceaction', 'battelinvite_noncename' );
	// Get the location data if its already been entered

	$battle_invite = get_post_meta($post->ID, 'invited_user', true);
	
	// Echo out the field
	if($battle_invite){
		$battle_user = get_user_by("ID", $battle_invite);
		echo "<h4>".__("User invited: ","Cybersoldier")." ".$battle_user->display_name."</h4>";
	}
	else{
		$getid = isset($_GET["csid"]) ? $_GET["csid"] : 0;
		$the_user_id = $battle_invite ? $battle_invite : $getid ;
		$battle_user_info = cybersoldier_user_info($the_user_id);
		echo '<input type="text" name="invited_user" id="invited_user" value="'.$battle_user_info["user"]->display_name.'" class="widefat" />';
		echo '<input type="hidden" name="invited_user_id" id="invited_user_id" value="' . $the_user_id  . '" />';
		echo "<div id='found_users'></div>";
		//echo '<input type="button" name="invited_user_button" id="invited_user_button" value="'.__( 'Find User' ,"cybersoldier" ).'" class="widefat" />';
	}
}


/**
 * Handles saving the meta box.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return null
 */
add_action( 'save_post', 'save_battle_invite', 10, 2 );
add_action( 'edit_post', 'save_battle_invite', 10, 2 );
function save_battle_invite( $post_id, $post ) {
	// Add nonce for security and authentication.
	$nonce_name   = isset( $_POST['battelinvite_noncename'] ) ? $_POST['battelinvite_noncename'] : '';
	$nonce_action = 'battelinvite_nonceaction';

	// Check if nonce is set.
	if ( ! isset( $nonce_name ) ) {
		return;
	}

	// Check if nonce is valid.
	if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
		return;
	}

	// Check if user has permissions to save data.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Check if not an autosave.
	if ( wp_is_post_autosave( $post_id ) ) {
		return;
	}

	// Check if not a revision.
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	// OK, we're authenticated: we need to find and save the data
	$inv_user = $_POST['invited_user_id'];
	// save data in INVISIBLE custom field (note the "_" prefixing the custom fields' name
	//TODO: send email with invite and make a decline page. Or not show anything if not logged in.
	$current_user = wp_get_current_user();
	$battle = $post;
	$subject = sprintf ( __ ( "%s invited you to the battle %s" ), $current_user->display_name, $battle->post_title );
	$message = sprintf ( __ ( "<h4>%s have invited you to the battel %s</h4><p>To show %s who is the battle master of all universes, battle this cyber soldier in %s by visiting the link below.</p><p><a href='%s'>%s</a></p>" ), 
			$current_user->display_name, 
			$battle->post_title, 
			$current_user->display_name, 
			$battle->post_title, 
			get_permalink($battle->ID), 
			get_permalink($battle->ID) 
		);
	$headers = array('Content-Type: text/html; charset=UTF-8');
	$mailsent = wp_mail ( $battle_user ["user"]->user_email, $subject, $message, $headers );
	if($mailsent)
		update_post_meta($post_id, 'invited_user', $inv_user);
	else {
		update_post_meta($post_id, 'invited_user', $inv_user);
		add_action( 'admin_notices', 'addbattele__error' );
	}
	add_action( 'admin_notices', 'addbattele__error' );
		//echo "This should probably be a nicer warning, but unfortunatly there were no mail sent to battle opponent. $subject $message";
}

function addbattele__error() {
	$class = 'notice notice-error';
	$message = __( 'Irks! An error has occurred.', 'sample-text-domain' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
}

/**
 * Player user type
 */
function cybersoldier_add_player() {
	$result = add_role ( 'player', __( 'Cybersoldier' ) );
}
add_action ( 'init', 'cybersoldier_add_player' );

/**
 * Add capabilites
 */
function cybersoldier_add_user_caps() {
	$role = get_role ( 'player' );
	$role->add_cap ( 'read' );
	$role->add_cap ( 'edit_battle' );
	$role->add_cap ( 'edit_battles' );
	$role->add_cap ( 'edit_published_battles' );
	$role->add_cap ( 'publish_battles' );
	
	$role = get_role ( 'administrator' );
	$role->add_cap ( 'read' );
	$role->add_cap ( 'edit_battle' );
	$role->add_cap ( 'edit_battles' );
	$role->add_cap ( 'edit_published_battles' );
	$role->add_cap ( 'publish_battles' );	
}
add_action ( 'admin_init', 'cybersoldier_add_user_caps' );

/**
 * install stuff
 */
function cybersoldier_install() {
	global $wpdb;
	/*
	 * We'll set the default character set and collation for this table.
	 * If we don't do this, some characters could end up being converted
	 * to just ?'s when saved in our table.
	 */
	$charset_collate = '';
	
	if (! empty ( $wpdb->charset )) {
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}
	
	if (! empty ( $wpdb->collate )) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}
	
	$table_name = $wpdb->prefix . "cybersoldier_lines";
	$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
		id INT(11) NOT NULL AUTO_INCREMENT,
		post_id INT(11) NOT NULL,
		user_id INT(11) NOT NULL,
		line VARCHAR(255) NOT NULL,
		date_added DATETIME,
		date_used DATETIME,
		PRIMARY KEY (id)
	);";
	
	$wpdb->query ( $sql );
	
	$table_name = $wpdb->prefix . "cybersoldier_scores";
	$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
		id INT(11) NOT NULL AUTO_INCREMENT,
		line_id INT(11) NOT NULL,
		value INT(11) NOT NULL,
		post_user INT(11) NOT NULL,
		PRIMARY KEY (id)
	);";
	
	$wpdb->query ( $sql );
	
	
	$table_name = $wpdb->prefix . "cybersoldier_character_items";
	$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
		id INT(11) NOT NULL AUTO_INCREMENT,
		type VARCHAR(255) NOT NULL,
		name VARCHAR(255) NOT NULL,
		icon VARCHAR(255) NOT NULL,
		svg_info TEXT NOT NULL,
		PRIMARY KEY (id)
	);";
	
	$wpdb->query ( $sql );	
}
function cybersoldier_uninstall() {
	global $wpdb;
	
	// Delete any options thats stored also?
	// delete_option('wp_yourplugin_version');
	$table_name = $wpdb->prefix . "cybersoldier_line";
	//$wpdb->query ( "DROP TABLE IF EXISTS $table_name" );
	$table_name = $wpdb->prefix . "cybersoldier_score";
	//$wpdb->query ( "DROP TABLE IF EXISTS $table_name" );
}

register_activation_hook ( __FILE__, 'cybersoldier_install' );
register_deactivation_hook ( __FILE__, 'cybersoldier_uninstall' );

