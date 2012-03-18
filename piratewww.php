#!/usr/bin/php
<?php
$perfstart = microtime(true);
error_reporting(E_ALL && E_NOTICE);

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
        $settings['FF'] = true;
        $settings['TRIBUNE'] = true;
        $settings['REPORT'] = true;
        $settings['FULL'] = true;
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
        echo "Create a Pirate WWW in your htdocs starting from the base directory ".$settings['BASEDIR'].".\n";
        echo "\n";
        echo "Usage: ".$argv[0]." <option>\n";
        echo "\n";
        echo "--help, -help, -h, or -?	to get this help.\n";
        echo "--version, -v		to return the version of this file.\n";
        echo "--debug, -d		[TODO] to turn on output debugging.\n";
        echo "--test, -t		[TODO] to fake any write operation (filesystem, api).\n";
        echo "--basedir [directory]	to change base directory from ".$settings['BASEDIR']." .\n";
        echo "--createdirs, -c	to create needed dirs starting from basedir and touch empty templates and includes.";
        echo "--clean, -c		to delete *.html.\n";
        echo "--full, -f		to clean dirs and generate Formalfoo, Tribune, Report.\n";
        echo "--quickstart, -q		same as --clean, --createdirs and --full.\n";
        echo "\n";
        echo "Command line options override config files options.\n";
      exit;
      break;
    }; // switch options
  }; // for each option
}; // if is option

if ( $settings['DEBUG'] ) $www->debug();
if ( $settings['CLEAN'] ) $www->cleanprevious();
if ( $settings['CREATEDIRS'] ) $www->createdirs();
if ( $settings['FF'] ) $www->updateFormalfoo();
if ( $settings['TRIBUNE'] ) $www->updateTribune();
if ( $settings['REPORT'] ) $www->updateReport();

// cronometro.
$perfstop = microtime(true);
$perfs = $perfstop - $perfstart;
$perfstime = number_format($perfs,5,',','.');
echo "Tempo impiegato dallo script: $perfstime secondi\n";
?>
