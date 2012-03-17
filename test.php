#!/usr/bin/php
<?php
$perfstart = microtime(true);

require_once 'configure.php';
require_once 'libpiratewww.class.php';
require_once 'liblqfb.class.php';
require_once 'libpage.class.php';
require_once 'libpage.ext.class.php';

$bla = new Piratewww();
//$bla->debuggg();
$bla->updateFormalfoo();
$bla->updateTribune();
$bla->updateReport();

// cronometro.
$perfstop = microtime(true);
$perfs = $perfstop - $perfstart;
$perfstime = number_format($perfs,5,',','.');
echo "Tempo impiegato dallo script: $perfstime secondi\n";
?>