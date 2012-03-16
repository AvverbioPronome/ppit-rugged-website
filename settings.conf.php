<?php

// basedir
$settings['BASEDIR']='./';
// dove sono i template (ie: wikipages, tribuna, verbale, etc)
$settings['TEMPLATEDIR'] = 'templates/')

// dove sono gli include (ie: ppheader, ppfooter,etc)
$settings['INCLUDEDIR'] = 'includes/';

// dove scrivere i file html finali
$settings['HTDOCS'] = 'html/';

// dove prelevare i testi
$settings['WIKIURL'] = 'https://dev.partitopirata.org/projects/ppit/wiki/';

// dove prelevare i lavori assembleari
$settings['LFAPIURL'] = 'http://apitest.liquidfeedback.org:25520/';

// Composizione Gazzetta
$settings['formalfoo'][] = array('Il_Partito_Pirata', 28);
$settings['formalfoo'][] = array('Manifesto', 13);
$settings['formalfoo'][] = array('Statuto', 45); 
$settings['formalfoo'][] = array('Garanzia_di_Iscrizione_ed_Esclusione', 11);
$settings['formalfoo'][] = array('Lettera_di_Intento_Pirata', 4);
$settings['formalfoo'][] = array('Lettera_di_Assunzione_Responsabilità_Artistiche',5);
$settings['formalfoo'][] = array('Lettera_di_Assunzione_Responsabilità_Tecniche', 27);
$settings['formalfoo'][] = array('Lettera_di_Assunzione_Responsabilità_Uomini_Pubblicamente_Armati', 5);
$settings['formalfoo'][] = array('Modulo_Iscrizione_e_Certificazione',5);
$settings['formalfoo'][] = array('Modulo_Personale_del_Certificatore',5);
$settings['formalfoo'][] = array('Modulo_Contabile_del_Certificatore',3);

// staticonf.
$settings['debug'] = false;
$settings['test'] = false;
$settings['cleanprevious'] = false;

?>