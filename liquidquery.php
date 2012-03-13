<?php

define(MAXATTEMPT, 2);

class liquidquery{
	
	private $apiurl='http://apitest.liquidfeedback.org:25520/';
	
	function __construct($apiserver)
	{
		$this->apiurl=$apiserver;
	}
	
	function getSomething($what, $querystring, $tnt=MAXATTEMPT)
	{
		$i=0;
		do{
			$draftsurl= $this->apiurl.$what.'?'.$querystring;			
			$draftsjson = file_get_contents($draftsurl,0,null,null);
			$drafts = json_decode($draftsjson, true);
			//print_r($drafts);
			$i++;
		}while($drafts['status']!='ok' && $i <= $tnt);
		
		if($i < $tnt)
			return $drafts['result'];
		elseif( $drafts['status'] )
			return $drafts['status'];
		else
			return false;
	}
	
	function getDrafts($querystring='', $tnt=MAXATTEMPT)
	{
		$txts = $this->getSomething('draft', $querystring, $tnt);
		
		foreach($txts as $txt){
			$res=$this->getInitInfo($txt['initiative_id']);
			//print_r($res);
			$txt['name']=$res['name'];
			$txn[]=$txt;
		};
		
		unset($txt, $res, $txts);
		
		if (is_array($txn))
			return $txn;
		else
			return false;
	}
	
	function getApproved($limit, $offset, $tnt=MAXATTEMPT){
		
		$qs .= 'include_initiatives=true&'.'include_issues=true&';
		$qs .= 'issue_state=finished_with_winner&';
		$qs .= 'initiative_winner=true&';
		$qs .= 'current_draft=true&'.'render_content=html&';
		$qs .= 'limit='.$limit.'&'.'offset='.$offset.'&';
		
		return $this->getDrafts($qs, $tnt);
		
	}
	
	function getInitInfo($id)
	{
		$res = $this->getSomething('initiative', 'initiative_id='.$id);
		return $res[0];
	}

};
?>