#!/usr/bin/php
<?php
$perfstart = microtime(true);

require_once 'configure.php';
require_once 'libpiratewww.class.php';
require_once 'liblqfb.class.php';
require_once 'libpage.class.php';
require_once 'libpage.ext.class.php';


$www = new Piratewww();

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
        error_reporting(E_ALL && E_NOTICE);
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
      case "--clean":
        $settings['CLEAN'] = true;
        break;
      case "-c":
      case "--createdirs":
        $settings['CREATEDIRS'] = true;
        break;
      case "-f":
      case "--full":
        $settings['CLEAN'] = true;
        $settings['FULL'] = true;
        // i'm deliberately not breaking here.
      case "--update":
      case "-u":
        $settings['FF'] = true;
        $settings['TRIBUNE'] = true;
        $settings['REPORT'] = true;
        break;
      case "-q":
      case "--quickstart":
        $settings['CLEAN'] = true;
        $settings['CREATEDIRS'] = true;
        $settings['FF'] = true;
        $settings['TRIBUNE'] = true;
        $settings['REPORT'] = true;
        $settings['FULL'] = true;
        $settings['QUICKSTART'] = true;
        break;
      case "-?":
      case "-h":
      case "--help":
      default:      
        $help .= "Create a Pirate WWW in your htdocs starting from the base directory ".$settings['BASEDIR'].".\n\n";

        $help .= "Usage: ".$argv[0]." <option>\n\n";

        $help .= "--help, -help, -h, or -?	to get this help.\n\n";
        
        $help .= "--version, -v		to return the version of this file.\n";
        $help .= "--debug, -d		[TODO] to turn on output debugging.\n";
        $help .= "--test, -t		[TODO] to fake any write operation (filesystem, api).\n";
        $help .= "--basedir [dir]   	to change base directory from ".$settings['BASEDIR']." .\n";
        $help .= "--createdirs, -c	to create needed dirs starting from basedir and touch empty templates and includes.\n";
        $help .= "--clean, -c		to delete ".$settings['BASEDIR'].$settings['HTDOCS']."*.html.\n";
        $help .= "--full, -f		to clean dirs and generate Formalfoo, Tribune, Report.\n";
        $help .= "--quickstart, -q	same as --clean, --createdirs and --full.\n";
        $help .= "--update, -u            same as --full, without cleaning dirs";
        $help .= "\n";
        $help .= "Command line options override config files options.\n";
        
        echo $help; // wordwrap($help, 80, "\n                  "); //non funziona il dannato.
      exit;
      break;
    }; // switch options
  }; // for each option
}; // if is option

if ( $settings['DEBUG'] ) $www->debuggg();
if ( $settings['CLEAN'] ) $www->cleanprevious();
if ( $settings['CREATEDIRS'] ) $www->createdirs();
if ( $settings['FF'] ) $www->updateFormalfoo();
if ( $settings['TRIBUNE'] ) $www->updateTribune();
if ( $settings['REPORT'] ) $www->updateReport();

// cronometro.
printf("Tempo impiegato dallo script: %.5f secondi\n", microtime(true) - $perfstart);
?>