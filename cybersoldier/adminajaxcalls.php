<?php
$plugins_path = plugins_url ( 'cybersoldier' );

wp_localize_script ( 'cybersoldier-script', 'ajax_object', array (
		'ajax_url' => admin_url ( 'admin-ajax.php' ),
		'we_value' => 1337 
) );

function find_battle_user(){
	$user_search = isset($_POST["find_user"]) ? $_POST["find_user"] : false;
	$user_query = new WP_User_Query( array( 'search' => '*'.esc_attr( $user_search ).'*', ) );
	$playaz = $user_query->get_results();
	$return_playaz = array();
	foreach($playaz as $playah){
		$return_playaz[] = array("id"=>$playah->id,"name"=>$playah->display_name) ;
	}
	echo wp_send_json($return_playaz);
	wp_die();
}
add_action ( 'wp_ajax_find_battle_user', 'find_battle_user' );


function update_character_info() {
	if (current_user_can ( 'administrator' )) {
		global $wpdb;
		include_once ('character_info.php');

		$table_name = $wpdb->prefix . "cybersoldier_character_items";
		$delete = $wpdb->query("TRUNCATE TABLE $table_name");
		
		foreach ( $character_items as $chitem ) {
			$insert_array = array (
					"type" => $chitem ["type"],
					"name" => $chitem ["name"],
					"icon" => $chitem ["icon"],
					"svg_info" => $chitem ["svg_info"] 
			);
			
			$worked = $wpdb->insert ( $table_name, $insert_array);
			
			if ($worked)
				echo "Updated: " . $chitem ["name"] . " OK<br />";
			else
				echo "Updated: " . $chitem ["name"] . " " . $chitem ["type"] . " error: " . $worked . $wpdb->print_error () . "<br />";
		}
		echo "done...";
		
		// echo "<pre>".print_r($character_items,true)."</pre>";
	} else
		echo "you are not admin, are you?";
}
add_action ( 'wp_ajax_update_character_info', 'update_character_info' );

/**
 * Set meta value for user grejs.
 */
function set_soldier_item() {
	global $wpdb;
	$table_name = $wpdb->prefix . "cybersoldier_character_items";
	$selected_item = isset($_POST['selected_item']) ? $_POST['selected_item'] : false;
	$selected_item_color = isset($_POST['selected_item_color']) ? $_POST['selected_item_color'] : false;
	$key_value = explode("_",$selected_item);
	$cleankey = str_replace("cybersoldier", "", $key_value[0]);
	$user_id = get_current_user_id();
	if(isset($key_value[0])){
		if($selected_item && isset($key_value[1]) && is_numeric($key_value[1])){
			$worked = update_user_meta( $user_id, $key_value[0], $key_value[1]);
			$sql = "SELECT * FROM $table_name WHERE type = '".$cleankey."' AND  id = '".$key_value[1]."'";
		}
		else if($selected_item_color){
			$worked_too = update_user_meta( $user_id, $key_value[0]."_color", $selected_item_color);
			$sql = "SELECT * FROM $table_name WHERE type = '".$cleankey."' LIMIT 1";
		}
		
		$item = $wpdb->get_results ( $sql );
		$item[0]->type = isset($item[0]->type) ? $item[0]->type : $cleankey;
		$item[0]->color = $selected_item_color;
		echo wp_send_json($item);
	}
	
	
	wp_die ();
}
add_action ( 'wp_ajax_set_soldier_item', 'set_soldier_item' );

/**
 * Set all user meta stuffs..
 */
function set_soldier_items() {
	global $wpdb;
	$table_name = $wpdb->prefix . "cybersoldier_character_items";
	$user_id = get_current_user_id();
	$return_array = array();
	$user_metas = get_user_meta($user_id);
	//die("<pre>".print_r($user_metas,true)."</pre>");
	foreach($user_metas as $key=>$met){
		if(strpos($key,"cybersoldier")!==false){
			$type = str_replace("cybersoldier", "", $key);
			$value = isset($met[0]) ? $met[0] : $met;
			//echo $type."<br />";
			//die("<pre>".print_r($met[0],true)."</pre>");
			if(strpos($type,"_color")!==false){
				$type = str_replace("_color", "", $type);
				$return_array[$type]["color"] = $value;
			}
			else{
				$return_array[$type]["itemid"] = $value;
				$itemsvg = getCSSVGItem($type,$value);
				$return_array[$type]["svg_info"] = isset($itemsvg->svg_info) ? $itemsvg->svg_info : false;
			}
		}
	}
	
	
	
	echo wp_send_json($return_array);
	


	wp_die ();
}
add_action ( 'wp_ajax_set_soldier_items', 'set_soldier_items' );


function getCSSVGItem($type,$id){
	if(is_numeric($id)){
		global $wpdb;
		$table_name = $wpdb->prefix . "cybersoldier_character_items";
		$sql = "SELECT * FROM $table_name WHERE type = '".$type."' AND  id = '".$id."'";
		$item = $wpdb->get_results ( $sql );
		return isset($item[0]) ? $item[0] : false;
	}
	else
		return false;
}

function saveSVGImage(){
	$plugins_path = plugins_url ( 'cybersoldier' );
	$user_id = 	get_current_user_id();
	$imgData = 	$_POST["imageData"]; // Probably a good idea to sanitize the input!
	$imgData = 	str_replace(" ", "+", $imgData);
	$imgData = 	substr($imgData, strpos($imgData, ","));
	$file =		fopen(WP_PLUGIN_DIR."/cybersoldier/users/".$user_id.'.png', 'wb');
	$suxess = 	fwrite($file, base64_decode($imgData));
	//echo WP_PLUGIN_DIR."/cybersoldier/users/".$user_id.'.png '.print_r($file,true);
	fclose($file);
}
add_action ( 'wp_ajax_save_cssvg_image', 'saveSVGImage' );


?>