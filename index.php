<?php
//make sure the timezone is set to the same as the blogs we're pulling from
date_default_timezone_set('America/Los_Angeles');

//this function scans a directory and returns the directoy in order of when the files were created
function scan_dir($dir) {
    $ignored = array('.', '..', '.svn', '.htaccess');
    $files = array();
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }
    arsort($files);
    $files = array_keys($files);
    return ($files) ? $files : false;
}

//if a date is set then use those numbers
//otherwise use today's date to build the directory
if(isset($_GET['date'])){
	$dateParts = explode("-", $_GET['date']);
	$year = $dateParts[0];
	$month = $dateParts[1];
	$day = $dateParts[2];
}else{
	$year = date('Y');
	$month = date('m');
	$day = date('d');
}

//create the dir path from the date
$dir = "articles/$year/$month/$day";
//create a date variable to send to the article page
$dateVar = "$year-$month-$day";

//scanning the directory for this day
$articles = scan_dir($dir);

//for rendering at the top of the page
$dateObj = DateTime::createFromFormat('!Y-m-d', "$year-$month-$day");
$readableDate = $dateObj->format("l, F j");


//create variables for yesterday, so we can create a link at the bottom of the page
//and a user can move to aricles from yesterday
$yesterday = date("l, F j", strtotime("$month/$day/$year") - 60 * 60 * 24);
$Yyear = date('Y', strtotime("$month/$day/$year") - 60 * 60 * 24);
$Ymonth = date('m', strtotime("$month/$day/$year") - 60 * 60 * 24);
$Yday = date('d', strtotime("$month/$day/$year") - 60 * 60 * 24);
$Ydir = "articles/$YYear/$YMonth/$Yday";

$yesterdayVar = "$Yyear-$Ymonth-$Yday";

//if the date is set we need to check if there's a day after this
//and add a link for that day

?>


<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>FontPage Vancouver</title>
		<link rel="stylesheet" type="text/css" href="style.css" />
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
		<!--[if IE]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<div id="wrapper">

			<div id="date">
				<h3><?php echo $readableDate; ?></h3>
			</div>

			<?php
			foreach($articles as $article){
				$image = $dir . "/" . $article . "/image.jpg";
				$title = file_get_contents($dir . "/" . $article . "/header.txt");
				$publisher = file_get_contents($dir . "/" . $article . "/publisher.txt");
			?>

			<div class="article">
				<a href='article.php?date=<?php echo $dateVar; ?>&title=<?php echo $article; ?>'><img src='<?php echo $image; ?>' alt='<?php echo $title; ?>' /></a>
				<h1><a href='article.php?title=<?php echo $article; ?>'><?php echo $title; ?></a></h1>
				<h2><?php echo $publisher; ?></h2>
			</div>

			<?php
			} // end of the loop
			?>

			<div id="yesterday">
				<a href="index.php?date=<?php echo $yesterdayVar; ?>"><?php echo $yesterday; ?></a>
			</div>
		</div>
	</body>
</html>