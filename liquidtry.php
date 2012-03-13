#!/usr/bin/php
<?php
require_once('liquidquery.php');
$lqfb = new liquidquery('http://apitest.liquidfeedback.org:25520/');

$drafts = $lqfb->getDrafts('all', true, true, 1);

foreach($drafts as $draft){
	echo $draft['initiative_id']."\n";
}
?>