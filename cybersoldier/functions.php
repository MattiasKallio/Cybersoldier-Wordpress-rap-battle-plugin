<?php

function addTheScripts(){
	$plugins_path = plugins_url ( 'cybersoldier' );
	wp_register_style	('googleFonts_cybersoldier', 'https://fonts.googleapis.com/css?family=Bad+Script|Nothing+You+Could+Do|Rancho|Shadows+Into+Light|Shadows+Into+Light+Two|Waiting+for+the+Sunrise');
	wp_enqueue_style	( 'googleFonts_cybersoldier');
	wp_enqueue_script	('bootstrap-js', $plugins_path . '/js/bootstrap.js',array("jquery"));
	wp_enqueue_style	('bootstrap', $plugins_path . '/css/bootstrap.css');
	wp_enqueue_style 	( 'cybersoldier-main', $plugins_path . '/css/main.css' );
	wp_enqueue_script 	( 'svg-script', $plugins_path . '/js/jquery.svg.js', array ('jquery' ));
	wp_enqueue_script 	( 'canvgrgb-script', $plugins_path . '/js/rgbcolor.js', array ('jquery' ));
	wp_enqueue_script 	( 'canvg-script', $plugins_path . '/js/canvg.js', array ('jquery' ));
	wp_enqueue_script 	( 'cybersoldier-script', $plugins_path . '/js/cybersoldier-main.js', array ('jquery' ));
	wp_enqueue_script 	( 'jscolor', $plugins_path . '/js/jscolor.min.js', array ('jquery' ));

	wp_localize_script ( 'cybersoldier-script', 'ajax_object', array (
			'ajax_url' => admin_url ( 'admin-ajax.php' ),
			'we_value' => 1337
	) );
	
}

add_action('wp_enqueue_scripts','addTheScripts',99999);
add_action('admin_enqueue_scripts','addTheScripts', 99999);

add_filter( 'query_vars', 'add_cs_vars', 10, 1 );
function add_cs_vars($vars)
{
	$vars[] = 'csid'; // var1 is the name of variable you want to add
	return $vars;
}

add_action('init', 'sessionStarter', 1);
function sessionStarter() {
	if(!session_id()) {
		session_start();
	}
}

/**
 * Fix time
 *
 * @param unknown $time        	
 * @param string $show_seconds        	
 * @return string
 */
function cybersoldier_time_fixer($time, $show_seconds = true) {
	if ($show_seconds)
		return date ( "D M d Y H:i:s", $time );
	else
		return date ( "D M d Y H:i", $time );
}

function cybersoldier_user_info($user_id){
	global $wpdb;
	$return_array = array();
	$start_user = get_user_by("ID", $user_id);
	
	$return_array["user"] = $start_user;
	$return_array["battle_list"] = "";
	
	$query = "
	SELECT p.* FROM   $wpdb->postmeta m JOIN $wpdb->posts p ON p.id = m.post_id
	WHERE  ( m.meta_key = 'invited_user' AND m.meta_value = '$user_id' ) AND p.post_status = 'publish' AND p.post_type='battle'
	UNION DISTINCT SELECT *	FROM   $wpdb->posts p
	WHERE  post_author = $user_id AND p.post_status = 'publish' AND p.post_type='battle'
	GROUP  BY p.id
	ORDER  BY post_date DESC
	";
	
	$start_user_battles = $wpdb->get_results($query, OBJECT);
	$image_path = WP_PLUGIN_DIR."/cybersoldier/users/".$user_id.'.png';
	$image_url = WP_PLUGIN_URL."/cybersoldier/users/".$user_id.'.png';
	
	$return_array["image"] =  file_exists($image_path) ? $image_url : false;
	
		
	if ( $start_user_battles ){
		global $post;
		foreach ( $start_user_battles as $post ){ 
			setup_postdata($post);
			$guid = get_the_guid();
			$title = get_the_title();
			$return_array["battle_list"] .= "<div class='battle_box'><a href='$guid'>$title</a></div>";
			//the_content ();
		}
	}
	
	wp_reset_postdata (); 
	
	return $return_array;
}

