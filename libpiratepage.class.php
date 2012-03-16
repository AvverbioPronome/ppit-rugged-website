<?php
class Piratepage {
	private $type = "wiki";
	private $subs=array();

	public $id;
        public $title;
	public $content;
	public $template;
	public $html;

	function __construct($type=NULL) {
		$this->type = $type;
	}

	private function loadTemplate() {
		$this->html = file_get_contents($this->template);
	}

	private function loadInclude() {
		return file_get_contents($this->includes.);
	}

	private function addSub($tag, $re){
		$this->$subs[0][]=$tag;
		$this->$subs[1][]=$re; 
	}

	private function makeSubs() {
		// all pages
		$this->addSub('<!--include:ppheader-->', );
		$re[] = $this->ftbi('ppheader');
		$tag[] = '<!--include:sitenav-->';
		$re[] = $this->ftbi('sitenav');
		$tag[] = '<!--include:ppfooter-->';
		$re[] = $this->ftbi('ppfooter');
		
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
		$re[]=$this->id;
		$tag[]='<!--templating:fancytitle-->';
		$re[]=str_replace('_', ' ', $this->id);

		$this->html = str_replace($this->subs[0], $this->subs[1], file_get_contents($this->template));
	}
	

	function makePage($source) {
		$this->makeSubs();
		$this->html = "<article id=init".$initiative['initiative_id'].">";
		$this->html .= "<h4>Tema n. "."null"."->Iniziativa n.".$initiative['initiative_id']."->Bozza n.".$initiative['id'].":</h4>";
		$this->html .= "<h1>'".$initiative['name']."'</h1>";
		$this->html .= $initiative['content'];
		$this->html .= "<footer>Pubblicato <time datetime=".$initiative['created'].">".$initiative['created']."</time> da Spugna, portavoce dell'Assemblea Permanente,"." tags "."null"."</footer>";
		$this->html .= "</article>\n";
	}

	function makeIndex($pages) {
		foreach ( $pages as $page ) {
			$this->html .= "<article id=init".$page['id'].">";
			$this->html .= $page['created']." - <a href='".$initurl.".html'>'".$page['name']."'</a>"." (<a href=\"#\">T"."null"."I".str_pad($page['initiative_id'], 5, '0', STR_PAD_LEFT)."D".str_pad($page['id'], 5, '0', STR_PAD_LEFT)."</a>)";
			$this->html .= "</article>\n";
		}
	}

	function writePage($dir=NULL, $filename=NULL){
		// scrive la fottuta pagina sul disco.
		if (!$filename) $filename=$this->pagename.$this->extension;
		file_put_contents($dir.$filename, $this->htm);
		$this->saved_as=$filename;
	}
}
?>
