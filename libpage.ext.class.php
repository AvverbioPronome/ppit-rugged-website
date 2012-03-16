<?php
require_once 'libpage.class.php';

class Formalfoo extends Piratepage{
	//dubito che dovremmo mettere qualcosa qui.
}

class Indice extends Piratepage{
	public $excerptlen=3000;
	
	function addElement($page){
		// $code come oggetto Piratepage? Si, ok.

		$this->content .= '<section id='.$page->id.'>';
		$this->content .= '<h1><a href="'.$page->id.'">'.$page->title.'</a></h1>';
		$this->content .= strlen($page->content) > $this->excerptlen ? substr($page->content, 0, $this->excerptlen).'[continua...]' : $page->content;
		$this->content .= "<footer>Pubblicato <time datetime="./*$source['created'].*/">"./*$source['created'].*/"</time> da Spugna, portavoce dell'Assemblea Permanente,"." tags "."null"."</footer>";
		$this->content .= '</section>'."\n";

	}
}

class Liquidpage extends Piratepage{
	
	// roba che dovrebbero condividere Report e Tribune?
	
	function __construct($source, $type) {
		
		parent::__construct($type);
		
		$this->content .= "<article id=init".$source['initiative_id'].">";
		$this->content .= "<hgroup><h4>Tema n. "."null"."->Iniziativa n.".$source['initiative_id']."->Bozza n.".$source['id'].":</h4>";
		$this->content .= "<h1>'".$source['name']."'</h1></hgroup>";
		$this->content .= $source['content'];
		$this->content .= "<footer>Pubblicato <time datetime="./*$source['created'].*/">"./*$source['created'].*/"</time> da Spugna, portavoce dell'Assemblea Permanente,"." tags "."null"."</footer>";
		$this->content .= "</article>\n";
	}
}

class Report extends Liquidpage{
	
	function __construct($source, $type='report'){

		$this->id='verbale_'.$source['draft_id'];
		
		$this->title = "Verbale";
		$this->content = '<p>Il Verbale del Partito Pirata riporta fedelmente tutta l\'attivit&agrave; dell\'Assemblea Permanente  elencando tutte le iniziative e rielencandole quando vengono modificate dai relatori.</p>'."\n";
		
		parent::__construct($type);

	}
}

class Tribune extends Liquidpage{
	
	function __construct($source, $type='tribune'){
		
		$this->id='tribuna_'.$source['initiative_id'];
		$this->template='./templates/tribune.html';
		$this->title = "Tribuna";
		$this->content = 'introtribuna?'."\n";
		
		parent::__construct($source, $type);
		
	}
}

?>