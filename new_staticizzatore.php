#!/usr/bin/php
<?php
error_reporting(E_ALL && E_NOTICE);
$perfstart = microtime(true);

require_once 'new_staticizzatore.conf';
require_once 'libpiratewww.php';
require_once 'liblf.php';

$lfapi = new liquidquery('http://apitest.liquidfeedback.org:25520/');
$www = new piratewww('IT');

// command parsing with get_opt()


//cronometro, ignorare.
$perfstop = microtime(true);
$perfs = $perfstart - $perfstop;
$perfstime = number_format($perfs,5,',','.');
echo "Tempo impiegato dallo script: $tempo secondi\n";
?>
