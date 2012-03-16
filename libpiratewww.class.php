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
<<<<<<< HEAD
			createPage($type, $draft);
=======
			pagefromlf($draft,$type); 
				//ALERT: non credo che le variabili interne di questa funzione passino fuori, tipo $init e $page
			$indice->appendToBody("\n");
			$indice->appendToBody("<section id=init".$init['initiative_id'].">");
			$indice->appendToBody('<h1><a href="'.$page->saved_as.'">Proposta n° '.$init['initiative_id'].': '.$init['name']."</a></h1>");
			$indice->appendToBody(strlen($init['content']) < 3000 ? $init['content'] : substr($init['content'], 0, 3000).'[continua...]');
			$indice->appendToBody('<p><small>Pubblicato <time datetime='.$initiative['created'].'>'.$initiative['created'].'</time> da Spugna, portavoce dell\'Assemblea Permanente, nel Contesto '."TODO".' con tags '."TODO".'</small></p>');
			$indice->appendToBody('</section>');
>>>>>>> fdcb58b3d365c03443d2b465a9dbad834190a520
		}
	}	

	private function writePages($type, $last) {
		foreach($pages as $page) {
			$page->writePage($type, $last);
		}
		$pages = array();
	}

<<<<<<< HEAD
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
=======
	function updateFormalfoo($wikiurls=NULL, $htdocs=NULL) {
		$this->fetchwiki($wikiurls);
		$this->writepages($htdocs);
	}
	
	function updateReport($htdocs=NULL) {
		$this->fetchlf("report");
		$this->writepages($htdocs);
	}

	function updateTribune($htdocs=NULL) {
		$this->fetchlf("tribune");
		//this->fetchforum();
		$this->writepages($htdocs);
>>>>>>> fdcb58b3d365c03443d2b465a9dbad834190a520
	}
};
?>
