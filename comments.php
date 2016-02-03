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


//The InboxMessage class contains all the parameters for each inbox message
//and the  function for actually rendering the html
class InboxMessage {
	public function renderComment(){
		$html = "<div class='comment' data-comment-id='". $this->id ."'>";
		$html .= "<div class='comment-head'>";
		$html .= "<span>$this->username</span> posted at $this->date </div>";
		#$html .= "<a class='reply-btn'>Reply</a> : <a class='message-user-btn'>Message User</a></div>";
		$html .= "<p>". $this->comment ."</p>";
		$html .= "<div class='comment-actions'><a class='reply-btn'>Reply</a> : <a class='message-user-btn'>Direct Message</a> : <a href='story". $this->article_path ."#comment$this->id'>View Context</a> : <a href='functions/read-inbox-item.php?id=$this->id&context=comment'>Mark as Read</a></div>";
		$html .= "</div>";
		echo $html;
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



?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Fontpage Vancouver - Comments</title>
		<link rel="stylesheet" type="text/css" href="skin/style.css" />
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
					//check to see if a message has been sent or reply made
					if( isset( $_SESSION['message_sent'] ) ){
						echo "<div class='top-notification'>Direct Message Sent to User</div>";
						unset( $_SESSION['message_sent'] );
					}else if( isset( $_SESSION['comment_sent']) ){
						echo "<div class='top-notification'>Thank you for your comment</div>";
						unset( $_SESSION['comment_sent'] );
					}else if( isset( $_SESSION['comment_marked']) ){
						echo "<div class='top-notification'>Comment has been marked as read</div>";
						unset( $_SESSION['comment_marked'] );
					}else if( isset( $_SESSION['message_marked']) ){
						echo "<div class='top-notification'>Message has been marked as read</div>";
						unset( $_SESSION['message_marked'] );
					}

					$unreadComments->renderSet();
					echo "<div id='old-interactions'>";
					$readComments->renderSet();
					echo "</div>";

				?>
			</div><!-- end of ARTICLE body -->
			<?php

			?>
		</div><!-- end of WRAPPER -->

		<!-- start of footer -->

		<!-- end of footer -->
	</body>
</html>
