<?php

$date = $_GET['date'];
$title = $_GET['title'];

$directory = str_replace("-", "/", $date);

//get today's Year, Month, and Day
$year = date('Y');
$month = date('m');
$day = date('d');

//get the Year, Month, and Day of the article we're looking at
$dateParts = explode("-", $date);
$articleY = $dateParts[0];
$articleM = $dateParts[1];
$articleD = $dateParts[2];

//if the article's date is the same as today, then the back button will be to index
//if not the the back button has to go back to the day we were looking at
if($year == $articleY && $month == $articleM && $day == $articleD){
	$back = "index.php";
}else{
	$back = "index.php?date=$date";
}

$headline = file_get_contents("articles/" . $directory . "/" . $title . "/header.txt");
$image = "articles/" . $directory . "/" . $title . "/image.jpg";
$content = file_get_contents("articles/" . $directory . "/" . $title . "/content.txt");
$publisher = file_get_contents("articles/" . $directory . "/" . $title . "/publisher.txt");
$link = file_get_contents("articles/" . $directory . "/" . $title . "/link.txt");

?>

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Fontpage Vancouver</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
		<!--[if IE]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<div id="wrapper">


			<div id="backButton">
				<a href="<?php echo $back; ?>">Back to Feed</a>
			</div>

			<h1><?php echo $headline; ?></h1>
			<img src="<?php echo $image; ?>" alt="<?php echo $headline; ?>">
			<h2><?php echo $publisher; ?></h2>
			<?php echo $content; ?>

			<div id="source-link">
				<?php echo $link; ?>
			</div>

		</div>
	</body>
</html>