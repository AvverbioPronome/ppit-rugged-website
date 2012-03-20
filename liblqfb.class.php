<?php

require_once 'configure.php';

interface Liquid {
	// 
	public function getSomething($from, $where);
	// 
	public function getDrafts($querystring);
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

		$host = $settings['LFCORE'];
		$port = $settings['LFCORE'];
		$user = $settings['LFCORE'];
		$password = $settings['LFCORE'];
		$dbname = $settings['LFCORE'];

		$this->coreserver = pg_pconnect("host=".$host,"port=".$port,"user=".$user,"password=".$password,"dbname=".$dbname);
		$this->tnt = $settings['LFMAXTENT'];
	}

	function __destruct() {
		pg_close($this->coreserver);
	}
	
	function getSomething($from, $where=NULL) {
		$query = "SELECT * FROM ".$from;
		if ( $where != NULL ) {
			$query .= " WHERE ".$where;
		}
		$query .= ";";
		$result = pg_exec($this->coreserver, $query);
		if (count($result) > 1) {
			return pg_fetch_array($result, PGSQL_ASSOC);
		} else {
			return false;
		}
	}

	function getDrafts($querystring='') {
		$txts = $this->getSomething('draft', $querystring);
		foreach($txts as $txt){
			$res=$this->getInitiativeInfo($txt['initiative_id']);
			$txt['issue_id']=$res['issue_id'];
			$txt['name']=$res['name'];
			$txt['created']=$res['created'];
			$txn[$txt['id']]=$txt;

			$res=$this->getIssueInfo($txt['issue_id']);
			$txt['area_id']=$res['area_id'];
			$txn[$txt['id']]=$txt;
			
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

	function getAreaInfo($id) {
		$res = $this->queryAreas('area', 'area_id='.$id);
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getIssueInfo($id) { 
		$res = $this->queryIssues('issue', 'issue_id='.$id);
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getInitiativeInfo($id)	{ 
		$res = $this->queryInitiative('initiative', 'initiative_id='.$id);
		if ($res)
			return $res[0];
		else
			return false;
	}

	function getApproved($offset, $limit) {
// qui tocca studiarsi core.sql di lqfb  per generare una query identica a quella fatta via api
		$qs = '';
/*		$qs = 'include_initiatives=true&'.'include_issues=true&';
		$qs .= 'issue_state=finished_with_winner&';
		$qs .= 'initiative_winner=true&';
		$qs .= 'current_draft=true&'.'render_content=html&';
		$qs .= 'limit='.$limit.'&'.'offset='.$offset.'&';*/
		return $this->getDrafts($qs);		
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
			//$txn[$txt['id']]=$txt;

			$res=$this->getIssueInfo($txt['issue_id']);
			$txt['area_id']=$res['area_id'];
			//$txn[$txt['id']]=$txt;
			
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
