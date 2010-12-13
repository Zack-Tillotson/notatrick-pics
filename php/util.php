<?php

include_once 'constants.php';
include_once 'destructs.php';

function funcSort($itemA, $itemB) {
	$diff = $itemB->changeDate - $itemA->changeDate;
	if($diff == 0) {
		$diff = $itemA->name < $itemB->name ? -1 : 1;
	}
	return -1 * $diff;
}

function ensureValidUrl() {

	if(!file_exists(HOME_LOC . substr($_SERVER['REQUEST_URI'], 1))) {
		header("HTTP/1.0 404 Not Found");
	} else {


	}

}

// Will get the full list of folders starting at the base location
function getFolderList($reqPath = null) {

	$reqCmd = LS_CMD;
	$flatLook = true;

	if($reqPath == null) {
		$reqPath = BASE_LOC;
		$flatLook = false;
	}

	$reqPath = HOME_LOC . $reqPath;

	// Run the system command to get the folder's items
	exec($reqCmd . " " . $reqPath, $lines);

	// Build the list of folders
	$fList = array();
	for($i = 0; $i <= count($lines); $i++) {

		$fullPath = $lines[$i];
		$i+=2;

		$fileName = array_reverse(explode("/", substr($fullPath, strlen(HOME_LOC . BASE_LOC))));
		$fileName = substr($fileName[0], 0, -1);

		$relPath = array_slice(explode("/", substr($fullPath, strlen(HOME_LOC . BASE_LOC), -1)), 1, -1);

		$conRelPath = $relPath;
		$conRelPath[] = $fileName;

		$contentList = array();
		$latestChange = 0;
		while(preg_match('/[^ ]+ *[^ ]+ *[^ ]+ *([^ ]+ *[^ ]+ *[^ ]+) *(.+$)/', $lines[$i], $pieces)) {

			$i++; // Make sure to increment i in here so that we're not accidently skipping lines

			$conDate = strtotime($pieces[1]);
			$conName = $pieces[2];

			if(substr($conName, -1) == "*") {
				$conName = substr($conName, 0, -1);
			}

			if($conName == "tn/") { continue; } 

			$contentList[] = new FolderItem($conName, $conRelPath, $conDate);

			if($latestChange > $conDate) {
				$latestChange = $conDate;
			}

		}

		usort($contentList, "funcSort");

		$fList[] = new FolderItem($fileName, $relPath, $latestChange, $contentList);

		if($flatLook) { 
			break; 
		}

 	}

	return $fList;

}

function createThumbnail($pathToImage, $pathToThumb, $thumbWidth, $thumbHeight)
{

	$dir = explode("/", $pathToThumb);
	array_pop($dir);
	$dir = implode("/", $dir);

	if(!file_exists($dir)) {
		mkdir($dir, 0777, true);
	}

	touch($pathToImage);
	$info = pathinfo($pathToImage);

	$img = imagecreatefromjpeg( $pathToImage );
	$width = imagesx( $img );
	$height = imagesy( $img );

	$new_width = $thumbWidth;
	$new_height = $thumbHeight;

	$tmp_img = imagecreatetruecolor( $new_width, $new_height );
	imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
	imagejpeg($tmp_img, $pathToThumb);

}

?>
