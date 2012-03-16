<?php
class Piratepage {
	private $type = "wiki";
	private $templates="./templates/";
	private $includes="./includes/";
        private $pagetitle='';
	private $pagetext='';
	private $template='';
	private $subs=array();
	private $pagehtml='';

	public $pageid='';
	public $extension='.html';
	public $saved_as='';

	function __construct($type, $templates=NULL, $includes=NULL) {
		//
		$this->type = $type;
		$this->includes = $includes ? $includes : $this->includes;
		$this->templates = $templates ? $templates : $this->templates;
	}

	private function ftbi($filename) {
		// questa serve solo al motore di templating per scrivere meno codice.
		// quando si voglino includere gli includes.
		return file_get_contents($includes.$filename.'.html');
	}

	private function loadSubs() {
		// Quali sono i subs del nostro sito?
		// cal. li aveva messi fuori dalla classe per mantenerla più generica
		// possibile, ma va bene anche così. 
		
		// ALERT: mettendoli fuori dalla classe gli includes vengono letti una volta sola e
		//		  tenuti in memoria: invece, così vengono letti dal disco per ogni pagina!
		
		// all pages
		$tag[] = '<!--include:ppheader-->';
		$re[] = $this->ftbi('ppheader');
		$tag[] = '<!--include:sitenav-->';
		$re[] = $this->ftbi('sitenav');
		$tag[] = '<!--include:ppfooter-->';
		$re[] = $this->ftbi('ppfooter');
		
		// Redmine's html validation.
		$tag[] = '<a name=';
		$re[] = '<a id=';
		$tag[] = '<br /><br />';
		$re[] = '</p><p>'; //si, lo so che sono due cose diverse. ma non lo sapete usare.
		$tag[] = '<br />';
		$re[] = ' '; //si che le fa in ordine... :D
		
		// Templating
		$tag[]='<!--include:textgoeshere-->';
		$re[]=$this->pagetext;
		$tag[]='<!--templating:title-->';
		$re[]=$this->pagename;
		$tag[]='<!--templating:fancytitle-->';
		$re[]=str_replace('_', ' ', $this->pagename);
		
		// consolidation
		$this->$subs[0]=$tag;
		$this->$subs[1]=$re;
	}
	
	public function addSub($tag, $re){
		// aggiunge una sostituzione a quelle definite sopra. nel caso una pagina abbia bisogno 
		// di una sostituzione particolare
		
		$this->$subs[0][]=$tag;
		$this->$subs[1][]=$re; 
		// non metto controlli esclusivamente perché i parametri sono obbligatori.
	}

	private function loadFromBody($body){
		// carica nel contenuto della pagina, da quello che dovrebbe contenere il suo body.
		return $this->pagetext=$body;
	}
	
	private function appendToBody($txt){
		// appende a quanto di sopra.
		return $this->pagetext .= $txt;
	}
	
	// no more function pageFromWiki
	// implementare con $this->pageFromBody()

	function makePage($type, $source) {
		switch($type) {
			case "wiki":
				$htmlurl = $wikiurl.$wikipage.'.html';
				$html = file_get_contents($htmlurl); 
				// http://stackoverflow.com/a/4911037
				if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html, $matches)) {
					$body = $matches[1];
				}
				$this->loadFromBody($body);
			break;
			case "tribune":
				$this->appendToBody('');
			break;
			case "report":
				$this->appendToBody('');
			break;
		}
		$this->loadSubs();
		$this->pagehtml = str_replace($this->subs[0], $this->subs[1], file_get_contents($this->template));
	}

	function makeIndex($type, $pages) {
		$index = new Piratepage($type);
		foreach ( $pages as $page ) {
			$index->appendToBody('');
		}
		$index->pagehtml = makePage($type, $index);
		return $index;
	}

	function writePage($dir=NULL, $filename=NULL){
		// scrive la fottuta pagina sul disco.
		if (!$filename) $filename=$this->pagename.$this->extension;
		file_put_contents($dir.$filename, $this->htm);
		$this->saved_as=$filename;
	}
}
?>
	private function fetchLf($type, $last) {
		$drafts = array();
		$index = '';
		switch($type) {
			case "tribune":
				$drafts = $liq->getApproved($last,10);
				$index = new Piratepage('tribuna', $templates.'tribuna.html');
				$index->appendToBody('<p>La Tribuna Politica del Partito Pirata elenca le iniziative assembleari che hanno raggiunto l\'approvazione, accompagnate da eventuali commenti "a bocce ferme" da parte di chi desideri inviare degli approfondimenti sul significato delle scelte assembleari qui elencate, aggiungere una prospettiva storica, commentare le alternative bocciate dall\'assemblea, contestualizzare o descrivere i potenziali scenari aperti dal cambiamento approvato.</p>');
			break;
			case "report":
			break;
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

	function updateFormalfoo() {
		this->fetchWiki();
		this->writePages();
	}

	function updateReport($last = "1") {
		this->fetchLf("report", $last);
		this->writePages();
	}

	function updateTribune($last = "1") {
		this->fetchLf("tribune", $last);
		//this->fetchforum();
		this->writePages();
	}
};
?>