/**
 * Get battle lines
 */
function cybersoldier_battle_lines($battle_id,$battle_user=0,$battle_over=false){
	global $wpdb;

	$table1 = $wpdb->prefix . "cybersoldier_lines";

	$sql = "SELECT * FROM $table1 WHERE post_id=$battle_id AND date_used IS NOT NULL";
	$lines = $wpdb->get_results ( $sql );
	//echo "$sql<pre>".print_r($lines,true)."</pre>";
	$counter = 1;
	$return_text = "<div class='cybersoldier_lines'>";
	foreach ( $lines as $line ) {
		$left_str = $battle_user==$line->user_id ? " left" : "";
		$user = get_user_by("ID", $line->user_id);
		$return_text .= "<div class='cybersoldier_line_box $left_str'>";
		$return_text .= "<div class='user_info'>".$user->display_name." - ".$line->date_added."</div>";
		$return_text .= "<div class='badass_line'>".$line->line."</div>";
		if(!$battle_over){
			$return_text .= "<div class='score_bar' id='battle_".$line->id."'>
					<div class='score_number' id='score_1' style='background: #e0e0e0;'>1</div>
					<div class='score_number' id='score_2' style='background: #d0d0d0;'>2</div>
					<div class='score_number' id='score_3' style='background: #c0c0c0;'>3</div>
					<div class='score_number' id='score_4' style='background: #b0b0b0;'>4</div>
					<div class='score_number' id='score_5' style='background: #a0a0a0;'>5</div>
				</div>";
		}
		$return_text .= "</div>";
		$counter ++;
		// $lan_parsed = unserialize ( $lan->meta_value );
		// $lans_list = explode ( "\n", $lan_parsed ["fill_choices"] );
	}
	$return_text .= "</div>";
	return $return_text;
	
}


/**
 * Get number of battle lines added
 */
function cybersoldier_battle_lines_number($battle_id,$battle_user=0){
	global $wpdb;

	$table1 = $wpdb->prefix . "cybersoldier_lines";
	$sql = "SELECT * FROM $table1 WHERE post_id=$battle_id AND date_used IS NOT NULL";
	$lines = $wpdb->get_results ( $sql );
	return count($lines);
}

/**
 * Checking if user is in battle
 * @param int $battle_id
 * @param int $user_id
 * @return boolean
 */

function cybersoldier_in_battle($battle_id,$user_id){
	global $wpdb;
	$query = "
	SELECT p.* FROM   $wpdb->postmeta m JOIN $wpdb->posts p ON p.id = m.post_id
	WHERE  ( m.meta_key = 'invited_user' AND m.meta_value = '$user_id' ) AND p.post_status = 'publish' AND p.post_type='battle' AND p.ID=$battle_id
	UNION DISTINCT SELECT *	FROM   $wpdb->posts p
	WHERE  post_author = $user_id AND p.post_status = 'publish' AND p.post_type='battle' AND p.ID=$battle_id
	GROUP  BY p.id
	ORDER  BY post_date DESC
	";
	$user_battles = $wpdb->get_results($query, OBJECT);
	return count($user_battles,true)>0;
}

/**
 * 
 * @param int $battle_id The id of the battle you want
 * @param string $id_only if you only want the id, ids of the users.
 * @param int $opponent_user_id if you only wat the opponent of the user in the current battle.
 * @return different
 */
function users_in_battle($battle_id, $id_only= true, $opponent_user_id = false){
	global $wpdb;

	$post = get_post($battle_id);
	$author_id = $post->post_author;
	$battle_user_id = get_post_meta($battle_id,"invited_user",true);
	
	if($id_only && $opponent_user_id){
		return $author_id == $opponent_user_id ? $battle_user_id : $author_id;
	}
	else if(!$id_only && $opponent_user_id){
		return $author_id == $opponent_user_id ? cybersoldier_user_info($battle_user_id) : cybersoldier_user_info($author_id);
	}
	else if($id_only)
		return array("start_user"=>$author_id,"invited_user"=>$battle_user_id);

	return array("start_user"=>cybersoldier_user_info($author_id),"invited_user"=>cybersoldier_user_info($battle_user_id));
}


