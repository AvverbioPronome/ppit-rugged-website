<?php

require_once 'configure.php';
require_once 'libpage.ext.class.php';
require_once 'libpiratewww.class.php';

class Piratepage {
	private $type = "wiki";
	private $subs;

	public $id;
    public $title;
	public $content;
	public $template;
	private $html;
	private $settings;

	function __construct($type=NULL) { // miche', a che cazzo serve sto type?
		global $settings;
		$this->settings=$settings;
		
		$this->type = $type;
	}

	private function loadFile($f) {
		return file_get_contents($f);
	}
	
	private function ftbi($t){
		return $this->loadFile($this->settings['BASEDIR'].$this->settings['INCLUDES'].$t);
	}
	
	public function addSub($tag, $re){
		$this->subs['tag'][] = $tag;
		$this->subs['re'][] = $re;
	}

	private function loadSubs() {
		// all pages
		$tag[] = '<!--include:ppheader-->';
		$re[] = $this->ftbi('ppheader.inc.html');
		$tag[] = '<!--include:sitenav-->';
		$re[] = $this->ftbi('sitenav.inc.html');
		$tag[] = '<!--include:ppfooter-->';
		$re[] = $this->ftbi('ppfooter.inc.html');
		
		// Templating
		$tag[]='<!--include:textgoeshere-->';
		$re[]=$this->content;
		$tag[]='<!--templating:id-->';
		$re[]=$this->id;
		$tag[]='<!--templating:title-->';
		$re[]=$this->title;
		$tag[]='<!--templating:fancytitle-->';
		$re[]=$this->title;
		
		// Redmine's html validation.
		$tag[] = '<a name=';
		$re[] = '<a id=';
		$tag[] = '<br /><br />';
		$re[] = '</p><p>';
		//$tag[] = '<br />';
		//$re[] = ' '; //si che le fa in ordine... :D
		

		$this->subs['tag'] = $tag;
		$this->subs['re'] = $re;

	}
	
	private function make(){
		$this->loadSubs();
		$this->html = $this->loadFile($this->settings['BASEDIR'].$this->settings['TEMPLATES'].$this->template);
		return $this->html = str_replace($this->subs['tag'], $this->subs['re'], $this->html);
	}

	function writePage(){
		$this->make();
		return file_put_contents($this->settings['BASEDIR'].$this->settings['HTDOCS'].$this->id.'.html', $this->html);
	}
};
?>
