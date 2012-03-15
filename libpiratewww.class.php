<?php
require_once('liblqfb.class.php');
require_once('libpagina.class.php');

class piratewww {
	private $lfapi;
	private $pages;
	
	function __construct($local=NULL, $lfapiurl=NULL) {
		$this->lfapi = new liquidquery($lfapiurl);
		$this->pages = array();
	}
	
	private function ftbi($filename) {
		return file_get_contents();
	}

	private function pagefromfile($file=NULL) {
		$pages[$file] = $page;
	}

	private function pagefromwiki($wikipage=NULL) {
		$pages[$wikipage] = $page;
	}

	private function pagefromlf($template=NULL) {
		switch($template) {
			case "tribune":
			break;
			case "report":
			break;
		}
		$pages[$draft] = $page;
	}
	
	private function fetchfiles($docsdir) {
		$files = array_diff(scandir($docsdir), array('.', '..'));
		foreach ( $files as $file ) {
			pagefromfile($file);
		}
	}
	
	private function fetchwiki($wikipages) {
		foreach ( $wikipages as $wikipage ) {
			pagefromwiki($wikipage);
		}
	}
	
	private function fetchlf($type) {
		$drafts = '';
		switch($type) {
			case "tribune":
			break;
			case "report":
			break;
		}
		foreach ( $drafts as $draft ) {
			pagefromlf($draft,$type);
		}
	}	

	private function writepages($htdocs) {
		foreach($pages as $name => $text) {
			if (!file_exists($htdocs.$name.'.html')) {
				file_put_contents($htdocs.$name.'.html', $text);
			}
		}
	}

	function createFormalfoo($wikiurls=NULL, $htdocs=NULL) {
		this->fetchwiki($wikiurls);
		this->writepages($htdocs);
	}
	
	function createReport($htdocs=NULL) {
		this->fetchlf("report");
		this->writepages($htdocs);
	}

	function createTribune($htdocs=NULL) {
		this->fetchlf("tribune");
		//this->fetchforum();
		this->writepages($htdocs);
	}
};
?>
