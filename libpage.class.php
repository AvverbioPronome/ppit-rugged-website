<?php
class Piratepage {
	private $type = "wiki";
	private $subs;

	public $id;
    public $title;
	public $content;
	public $template;
	private $html;

	function __construct($type=NULL) {
		$this->type = $type;
	}

	private function loadFile($file) {
		return file_get_contents($file);
	}

	private function loadSubs() {
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
		$tag[]='<!--templating:id-->';
		$re[]=$this->id;
		$tag[]='<!--templating:title-->';
		$re[]=$this->title;
		$tag[]='<!--templating:fancytitle-->';
		$re[]=$this->title;

		$this->subs['tag'] = $tag;
		$this->subs['re'] = $re;

	}
	
	private function make(){
		$this->loadSubs();
		$this->html = $this->loadFile($this->template);
		$this->html = str_replace($this->subs['tag'], $this->subs['re'], $this->html);
	}

	function writePage($dir=null){
		$this->make();
		file_put_contents($dir.$this->id, $this->html);
	}
};
?>
