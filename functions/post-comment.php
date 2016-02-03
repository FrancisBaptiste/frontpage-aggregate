<?php
session_start();

include("../includes/connection.php");

//if this is a direct message to the user create that query and stop the rest of the script
if( isset($_POST['response_id']) && isset($_POST['message_user']) ){

	//prepare inbox query

	$message = $_POST['comment'];
	$from_user =  $_SESSION['user_id'];
	//to get the $to_user look up the user id associated with the comment
	$to_user_comment_id = $_POST['response_id'];

	//if this direct message response is coming from inbox.php then response_id will be undefined, because there is no comment to reference
	//so we swipe this id with the id of the message
	if($to_user_comment_id == "undefined"){
		$to_user_message_id = $_POST['message_id'];
		$to_user_query = $connection->query("SELECT from_user FROM messages WHERE id=$to_user_message_id");
		while($row = $to_user_query->fetch_assoc()){
			$to_user = $row['from_user'];
		}
	}else{
		$to_user_query = $connection->query("SELECT user_id FROM comments WHERE id=$to_user_comment_id");
		while($row = $to_user_query->fetch_assoc()){
			$to_user = $row['user_id'];
		}
	}

	$statement = $connection->prepare("INSERT INTO messages(message, to_user, from_user) VALUES(?,?,?)");
	$statement->bind_param('sii', $message, $to_user, $from_user);
	$execute = $statement->execute();

	//if this direct message is sent from the imbox page we should set this message to viewed
	if(strpos($_SESSION['recent_page'], 'inbox.php') !== false){
		$connection->query("UPDATE messages SET viewed=1 WHERE id=$to_user_message_id");
	}

	if($execute){
		//create a session var for the message
		$_SESSION['message_sent'] = 1;
		//now send back to the main page before the rest fo the function runs
		if(strpos($_SESSION['recent_page'], 'inbox.php?id=') !== false){
			header("Location: " . $_SESSION['recent_page'] . "#bottom");
		}else{
			header("Location: " . $_SESSION['recent_page']);
		}

	}


	exit;

}else{


	//the the response_id is set then this is coming from a reply form and not the normal comment form
	//the statement will be prepared differently depending on what form is used
	if( isset($_POST['response_id']) ){
		//prepart the statement with response_id
		$statement = $connection->prepare("INSERT INTO comments(article_path, user_id, comment, response_id, response_viewed) VALUES(?,?,?,?,?)");
		$response_id = $_POST['response_id'];
		$response_viewed = 0;
		//note the default for response_viewed is NULL, not 0.
	}else{
		//prepart the statement without the response_id
		$statement = $connection->prepare("INSERT INTO comments(article_path, user_id, comment) VALUES(?,?,?)");
	}

	//define the values
	//these values are common for both versions of the statement
	$user_id = $_SESSION['user_id'];
	$comment = $_POST['comment'];

	//if this the comment is being made from the inbox page, we'll have to get the
	//path from the response id
	//else we get it from the previous page
	if(strpos($_SESSION['recent_page'], 'inbox.php') !== false || strpos($_SESSION['recent_page'], 'comments.php') !== false){
		$commentID = $_POST['response_id'];
		$pathQuery = $connection->query("SELECT article_path FROM comments WHERE id=$commentID");
		while($row = $pathQuery->fetch_assoc()){
			$path = $row['article_path'];
		}
	}else{
		$recentPage = $_SESSION['recent_page'];
		$parts = explode("story", $recentPage);
		$path = $parts[1];
	}



	//if the response_id is set add that to the bind
	if( isset($_POST['response_id']) ){
		$statement->bind_param('sisii', $path, $user_id, $comment, $response_id, $response_viewed);
	}else{
		$statement->bind_param('sis', $path, $user_id, $comment);
	}

	//exectute the statement
	$commentSubmit = $statement->execute();

	//if this reply is being made from the inbox or comments page we should set the original comment to viewed
	if(strpos($_SESSION['recent_page'], 'inbox.php') !== false || strpos($_SESSION['recent_page'], 'comments.php') !== false){
		//make sure the 'response_viewed' is set to 1
		$connection->query("UPDATE comments SET response_viewed=1 WHERE id=$commentID");
	}

	//create a session var to show the notification on the article page
	$_SESSION['comment_sent'] = 1;



	//send the user back to the page they came from
	if($commentSubmit){
		header("Location: " . $_SESSION['recent_page']);
	}else{
		echo "something didn't work";
		echo "<br/> path: $path <br/> user: $user_id <br/> comment: $comment <br/> response: $response_id <br/> viewed: $response_viewed <br/>";
	}


}


?>