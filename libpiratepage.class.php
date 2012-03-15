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
		//
		$this->pageid = $name;
		$this->template = $template;
	}

	private function ftbi($filename) {
	 return file_get_contents('./html/inc/'.$filename.'.html');
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

	private function make(){
         $this->loadSubs();
         $this->htm = str_replace($this->subs[0], $this->subs[1], file_get_contents($this->tpl));
	}

	function loadFromBody($body){
		//
		return $this->pagetext=$body;
	}
	
	function appendToBody($txt){
		//
		return $this->pagetext .= $txt;
	}
	
	// no more function pageFromWiki
	// implementare con $this->pageFromBody()
	
	function writePage($dir=NULL, $filename=NULL){
		//
		$this->make();
		if (!$filename) $filename=$this->pagename.$this->extension;
		file_put_contents($dir.$filename, $this->htm);
		$this->saved_as=$filename;
	}
}
?>
