<?php

require_once 'configure.php';
require_once 'libpage.class.php';
require_once 'liblqfb.class.php';

class Formalfoo extends Piratepage {
	function __construct() {
		parent::__construct();

		$this->template= 'wikipages.html'; //solo il nome del file dentro la certella dei templates.
	}
	//dubito che dovremmo mettere qualcosa qui.
}

class Indice extends Piratepage {
	private $pages;
	private $chunksno;
	private $prefix;

	public $excerptlen=3000;
	public $intro;
	
	function __construct($template) {
		parent::__construct();
		
		$this->template= $template.'.html';
	}

	private function writeIndex() {
		parent::writePage(); //è inutile. si poteva chiamare direttamente.
	}

	private function chunking() {
		$this->chunksno = 0;
		$chunks = array_chunk($this->pages, $this->settings['INDEXPAGE'], true);
		$this->chunksno = count($chunks);
		$this->chunksno--;
		return $chunks;
	}

        private function addComments($draftid) {
                $comments[0] = "http://blog.partitopirata.org/2012/12/30/post-name";
                $comments[1] = "http://www.ilfattoquotidiano.it/2012/03/19/il-successo-di-costruire-la-normalita/198683/";
                $comments[2] = "http://www.beppegrillo.it/2012/03/passaparola_viv/index.html";
                $comments[3] = "http://dentroefuoricasapound.wordpress.com/2012/01/23/intervista-sul-libro-mediapolitika/";
                return $comments;
        }

	private function elementsToHtml($pages) {
		foreach ( $pages as $page ) {
		        $comments = $this->addComments($page->source['id']);
			// $page come oggetto Piratepage::Liquidpage? Si, ok.
			// $page->source contiene l'initiative, si possono usare i suoi pezzi per comporre l'indice.
			$this->content .= "\n".'<dt id='.$page->source['id'].'><a href="'.$page->id.'.html">'.$page->title.': '.$page->source['name'].'</a></dt>'."\n"; // article dentro dl?!?
			$this->content .= '<dd>'."\n";
			$this->content .= 'Tema n. '.$page->source['issue_id'].' - Area n. '.$page->source['area_id'].' ( '.$page->source['area_name'].' )'."<br>\n";
			$this->content .= 'ID: '.hash('sha256', $page->source['created'].$page->source['id'].$page->source['name'].$page->source['content'])."\n";
			$this->content .= "<p><small>Pubblicato in Gazzetta Ufficiale dall'Assemblea Permanente,<br> li' <time datetime=".$page->source['created'].">".$page->source['created'].".</time></small></p></dd>\n";
			if ( $this->prefix == "tribuna" ) {
        			$this->content .= '<ul>'."\n";
                                foreach ( $comments as $comment ) {
        	        		$this->content .= '<li>Commento: <a href="'.$comment.'">'.$comment.'</a></li>'."\n";
                                }
                                $this->content .= '</ul>'."\n";
                        }
		}
	}

	function addElement($page) {
		$this->pages[] = $page;
	}

	function createIndex() {
		$chunks = $this->chunking();
		$indexchunk = 0;
		$this->prefix = $this->id;
		foreach($chunks as $chunk) {
			$this->content = '<dl>';
			$this->elementsToHtml($chunk);
			$this->content .= '</dl>'."\n";
			if ( $this->chunksno > 0 ) {
				if ( $indexchunk > 0 ) $this->id = $this->prefix.'_i'.$indexchunk;
				$previd = $indexchunk - 1;
				$nextid = $indexchunk + 1;
				$this->content .= '<div align="center">'; //ovvove.
				if ( $indexchunk != 0 ) {
					if ( $indexchunk > 1 ) {
						$prevlink = $this->prefix."_i".$previd.'.html';
					} else {
						$prevlink = $this->prefix.'.html';
					}
					$this->content .= '<a href="'.$prevlink.'">';
					$this->content .= 'Successive';
					$this->content .= '</a>';
				}
				if ( $indexchunk != 0 && $indexchunk != $this->chunksno ) {
					$this->content .= ' | ';
				}
				if ( $indexchunk != $this->chunksno ) {
					$this->content .= '<a href="'.$this->prefix."_i".$nextid.'.html'.'">';
					$this->content .= 'Precedenti';
					$this->content .= '</a>';
				}
				$this->content .= '</div>';
			}
			$this->writeIndex($indexchunk);
			$indexchunk++;
		}
	}
}

class Liquidpage extends Piratepage {
        private $cosa;
	public $source;

	// roba che dovrebbero condividere Report e Tribune?
	function __construct($source, $cosa) {
		parent::__construct();
		
		$this->source=$source;
		$this->cosa = $cosa;

        switch($cosa) {
        case "report":
            $this->id='verbale_'.$source['id'];
            $this->template='report.html';
            $this->title = 'Proposta n. '.$source['id'];
        break;
        case "tribune":
            $this->id='tribuna_'.$source['initiative_id'];
            $this->template='tribune.html';
            $this->title = 'Iniziativa n. '.$source['initiative_id'];
        break;
        }
        $this->content .= "<article id=init".$this->id.">";
        $this->content .= "<hgroup><h6>Area n. ".$source['area_id']." &#x2283; Tema n. ".$source['issue_id']." &#x2283; Iniziativa n.".$source['initiative_id']." &#x220B; Proposta n.".$source['id']."</h6>";
        $this->content .= "<h1>".$source['name']."</h1>";
        $this->content .= "<h6>ID: ".hash('sha256', $source['created'].$source['id'].$source['name'].$source['content'])."</h6></hgroup>\n";
        $this->content .= "<p>".$source['content']."</p>";
        $this->content .= "<footer>Pubblicato in Gazzetta Ufficiale dall'Assemblea Permanente,<br> li' <time datetime=".$source['created'].">".$source['created'].".</time></footer>";
        $this->content .= "</article>\n";
	}

	function type() {
	    return $this->type;
        }
}

?>
