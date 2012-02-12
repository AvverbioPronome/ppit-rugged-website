#!/usr/bin/php
<?php
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

$tag[] = '<!--include:ppbar-->';
 $re[] = file_get_contents('./html/inc/ppbar.html');

$tag[] = '<!--include:sitenav-->';
 $re[] = file_get_contents('./html/inc/sitenav.html');

// qui una volta c'era un coso che aggiustava i path dei css e simili,
// in forma di brutale sostituzione.

$scandir=array_diff(
	scandir($settings['sourcf']),
	array('.', '..'));

foreach($scandir as $name){
	$pathin=pathinfo($name);
	if($pathin['extension'] != 'html') continue;
	
	file_put_contents(
		$settings['desf'].$name,
		str_replace($tag, $re, file_get_contents($settings['sourcf'].$name))
	);
};
$fine = microtime(true);
$tempo_impiegato = $fine - $inizio;
$tempo = number_format($tempo_impiegato,5,',','.');
echo "Tempo impiegato dallo script: $tempo secondi\n";
?>