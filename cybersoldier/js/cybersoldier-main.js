jQuery(document).ready(function ($) {
	
	if($( "#svgintro" ).length){
		var a = document.getElementById("c_body1");
		a.addEventListener("load", function() {
			var svgDoc = a.contentDocument; // get the inner DOM of alpha.svg
			svgDoc.documentElement.setAttribute("width", 300);
			svgDoc.documentElement.setAttribute("height", 500);
			var svgRoot = svgDoc.documentElement;
			$("#svgintro").html(svgRoot);
			
			setUserItems();
			$(".body_color1").attr("fill", $("#body_1").val());
		}, false);
		
		function setUserItems(){
			var data = {'action': 'set_soldier_items'};
			
			$.post(
				ajax_object.ajax_url, 
				data,
				function(response) {
					console.log("Wat: "+response);
					
					$.each( response, function( key, item ) {
						//console.log( key + ": " + item.color );
						var type = key;
						var svg_info = item.svg_info;
						var color = item.color;
						
						console.log(type);
						
						$("#character_" + type).text("");
						if(svg_info != false)
							$("#character_" + type).append("<svg>" + svg_info + "</svg>");
						$("." + type + "_color1").attr("fill", "#"+color);
						
					});
					
//					var resp = response;
//					var type = resp.type;
//					var svg_info = resp.svg_info;
//					var color = resp.color;
//					
//					console.log(color);
//					
//					$("#character_" + type).text("");
//					$("#character_" + type).append("<svg>" + svg_info + "</svg>");
//					$("." + type + "_color1").attr("fill", "#"+color);
//					
//					
//					$(".wait_one_minute").slideUp();
				}
			);
			
		}
		
	}
	
	$(".cybersoldier_lines").on("click",".score_number",function(){
		var ths_num = $(this).attr("id").split("_")[1];
		var line_id = $(this).parent().attr("id").split("_")[1];
		
		
		
		var data = {'action': 'add_score_to_battle_item','line_id':line_id,'score':ths_num};
		$(".wait_one_minute").fadeIn().css("display","inline-block");
		$(".out_box").fadeOut();
		$.post(
			ajax_object.ajax_url, 
			data,
			function(response) {
				//alert(ths_num + " battle: "+response);
				//var resp = JSON.parse(response);
				alert(response.html_str);
				$(".out_box").html(response);
				$(".out_box").fadeIn();
				$(".wait_one_minute").slideUp();
			}
		);
		
	});
	
	$(".cybersoldier-admin-content").on("click","#set-character-info", function(){
		
		var data = {'action': 'update_character_info'};
		$(".wait_one_minute").fadeIn().css("display","inline-block");
		$(".out_box").fadeOut();
		$.post(
			ajax_object.ajax_url, 
			data,
			function(response) {
				//var resp = JSON.parse(response);
				$(".out_box").html(response);
				$(".out_box").fadeIn();
				$(".wait_one_minute").slideUp();
			}
		);
	});
		
	$(".cybersoldier_edit").on("click","#testknappen", function(){
		var data = {'action': 'set_soldier_item'};
		$(".wait_one_minute").fadeIn().css("display","inline-block");
		$(".out_box").fadeOut();
		$.post(
			ajax_object.ajax_url, 
			data,
			function(response) {
				//var resp = JSON.parse(response);
				console.log(response);
				$(".wait_one_minute").slideUp();
			}
		);
	});
	
	$(".cybersoldier_edit").on("click",".items_top_icon",function(){
		var ths_type = $(this).attr("id").split("_")[1];
		console.log(ths_type);
		$(".ci_icons_box").fadeOut(200);
		$("#ci_"+ths_type).delay( 800 ).slideDown();
	});
	
	$(".cybersoldier_edit").on("click",".ci_icons_box img", function(){
		var data = {'action': 'set_soldier_item'};
		var ths_id = $(this).attr("id");
		
		var ths_type = $(this).attr("id").split("_");
		//if(ths_type[1] == "remove")
			
		var ths_color = $("#"+ths_type[0]+"_color").val();
		var data = {
			'action': 'set_soldier_item',
			"selected_item":ths_id,
			"selected_item_color":ths_color
		};
		console.log(ths_id);
		$(".out_box").fadeOut();
		$.post(
			ajax_object.ajax_url, 
			data,
			function(response) {
				
				resp = response[0];
				var type = resp.type;
				var svg_info = resp.svg_info;
				var color = resp.color;
				
				$("#ci_"+type+" .ci_icon").removeClass("selected");
				$("#"+ths_id).parent().addClass("selected");
				
				$("#character_" + type).text("");
				$("#character_" + type).append("<svg>" + svg_info + "</svg>");
				$("." + type + "_color1").attr("fill", "#"+color);

				saveImage();
			}
		);
	});	
	
	$(".cybersoldier_edit").on("change","input", function(){
		var ths_type = $(this).attr("id").split("_")[0];
		var color = $(this).val();
		var ths_id = $(this).attr("id");
		var data = {'action': 'set_soldier_item',"selected_item":ths_id,"selected_item_color":color};
		console.log("this id "+color);
		$(".wait_one_minute").fadeIn().css("display","inline-block");
		$(".out_box").fadeOut();
		$.post(
			ajax_object.ajax_url, 
			data,
			function(response) {
				//var resp = JSON.parse(response);
				console.log(response[0]);
				$("." + response[0].type + "_color1").attr("fill", "#"+color);
				saveImage();
			}
		);
	});	
	
	
	$(".cybersoldier_reply_box").on("click","#cybersoldier_submit_reply",function(e){
		e.preventDefault();
		var thsid = $("#current_post").val();
		var reply_text = $("#cybersoldier_reply_text").val();
		
		//console.log("sending" + thsid + " and" + reply_text);
		
		var data = {'action': 'add_battle_reply','post_id':thsid,'reply_text':reply_text};
		
		$(".wait_one_minute").fadeIn().css("display","inline-block");
		$(".out_box").fadeOut();
		$.post(
			ajax_object.ajax_url, 
			data,
			function(response) {
				console.log(response);
				//var resp = JSON.parse(response);
				$(".cybersoldier_lines").append(response.html_str);
				$( ".cybersoldier_reply_box input" ).prop( "disabled", true );
				$(".cybersoldier_reply_box").fadeOut("slow",function(){
					$(".cybersoldier_reply_box").remove();
				});
				$(".wait_one_minute").slideUp();
			}
		);
	});
	
	function saveImage(){
		canvg("canvas", $("#svgintro").html());		
		var c=document.getElementById("canvas");
		var d=c.toDataURL("image/png");
		var data = {'action': 'save_cssvg_image',imageData: d};
		
		$(".out_box").fadeOut();
		$.post(
			ajax_object.ajax_url, 
			data,
			function(response) {
				$("#canvas").hide();
			}
		);
		
		
	}	
});