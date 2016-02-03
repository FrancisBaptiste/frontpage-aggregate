$(function(){
	//change the $scriptPath on lines 7 and 54 when the site goes live

	//create even listener for the reply button
	$(".reply-btn").click(function(){
		//this path might change when we go live
		$scriptPath = "http://localhost:8888/frontpagevancouver/functions/post-comment.php";

		//create reference to the comment block
		$commentBlock = $(this).parent().parent();
		//get the ID of the comment
		$commentID =  $commentBlock.attr("data-comment-id");
		//remove all instances of #reply-form
		//in the words of Higlander, there can only be one
		$("#reply-form").remove();
		//create the reply form
		$replyForm = "<div id='reply-form'><div id='reply-form-inner'>";
		$replyForm += "<form method='POST' action='" + $scriptPath + "'>";
		$replyForm += "<div id='reply-box'>";
		$replyForm += "<input type='text' placeholder='Reply to this comment...' name='comment' />";
		$replyForm += "</div>";
		$replyForm += "<input type='hidden' name='response_id' value='" + $commentID + "' />";
		$replyForm += "<input type='submit' id='reply-input-btn' value='Reply' />";
		$replyForm += "</form></div></div>";
		//now add #reply-form to the comment that's been clicked
		$commentBlock.children('p').after($replyForm);
		$("#reply-box input").focus();

	});

	//NOTE: this click function is exactly the same as the one above except for
	//the addition of 'message_user' hidden input value, and some different wording
	//create even listener for the reply button
	$(".message-user-btn").click(function(){
		//create reference to the comment block
		$commentBlock = $(this).parent().parent();
		//get the ID of the comment
		$commentID =  $commentBlock.attr("data-comment-id");

		//if this $commentID is undefined, then that means the message is coming from the inbox, and not from the article.php page
		//in this case we need to send another variable to post-comment.php
		if($commentID == undefined){
			$messageID = $commentBlock.attr("data-message-id");
			$formInsert = "<input type='hidden' name='message_id' value='" + $messageID + "' />";
		}else{
			$formInsert = "";
		}

		//remove all instances of #reply-form
		//in the words of Higlander, there can only be one
		$("#reply-form").remove();

		//this path might change when we go live
		$scriptPath = "http://localhost:8888/frontpagevancouver/functions/post-comment.php";
		$replyForm = "<div id='reply-form'><div id='reply-form-inner'>";
		$replyForm += "<form method='POST' action='" + $scriptPath + "'>";
		$replyForm += "<div id='reply-box'>";
		$replyForm += "<input type='text' placeholder='Send user a direct message...' name='comment' />";
		$replyForm += "</div>";
		$replyForm += "<input type='hidden' name='response_id' value='" + $commentID + "' />";
		$replyForm += $formInsert;
		$replyForm += "<input type='hidden' name='message_user' value='1' />";
		$replyForm += "<input type='submit' id='reply-input-btn' value='Message' />";
		$replyForm += "</form></div></div>";
		//now add #reply-form to the comment that's been clicked
		$commentBlock.children('p').after($replyForm);
		$("#reply-box input").focus();
	});

	//create the scroll event
	$(window).scroll(function(){
		$placeholderPosition = $("#comment-form-placeholder").offset().top;
		$scrollPosition = $(window).scrollTop() + $(window).height() - 40;
		if($scrollPosition >= $placeholderPosition){
			if( $("#comment-form").css('position') == 'fixed' ){
				$("#comment-form").css({'position': 'absolute', 'top': $placeholderPosition, 'bottom': 'auto'});
				//animate the color
				$("#comment-btn").css({'background': '#888', 'color': 'white', 'border': '1px solid #888'});
				$("#comment-form #text-box").css("border-top", "1px solid #888");
			}
		}else{
			if( $("#comment-form").css('position') == 'absolute' ){
				$("#comment-form").css({'position': 'fixed', 'top': 'auto', 'bottom': 0});
				//animate the color
				$("#comment-btn").css({'background': '#EFEFEF', 'color': 'white', 'border': '1px solid #DDD', 'color': '#666'});
				$("#comment-form #text-box").css("border-top", "1px solid #DDD");
			}
		}
	});

	//if the window is resized make sure the comment form is in the right place
	$(window).resize(function(){
		$placeholderPosition = $("#comment-form-placeholder").offset().top;
		$scrollPosition = $(window).scrollTop() + $(window).height() - 40;
		if($scrollPosition >= $placeholderPosition){
			if( $("#comment-form").css('position') == 'absolute' ){
				$("#comment-form").css({'top': $placeholderPosition, 'bottom': 'auto'});
			}
		}
	});


	$(window).scrollTop(1);
});