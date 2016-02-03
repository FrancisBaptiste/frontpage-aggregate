<?php

//this function scans a directory and returns the directoy in order of when the files were created
function scan_dir($dir) {
	//first check to see if this directory even exists. If it does proceed
	if(file_exists($dir)){
		$ignored = array('.', '..', '.svn', '.htaccess', '.DS_Store');
	    $files = array();
	    foreach (scandir($dir) as $file) {
	        if (in_array($file, $ignored)) continue;
	        $files[$file] = filemtime($dir . '/' . $file);
	    }
	    arsort($files);
	    $files = array_keys($files);
	    return ($files) ? $files : false;
	}else{
		//if the directory doesn't exist return false
		return false;
	}

}

?>