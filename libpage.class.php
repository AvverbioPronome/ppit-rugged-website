<?php
class Piratepage {
	private $type = "wiki";
	private $subs;

	public $id;
        public $title;
	public $content;
	public $template;
	public $html;

	function __construct($type=NULL) {
		$this->type = $type;
	}

	private function loadFile($file) {
		return file_get_contents($file);
	}

	private function makeSubs() {
		// all pages
		$tag[] = '<!--include:ppheader-->';
		$re[] = 'ppheader';
		$tag[] = '<!--include:sitenav-->';
		$re[] = 'sitenav';
		$tag[] = '<!--include:ppfooter-->';
		$re[] = 'ppfooter';
		
		// Redmine's html validation.
		$tag[] = '<a name=';
		$re[] = '<a id=';
		$tag[] = '<br /><br />';
		$re[] = '</p><p>';
		$tag[] = '<br />';
		$re[] = ' '; //si che le fa in ordine... :D
		
		// Templating
		$tag[]='<!--include:textgoeshere-->';
		$re[]=$this->content;
		$tag[]='<!--templating:title-->';
		$re[]=$this->title;
		$tag[]='<!--templating:fancytitle-->';
		$re[]=str_replace('_', ' ', $this->title);

		$this->subs['tag'] = $tag;
		$this->subs['re'] = $re;

		$this->html = $this->loadFile($this->template);
		$this->html = str_replace($this->subs['tag'], $this->subs['re'], $this->html);
	}

	function makePage($source) {
		switch( $this->type ) {
			case "report":
				$this->title = "Verbale";
				$this->content = '<p>Il Verbale del Partito Pirata riporta fedelmente tutta l\'attivita\' dell\'Assemblea Permanente  elencando tutte le iniziative e rielencandole quando vengono modificate dai relatori.</p>'."\n";
			break;
			case "tribune":
				$this->title = "Tribuna";
			break;
			case "formalfoo":
				$this->title = "Gazzetta";
			break;
		}
		$this->content = "<article id=init".$source['initiative_id'].">";
		$this->content .= "<h4>Tema n. "."null"."->Iniziativa n.".$source['initiative_id']."->Bozza n.".$source['id'].":</h4>";
		$this->content .= "<h1>'".$source['name']."'</h1>";
		$this->content .= $source['content'];
		$this->content .= "<footer>Pubblicato <time datetime="./*$source['created'].*/">"./*$source['created'].*/"</time> da Spugna, portavoce dell'Assemblea Permanente,"." tags "."null"."</footer>";
		$this->content .= "</article>\n";
		
		$this->makeSubs();
		unset($this->content, $this->subs);
	}

	function makeIndex($pages) {
		switch( $this->type ) {
			case "report":
				$this->title = "Verbale";
				$this->content = '<p>Il Verbale del Partito Pirata riporta fedelmente tutta l\'attivita\' dell\'Assemblea Permanente  elencando tutte le iniziative e rielencandole quando vengono modificate dai relatori.</p>'."\n";
			break;
			case "tribune":
				$this->title = "Tribuna";
			break;
			case "formalfoo":
				$this->title = "Gazzetta";
			break;
		}
		foreach ( $pages as $page ) {
			$this->content .= "<article id=init".$page['id'].">";
			$this->content .= /*$page['created'].*/" - <a href='".$this->type."_".$page['id'].".html'>'".$page['name']."'</a>"." (<a href=\"#\">T"."null"."I".str_pad($page['initiative_id'], 5, '0', STR_PAD_LEFT)."D".str_pad($page['id'], 5, '0', STR_PAD_LEFT)."</a>)";
			$this->content .= "</article>\n";
		}
		$this->makeSubs();
		unset($this->content, $this->subs);
	}

	function writePage($dir=NULL){
		if ( file_exists($dir) ) {
			file_put_contents($dir.$this->id, $this->html);
		}
	}
};
?>
