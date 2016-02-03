<?php
session_start();

//get all the comments
include("includes/connection.php");

//set the current page as the 'recent_article' variable
//so if someone logs in from this page they get sent back to this page
$previous_page = $_SESSION['recent_page'];
$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$_SESSION['recent_page'] = $actual_link;

//first step, get a list of replies to the user's comments
$user_id = $_SESSION['user_id'];

//if we're viewing a conversation, set that convo as 'viewed
if(isset($_GET['id'])){
	$thisMessageId = $_GET['id'];
	$connection->query("UPDATE messages SET viewed=1 WHERE id=$thisMessageId");
}


//create a message set for undread comment responses
$unreadComments = new MessageSet();
//create a message set for read comments
$readComments = new MessageSet();
//get all comments made by user
$comments = $connection->query("SELECT * FROM comments WHERE user_id=$user_id");
while($row = $comments->fetch_assoc() ){
	//get all the replies to the user's comments
	$commentID = $row['id'];
	$replies = $connection->query("SELECT * FROM comments WHERE response_id=$commentID AND user_id!=$user_id");
	while($reply = $replies->fetch_assoc() ){
		//create an inbox object
		$commentResponse = new InboxMessage();
		$commentResponse->id = $reply['id'];
		$commentResponse->comment = $reply['comment'];
		$commentResponse->user_id = $reply['user_id'];
		$commentResponse->date = $reply['date'];
		$commentResponse->article_path = $reply['article_path'];
		$commentResponse->is_comment = 1;
		//get the username from ID
		$userLookup = $connection->query("SELECT username FROM users WHERE id=$commentResponse->user_id");
		while($user = $userLookup->fetch_assoc()){
			$commentResponse->username = $user['username'];
		}
		//if the response has been viewed add it to the unread MessageSet
		if($reply['response_viewed'] == 0){
			$unreadComments->addMessage($commentResponse);
		}else{
			$readComments->addMessage($commentResponse);
		}
	}
	//replies that have already been read
}


//create a set for unread messages
$unreadMessages = new MessageSet();
//create a set for red messages
$readMessages = new MessageSet();
$inbox = $connection->query("SELECT * FROM messages M1 WHERE id=(SELECT MAX(id) FROM messages WHERE from_user=M1.from_user) AND to_user=$user_id ORDER BY id DESC");

while($row = $inbox->fetch_assoc()){
	$newMessage = new InboxMessage();
	$newMessage->comment = $row['message'];
	$newMessage->date = $row['date'];
	$newMessage->is_comment = 0;
	$newMessage->user_id = $row['from_user'];
	$newMessage->id = $row['id'];
	//get the username from ID
	$userLookup = $connection->query("SELECT username FROM users WHERE id=$newMessage->user_id");
	while($user = $userLookup->fetch_assoc()){
		$newMessage->username = $user['username'];
	}
	//if the message has been viewed add it to the 'unread' set
	if($row['viewed'] == 0){
		$unreadMessages->addMessage($newMessage);
	}else{
		$readMessages->addMessage($newMessage);
	}

}

//The InboxMessage class contains all the parameters for each inbox message
//and the  function for actually rendering the html
class InboxMessage {
	public function renderComment(){
		$html = "<div class='comment' data-comment-id='". $this->id ."'>";
		$html .= "<div class='comment-head'>";
		$html .= "<span>$this->username</span> posted at $this->date </div>";
		$html .= "<p>". $this->comment ."</p>";
		$html .= "<div class='comment-actions'><a class='reply-btn'>Reply</a> : <a class='message-user-btn'>Direct Message</a> : <a href='story". $this->article_path ."#comment$this->id'>View Context</a> : <a href='functions/read-inbox-item.php?id=$this->id&context=comment'>Mark as Read</a></div>";
		$html .= "</div>";
		echo $html;
	}
	public function renderMessage(){
		$html = "<a class='inbox-message' href='inbox.php?id=$this->id#bottom'><div class='message-wrapper' data-message-id='". $this->id ."'>";
		$html .= "<div class='message-head'>$this->username</div>";
		$html .= "<p>". $this->commentPreview() ."</p>";
		$html .= "</div></a>";
		echo $html;
	}
	public function commentPreview(){
		$comment = $this->comment;
		if(str_word_count($comment) > 5){
			$pieces = explode(" ", $comment);
			$first_part = implode(" ", array_splice($pieces, 0, 5));
			return $first_part . "...";
		}else{
			return $comment;
		}
	}
}

