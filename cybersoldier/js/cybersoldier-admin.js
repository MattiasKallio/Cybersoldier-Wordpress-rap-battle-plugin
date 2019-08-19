jQuery(document).ready(function ($) {

	
	$("#battle_invite").on("input","#invited_user", function(){
		var ths_type = $(this).attr("id").split("_")[0];
		var search = $(this).val();
		var data = {"action": "find_battle_user","find_user":search};
		$(".wait_one_minute").fadeIn().css("display","inline-block");
		$(".out_box").fadeOut();
		$.post(
			ajax_object.ajax_url, 
			data,
			function(response) {
				//var resp = JSON.parse(response);
				console.log(response);
				$("#found_users").text("");
				
				$.each(response, function( index, value ) {
					console.log( index + ": " + value.name );
					$("#found_users").append("<div class='battle_user' id='user_"+value.id+"'>"+value.name+"</div>");
				});
				
				$("#found_users").val(response);
			}
		);
	});
	
	$("#battle_invite").on("click",".battle_user",function(){
		var ths_id = $(this).attr("id").split("_")[1];
		var ths_name = $(this).html();
		$("#invited_user_id").val(ths_id);
		$("#invited_user").val(ths_name);
	});
	
	
	$(".switch").on("click",function(){
		
		var inputten = $(this).find("input[type=checkbox]");
		if(inputten.is(':checked'))
			inputten.attr('checked', false);
		else
			inputten.attr('checked', true);
		console.log("clikk"+inputten.attr("type"));
	})
	
});