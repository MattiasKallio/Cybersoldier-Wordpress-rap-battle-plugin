<!-- 
         ______   ___  ___  _______    _______   _______                      
        /" _  "\ |"  \/"  ||   _  "\  /"     "| /"      \                     
       (: ( \___) \   \  / (. |_)  :)(: ______)|:        |                    
        \/ \       \\  \/  |:     \/  \/    |  |_____/   )                    
        //  \ _    /   /   (|  _  \\  // ___)_  //      /                     
       (:   _) \  /   /    |: |_)  :)(:      "||:  __   \                     
        \_______)|___/     (_______/  \_______)|__|  \___)                    
                                                                       
  ________   ______    ___       ________   __     _______   _______   
 /"       ) /    " \  |"  |     |"      "\ |" \   /"     "| /"      \  
(:   \___/ // ____  \ ||  |     (.  ___  :)||  | (: ______)|:        | 
 \___  \  /  /    ) :)|:  |     |: \   ) |||:  |  \/    |  |_____/   ) 
  __/  \\(: (____/ //  \  |___  (| (___\ |||.  |  // ___)_  //      /  
 /" \   :)\        /  ( \_|:  \ |:       :)/\  |\(:      "||:  __   \  
(_______/  \"_____/    \_______)(________/(__\_|_)\_______)|__|  \___) 
                                    
						   Wanna see more? 
                    Visit http://cybersolider.com
                                                                       
 -->
<?php 
	get_header(); 
?>
			
			<div id="content" class="clearfix row dotted-border bootstrap-wrapper">		
				<div id="main" role="main">
					<div class="main-content clearfix">
					<?php 
						if (have_posts()) : while (have_posts()) : the_post(); 
						//Start user stuff
						$current_user_id = get_current_user_id();
						$battle_id = get_the_ID();
						$start_user_id = get_the_author_ID();
						$battle_user_id = get_post_meta(get_the_ID(),"invited_user",true);
						$max_number_of_lines = get_option('number_of_lines', 10);
						$refresh_page_for_opponent = get_option('refresh_page_for_opponent', true);
						$time_to_end = get_option('cybersoldier_time_to_end', 24);
						$cybersoldier_url_to_infopage = get_option('cybersoldier_url_to_infopage', 'cybersolider/');
						$number_of_lines = cybersoldier_battle_lines_number($battle_id);
						$start_user_info = cybersoldier_user_info($start_user_id);
						$battle_user_info = cybersoldier_user_info($battle_user_id);
						$start_time = get_the_time("Y-m-d H:m:s");
						$end_date = date("Y-m-d H:m:s", strtotime('+'.$time_to_end.' hours', strtotime($start_time)));
						$battle_over = strtotime($end_date) - time() < 0;
						$start_date = new DateTime();
						$since_start = $start_date->diff(new DateTime($end_date));
						$time_left = !$battle_over ? $since_start->days.' days ' . date("H:i:s",strtotime($since_start->h . ":" .$since_start->i. ":".$since_start->s)) : "battle over, and you can no longer vote";
					?>
					<input type="hidden" id="battle_id" value="<?php echo $battle_id; ?>">
					<div class="col-sm-3">
						<div class="battle_user">
							<h2><a href="<?php echo $cybersoldier_url_to_infopage; ?>?csid=<?php echo $start_user_info["user"]->id ?>"><?php echo $start_user_info["user"]->display_name; ?></a></h2>
							<div class="row">
								<div class="col-xs-12">
									<img src="<?php echo $start_user_info["image"]; ?>" />
								</div>
								<div class="col-xs-12">
									<div class="battle_user_battles">
										<?php echo $start_user_info["battle_list"]; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<article class="col-sm-6" id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">
						<div class="page-header"><h1><?php the_title(); ?></h1></div>
						<div class="battle-top">
						<div class="battle-time-left"><?php echo sprintf(__("<div class='time-text'>Time left: <br /></div><div class='time'>%s</div>"),$time_left) ?></div>
						<div class="meta"><?php __("Started", "cybersoldier"); ?> <time datetime="<?php echo the_time('Y-m-j'); ?>" pubdate><?php the_time(); ?></time> <?php _e("by", "cybersoldier"); ?> <a href="<?php echo $cybersoldier_url_to_infopage; ?>?csid=<?php echo $start_user_info["user"]->id ?>"><?php echo $start_user_info["user"]->display_name; ?></a> </div>
						 
							<?php
							//echo "--- datumen $start_time : $end_date : Time left: ".$date_between." ---<br >";
								$content =  get_the_content();
								$content = apply_filters( 'the_content', $content );
								echo $content ; 
							?>
						</div><!--End of battle top-->
							<?php	
								echo cybersoldier_battle_lines(get_the_ID(),$start_user_id,$battle_over);
								
								$who_went_last = cybersoldier_who_went_last(get_the_ID());
								$is_in_battle = cybersoldier_in_battle($battle_id,$current_user_id);
								
								$my_turn = $is_in_battle && $current_user_id!=$who_went_last ? true : false;
								
								if($my_turn && $number_of_lines<$max_number_of_lines):
								$current_user = get_user_by("ID", $current_user_id);
							?>
							<div class="cybersoldier_reply_box">
								<?php printf(__("It's your turn %s, you better reply now, before you get defeated", "cybersoldier"),$current_user->display_name); ?>
								<form id="cybersoldier_reply">
								<textarea id="cybersoldier_reply_text"></textarea>
								<input type="hidden" id="current_post" value="<?php echo get_the_ID();?>" />
								<input type="submit" id="cybersoldier_submit_reply" value="<?php _e("Hit it", "cybersoldier"); ?>">
								</form>
							</div>
							<?php 
								elseif($is_in_battle && $refresh_page_for_opponent):
									header('Refresh: 10');
								endif;
								//comments_template('',true); 
							?>
					</article>
					<div class="col-sm-3">
						<div class="battle_user">
							<h2><a href="<?php echo $cybersoldier_url_to_infopage; ?>?csid=<?php echo $battle_user_info["user"]->id ?>"><?php echo $battle_user_info["user"]->display_name; ?></a></h2>
							<div class="row">
								<div class="col-xs-12">
									<img src="<?php echo $battle_user_info["image"]; ?>" />
								</div>
								<div class="col-xs-12">
									<div class="battle_user_battles">
										<?php echo $battle_user_info["battle_list"]; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					</div>
					<?php endwhile; ?>			
					
					<?php else : ?>
					
					<article id="post-not-found">
					    <header>
					    	<h1><?php _e("Not Found", "wpwebbigt"); ?></h1>
					    </header>
					    <section class="post_content">
					    	<p><?php _e("Sorry, but the requested resource was not found on this site.", "wpwebbigt"); ?></p>
					    </section>
					    <footer>
					    </footer>
					</article>
					
					<?php endif; ?>
			
				</div> <!-- end #main -->
    
			</div> <!-- end #content -->

<?php get_footer(); ?>