<?php
class Liquidquery {
	
	private $apiserver=NULL; // default
	private $tnt=2; // default
	
	function __construct($apiserver=NULL, $tnt=NULL)
	{ // parametri del server e regolazione dell'insistenza.
		
		$this->apiserver = $apiserver ? $apiserver : exit(1);
		$this->tnt = $tnt ? $tnt : $this->tnt;
	}
	
	function getSomething($what, $querystring)
	{ // la funzione brutale. fa una query.
		
		$i=0;
		do{
			$draftsurl= $this->apiserver.$what.'?'.$querystring;			
			$draftsjson = file_get_contents($draftsurl,0,null,null);
			$drafts = json_decode($draftsjson, true);
			//print_r($drafts);
			$i++;
		}while($drafts['status']!='ok' && $i <= $this->tnt);
		
		if($i < $this->tnt)
			return $drafts['result'];
		elseif( $drafts['status'] )
			return $drafts['status'];
		else
			return false;
	}
	
	function getDrafts($querystring='')
	{ //
		
		$txts = $this->getSomething('draft', $querystring);
		
		foreach($txts as $txt){
			$res=$this->getInitiativeInfo($txt['initiative_id']);
			//print_r($res);
			$txt['name']=$res['name'];
			$txn[]=$txt;
		};
		
		
		if (is_array($txn))
			return $txn;
		else
			return false;
	}
	
	function getApproved($offset, $limit)
	{ //
		
		$qs .= 'include_initiatives=true&'.'include_issues=true&';
		$qs .= 'issue_state=finished_with_winner&';
		$qs .= 'initiative_winner=true&';
		$qs .= 'current_draft=true&'.'render_content=html&';
		$qs .= 'limit='.$limit.'&'.'offset='.$offset.'&';
		
		return $this->getDrafts($qs);		
	}
	
	function getInitiativeInfo($id)
	{ // mah, apiserver: dimmi un po' di questa proposta...
		
		$res = $this->getSomething('initiative', 'initiative_id='.$id);
		
		if ($res)
			return $res[0];
		else
			return false;
	}

};
?>
