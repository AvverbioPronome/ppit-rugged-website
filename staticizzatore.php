#!/usr/bin/php
<?php
error_reporting(E_ALL && E_NOTICE);
$inizio = microtime(true);
// dove stanno i nostri simpatici sorgenti?
$settings['sourcf']='./html/editme/'; 

// dove vomito il risultato?
$settings['desf']='./html/'; 

// cancella output pre-esistente (ie: tutti i .html in ./html/)
if (isset($argv)){
    if ( $argv[1]  == "-d"){
        if ( file_exists($settings['desf']) ){
            $comando="rm ".$settings['desf']."*.html";
            $uscita[0]="Ok";
            $ritorno=1;
            exec($comando,$uscita,$ritorno);
            if ( $ritorno != 0 ){
                echo "ATTENZIONE: non ho potuto cancellare i file .html pre-esistenti\n";
            };
        };
    }
};


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
 
$tag[] = '<br /><br />';
 $re[] = '</p><p>'; //si, lo so che sono due cose diverse. ma non lo sapete usare.
 
$tag[] = '<br />';
 $re[] = ' '; //si che le fa in ordine... :D

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
"Modulo_Contabile_del_Certificatore",
"Storia", "Assemblea_Occasionale_2012"
);

function pagefrombody($body, $pagename, $template='wikipages'){
	return str_replace(
		array('<!--include:text_from_wiki_goes_here-->',
			'<!--templating:title-->',
			'<!--templating:fancytitle-->'),
		array($body,$pagename,str_replace('_', ' ', $pagename)),
		file_get_contents('./html/editme/'.$template.'.html')
	 );
}; // questa funzione serve anche più avanti per lqfb. attenzione.
 
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
 *   Caricamento tribuna / verbale (LQFB).
 *   ===================================
 *
 */

require_once 'liblf.php';

$liq = new liquidquery('http://apitest.liquidfeedback.org:25520/');

$tutte = $liq->getDrafts(""); // tutte le bozze.
$indexbody='<p>Il Verbale del Partito Pirata riporta fedelmente tutta l\'attivita\' dell\'Assemblea Permanente
elencando tutte le iniziative e rielencandole quando vengono modificate dai relatori.</p>';
foreach($tutte as $initiative){
	$sep='_';
	$initurl='verbale'.$sep.$initiative['initiative_id'];
	$page[$initurl] = pagefrombody($initiative['content'], $title);
	
	$page[$initurl] = "<article id=init".$initiative['initiative_id'].">";
	$page[$initurl] .= "<h4>Tema n. "."null"."->Iniziativa n.".$initiative['initiative_id']."->Bozza n.".$initiative['id'].":</h4>";
	$page[$initurl] .= "<h1>'".$initiative['name']."'</h1>";
	$page[$initurl] .= $initiative['content'];
	$page[$initurl] .= "<footer>Pubblicato <time datetime=".$initiative['created'].">".$initiative['created']."</time> da Spugna, portavoce dell'Assemblea Permanente,"." tags "."null"."</footer>";
	$page[$initurl] .= "</article>\n";
	
	$page[$initurl]=pagefrombody($page[$initurl], $initiative['name'], 'verbale');
	
	$indexbody .= "<article id=init".$initiative['id'].">";
	$indexbody .= $initiative['created']." - <a href='".$initurl.".html'>'".$initiative['name']."'</a>"." (<a href=\"#\">T"."null"."I".str_pad($initiative['initiative_id'], 5, '0', STR_PAD_LEFT)."D".str_pad($initiative['id'], 5, '0', STR_PAD_LEFT)."</a>)";
	$indexbody .= "</article>\n";
}
$page['verbale']=pagefrombody($indexbody, 'Verbale', 'verbale');
unset($indexbody);

$approvate = $liq->getApproved(10,0,2); // solo le prime dieci.
$indexbody='<p>La Tribuna Politica del Partito Pirata elenca le iniziative assembleari
che hanno raggiunto l\'approvazione, accompagnate da eventuali commenti "a bocce
ferme" da parte di chi desideri inviare degli approfondimenti sul
significato delle scelte assembleari qui elencate, aggiungere una prospettiva storica, 
commentare le alternative bocciate dall\'assemblea, contestualizzare o 
descrivere i potenziali scenari aperti dal cambiamento approvato.</p>';
foreach($approvate as $initiative){
	$sep='_';
	$initurl='tribuna'.$sep.$initiative['initiative_id'];
	$page[$initurl] = pagefrombody($initiative['content'], $title);
	
	$page[$initurl] = "<article id=init".$initiative['initiative_id'].">";
	$page[$initurl] .= "<h1><a href='".$initurl.".html'>Iniziativa n.".$initiative['initiative_id'].": '".$initiative['name']."'</a></h1>";
	$page[$initurl] .= $initiative['content'];
	$page[$initurl] .= "<footer>Pubblicato <time datetime=".$initiative['created'].">".$initiative['created']."</time> da Spugna, portavoce dell'Assemblea Permanente, nel Contesto "."TODO"." con tags "."TODO"."</footer>";
	$page[$initurl] .= "</article>\n";
	
	$page[$initurl]=pagefrombody($page[$initurl], $initiative['name'], 'tribuna');
	
	$indexbody .= "<article id=init".$initiative['initiative_id']."><ul>";
	$indexbody .= "<li>".$initiative['created']."<a href='".$initurl.".html'>Iniziativa n.".$initiative['initiative_id'].": '".$initiative['name']."'</a></li>";
	$indexbody .= "</ul></article>\n";
};
$page['tribuna']=pagefrombody($indexbody, 'Tribuna', 'tribuna');
unset($indexbody);


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
        
        if (!file_exists($settings['desf'].$name.'.html')) {
            file_put_contents(
                    $settings['desf'].$name.'.html',
                    str_replace($tag, $re, $text)
                    );
        };
};

//cronometro, ignorare.
$fine = microtime(true);
$tempo_impiegato = $fine - $inizio;
$tempo = number_format($tempo_impiegato,5,',','.');
echo "Tempo impiegato dallo script: $tempo secondi\n";
?>