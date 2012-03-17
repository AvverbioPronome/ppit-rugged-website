<?php

require_once 'configure.php';
require_once 'libpage.ext.class.php';
require_once 'liblqfb.class.php';

class Piratewww {
	private $settings;	

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
		$indice->id='verbale';
		$indice->addSub('<!--include:indexintro-->', file_get_contents($this->settings['BASEDIR'].$this->settings['INCLUDES'].'indexintro.verbale.inc.html'));

		foreach($lfapi->getDrafts($offset, $limit) as $a){
			$pagina = new Report($a);
			$indice->addElement($pagina);
			$pagina->writePage(); // se ne fotte delle cartelle. non è un gran problema.
		}
		$indice->writePage();
	}
	
	function updateTribune($offset=0, $limit=100) {
		$lfapi = new Liquidquery($this->settings['LFAPIURL']);
		$indice = new Indice('tribune');
		$indice->id='tribuna';
		$indice->addSub('<!--include:indexintro-->', file_get_contents($this->settings['BASEDIR'].$this->settings['INCLUDES'].'indexintro.tribuna.inc.html'));

		foreach($lfapi->getApproved($offset, $limit) as $a){
			$pagina = new Tribune($a);
			$indice->addElement($pagina);
			$pagina->writePage(); 
		}
		$indice->writePage();
	}
	
	function debuggg(){
		print_r($this->settings);
		
	}
	// create needed dirs and touch empty skels for needed includes and templates
	function createdirs($dir=NULL) {
	  // create dirs
	  if ( file_exists($dir) ){
		$comando="mkdir ".$dir.$settings['TEMPLATES']." ".$dir.$settings['INCLUDES']." ".$dir.$settings['HTDOCS']." ";
		$uscita[0]="Ok";
		$ritorno=1;
		exec($comando,$uscita,$ritorno); // http://it.php.net/manual/en/function.mkdir.php
		if ( $ritorno != 0 ){
		  echo "ATTENZIONE: non ho potuto creare le directories necessarie.\n";
		  exit(1);
		};
	  };
	  // create empty includes
	  if ( file_exists($dir) ){
		$comando="touch ".$dir.$settings['INCLUDES']."ppheader.inc.html ".$dir.$settings['INCLUDES']."ppfooter.inc.html ".$dir.$settings['INCLUDES']."sitenav.inc.html";
		$uscita[0]="Ok";
		$ritorno=1;
		exec($comando,$uscita,$ritorno); // http://it.php.net/manual/en/function.mkdir.php
		if ( $ritorno != 0 ){
		  echo "ATTENZIONE: non ho potuto creare gli includes.\n";
		  exit(1);
		};
	  };
	  // create empty templates
	  if ( file_exists($dir) ){
		$comando="mkdir ".$dir.$settings['TEMPLATES']."wikipages.html ".$dir.$settings['TEMPLATES']."report.html ".$dir.$settings['TEMPLATES']."tribune.html";
		$uscita[0]="Ok";
		$ritorno=1;
		exec($comando,$uscita,$ritorno); // http://it.php.net/manual/en/function.mkdir.php
		if ( $ritorno != 0 ){
		  echo "ATTENZIONE: non ho potuto creare i templates.\n";
		  exit(1);
		};
	  };
	}

	// clean previous .html files from htdocs
	function cleanprevious($htdocs=NULL) {
	  if ( file_exists($htdocs) ){
		$comando="rm ".$settings['BASEDIR'].$settings['HTDOCS']."*.html";
		$uscita[0]="Ok";
		$ritorno=1;
		exec($comando,$uscita,$ritorno); // eeeehm, http://it.php.net/manual/en/function.unlink.php
		if ( $ritorno != 0 ){
		  echo "ATTENZIONE: non ho potuto cancellare i file .html pre-esistenti\n";
		  exit(1);
		};
	  };
	}

};
?>
