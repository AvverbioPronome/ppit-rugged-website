<?php
require_once('liblqfb.class.php');
require_once('libpiratepage.class.php');

class Piratewww {
	private $basedir;
	private $tpldir;
	private $incdir;
	private $lfapi;

	public $pages;

	function __construct($basedir=NULL, $tpldir=NULL, $incdir=NULL, $lfapiurl=NULL, $local="IT") {
		$this->basedir = $basedir;
		$this->tpldir = $basedir.$tpldir;
		$this->incdir = $basedir.$incdir;
		$this->lfapi = new Liquidquery($lfapiurl);
		$this->pages = array();
	}
	
	private function pagefromfile($file=NULL) {
//		$page = new Page();
//		$pages[$file] = $page;
	}

	private function pagefromwiki($wikipage=NULL) {
		$page = new Piratepage($wikipage, $this->tpldir.'wikipages.html');
		
		$htmlurl = $wikibaseurl.$wikipage.'.html';
		if ($wikipage == 'Statuto') $htmlurl .= '?version=45';
		
		//non controlla se ci sono 404, ma dovrebbe andare bene comunque
		$html = file_get_contents($htmlurl); 
		// http://stackoverflow.com/a/4911037
		if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html, $matches)) {
			$body = $matches[1];
		}
		$page->loadFromBody($body);

		$pages[$wikipage] = $page;
	}

	private function pagefromlf($template=NULL) {
		switch($template) {
			case "tribune":
				$page = new Pagina($init['initiative_id'], './html/templates/tribuna.html');
				$page->appendToBody('<article id=init'.$init['initiative_id'].'><h1>'.$init['name'].'</h1>');
				$page->appendToBody($init['content']);
				$page->appendToBody('</article>');
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
			$wikipage=trim($wikipage);
			pagefromwiki($wikipage);
		}
	}
	
	private function fetchlf($type) {
		$drafts = array();
		$index = '';
		switch($type) {
			case "tribune":
				$drafts = $liq->getApproved(0,10);
				$index = new Piratepage('tribuna', './html/templates/tribuna.html');
				$index->appendToBody('<p>La Tribuna Politica del Partito Pirata elenca le iniziative assembleari che hanno raggiunto l\'approvazione, accompagnate da eventuali commenti "a bocce ferme" da parte di chi desideri inviare degli approfondimenti sul significato delle scelte assembleari qui elencate, aggiungere una prospettiva storica, commentare le alternative bocciate dall\'assemblea, contestualizzare o descrivere i potenziali scenari aperti dal cambiamento approvato.</p>');
			break;
			case "report":
			break;
		}
		foreach ( $drafts as $draft ) {
			pagefromlf($draft,$type);
			$indice->appendToBody("\n");
			$indice->appendToBody("<section id=init".$init['initiative_id'].">");
			$indice->appendToBody('<h1><a href="'.$page->saved_as.'">Proposta n° '.$init['initiative_id'].': '.$init['name']."</a></h1>");
			$indice->appendToBody(strlen($init['content']) < 3000 ? $init['content'] : substr($init['content'], 0, 3000).'[continua...]');
			$indice->appendToBody('<p><small>Pubblicato <time datetime='.$initiative['created'].'>'.$initiative['created'].'</time> da Spugna, portavoce dell\'Assemblea Permanente, nel Contesto '."TODO".' con tags '."TODO".'</small></p>');
			$indice->appendToBody('</section>');
		}
	}	

	private function writePages($htdocs) {
		foreach($pages as $page) {
			$pagename = '';
			if (!file_exists($pagename.'.html')) {
				$page->writePage($htdocs, $pagename.'.html');
			}
		}
	}

	function updateFormalfoo($wikiurls=NULL, $htdocs=NULL) {
		this->fetchwiki($wikiurls);
		this->writepages($htdocs);
	}
	
	function updateReport($htdocs=NULL) {
		this->fetchlf("report");
		this->writepages($htdocs);
	}

	function updateTribune($htdocs=NULL) {
		this->fetchlf("tribune");
		//this->fetchforum();
		this->writepages($htdocs);
	}
};
?>
