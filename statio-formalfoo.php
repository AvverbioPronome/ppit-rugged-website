<?php
require_once 'pagina.class.php';
require_once 'sostituzioni.list.php'; // definisce $tag e $re, insieme a $subs

//pagine dalla wiki.
	
	$wikibaseurl = 'https://dev.partitopirata.org/projects/ppit/wiki/';
	echo "formalfoo:\t";
		
	foreach(file('wikipages.list', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $wikipage){
		$wikipage=trim($wikipage);
		$page = new Pagina($wikipage, $subs, './html/templates/wikipages.html');
		
		$htmlurl = $wikibaseurl.$wikipage.'.html';
			if ($wikipage == 'Statuto') $htmlurl .= '?version=45';
		
		//non controlla se ci sono 404, ma dovrebbe andare bene comunque
		$html = file_get_contents($htmlurl); 
		// http://stackoverflow.com/a/4911037
		if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html, $matches)) {
				$body = $matches[1];
			}
		 
		
		$page->loadFromBody($body);
		
		if ($page->pagename == "Il_Partito_Pirata")
			$page->salva('./html/', 'index.html');
		else
			$page->salva('./html/');
		
		echo '*';
		
	}
	echo "\n";
?>