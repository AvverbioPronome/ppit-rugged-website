<?php
require_once 'liblqfb.class.php';
require_once 'libpage.class.php';
require_once 'libpage.ext.class.php';

class Piratewww {
	private $settings;
	private $index;
	private $lfapi;
	
	public $pages;
	

	function __construct() {
		global $settings;
		$this->settings=$settings;
	}

	/* Questa roba non mi pare sia più prevista...
	private function fetchFiles($docsdir) {
		$files = array_diff(scandir($this->settings['']), array('.', '..'));
		foreach ( $files as $file ) {
			$pages[$file] = createPage("file", $file);
		}
	}
	*/
	
	private function fetchWiki($wikipage) {
		
		$html=file_get_contents($this->settings['WIKIURL'].$wikipage[0].'.html?version='.$wikipage[1]);
		
		// http://stackoverflow.com/a/4911037
		if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html, $matches))
			return $matches[1];
		else
			return false;
	}

	private function fetchForum() {
	}

	function updateFormalfoo() {	
		foreach ($this->settings['FORMALFOO'] as $wikipage) {
			$page = new Formalfoo();
			$page->id = $wikipage == $this->settings['FORMALFOO'][0] ? 'index' : $wikipage[0];
			$page->content = $this->fetchWiki($wikipage);
			$page->writePage();
		}
	}

	function updateReport($offset=0, $limit=100) {
		$lfapi = new Liquidquery($this->settings['LFAPIURL']);
		$indice = new Indice('report');
		$indice->id='Verbale';
		foreach($lfapi->getDrafts($offset, $limit) as $a){
			$pagina = new Report($a);
			$indice->addElement($pagina);
			$pagina->writePage(); // se ne fotte delle cartelle. non è un gran problema.
		}
		$indice->writePage();
	}

	function updateTribune() {
		$lfapi = new Liquidquery($this->settings['LFAPIURL']);
		$indice = new Indice('tribune');
		$indice->id='Tribuna';
		foreach($lfapi->getApproved($offset, $limit) as $a){
			$pagina = new Tribune($a);
			$indice->addElement($pagina);
			$pagina->writePage(); // se ne fotte delle cartelle. non è un gran problema.
		}
		$indice->writePage();
	}
	
	function debuggg(){
		print_r($this->settings);
		
	}
};
?>
