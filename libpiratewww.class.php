<?php
require_once 'liblqfb.class.php';
require_once 'libpage.class.php';

class Piratewww {
	private $basedir="./";
	private $wikiurl="https://dev.partitopirata.org/projects/ppit/wiki/";
	private $lfapiurl="http://apitest.liquidfeedback.org:25520/";
	private $templates;
	private $includes;
	private $htdocs;
	private $locale;
	private $lfapi;
	private $index;

	public $pages;

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
		switch( $type ) {
			case "report":
				$page->id = $type."_".$source['id'];
				$page->template = $this->templates."report.html";
			break;
			case "tribune":
				$page->id = $type."_".$source['id'];
				$page->template = $this->templates."tribune.html";
			break;
			default:
				$page->id = $type."_".$type;
				$page->template = $this->templates."wikipages.html";
			break;
		}
		$page->makePage($source);
		$this->pages[$page->id] = $page;
	}

	private function createIndex($type, $sources) {
		$page = new Piratepage($type);
		$page->id = $type."_index";
		$page->template = $this->templates.$type.".html";
		$page->makeIndex($sources);
		$this->pages[$page->id] = $page;
	}

	private function fetchFiles($docsdir) {
		$files = array_diff(scandir($docsdir), array('.', '..'));
		foreach ( $files as $file ) {
			$pages[$file] = createPage("file", $file);
		}
	}
	
	private function fetchWiki($wikipages) {
		foreach ( $wikipages as $wikipage ) {
			$wikipage=trim($wikipage);
			$page = $wikiurl.$wikipage;
			$pagecontent = file_get_contents($page);
			// http://stackoverflow.com/a/4911037
			if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $pagecontent, $matches)) {
				$pagecontent = $matches[1];
			}
			$this->createPage($wikipage, $pagecontent);
		}
	}
	
	private function fetchLf( $type, $last ) {
		$drafts = NULL;
		switch( $type ) {
			case "tribune":
				$drafts = $this->lfapi->getApproved($last);
			break;
			case "report":
				$drafts = $this->lfapi->getDrafts($last);
			break;
		}
		foreach ( $drafts as $draft ) {
			$this->createPage($type, $draft);
		}

		$this->createIndex($type, $drafts);
	}	

	private function fetchForum() {
	}

	private function writePages($last=NULL) {
		foreach($this->pages as $page) {
			$page->writePage($this->htdocs);
		}
		$this->pages = array();
	}

	function updateFormalfoo($wikipages) {
		$this->fetchWiki($wikipages);
//		$this->writePages("wiki");
	}

	function updateReport($last = "1") {
		$this->fetchLf("report", $last);
		$this->writePages($last);
	}

	function updateTribune($last = "1") {
		$this->fetchLf("tribune", $last);
		$this->fetchforum();
		$this->writePages($last);
	}
};
?>
