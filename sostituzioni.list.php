<?php
 function ftbi($filename){
 	return file_get_contents('./html/inc/'.$filename.'.html');
 }

$tag[] = '<!--include:ppheader-->';
 $re[] = ftbi('ppheader');

$tag[] = '<!--include:sitenav-->';
 $re[] = ftbi('sitenav');
 
$tag[] = '<!--include:ppfooter-->';
 $re[] = ftbi('ppfooter');
 
//qui cominciano le sostituzioni per validare l'html di redmine.
 
$tag[] = '<a name=';
 $re[] = '<a id='; 
 
$tag[] = '<br /><br />';
 $re[] = '</p><p>'; //si, lo so che sono due cose diverse. ma non lo sapete usare.
 
$tag[] = '<br />';
 $re[] = ' '; //si che le fa in ordine... :D
 
 
$subs[0]=$tag; $subs[1]=$re;

?>