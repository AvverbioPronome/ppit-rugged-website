#!/usr/bin/php
<?php
error_reporting(E_ALL && E_NOTICE);
$inizio = microtime(true);
// dove stanno i nostri simpatici sorgenti?
$settings['sourcf']='./html/editme/'; 

// dove vomito il risultato?
$settings['desf']='./html/'; 

/*
 *
 * Si, sono stronzo. E questo non è mediawiki, che controlla che l'html
 * sia well-formed prima di mandarlo a se stessa. E no, non mi fido di 
 * quello che scrivono gli utenti, ma questo è abbastanza condiviso.
 *
 * Quindi, i tag sono definiti uno alla volta. Anche per motivi di 
 * performance: php è già abbastanza lento, vogliamo anche metterlo a 
 * lavorare con espressioni regolari? Una riscrittura in perl di questa 
 * merda sarebbe benvenuta, ma io non conosco perl.
 *
 * l'alternanza di $tag[] e $re[] deve essere STRETTAMENTE perfetta,
 * altrimenti non sappiamo più cosa viene sostituito con cos'altro.
 * cercherò qualcosa per sostituire ciò... forse.
 * -- cal.
 *
 */


/*
 *   Definizione sostituzioni.
 *   =========================
 *
 */
 
 function ftbi($filename){
 	return file_get_contents('./html/inc/'.$filename.'.html');
 }

$tag[] = '<!--include:ppheader-->';
 $re[] = ftbi('ppheader');

$tag[] = '<!--include:sitenav-->';
 $re[] = ftbi('sitenav');
 
$tag[] = '<!--include:ppfooter-->';
 $re[] = ftbi('ppfooter');
 
 
//qui cominciano le sostituzioni per validare l'html di redmine.
 
$tag[] = '<a name=';
 $re[] = '<a id='; 

// qui una volta c'era un coso che aggiustava i path dei css e simili,
// in forma di brutale sostituzione.

/*
 *   Caricamento pagine dalla redmine wiki.
 *   ===================================
 *
 */
 
/*
allora, funziona così: esiste un modello, esiste */
 
$wikipages = array(
"Il_Partito_Pirata", 
"Manifesto", 
"Statuto", 
"Garanzia_di_Iscrizione_ed_Esclusione", 
"Lettera_di_Intento_Pirata", 
"Lettera_di_Assunzione_Responsabilità_Artistiche",
"Lettera_di_Assunzione_Responsabilità_Tecniche",
"Lettera_di_Assunzione_Responsabilità_Uomini_Pubblicamente_Armati",
"Modulo_Iscrizione_e_Certificazione",
"Modulo_Personale_del_Certificatore",
"Modulo_Contabile_del_Certificatore"
);

function pagefrombody($body, $pagename, $template='wikipages'){
		return str_replace(
		array('<!--include:text_from_wiki_goes_here-->',
			'<!--templating:title-->',
			'<!--templating:fancytitle-->'),
		array($body,$pagename,str_replace('_', ' ', $pagename)),
		file_get_contents('./html/editme/'.$template.'.html')
	 );
};
 
function pagefromwiki($pagename){
	
	$baseurl = 'https://dev.partitopirata.org/projects/ppit/wiki/';
	$htmlurl = $baseurl.$pagename.'.html';
	if ( $pagename == "Statuto" ){
	        $htmlurl = $htmlurl.'?version=45';
	}
	//non controlla se ci sono 404, ma dovrebbe andare bene comunque
	$html = file_get_contents($htmlurl); 
	// http://stackoverflow.com/a/4911037
	if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html, $matches)) {
			$body = $matches[1];
		}
	 
	return pagefrombody($body, $pagename, 'wikipages');  
 };
  
 
 foreach($wikipages as $bla){
 	$page[$bla]=pagefromwiki($bla);
 };
 
 
 /*
 *   Caricamento pagine pseudodinamiche.
 *   ===================================
 *
 */

// bla bla bla.
//$page['indirizzo']=testo della pagina, che può includere tag definiti sopra


/*
 *   Caricamento pagine statiche dal filesystem.
 *   ===========================================
 *
 */

$scandir=array_diff(scandir($settings['sourcf']), array('.', '..'));

foreach($scandir as $file){	
	if(is_file($settings['sourcf'].$file)){//non dovrebbero esserci cartelle, ma non si sa mai
		$pathin=pathinfo($file);
		if($pathin['extension'] == 'html'){
			$page[$pathin['filename']] = file_get_contents($settings['sourcf'].$file);
		}
	}else{
		echo "Non devono esserci cartelle in `".$settings['sourcf']."`.\n Ed in ogni caso, le sto ignorando: si, parlo di `".$file."`.\n";
	};
};

/*
 *   Caricamento verbale (LQFB).
 *   ===================================
 *
 */

function pagefromlqfb($pagename) {
/*
        TODO: crea albero directory := ./area/issue/initiative/draft/
        	non supporta la creazione di cartelle. scegliere altro simbolo.
        TODO: per ogni draft crea file html (senza html-body; file da includere in una funzione successiva che compone verbale.html)
*/
};


$apiurl = "http://apitest.liquidfeedback.org:25520/";
$bla = array();
do{
	$draftsurl= $apiurl."draft?current_draft=true";
	$draftsjson = file_get_contents($draftsurl,0,null,null);
	$drafts = json_decode($draftsjson, true);
	//print_r($drafts);
}while($drafts['status']!='ok');

foreach($drafts['result'] as $draft){
	$sep='';
	$page['proposta'.$sep.$draft['initiative_id']]
		=pagefrombody($draft['content']);

};

/*
$initiativesurl= $apiurl."initiative";
$initiativesjson = file_get_contents($initiativesurl,0,null,null);
$initiatives = json_decode($initiativesjson);
var_dump($initiatives, true);
$issuesurl= $apiurl."issue";
$issuesjson = file_get_contents($issuesurl,0,null,null);
$issues = json_decode($issuesjson);
var_dump($issues, true);
$areasurl = $apiurl."area";
$areasjson = file_get_contents($areasurl,0,null,null);
$areas = json_decode($areasjson);
var_dump($areas, true);

foreach($drafts as $draft){
        TODO: draft id => $bla,
        TODO: datetime => $bla,
        TODO: draft text => $bla,
        foreach($initiatives as $initiative){
                TODO: initiative id => $bla,
                TODO: initiative name => $bla,
                foreach($issues as $issue){
                        TODO: issue id => $bla,
                        TODO: alternatives => $bla,
                        TODO: area id => $bla,
                        foreach($areas as $area){
                                if ( $area == $bla[area id]){
                                        TODO: area name => $bla
                                } 
                        };
                };
        };
        $page[$bla] = pagefromlqfb($bla);
};


*/

/*
 *   Applicazione delle sostituzioni e stampa.
 *   =========================================
 *
 */
foreach($page as $name => $text){ 
        if ( $name == 'Il_Partito_Pirata' ){
                $name = index;
        }elseif($name=='wikipages'){
        	continue;
        };
        
        file_put_contents(
                $settings['desf'].$name.'.html',
                str_replace($tag, $re, $text)
        );
};

//cronometro, ignorare.
$fine = microtime(true);
$tempo_impiegato = $fine - $inizio;
$tempo = number_format($tempo_impiegato,5,',','.');
echo "Tempo impiegato dallo script: $tempo secondi\n";
?>