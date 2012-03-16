<?php
require_once('liblqfb.class.php');
require_once('libpiratepage.class.php');

class Piratewww {
	private $basedir="./";
	private $wikiurl="https://dev.partitopirata.org/projects/ppit/wiki/";
	private $lfapiurl="http://apitest.liquidfeedback.org:25520/";
	private $templates="templates/";
	private $includes="includes/";
	private $htdocs="html/";
	private $locale="IT";
	private $lfapi;
	private $index;

	function __construct($basedir=NULL, $wikiurl=NULL, $lfapiurl=NULL, $templates="templates/", $includes="includes/", $htdocs="html/", $locale="IT") {
		$this->basedir = $basedir ? $basedir : $this->basedir;
		$this->wikiurl = $wikiurl ? $wikiurl : $this->wikiurl;
		$this->lfapiurl = $lfapiurl ? $lfapiurl : $this->lfapiurl;
		$this->templates = $basedir.$templates;
		$this->includes = $basedir.$includes;
		$this->htdocs = $basedir.$htdocs;
		$this->locale = $locale;
		$this->lfapi = new Liquidquery($lfapiurl);
	}
	
	private function createPage($type, $source) {
		$page = new Piratepage($type);
		$page->makePage($type, $source, $index);
		return $page;
	}
	
	private function fetchFiles($docsdir) {
		$files = array_diff(scandir($docsdir), array('.', '..'));
		foreach ( $files as $file ) {
			createPage("file", $file);
		}
	}
	
	private function fetchWiki($wikipages) {
		foreach ( $wikipages as $wikipage ) {
			$wikipage=trim($wikipage);
			$wiki = $wikiurl.$wikipage;
			createPage("wiki", $wiki);
		}
	}
	
	private function fetchLf( $type, $last ) {
		$drafts = NULL;
		$this->index = new Piratepage($type);
		switch( $type ) {
			case "tribune":
				$drafts = $liq->getApproved($last);
			break;
			case "report":
				$drafts = $liq->getDrafts($last);
			break;
		}
		foreach ( $drafts as $draft ) {
			createPage($type, $draft);
		}
	}	

	private function writePages($type, $last) {
		foreach($pages as $page) {
			$page->writePage($type, $last);
		}
		$pages = array();
	}

	function updateFormalfoo() {
		$this->fetchWiki();
		$this->writePages("wiki");
	}

	function updateReport($last = "1") {
		$this->fetchLf("report", $last);
		$this->writePages("report", $last);
	}

	function updateTribune($last = "1") {
		this->fetchLf("tribune", $last);
		//this->fetchforum();
		this->writePages("tribune", $last);
	}
};
?>
