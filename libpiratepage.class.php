<?php
class Piratepage {
	private $type = "wiki";
	private $includes="./includes/";
        private $pagetitle='';
	private $pagetext='';
	private $template='';
	private $subs=array();
	private $pagehtml='';

	public $pageid='';
	public $extension='.html';
	public $saved_as='';

	function __construct($type, $includes=NULL) {
		//
		$this->type = $type;
		$this->includes = $includes ? $includes : $this->includes;
	}

	private function ftbi($filename) {
	 return file_get_contents($includes.$filename.'.html');
        }

        private function loadSubs() {
         // all pages
         $tag[] = '<!--include:ppheader-->';
         $re[] = ftbi('ppheader');
         $tag[] = '<!--include:sitenav-->';
         $re[] = ftbi('sitenav');
         $tag[] = '<!--include:ppfooter-->';
         $re[] = ftbi('ppfooter');
         // Redmine's html validation.
         $tag[] = '<a name=';
         $re[] = '<a id=';
         $tag[] = '<br /><br />';
         $re[] = '</p><p>'; //si, lo so che sono due cose diverse. ma non lo sapete usare.
         $tag[] = '<br />';
         $re[] = ' '; //si che le fa in ordine... :D
         // Templating
         $this->subs[0][]='<!--include:textgoeshere-->';
         $this->subs[1][]=$this->pagetext;
         $this->subs[0][]='<!--templating:title-->';
         $this->subs[1][]=$this->pagename;
         $this->subs[0][]='<!--templating:fancytitle-->';
         $this->subs[1][]=str_replace('_', ' ', $this->pagename);

         // consolidation
         $subs[0]=$tag;
         $subs[1]=$re;
        }

	private function loadFromBody($body){
		//
		return $this->pagetext=$body;
	}
	
	private function appendToBody($txt){
		//
		return $this->pagetext .= $txt;
	}
	
	// no more function pageFromWiki
	// implementare con $this->pageFromBody()

	function makePage($type) {
		switch($type) {
			case "wiki":
				$htmlurl = $wikiurl.$wikipage.'.html';
				// non controlla se ci sono 404, ma dovrebbe andare bene comunque
				$html = file_get_contents($htmlurl); 
				// http://stackoverflow.com/a/4911037
				if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html, $matches)) {
					$body = $matches[1];
				}
				$this->loadFromBody($body);
			break;
			case "tribune":
				foreach ( $drafts as $draft ) {
					$indice->appendToBody("\n");
					$indice->appendToBody("<section id=init".$init['initiative_id'].">");
					$indice->appendToBody('<h1><a href="'.$page->saved_as.'">Proposta n° '.$init['initiative_id'].': '.$init['name']."</a></h1>");
					$indice->appendToBody(strlen($init['content']) < 3000 ? $init['content'] : substr($init['content'], 0, 3000).'[continua...]');
					$indice->appendToBody('<p><small>Pubblicato <time datetime='.$initiative['created'].'>'.$initiative['created'].'</time> da Spugna, portavoce dell\'Assemblea Permanente, nel Contesto '."TODO".' con tags '."TODO".'</small></p>');
					$indice->appendToBody('</section>');
				}
				$this->appendToBody('<article id=init'.$init['initiative_id'].'><h1>'.$init['name'].'</h1>');
				$this->appendToBody($init['content']);
				$this->appendToBody('</article>');
			break;
			case "report":
			break;
		}
		$this->loadSubs();
		$this->pagehtml = str_replace($this->subs[0], $this->subs[1], file_get_contents($this->template));
	}
	
	function writePage($dir=NULL, $filename=NULL){
		//
		$this->make();
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
