<?php
//make sure the timezone is set to the same as the blogs we're pulling from
date_default_timezone_set('America/Los_Angeles');

//Simple HTML Dom is used to parse the webpage to find images
require("includes/simple_html_dom.php");
//simple image is a small script that makes it easier to resize images
require("includes/simpleimage.php");

//list of blogs to pull the information from
$blogs = array(
	"http://www.vancitybuzz.com/feed/",
	"http://604now.com/feed/",
	"http://scoutmagazine.ca/feed",
	"http://www.miss604.com/feed/",
	"http://itstodiefor.ca/feed/",
	"http://www.insidevancouver.ca/feed/"
);

$printNews = array(
  "http://rss.canada.com/get/?F259",
  "http://metronews.ca/news/vancouver/feed/",
  "http://vancouver.24hrs.ca/rss.xml"
  //"http://www.straight.com/content/rss" I removed the straight because their website is whack
);


//custom set
$custom1 = array(
	"http://www.vancitybuzz.com/feed/",
	"http://604now.com/feed/",
	"http://scoutmagazine.ca/feed",
	"http://www.miss604.com/feed/",
	"http://itstodiefor.ca/feed/",
	"http://www.insidevancouver.ca/feed/",
	"http://rss.canada.com/get/?F259",
	"http://metronews.ca/news/vancouver/feed/",
	"http://vancouver.24hrs.ca/rss.xml",
	"http://www.vancitybuzz.com/feed/",
	"http://metronews.ca/news/vancouver/feed/",
	"http://604now.com/feed/",
	"http://www.vancitybuzz.com/feed/",
	"http://vancouver.24hrs.ca/rss.xml"
);

