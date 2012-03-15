<?php
require_once 'pagina.class.php';
require_once 'sostituzioni.list.php'; // definisce $tag e $re, insieme a $subs

require_once 'liquidquery.class.php';

$indice = new Pagina('tribuna', $subs, './html/templates/tribuna.html');
$indice->appendToBody('<p>La Tribuna Politica del Partito Pirata elenca le iniziative assembleari
che hanno raggiunto l\'approvazione, accompagnate da eventuali commenti "a bocce
ferme" da parte di chi desideri inviare degli approfondimenti sul
significato delle scelte assembleari qui elencate, aggiungere una prospettiva storica, 
commentare le alternative bocciate dall\'assemblea, contestualizzare o 
descrivere i potenziali scenari aperti dal cambiamento approvato.</p>');

$liq = new liquidquery('http://apitest.liquidfeedback.org:25520/');

echo "initiatives:\t";

foreach($liq->getApproved(0,10) as $init){
	
	$page = new Pagina($init['initiative_id'], $subs, './html/templates/tribuna.html');
	$page->appendToBody('<article id=init'.$init['initiative_id'].'><h1><a href='.$page->pagename.$page->extension.'">'.$init['name']."</a></h1>");
	$page->appendToBody($init['content']);
	$page->appendToBody('</article>');
	
	$page->salva('./html/', 'tribuna_init'.$page->pagename.'.html');
	
	$indice->appendToBody("\n");
	$indice->appendToBody("<section id=init".$init['initiative_id'].">");
	$indice->appendToBody('<h1><a href="'.$page->saved_as.'">'.$init['name']."</a></h1>");
	$indice->appendToBody(strlen($init['content']) < 3000 ? $init['content'] : substr($init['content'], 0, 3000).'[continua...]');
	$indice->appendToBody('<p><small>Pubblicato <time datetime='.$initiative['created'].'>'.$initiative['created'].'</time> da Spugna, portavoce dell\'Assemblea Permanente, nel Contesto '."TODO".' con tags '."TODO".'</small></p>');
	$indice->appendToBody('</section>');
	echo '*';

}

$indice->salva('./html/', 'tribuna.html');
echo "\n";

?>