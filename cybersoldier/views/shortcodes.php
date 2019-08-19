<?php
/*
 * 
 * Shortcodes for cybersoldier
 *
 */

/**
 * The user page
 */
function user_page(){
	$current_user = wp_get_current_user();
	if ( in_array( 'administrator', (array) $current_user->roles) || in_array( 'player', (array) $current_user->roles) ) {
	$plugins_path = plugins_url ( 'cybersoldier' );
	$role = get_role( 'player' );
	$all_types = cybersoldier_character_items();
	$user_id = get_current_user_id();	
	$body_color = get_user_meta($user_id,"cybersoldierbody_color",true);
	$body_color = $body_color ? $body_color : "EBA16C";
?>

	<div class="cybersoldier_edit bootstrap-wrapper">
		<h2>Cybersoldier</h2>
				To edit your userinformation, such as Username etc, go <a href="<?php echo get_edit_user_link(); ?>">here</a>. That is the usual user-page for Wordpress with some Cybersoldier-things added.

				<p>Settings, gube, lista med battels på gång, scores etc.</p>
		<div class='row'>
			<div class='col-sm-4'>
				<div id="svgintro" class="hasSVG">	
					<object data="<?php echo $plugins_path ?>/character/body1.svg" type="image/svg+xml" id="c_body1"></object>
				</div>
				<canvas id="canvas" width="250" height="400"></canvas>
 				<img id="user_image" />
			</div>
			<div class='col-sm-8'>
				
				<h2>Cybersolider items</h2>

				<div class="row">
					<div class="col-sm-12">
					<div class="items_top_icon" id="topicon_body">Body</div>
					<?php foreach($all_types as $type => $items): ?> 
						<div class="items_top_icon" id="topicon_<?php echo strtolower($type); ?>"><?php echo $type; ?></div>
					<?php endforeach;?>
					</div>
				</div>
				<div class="row ci_gray_boxes_wrap">
					<div class="col-sm-12">
						<div class="ci_icons_box" id="ci_body" style="display:block;">
							<h4>Body</h4>
							<input class="jscolor" id="cybersoldierbody_color" value="<?php echo $body_color ?>">
							<div class='ci_icon'><img src="<?php echo $plugins_path ?>/character_icons/body1.png" id="cybersoldierbody_1" /></div>					
						</div>
					</div>
					
					
					<?php 

					foreach($all_types as $type => $items): 	
						$type_lw = strtolower($type);
						$user_selected_color = get_user_meta($user_id,"cybersoldier".$type_lw."_color",true);
						$user_selected_color = $user_selected_color ? $user_selected_color : "FFFFFF";
						$user_selected_item = get_user_meta($user_id,"cybersoldier".$type_lw,true);	
					?>
						<div class="col-sm-12">
							<div class="ci_icons_box" id="ci_<?php echo $type_lw; ?>">
								<h4><?php echo $type; ?></h4>
								<input class="jscolor" id="<?php echo "cybersoldier".$type_lw; ?>_color" value="<?php echo $user_selected_color; ?>">
								<?php 
									foreach($items as $item){
										$selected = $user_selected_item == $item->id ? "selected" : "";
										$icon = $item->icon;
										$outid = "cybersoldier".$item->type."_".$item->id;
										echo "<div class='ci_icon $selected'><img src='$plugins_path/character_icons/$icon' id='$outid' /></div>";
									}
									echo "<div class='ci_icon'><img src='$plugins_path/character_icons/remove.png' id='".$type_lw."_remove' /></div>";
								?>
								
							</div>
						</div>
					<?php 
						endforeach;
					?>
					
			</div>
			<h2>Your battles</h2>
			<div class="row">
				<?php 
					//print_r(users_battles($current_user->ID));
					$battles = users_battles($current_user->ID);
					foreach($battles as $battle):
						$start_user_info = cybersoldier_user_info($battle->post_author);
						$battle_user_id = get_post_meta($battle->ID,"invited_user",true);
						$battle_user_info = cybersoldier_user_info($battle_user_id);
				?>
					<div class="col-sm-4"><h4><a href="<?php echo $battle->guid; ?>"><?php echo $battle->post_title; ?></a></h4><p><?php echo $battle->post_content; ?></p>
					<strong><?php echo $start_user_info["user"]->display_name; ?> VS <?php echo $battle_user_info["user"]->display_name; ?></strong></div>
				<?php 
					endforeach;
				?>
			</div>
			<h2>Settings</h2>
			<div class="row">
				Cybersolider.com ID
				För att koppla sin gubbe till cs.com skicka upp met populära quotes, wordlrank, mest likes? Högst total score.
				Skicka top 5 quotes.
				http: on site url... ie MyOwnRapbattles.com
			</div>
		</div>
		</div>
	</div>
		
	<?php 
	}else{
		echo __("Sorry bra, no matter what you girl says, you're not a playah...");
	}
}
add_shortcode('user_page', 'user_page');