/*
This function will pull all the content onto the local site
and store it in the proper directory structure
*/
function getSingle($feed_url = "http://www.vancitybuzz.com/feed/", $limit = 1) {
    $feed = file_get_contents($feed_url);
    if($feed == false){
	    echo "couldn't get feed for $feed_url <br/>";
	    return;
    }


    try { $x = new SimpleXMLElement($feed); } catch (Exception $e) { echo $e; }

	$i = 0;
	$articlesAdded = 0;
    foreach($x->channel->item as $entry) {
	    if($articlesAdded < $limit){
		    //check to make sure this post is from today
		    $dateCheck = date('j') . " " . date('M');
		    $pubDate = $entry->pubDate;
		    if( strstr($pubDate, $dateCheck) === false){
			    continue;
		    }
		    //get the content for the entry
		    $title = $entry->title;
		    $link = $entry->link;
		    $description = $entry->description;

		    //make the new directory name from the link
		    $dirName = "";
		    if( strstr($link, "/news/metro/") !== false){ // this one is specific to Vancouver Sun
		        $parts = explode("/news/metro/", $link);
		        $parts2 = explode("/", $parts[1]);
		        $dirName = $parts2[0];
		    }else if(strstr($link, ".html") !== false){
			    $linkEnd = substr($link, 0, -5);
		        $dirName = end( explode("/", $linkEnd));
		    }else if(substr($link, -1) == "/"){
			    $linkEnd = substr($link, 0, -1);
		        $dirName = end(explode("/", $linkEnd));
		    }else{
			    $dirName = end(explode("/", $link));
		    }

			//if the directory isn't set move on to the next story
			//also if the directory name is "story.html" then just move on
			//that happens sometimes with Vancouver Sun stories
	        if($dirName == "" || $dirName == "story.html"){
		        echo "Directory name not set for $link <br/>";
		        continue;
	        }

	        //if a dir name is set with '+' instead of '-', change it so it's consistent
	        $dirName = str_replace('+', '-', $dirName);

	        //the year, month, and day are used in the directory structure
	        //we'll also use this info to check if the directory already exists
			$year = date('Y');
			$month = date('m');
			$day = date('d');
			$newDirPath = "articles/$year/$month/$day/$dirName/";

	        if(file_exists($newDirPath)){
		        echo " story exists at $newDirPath, moving to next... <br/>";
		        continue;
		    }
		    echo "adding article at $dirName <br/>";

	        echo "<a href='$link' target='_blank'>$title</a><br/>";

			//get the html from the link so we can look for the image
	        $html = file_get_html($link);
	        if($html){
		        echo "file get html worked <br/>";
	        }else{
		        echo "file get html didn't work!! <br/>";
		        echo "try curl<br/>";
		        $base = $link;
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_URL, $base);
				curl_setopt($curl, CURLOPT_REFERER, $base);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				$str = curl_exec($curl);
				curl_close($curl);

				// Create a DOM object
				$html = new simple_html_dom();
				// Load HTML from a string
				$html->load($str);
	        }
	        $image = "";
			//an array of all the possible DOM paths to the image
			$imagePaths = array(
				"#sec-heading-image img",
				".entry img",
				"#content-area img",
				"#scout-single-post-content img",
				".image-container > span > img",
				".entry-content img",
				".entry .entry-inner img",
				"#imageBox .storyimage img",
				"#storyphoto",
				".feature .article-artwork img",
				".story_photo img",
				"#article .media img",
				".picturefill img"
			);
			//loop through all these Dom Paths until we find one
			// (rather than looping through them all I could just write a bunch of conditionals. If this than use x path, for example )
	        foreach($imagePaths as $path){
		        $imageSearch = $html->find($path);

		        //if we find an image at this path then get the src and fix whatever bugs there might be
		        if(count($imageSearch) > 0){
			        $image = $imageSearch[0]->attr['src'];
			        //if the first source is empty for some reason, try the next one.
			        if($imageSearch[0]->attr['src'] == ""){
				        $image = $imageSearch[1]->attr['src'];
			        }
			        //a fix for vancity buzz
			        if($feed_url == "http://www.vancitybuzz.com/feed/"){
				        $image = $imageSearch[0]->attr['data-lazy-src'];
			        }
			        //for getting the larger image in 'it's to die for' blog
				    $image = str_replace("w368", "w1500", $image);

				    //if we found an image then just break so we don't keep iterating
			        break;
		        }
	        }

			//if we found an image continue
	        if($image !== ""){
				//the full path for our new article content
				$contentFile = "$newDirPath/content.txt";
				$headerFile = "$newDirPath/header.txt";
				$publisherFile = "$newDirPath/publisher.txt";
				$imageFileName = "$newDirPath/image.jpg";
				$linkFile = "$newDirPath/link.txt";

				//fix for the Vancouver Sun images. The Sun's paths are relative, so we need to make them absolute
				if(strstr($link, "/metro/") !== FALSE){
					$image = "http://www.vancouversun.com/news/" . $image;
				}

				//move the image file to local temp directory
				$tmpName = time() . ".jpg";
				$imageFile = file_get_contents($image);
				if($imageFile){
					//if we got the image, move it to a local location
					$moveImage = file_put_contents("images/tmp/$tmpName", $imageFile);
					//if the local copy is made continue
					if($moveImage){
						//if we have an image, go ahead and create the new directory
						if(!file_exists(dirname($file))){
							mkdir(dirname($contentFile), 0777, true);
						}
						//add the content and the header files to the directory
						file_put_contents($contentFile, $description);
						file_put_contents($headerFile, $title);

						//finally, add the image to the directory
						$image = new SimpleImage();
						$image->load("images/tmp/$tmpName");
						$image->resize(500,325);
						$image->save($imageFileName);

						//set the publisher data
						$publisher = "";
						switch($feed_url){
							case "http://www.vancitybuzz.com/feed/":
								$publisher = "<a href='http://www.vancitybuzz.com/'>Vancity Buzz</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at Vancity Buzz</a>";
								break;
							case "http://604now.com/feed/":
								$publisher = "<a href='http://604now.com/'>604 Now</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at 604 Now</a>";
								break;
							case "http://scoutmagazine.ca/feed":
								$publisher = "<a href='http://scoutmagazine.ca/'>Scout Magazine</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at Scout Magazine</a>";
								break;
							case "http://www.miss604.com/feed/":
								$publisher = "<a href='http://www.miss604.com/'>Miss 604</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at Miss 604</a>";
								break;
							case "http://itstodiefor.ca/feed/":
								$publisher = "<a href='http://itstodiefor.ca/'>To Die For Vancouver</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at To Die For Vancouver</a>";
								break;
							case "http://www.insidevancouver.ca/feed/":
								$publisher = "<a href='http://www.insidevancouver.ca/'>Inside Vancouver</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at Inside Vancouver</a>";
								break;
							case "http://rss.canada.com/get/?F259":
								$publisher = "<a href='http://www.vancouversun.com/index.html'>Vancouver Sun</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at the Vancouver Sun</a>";
								break;
							case "http://metronews.ca/news/vancouver/feed/":
								$publisher = "<a href='http://metronews.ca/'>Metro Vancouver</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at Metro Vancouver</a>";
								break;
							case "http://vancouver.24hrs.ca/rss.xml":
								$publisher = "<a href='http://vancouver.24hrs.ca/'>24 Hours Vancouver</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story at 24 Hours Vancouver</a>";
								break;
							default:
								$publisher = "<a href=''>Unknown</a>";
								$linkContent = "<a href='$link' target='_blank'>Read full story here</a>";
								break;
						}
						file_put_contents($publisherFile, $publisher);
						file_put_contents($linkFile, $linkContent);

						//add a count to the $articlesAdded var
						$articlesAdded++;
					}else{
						echo "file put contents FAILED for $image<br/>";
					}
				}else{
					echo " file get contents FAILED for $image <br/>";
				}

	        }else{
		        echo "No image was found <br/>";
	        }

	    }
	   $i++;
    }
}






foreach($custom1 as $single){
	echo "<a href='$single'>$single</a><br/>";
	getSingle($single, 1);
	echo "<br/>";
}

?>