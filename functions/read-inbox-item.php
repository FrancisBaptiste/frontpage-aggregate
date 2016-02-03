<?php
	session_start();
	include("../includes/connection.php");

	//only continue if the user is logged in and the previous page was the inbox
	if(isset($_SESSION['user_id']) && strpos($_SESSION['recent_page'], 'inbox.php') !== false || isset($_SESSION['user_id']) && strpos($_SESSION['recent_page'], 'comments.php') !== false){

		$user = $_SESSION['user_id'];

		$id = $_GET['id'];
		$context = $_GET['context'];

		if($context == "comment"){
			//check if the current user is the recepient of the comment
			//this is what will stop someone from theoretically running 'read-inbox-item.php?id=x&context=comment' for every ID
			//because we're using GET, someone could do that and mark every comment as read.
			//this way, you can only mark a comment as read if you're signed in as the appropriate user

			$confirmUser = $connection->query("SELECT user_id FROM comments WHERE id=(SELECT response_id FROM comments WHERE id=$id)");
			while($row = $confirmUser->fetch_assoc()){
				$confirmID = $row['user_id'];
				//if the confirmID is that of the current user, continue
				if($confirmID == $user){
					$mark = $connection->query("UPDATE comments SET response_viewed=1 WHERE id=$id ");
					$_SESSION['comment_marked'] = 1;
					header("Location: " . $_SESSION['recent_page']);
				}
			}

		}else if($context == "message"){
			//same idea as above. We want to make sure that the user signed in is the one making the request
			//so that this script can only be used to affect the logged in user's messages
			$confirm = $connection->query("SELECT to_user FROM messages WHERE id=$id");
			while($row = $confirm->fetch_assoc()){
				if($user == $row['to_user']){
					$mark = $connection->query("UPDATE messages SET viewed=1 WHERE id=$id ");
					$_SESSION['message_marked'] = 1;
					header("Location: " . $_SESSION['recent_page']);
				}
			}

		}

	}

?>