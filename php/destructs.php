<?php

include_once 'constants.php';
include_once 'util.php';

class ThumbnailLink {

	public $link;
	public $width;
	public $height;

	function __construct($l, $w, $h) {
		$this->link = $l;
		$this->width = $w;
		$this->height = $h;
	}

}

class FolderItem {

	public $name = "";
	public $path; // Array
	public $changeDate;
	public $link; // String
	public $content; // Array
	public $isFolder = true;
	public $iconLink;
	public $title;

	private $thumbnail;

	function __construct($n, $p, $d, $s = null) {

		$this->name = $n;
		$this->path = $p;
		$this->changeDate = $d;
		$this->content = $s;

		if($s == null) {
			$this->content = array();
			if(preg_match("/\\/$/", $this->name)) {
				$this->isFolder = true;
				$this->name = substr($this->name, 0, -1);
			} else {
				$this->isFolder = false;
			}
		}

		// Build the link
		$this->link = "/" . BASE_LOC;
		foreach($this->path as $p) {
			if($p == "") { continue; }
			$this->link = $this->link . "/" . $p;
		}
		$this->link .= "/" . $this->name . ($this->isFolder ? "/" : "");

		// Weird behavior of base dir
		if($this->name == "") {
			$this->name = BASE_LOC;
			$this->isFolder = true;
			$this->link = "/" . BASE_LOC . "/";
		}

		// Title should not have file extension, should replace all -'s and _'s with spaces, and have a cap first word
		if(preg_match('/(.*)\..*$/', $this->name, $pieces)) {
			$this->title = $pieces[1];
		} else {
			$this->title = $this->name;
		}

		do {
			$this->title = preg_replace('/(.*)[-_](.+)/', '${1} ${2}', $this->title, -1, $count);
		} while($count > 0);

		$this->title = ucfirst($this->title);

		$this->iconLink = DEFAULT_ICON_LINK;
		if($this->isFolder) { 
			$this->iconLink = FOLDER_ICON_LINK;
		} elseif(preg_match('/\.[jJ][pP][gG]$/', $this->name)) {
			$this->iconLink = IMAGE_ICON_LINK;
		}

	}

	function toString() {

		return $this->name . "-" . print_r($this->path, true) . "=" . ($this->getIsNew() ? "true" : "false") . " [" . $this->link . "]";

	}
	
	function getIsNew() {
		$isNew = false;
		foreach($this->content as $con) {
			if($con->getIsNew()) {
				$isNew = true;
			}
		}
		return $isNew || $this->changeDate > strtotime("-7 day");
	}
	
	function getDepthOfSamePath(FolderItem $other = null) {

		if($other == null) {
			return 0; 
		}

		$tPath = $this->path;
		array_unshift($tPath, BASE_LOC);
		$tPath[] = $this->name;

		$oPath = $other->path;
		array_unshift($oPath, BASE_LOC);
		$oPath[] = $other->name;

		for($samePathCount = 0; 
		    sizeof($tPath) > $samePathCount && sizeof($oPath) > $samePathCount && $tPath[$samePathCount] == $oPath[$samePathCount]; 
		    $samePathCount++);

		return $samePathCount;


	}

	function getThumbnail() {

		if(!isset($this->thumbnail)) {

			$tnLoc = HOME_LOC . "/" . THUMBNAIL_LOC . $this->link;

			if(!file_exists($tnLoc)) {

				// Make sure the folder exists

				createThumbnail(HOME_LOC . "/" . $this->link, $tnLoc, THUMBNAIL_WIDTH, THUMBNAIL_HEIGHT);
					
			}

			$this->thumbnail = new ThumbnailLink("/" . THUMBNAIL_LOC . $this->link, THUMBNAIL_WIDTH, THUMBNAIL_HEIGHT);

		}

		return $this->thumbnail;

	}

}

?>
