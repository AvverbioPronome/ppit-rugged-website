<?php

require_once 'configure.php';

interface Liquid {
	// 
	public function getSomething($from, $where);
	// 
	public function getAreaInfo($id);
	// 
	public function getIssueInfo($id);
	// 
	public function getInitiativeInfo($id);
	// 
	public function getApproved($offset, $limit);
}

if($settings['LFENABLEPG'])    require_once 'libliquidcore.class.php';
if($settings['LFENABLEAPI'])   require_once 'libliquidapi.class.php';