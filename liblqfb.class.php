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

class Liquidapi implements Liquid {
	
	private $apiserver;
	private $tnt;
	
	function __construct() { // parametri del server e regolazione dell'insistenza.
		global $settings;
		
		$this->apiserver = $settings['LFAPIURL'];
		$this->tnt = $settings['LFMAXTENT'];
	}

	function __destruct() {
	}
	
	function getSomething($what, $querystring) { // la funzione brutale. fa una query.
		$i=0;
		do {
			$draftsurl= $this->apiserver.$what.'?'.$querystring;			
			$draftsjson = file_get_contents($draftsurl,0,null,null);
			$drafts = json_decode($draftsjson, true);
			//print_r($drafts);
			$i++;
		} while ($drafts['status']!='ok' && $i <= $this->tnt);
		
		if ($i < $this->tnt)
			return $drafts['result'];
		elseif ( $drafts['status'] )
			return $drafts['status'];
		else
			return false;
	}
	
	function getDrafts($querystring='') {
		$txts = $this->getSomething('draft', $querystring);
		if ($this->settings['DEBUG']) echo "gettando drafts\n";
		foreach($txts as $txt){
			$res=$this->getInitiativeInfo($txt['initiative_id']);
			$txt['issue_id']=$res['issue_id'];
			$txt['name']=$res['name'];
			$txt['created']=$res['created'];
			$res=$this->getIssueInfo($txt['issue_id']);
			$txt['area_id']=$res['area_id'];
			$res=$this->getAreaInfo($txt['area_id']);
			$txt['area_name']=$res['name'];

			$txn[$txt['id']]=$txt;
		};
		
		krsort($txn);
		
		if (is_array($txn))
			return $txn;
		else
			return false;
	}
	
	function getInitiativeInfo($id)	{ 
	// mah, apiserver: dimmi un po' di questa proposta...
		
		$res = $this->getSomething('initiative', 'initiative_id='.$id);
		
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getIssueInfo($id) { 
	// mah, apiserver: dimmi un po' di questo Tema...
		
		$res = $this->getSomething('issue', 'issue_id='.$id);
		
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getAreaInfo($id) { 
	// mah, apiserver: dimmi un po' di questa proposta...
		
		$res = $this->getSomething('area', 'area_id='.$id);
		
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getApproved($offset, $limit) {
		$qs = 'include_initiatives=true&'.'include_issues=true&';
		$qs .= 'issue_state=finished_with_winner&';
		$qs .= 'initiative_winner=true&';
		$qs .= 'current_draft=true&'.'render_content=html&';
		$qs .= 'limit='.$limit.'&'.'offset='.$offset.'&';
		return $this->getDrafts($qs);		
	}
};
?>
