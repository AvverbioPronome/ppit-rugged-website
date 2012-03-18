<?php

require_once 'configure.php';
require_once 'libpage.class.php';
require_once 'liblqfb.class.php';

class Formalfoo extends Piratepage{
	function __construct(){
		parent::__construct();
	
		$this->template=$this->settings['BASEDIR'].$this->settings['TEMPLATES'].'wikipages.html';
	}
	//dubito che dovremmo mettere qualcosa qui.
}

class Indice extends Piratepage{
	public $excerptlen=3000;
	public $intro;
	
	function __construct($template){
		parent::__construct();
		
		$this->template= $this->settings['BASEDIR'].$this->settings['TEMPLATES'].$template.'.html';
		$this->content = '<dl>';
	}
	
	function addElement($page){
		// $page come oggetto Piratepage::Liquidpage? Si, ok.
		// $page->source contiene l'initiative, si possono usare i suoi pezzi per comporre l'indice.

		$this->content .= '<dt><a href="'.$page->id.'.html">'.$page->title.'</a></dt>';
		$this->content .= '<dd><ul>';
		$this->content .= '<li>'.hash('sha256', $page->source['content']).'</li>';
		//altri <li> da aggiungere?
		$this->content .= '</ul></dd>';

	}
	
	function writePage(){
		$this->content .= '</dl>';
		parent::writePage();
	}
}

class Liquidpage extends Piratepage{
	public $source;

	// roba che dovrebbero condividere Report e Tribune?
	function __construct($source, $type) {
		parent::__construct($type);
		
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
	function __construct($source, $type='report'){
		parent::__construct($source, $type);

		$this->id='verbale_'.$source['id'];
		$this->title = 'Verbale: '.$source['name'];
		$this->template=$this->settings['BASEDIR'].$this->settings['TEMPLATES'].'report.html';
	}
}

class Tribune extends Liquidpage{
	function __construct($source, $type='tribune'){
		parent::__construct($source, $type);

		$this->id='tribuna_'.$source['initiative_id'];
		$this->template=$this->settings['BASEDIR'].$this->settings['TEMPLATES'].'tribune.html';
		$this->title = 'Tribuna: '.$source['name'];
	}
}

?>