function users_battles($user_id){
	global $wpdb;
	$query = "
	SELECT p.* FROM  $wpdb->postmeta m JOIN $wpdb->posts p ON p.id = m.post_id
	WHERE  ( m.meta_key = 'invited_user' AND m.meta_value = '$user_id' ) AND p.post_status = 'publish' AND p.post_type='battle'
	UNION DISTINCT SELECT *	FROM  $wpdb->posts p
	WHERE  post_author = $user_id AND p.post_status = 'publish' AND p.post_type='battle'
	GROUP  BY p.id
	ORDER  BY post_date DESC
	";
	$user_battles = $wpdb->get_results($query, OBJECT);
	return $user_battles;
}

/**
 * Get battle lines
 */
function cybersoldier_who_went_last($battle_id){
	global $wpdb;

	$table1 = $wpdb->prefix . "cybersoldier_lines";
	$sql = "SELECT * FROM $table1 WHERE post_id=$battle_id AND date_used IS NOT NULL ORDER BY date_used DESC LIMIT 1";
	$lines = $wpdb->get_results ( $sql );
	$last_user = isset($lines[0]->user_id) ? $lines[0]->user_id : false; 
	return $last_user;
}

/**
 * Get items
 */
function cybersoldier_character_items($type="all"){
	global $wpdb;
	
	$table1 = $wpdb->prefix . "cybersoldier_character_items";
	if($type=="all"){
		$items_arr = array();
		$sql = "SELECT * FROM $table1 ORDER BY type, name";
		$items = $wpdb->get_results ( $sql );
		//echo "<pre>".print_r($items,true)."</pre>";
		foreach($items as $item){
			
			//echo print_r($item);
			$type_name = ucfirst($item->type);
			$items_arr[$type_name][] = $item;
			
		}
		return $items_arr;
	}
	else{
		$sql = "SELECT * FROM $table1 WHERE type='$type' ORDER BY name";
		return $wpdb->get_results ( $sql );
	}
	$items = $wpdb->get_results ( $sql );
	//echo "$sql<pre>".print_r($lines,true)."</pre>";
	wp_die ();
}


/**
 * For updating the plugin.
 */


$api_url = 'http://webbigt.se/wp-plugin';
$plugin_slug = basename(dirname(__FILE__));


// Take over the update check
add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');

function check_for_plugin_update($checked_data) {
	global $api_url, $plugin_slug;

	if (empty($checked_data->checked))
		return $checked_data;

		$request_args = array(
				'slug' => $plugin_slug,
				'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
		);

		$request_string = prepare_request('basic_check', $request_args);

		// Start checking for an update
		$raw_response = wp_remote_post($api_url, $request_string);

		if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
			$response = unserialize($raw_response['body']);

			if (is_object($response) && !empty($response)) // Feed the update data into WP updater
				$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;

				return $checked_data;
}


// Take over the Plugin info screen
add_filter('plugins_api', 'my_plugin_api_call', 10, 3);

function my_plugin_api_call($def, $action, $args) {
	global $plugin_slug, $api_url;

	if ($args->slug != $plugin_slug)
		return false;

		// Get the current version
		$plugin_info = get_site_transient('update_plugins');
		$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
		$args->version = $current_version;

		$request_string = prepare_request($action, $args);

		$request = wp_remote_post($api_url, $request_string);

		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
		} else {
			$res = unserialize($request['body']);

			if ($res === false)
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
		}

		return $res;
}


function prepare_request($action, $args) {
	global $wp_version;

	return array(
			'body' => array(
					'action' => $action,
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);
}
?>