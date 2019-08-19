<?php
$plugins_path = plugins_url ( 'cybersoldier' );

wp_localize_script ( 'cybersoldier-script', 'ajax_object', array (
		'ajax_url' => admin_url ( 'admin-ajax.php' ),
		'we_value' => 1337 
) );

/**
 * Add battle reply, not for only
 */
function add_battle_reply() {
	global $wpdb;
	$post_id = isset ( $_POST ['post_id'] ) ? $_POST ['post_id'] : 0;
	$battle = get_post ( $post_id );
	$reply_text = stripslashes ( $_POST ['reply_text'] );
	$current_user = wp_get_current_user ();
	$battle_user = users_in_battle ( $post_id, false, $current_user->ID );
	$max_number_of_lines = get_option('number_of_lines', 10);
	$number_of_lines = cybersoldier_battle_lines_number($post_id);
	$who_went_last = cybersoldier_who_went_last ( $post_id );
	$is_in_battle = cybersoldier_in_battle ( $post_id, $current_user->ID );
	$my_turn = $is_in_battle && $current_user->ID != $who_went_last ? true : false;
	
	if ($my_turn && $number_of_lines<$max_number_of_lines) {
		$insert_array = array (
				"post_id" => $post_id,
				"user_id" => $current_user->ID,
				"line" => $reply_text,
				"date_added" => current_time ( "Y-m-d H:i:s" ) 
		);
		if ($post_id != 0)
			$insert_array ["date_used"] = current_time ( "Y-m-d H:i:s" );
		
		$table_name = $wpdb->prefix . "cybersoldier_lines";
		$worked = $wpdb->insert ( $table_name, $insert_array );
		
		$left_str = $battle_user ["user"]->ID == $current_user->ID ? " left" : "";
		$html_str = "";
		$html_str .= "<div class='cybersoldier_line_box $left_str'>";
		$html_str .= "<div class='user_info'>" . $current_user->display_name . " - " . current_time ( "Y-m-d H:i:s" ) . "</div>";
		$html_str .= "<div class='badass_line'>" . $reply_text . "</div>";
		$html_str .= "</div>";
		
		if ($worked) {
			$cybersoldier_send_mail_to_opponent = get_option('cybersoldier_send_mail_to_opponent', true);
			if ($cybersoldier_send_mail_to_opponent) {
				$subject = sprintf ( __ ( "%s just replyed in %s" ), $current_user->display_name, $battle->post_title );
				$message = sprintf ( __ ( "<h4>%s just replyed in %s</h4><p>Your opponent %s just replyed in the battle %s </p><p><a href='%s'>%s</a></p>" ), $current_user->display_name, $battle->post_title, $current_user->display_name, $battle->post_title, get_permalink($battle->ID), get_permalink($battle->ID) );
				$headers = array('Content-Type: text/html; charset=UTF-8');
				$html_str .= "Mail have been sent to ". $battle_user ["user"]->display_name;
				wp_mail ( $battle_user ["user"]->user_email, $subject, $message, $headers );
				//wp_mail ( "kallio.mattias@gmail.com", $subject, $message." ".$battle_user ["user"]->user_email, $headers );
				
			}
			
			echo wp_send_json ( array (
					"result" => "ok",
					"html_str" => $html_str
			) );
		} else
			echo "error: " . $worked . $wpdb->print_error ();
	} else
		die ( "nah, no cheating, I aint yo girlfriend..." );
		
		// send mail if checked
		
	// return box with reply.
	
	wp_die ();
}
add_action ( 'wp_ajax_add_battle_reply', 'add_battle_reply' );

/**
 * Add battle reply, not for only
 */
function add_score_to_battle_item() {
	global $wpdb;
	$line_id = isset ( $_POST ['line_id'] ) ? $_POST ['line_id'] : 0;
	$score = isset ( $_POST ['score'] ) ? $_POST ['score'] : 0;
	//$battle = get_post ( $post_id );
	
	$insert_array = array (
			"line_id" => $line_id,
			"value" => $score
	);
	

	$table_name = $wpdb->prefix . "cybersoldier_scores";
	$worked = $wpdb->insert ( $table_name, $insert_array );
	$allready_voted = false;
	
	if(!isset($_SESSION["vote_".$line_id])){
		$_SESSION["vote_".$line_id] = $score;
	}
	else
		$allready_voted = true;
		//unset($_SESSION["vote_".$line_id]);
	wp_send_json ( array (
			"result" => "ok",
			"html_str" => $allready_voted ? "You allready voted":"Thanks for voting!!"
	) );
	
	
	
	wp_die ();
}
add_action ( 'wp_ajax_add_score_to_battle_item', 'add_score_to_battle_item' );


?>