/**
 * The player page ie, the page show to the world
 */
function player_page(){
	$the_user_id = get_query_var( "csid", 0 );
	//$user = get_user_by("id", $the_user_id);
	$battle_user_info = cybersoldier_user_info($the_user_id);
	$user_description = get_user_meta($the_user_id,"description",true);
	$returnstr = "";
	//echo "<pre>".print_r($user_meta,true)."</pre>";
	$returnstr .= "<div class='bootstrap-wrapper cybersoldier_info_box'><div class='row'><div class='col-sm-6'><div class='cybersoldier_image'><img src='".$battle_user_info["image"]."' /></div></div>";
	$returnstr .= "<div class='col-sm-6'><h2>".$battle_user_info["user"]->display_name."</h2>".$user_description."<br /><br /><h4>Battles</h4>".$battle_user_info["battle_list"]."<br />";
	if(is_user_logged_in()){
		$returnstr .= "<a class='battle_me_bitch_button' href='".admin_url('post-new.php?post_type=battle&csid='.$the_user_id."'>Battle ".$battle_user_info["user"]->display_name."</a>");
	}
	else{
		$returnstr .= sprintf(__("You have to be logged in to battle %s"),$battle_user_info["user"]->display_name);
	}
	$returnstr .= "</div></div>";
	
	return $returnstr;
	//<br /><div class='battle_me_bitch_button'>Battle ".$battle_user_info["user"]->display_name."</div>
}
add_shortcode('player_page', 'player_page');

//battle pages, is just common categories, so hook on if battle...


function battles_list($atts){
	
	$attributes = shortcode_atts( array(
			'user_id' => false,
			'numberposts' => 1,
			'content'=>false,
			'images'=>false,
	), $atts );
	
	$args = array(
			'numberposts' => $attributes["numberposts"],
			'offset' => 0,
			'category' => 0,
			'orderby' => 'post_date',
			'order' => 'DESC',
			'include' => '',
			'exclude' => '',
			'meta_key' => '',
			'meta_value' =>'',
			'post_type' => 'battle',
			'post_status' => 'publish',
			'suppress_filters' => true
	);
	
	$recent_posts = wp_get_recent_posts( $args,ARRAY_A );
	
	foreach($recent_posts as $battle):
		$battle_id = $battle["ID"];

		$users = users_in_battle($battle_id, false);
		$permalink = get_permalink($battle_id);
		$start_user = $users["start_user"];
		$start_user_color = get_user_meta( $start_user["user"]->ID, "cybersoldierbody_color", true ); 
		$invited_user = $users["invited_user"];
		$invited_user_color = get_user_meta( $invited_user["user"]->ID, "cybersoldierbody_color", true );
		$show_images = $attributes["images"] == "true" ? true : false;
		$show_content = $attributes["content"] == "true" ? true : false;
		
		//echo "<pre>".print_r($invited_user,true)."</pre>";
		//echo $show_content ? "show $show_content" . $attributes["content"] : "not show";
	?>
		
		<div class="battle_list_box clearfix">
			<?php if($show_images) : ?>
			<div class="col-xs-4 col-sm-3">
				<img src="<?php echo $start_user["image"]; ?>" />
			</div>
			<?php endif; ?>
			<div class="<?php echo $show_images ? "col-xs-4 col-sm-6" : "col-xs-12" ?>"><h4><a href="<?php echo $permalink;?>"><?php echo $battle["post_title"];?></a></h4><p>
			<div class="battle_list_user_title" style="color: #<?php echo $start_user_color; ?>"><?php echo $invited_user["user"]->user_nicename; ?></div> vs
			<div class="battle_list_user_title" style="color: #<?php echo $invited_user_color; ?>"><?php echo $start_user["user"]->user_nicename; ?></div>
			<?php
				if($show_content)
					echo $battle["post_content"];
				
				echo "<br /><a href='". $permalink ."'>".__("Check the battle out")."</a>";
			?>
			
			</p></div>
			<?php if($show_images) : ?>
				<div class="col-xs-4 col-sm-3">
					<img src="<?php echo $invited_user["image"]; ?>" />
				</div>
			<?php endif; ?>
		</div>
		
	<?php endforeach;
}
add_shortcode('battles_list', 'battles_list');
?>