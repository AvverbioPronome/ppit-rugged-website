<?php

require_once 'configure.php';
require_once 'libpage.ext.class.php';
require_once 'libpiratewww.class.php';

class Piratepage {
	private $subs;
	private $html;
	private $moresubs;
	protected $settings;

	public $id;
        public $title;
	public $content;
	public $template;

	function __construct() {
		global $settings;
		$this->settings=$settings;
	}

	private function loadFile($f) {
		return file_get_contents($f);
	}
	
	private function loadInclude($t){
		return $this->loadFile($this->settings['BASEDIR'].$this->settings['INCLUDES'].$t);
	}
	
	public function addSub($tag, $re){
		$this->moresubs['tag'][] = $tag;
		$this->moresubs['re'][] = $re;
	}
	
	private function loadMoreSubs(){
	    if(is_array($this->moresubs)){
            foreach($this->moresubs['tag'] as $m){
                $this->subs['tag'][]=$m;
            }
            foreach($this->moresubs['re'] as $m){
                $this->subs['re'][]=$m;
            }
		}
	}

	private function loadSubs() {
		// all pages
		$tag[] = '<!--include:ppheader-->';
		$re[] = $this->loadInclude('ppheader.inc.html');
		$tag[] = '<!--include:sitenav-->';
		$re[] = $this->loadInclude('sitenav.inc.html');
		$tag[] = '<!--include:ppfooter-->';
		$re[] = $this->loadInclude('ppfooter.inc.html');
		
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
		
		$this->loadMoreSubs(); //senza di questa non funzionano gli indexintro!
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
