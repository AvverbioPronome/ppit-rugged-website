<?php
class Piratepage {
    private $pagetitle='';
	private $pagetext='';
	private $template='';
	private $subs=array();
	private $pagehtml='';

	public $pageid='';
	public $extension='.html';
	public $saved_as='';

	function __construct($name, $template){
		// istanzia la pagina, stabilendone nome e template
		$this->pageid = $name;
		$this->template = $template;

        $this->loadSubs();
	}

	private function ftbi($filename) {
		// questa serve solo al motore di templating per scrivere meno codice.
		// quando si voglino includere gli includes.
		return file_get_contents('./html/inc/'.$filename.'.html');
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

	private function make(){
		// compila la pagina. :D
		
		//chiamata a $this->loadSubs() spostata in $this->__construct()
        $this->htm = str_replace($this->subs[0], $this->subs[1], file_get_contents($this->tpl));
	}

	function loadFromBody($body){
		// carica nel contenuto della pagina, da quello che dovrebbe contenere il suo body.
		
		return $this->pagetext=$body;
	}
	
	function appendToBody($txt){
		// appende a quanto di sopra.
		
		return $this->pagetext .= $txt;
	}
	
	// no more function pageFromWiki
	// implementare con $this->pageFromBody()
	
	function writePage($dir=NULL, $filename=NULL){
		// scrive la fottuta pagina sul disco.
		
		$this->make();
		if (!$filename) $filename=$this->pagename.$this->extension;
		file_put_contents($dir.$filename, $this->htm);
		$this->saved_as=$filename;
	}
}
?>