//this class is for a set of messages, including a 'render' function
class MessageSet {

	public function addMessage($message){
		$this->messages[] = $message;
	}

	public function renderSet(){
		if(count($this->messages) > 0){
			foreach($this->messages as $thisMessage){
				if($thisMessage->is_comment == 1){
					$thisMessage->renderComment();
				}else{
					$thisMessage->renderMessage();
				}
			}
		}
	}
}


function renderConversation(){
	include("includes/connection.php");
	$activeUser = $_SESSION['user_id'];
	$messageID = $_GET['id'];

	$getUsername = $connection->query("SELECT * FROM users WHERE id=(SELECT from_user FROM messages WHERE id=$messageID)");
	while($row = $getUsername->fetch_assoc()){
		$username = $row['username'];
		$fromUser = $row['id'];
	}

	//this probably isn't the best query in the world, but it works
	$conversationMessages = $connection->query("SELECT * FROM messages WHERE to_user=$activeUser AND from_user=$fromUser OR to_user=$fromUser AND from_user=$activeUser");

	echo "<div id='full-conversation'>";

	while($row = $conversationMessages->fetch_assoc()){
		if($row['to_user'] == $activeUser){
			echo "<div class='conversation-message passive-user'>";
			$lastMessageID = $row['id'];
		}else{
			echo "<div class='conversation-message active-user'>";
		}
		echo "<p>" . $row['message'] . " <span>". $row['date'] ."</span></p></div>";
	}

	echo "</div>";

	echo '
	<div id="comment-form">
		<div id="comment-form-inner">
			<form method="POST" action="functions/post-comment.php">
				<div id="text-box">
					<input type="text" placeholder="Send a message..." name="comment" />
					<input type="hidden" name="message_user" value="1" />
					<input type="hidden" name="response_id" value="undefined" />
					<input type="hidden" name="message_id" value="'. $lastMessageID .'" />
				</div>
				<input type="submit" id="comment-btn" value="Send">
			</form>
		</div>
	</div>
	';

	echo "<div id='bottom'></div>";
}


?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Fontpage Vancouver - Inbox</title>
		<link rel="stylesheet" type="text/css" href="skin/style.css" />
		<link rel="stylesheet" type="text/css" href="skin/inbox.css" />
		<script type="text/javascript" src="skin/jquery-1.11.3.min.js"></script>
		<script type="text/javascript" src="skin/article.js"></script>
		<!--[if IE]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<div id="wrapper">

			<div id="main-header">
				<?php
				if(isset($_GET['id'])){
					echo '<a href="inbox.php"><img src="images/back-to-inbox.png" alt="Back to Inbox"/></a>';
				}else{

					if(strpos($previous_page, 'index.php') !== false){
						echo '<a href="'. $previous_page .'"><img src="images/back-to-feed-2.png" alt="Back to Feed"/></a>';
					}else{
						echo '<a href="index.php"><img src="images/back-to-feed-2.png" alt="Back to Feed"/></a>';
					}
				}
				?>

			</div>

			<!-- start of the ARTICLE body -->
			<div id="inbox-body">

				<?php
					echo $user_id;
					//check to see if a message has been sent or reply made
					if( isset( $_SESSION['message_sent'] ) ){
						echo "<div class='top-notification'>Direct Message Sent to User</div>";
						unset( $_SESSION['message_sent'] );
					}else if( isset( $_SESSION['message_marked']) ){
						echo "<div class='top-notification'>Message has been marked as read</div>";
						unset( $_SESSION['message_marked'] );
					}

					//if id is set render the conversation
					//otherwise, render the message sets
					if(isset($_GET['id'])){
						renderConversation();
					}else{
						echo "<div id='new-interactions'>";
						$unreadMessages->renderSet();
						echo "</div>";
						echo "<div id='old-interactions'>";
						$readMessages->renderSet();
						echo "</div>";
					}

				?>
			</div><!-- end of ARTICLE body -->
			<?php

			?>
		</div><!-- end of WRAPPER -->

		<!-- start of footer -->

		<!-- end of footer -->
	</body>
</html>
