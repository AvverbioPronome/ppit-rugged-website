<?php

require_once 'configure.php';
require_once 'libpage.class.php';
require_once 'liblqfb.class.php';

class Formalfoo extends Piratepage {
	function __construct() {
		parent::__construct();

		$this->template= 'wikipages.html'; //solo il nome del file dentro la certella dei templates.
	}
	//dubito che dovremmo mettere qualcosa qui.
}

class Indice extends Piratepage {
	private $pages;

	public $excerptlen=3000;
	public $intro;
	
	function __construct($template) {
		parent::__construct();
		
		$this->template= $template.'.html';
		$this->content = '<dl>';
	}

	private function elementsToHtml() {
		krsort($this->pages);
		foreach ( $this->pages as $page ) {
			// $page come oggetto Piratepage::Liquidpage? Si, ok.
			// $page->source contiene l'initiative, si possono usare i suoi pezzi per comporre l'indice.
			$this->content .= "\n".'<dt><a href="'.$page->id.'.html">'.$page->title.'</a></dt>';
			$this->content .= "\n".'<dd><ul>';
			$this->content .= '<li>ID: '.hash('sha256', /*$page->source['created'].*/$page->source['id'].$page->source['name'].$page->source['content']).'</li>';
			$this->content .= '<li>Creata: './*$page->source['created'].*/'</li>';
			//altri <li> da aggiungere?
			$this->content .= '</ul></dd>';
		}
	}
	
	function addElement($page) {
		$this->pages[$page->id] = $page;
	}
	
	function writePage(){
		$this->elementsToHtml();
		$this->content .= '</dl>';
		parent::writePage();
	}
}

class Liquidpage extends Piratepage{
	public $source;

	// roba che dovrebbero condividere Report e Tribune?
	function __construct($source) {
		parent::__construct();
		
		$this->source=$source;
		
		$this->content .= "<article id=init".$source['initiative_id'].">";
		$this->content .= "<hgroup><h4>Tema n. "."null"."->Iniziativa n.".$source['initiative_id']."->Bozza n.".$source['id'].":</h4>";
		$this->content .= "<h1>".$source['name']."</h1></hgroup>";
		$this->content .= $source['content'];
		$this->content .= "<footer>Pubblicato <time datetime="./*$source['created'].*/">"./*$source['created'].*/"</time> da Spugna, portavoce dell'Assemblea Permanente,"." tags "."null"."</footer>";
		$this->content .= "</article>\n";
	}
}

class Report extends Liquidpage{
	function __construct($source){
		parent::__construct($source);

		$source['id'] = str_pad($source['id'], 10, "0", STR_PAD_LEFT);
		$this->id='verbale_'.$source['id'];
		$this->title = 'Proposta n. '.$source['id'].': '.$source['name'];
		$this->template='report.html';
	}
}

class Tribune extends Liquidpage{
	function __construct($source){
		parent::__construct($source);

		$this->id='tribuna_'.$source['initiative_id'];
		$this->template='tribune.html';
		$this->title = 'Iniziativa n. '.$source['initiative_id'].': '.$source['name'];
	}
}

?>