#!/usr/bin/php
<?php
error_reporting(E_ALL && E_NOTICE);
$perfstart = microtime(true);

require_once('new_staticizzatore.conf');
require_once('libpiratewww.php');

echo "Init: ";

// create needed dirs
function createdirs($dir=NULL) {
  if ( file_exists($dir) ){
    $comando="mkdir ".$dir.$settings['templates']." ".$dir.$settings['includes']." ".$dir.$settings['htdocs']." ";
    $uscita[0]="Ok";
    $ritorno=1;
    exec($comando,$uscita,$ritorno);
    if ( $ritorno != 0 ){
      echo "ATTENZIONE: non ho potuto creare le directories necessarie.\n";
      exit(1);
    };
  };
}

// clean previous .html files from htdocs
function cleanprevious($htdocs=NULL) {
  if ( file_exists($htdocs) ){
    $comando="rm ".$settings['basedir'].$settings['htdocs']."*.html";
    $uscita[0]="Ok";
    $ritorno=1;
    exec($comando,$uscita,$ritorno);
    if ( $ritorno != 0 ){
      echo "ATTENZIONE: non ho potuto cancellare i file .html pre-esistenti\n";
      exit(1);
    };
  };
}

echo "OK\n";
echo "Config: ";

// config parsing
$settings['basedir']=$basedir;
unset($basedir);
$settings['templates']=$templates;
unset($templates);
$settings['includes']=$includes;
unset($includes);
$settings['htdocs']=$htdocs;
unset($htdocs);
$settings['wikiurl']=$wikiurl;
unset($wikiurl);
$settings['lfapi']=$lfapi;
unset($lfapi);
$settings['formalfoo']=array($index,$manifesto,$statuto,$iscrizione,$intento,$lara,$lart,$larm,$modiscri,$modident,$modquota);
unset($index,$manifesto,$statuto,$iscrizione,$intento,$lara,$lart,$larm,$modiscri,$modident,$modquota);
$settings['debug'] = false;
$settings['test'] = false;
$settings['cleanprevious'] = false;

echo "OK\n";
echo "Commands: ";

// command parsing
if (isset($argc)) {
  for ($i = 1; $i < $argc; $i++) {
    switch($argv[$i]) {
      case "-v":
      case "--version":
        echo $argv[0]." v0.1\n";
        exit;
        break;
      case "-d":
      case "--debug":
        $settings['debug'] = true;
        break;
      case "-t":
      case "--test":
        $settings['test'] = true;
        break;
      case "--base":
      case "--basedir":
      case "--dir":
        $settings['basedir'] = $argv[++$i];
        break;
      case "-c":
      case "--createdirs":
        createdirs($settings['basedir']);
        exit;
        break;
      case "-p":
      case "--cleanprevious":
        $settings['cleanprevious'] = true;
        break;
      case "-?":
      case "-h":
      case "--help":
        echo "Create a Pirate WWW in your htdocs starting from the base directory ".$settings['basedir'].".\n";
        echo "\n";
        echo "Usage: ".$argv[0]." <option>\n";
        echo "\n";
        echo "--help, -help, -h, or -?	to get this help.\n";
        echo "--version, -v		to return the version of this file.\n";
        echo "--debug, -d		to turn on output debugging.\n";
        echo "--test, -t		to fake any write operation (filesystem, api).\n";
        echo "--basedir [directory]	to change base directory from ".$settings['basedir']." .\n";
        echo "--createdirs, -c		to create needed dirs starting from basedir.";
        echo "\n";
        echo "Command line options override config files options.\n";
      exit;
      break;
    }; // switch options
  }; // for each option
}; // if is option

echo "OK\n";
echo "Exec: ";

// istanzia gli oggetti e lavora
$www = new piratewww('IT',$settings['lfapi']);

echo "OK\n";

// cronometro.
$perfstop = microtime(true);
$perfs = $perfstop - $perfstart;
$perfstime = number_format($perfs,5,',','.');
echo "Tempo impiegato dallo script: $perfstime secondi\n";
?>
