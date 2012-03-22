<?php
require_once 'liblqfb.class.php';

class Liquidcore implements Liquid {
	private $coreserver;
	private $tnt;
	
	function __construct() { // parametri del server e regolazione dell'insistenza.
		global $settings;

		$pg = parse_url($settings['LFCORE']);
		$connectstring = "host=".$pg['host']." port=".$pg['port']." user=".$pg['user']." password=".$pg['pass']." dbname=".ltrim($pg['path'],"/");
		$this->coreserver = pg_pconnect($connectstring);
		$this->tnt = $settings['LFMAXTENT'];
	}

	function __destruct() {
		pg_close($this->coreserver);
	}
	
	function getSomething($from, $where='') {
		$query = "SELECT * FROM ".$from;
		if ( $where != '' ) {
			$query .= $where;
		}
		$query .= ";";
		$result = pg_exec($this->coreserver, $query);
		$rows = '';
		while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
			$rows[] = $row;
		}
		return $rows;
	}


	function getAreaInfo($id) {
		$res = false;
		if ($id != '') $res = $this->getSomething('area', ' WHERE id='.$id);
		if ($res[0])
			return $res[0];
		else
			return false;
	}

	function getIssueInfo($id) {
		$res = false; 
		if ($id != '') $res = $this->getSomething('issue', ' WHERE id='.$id);
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getInitiativeInfo($id)	{
		$res = false;
		if ($id != '') $res = $this->getSomething('initiative', ' WHERE id='.$id);
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getDraftInfo($id) {
		$res = false;
		if ($id != '') $res = $this->getSomething('draft', ' WHERE id='.$id);
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getDrafts($qs='') {
		$drafts = $this->getSomething('draft', $qs);
		$txn = NULL;
		foreach($drafts as $draft) {
			$initiative = $this->getInitiativeInfo($draft['initiative_id']);
			$draft['issue_id']=$initiative['issue_id'];
			$draft['name']=$initiative['name'];
			$draft['created']=$initiative['created'];
			$issue=$this->getIssueInfo($initiative['issue_id']);
			$draft['area_id']=$issue['area_id'];
			$draft['closed']=$issue['closed'];
			$area=$this->getAreaInfo($issue['area_id']);
			$draft['area_name']=$area['name'];

			$txn[$draft['id']]=$draft;
		};
		
//		krsort($txn);
		
		if (is_array($txn))
			return $txn;
		else
			return false;
	}

	function getApproved($offset, $limit) {
/*		$qs = 'include_initiatives=true&'.'include_issues=true&';
		$qs .= 'issue_state=finished_with_winner&';
		$qs .= 'initiative_winner=true&';
		$qs .= 'current_draft=true&'.'render_content=html&';*/
		$qs = ' ORDER BY issue_id DESC'; 
		if ( $limit > 0 ) $qs .= ' LIMIT '.$limit;
		if ( $offset > 0 ) $qs .= ' OFFSET '.$offset;

		$battles = $this->getSomething('battle', $qs);
		$txn = NULL;
		foreach($battles as $battle) {
			$draft = $this->getSomething('current_draft', ' WHERE initiative_id='.$battle['winning_initiative_id']);
			$initiative = $this->getInitiativeInfo($draft[0]['initiative_id']);
			$draft[0]['issue_id']=$initiative['issue_id'];
			$draft[0]['name']=$initiative['name'];
			$draft[0]['created']=$initiative['created'];
			$issue=$this->getIssueInfo($initiative['issue_id']);
			$draft[0]['area_id']=$issue['area_id'];
			$draft[0]['closed']=$issue['closed'];
			$area=$this->getAreaInfo($issue['area_id']);
			$draft[0]['area_name']=$area['name'];

			$txn[$draft[0]['id']]=$draft[0];
		};
		
//		krsort($txn);
		
		if (is_array($txn))
			return $txn;
		else
			return false;
	}
};
?>