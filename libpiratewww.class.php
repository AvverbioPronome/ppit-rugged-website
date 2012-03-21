<?php

require_once 'configure.php';
require_once 'libpage.class.php';
require_once 'liblqfb.class.php';

class Piratewww {
	private $settings;

	function __construct() {
		global $settings;
		$this->settings=$settings;
	}

	private function fetchFiles($docsdir) {
		$files = array_diff(scandir($this->settings['']), array('.', '..'));
		foreach ( $files as $file ) {
			$pages[$file] = createPage("file", $file);
		}
	}
	
	private function fetchWiki($wikipage) {
		$html=file_get_contents($this->settings['WIKIURL'].$wikipage[0].'.html?version='.$wikipage[1]);
		return $this->extractBody($html);
    }
    
    function extractBody($html){
		// http://stackoverflow.com/a/4911037
		if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html, $matches))
			return $matches[1];
		else
			return false;
	}

        private function fetchLiquid($cosa, $offset, $limit, $switch="CORE") {
                if ( $switch == "CORE") {
                        $lqfb = new Liquidcore($this->settings['LFCORE']);
                } else {
                        $lqfb = new Liquidapi($this->settings['LFAPIURL']);
                }
                $indice = new Indice($cosa);
		
		switch($cosa){
		    case 'report':
		    	$indexintro='indexintro.verbale.inc.html';
                    	$indice->id = 'verbale';
                        $lfresult = $lqfb->getDrafts(' ORDER BY id DESC');
		    break;
		    case 'tribune':
        	    $indexintro='indexintro.tribuna.inc.html';
                $indice->id='tribuna';
		        $lfresult = $lqfb->getApproved($offset, $limit);
		    break;
		}
		
		$indice->addSub('<!--include:indexintro-->', file_get_contents($this->settings['BASEDIR'].$this->settings['INCLUDES'].$indexintro));
				
		if ($lfresult) foreach($lfresult as $a) {
                  $pagina = new Liquidpage($a, $cosa);
                  $pagina->writePage();
                  $indice->addElement($pagina);
		}
		$indice->createIndex();
    }

	// create needed dirs and touch empty skels for needed includes and templates
	function createdirs() {
	  // create dirs
	  if ( file_exists($this->settings['BASEDIR']) ){
		$comando="mkdir ".$this->settings['BASEDIR'].$this->settings['TEMPLATES']." ".$this->settings['BASEDIR'].$this->settings['INCLUDES']." ".$this->settings['BASEDIR'].$this->settings['HTDOCS']." ";
		$uscita[0]="Ok";
		$ritorno=1;
		exec($comando,$uscita,$ritorno); // http://it.php.net/manual/en/function.mkdir.php
		if ( $ritorno != 0 ){
		  echo "ATTENZIONE: non ho potuto creare le directories necessarie.\n";
		  exit(1);
		};
	  };

	  // create empty includes
	  if ( file_exists($this->settings['BASEDIR']) ){
		$comando="touch ".$this->settings['BASEDIR'].$this->settings['INCLUDES']."ppheader.inc.html ".$this->settings['BASEDIR'].$this->settings['INCLUDES']."ppfooter.inc.html ".$this->settings['BASEDIR'].$this->settings['INCLUDES']."sitenav.inc.html";
		$uscita[0]="Ok";
		$ritorno=1;
		exec($comando,$uscita,$ritorno); // http://it.php.net/manual/en/function.mkdir.php
		if ( $ritorno != 0 ){
		  echo "ATTENZIONE: non ho potuto creare gli includes.\n";
		  exit(1);
		};
	  };
	  // create empty templates
	  if ( file_exists($this->settings['BASEDIR']) ) {
		$comando="touch ".$this->settings['BASEDIR'].$this->settings['TEMPLATES']."wikipages.html ".$this->settings['BASEDIR'].$this->settings['TEMPLATES']."report.html ".$this->settings['BASEDIR'].$this->settings['TEMPLATES']."tribune.html";
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
	function cleanprevious() {
	  if ( file_exists($this->settings['BASEDIR'].$this->settings['HTDOCS']) ) {
		$comando="rm ".$this->settings['BASEDIR'].$this->settings['HTDOCS']."*.html";
		$uscita[0]="Ok";
		$ritorno=1;
		exec($comando,$uscita,$ritorno); // eeeehm, http://it.php.net/manual/en/function.unlink.php
		if ( $ritorno != 0 ){
		  echo "ATTENZIONE: non ho potuto cancellare i file .html pre-esistenti\n";
		  exit(1);
		};
	  };
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
		return $this->fetchLiquid('report', $offset, $limit);
	}
	
	function updateTribune($offset=0, $limit=100) {
		return $this->fetchLiquid('tribune', $offset, $limit);
	}
	
	function debuggg() {
		print_r($this->settings);
	}


};
?>
