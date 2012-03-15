#!/usr/bin/php
<?php
require_once 'liquidquery.class.php';

$lqfb = new liquidquery('http://apitest.liquidfeedback.org:25520/', 2);

switch($argv[1]){
	case 'approved':
		$qs='initiative_winner=true&';
		$qs .= 'issue_state=finished_with_winner&';
		break;
	case 'current':
	default:
};

$qs.='current_draft=true&';

echo $qs."\n";

//$drafts = $lqfb->getDrafts();
//foreach($drafts as $draft){ echo $draft['initiative_id']."\n";}

foreach($lqfb->getDrafts($qs) as $draft){
	echo $draft['initiative_id'].': '.$draft['name']."\n";
	echo "\t".str_replace("\n", "\n\t", wordwrap($draft['content'], 60, "\n"))."\n";
}
?>