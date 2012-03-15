<?php
class Pagina{
	public $pagename='';
	private $pagetext='';
	private $htm='';
	private $subs=array();
	public $extension='.html';
	public $saved_as=null;
	
	function __construct($name, $subs, $template){
		//
		$this->pagename = $name;
		$this->subs = $subs;
		$this->tpl = $template;
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
	
	private function make(){
		//
		$this->subs[0][]='<!--include:textgoeshere-->';
		$this->subs[1][]=$this->pagetext;
		
		$this->subs[0][]='<!--templating:title-->';
		$this->subs[1][]=$this->pagename;
		
		$this->subs[0][]='<!--templating:fancytitle-->';
		$this->subs[1][]=str_replace('_', ' ', $this->pagename);
		
		$this->htm = str_replace($this->subs[0], $this->subs[1], file_get_contents($this->tpl));
	}
	
	function salva($dir, $filename=null){
		//
		$this->make();
		if (!$filename) $filename=$this->pagename.$this->extension;
		file_put_contents($dir.$filename, $this->htm);
		$this->saved_as=$filename;
	}
	
}