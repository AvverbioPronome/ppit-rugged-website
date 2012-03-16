#!/usr/bin/php
<?php
$perfstart = microtime(true);

echo "Init: ";

require_once 'configure.php';
require_once 'libpiratewww.class.php';

// create needed dirs
function createdirs($dir=NULL) {
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

echo "OK\n";
echo "Commands: ";

// command parsing and exec
if ($argc > 1) {
  for ($i = 1; $i < $argc; $i++) {
    switch($argv[$i]) {
      
      case "-v":
      case "--version":
        echo $argv[0]." v0.1\n";
        break;
        exit;
      case "-d":
      case "--debug":
        $settings['DEBUG'] = true;
        break;
      case "-t":
      case "--test":
        $settings['TEST'] = true;
        break;
      case "--base":
      case "--basedir":
      case "--dir":
        $settings['BASEDIR'] = $argv[++$i];
        break;
      case "-p":
      case "--cleanprevious":
        $settings['CLEAN'] = true;
        break;
      case "-c":
      case "--createdirs":
        createdirs($settings['BASEDIR']);
        exit;
        break;
      case "-?":
      case "-h":
      case "--help":
      default:      
        echo "Create a Pirate WWW in your htdocs starting from the base directory ".$settings['BASEDIR'].".\n";
        echo "\n";
        echo "Usage: ".$argv[0]." <option>\n";
        echo "\n";
        echo "--help, -help, -h, or -?	to get this help.\n";
        echo "--version, -v		to return the version of this file.\n";
        echo "--debug, -d		to turn on output debugging.\n";
        echo "--test, -t		to fake any write operation (filesystem, api).\n";
        echo "--basedir [directory]	to change base directory from ".$settings['BASEDIR']." .\n";
        echo "--createdirs, -c	to create needed dirs starting from basedir.";
        echo "\n";
        echo "Command line options override config files options.\n";
      exit;
      break;
    }; // switch options
  }; // for each option
}; // if is option

// new www
$www = new Piratewww($settings['BASEDIR'], $settings['WIKIURL'], $settings['LFAPIURL'], $settings['TEMPLATES'], $settings['INCLUDES'], $settings['HTDOCS'], $settings['LOCALE']);
if ( $settings['FULL'] || $settings['QUICKSTART'] ) {
  $last="1";
} else {
  // TODO: scandir to figure out which is the last draft already updated
}
if ( $settings['FF'] || $settings['FULL'] || $settings['QUICKSTART'] ) $www->updateFormalfoo();
if ( $settings['TRIBUNE'] || $settings['FULL'] || $settings['QUICKSTART'] ) $www->updateTribune();
if ( $settings['REPORT'] || $settings['FULL'] || $settings['QUICKSTART'] ) $www->updateReport();

echo "OK\n";
echo "Post: ";

// operazioni finali
echo "OK\n";

// cronometro.
$perfstop = microtime(true);
$perfs = $perfstop - $perfstart;
$perfstime = number_format($perfs,5,',','.');
echo "Tempo impiegato dallo script: $perfstime secondi\n";
?>
