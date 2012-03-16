<?php

require_once 'libpage.ext.class.php';
require_once 'liblqfb.class.php';
//require_once 'libpiratewww.class.php';

// questo è quello a cui penso: il software deve essere a prova di scemo. 
// ma le classi principali devono restare pulite, cazzo di cristo.

$liq = new Liquidquery('http://apitest.liquidfeedback.org:25520/');

foreach($liq->getApproved(0,2) as $a){
	$pagina = new Tribune($a);
	$pagina->writePage(); // se ne fotte delle cartelle. non è un gran problema.
}



?>