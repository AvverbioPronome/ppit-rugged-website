Web: www.partitopirata.org - HTML CODE
======================================

## AVVERTENZA FONDAMENTALE.

i file da modificare sono in /html/editme.

NON modificare MAI i file html in /html, perché appena lanci
./staticizzatore.php, ello ti rollbackerebbe qualsiasi cosa,
sovrascrivendoli con quelli di /html/editme + le volute sostituzioni.


## Come funziona questa roba.

Tu scrivi un html qualsiasi, inserendo dove utile i tag definiti in
staticizzatore.php, e lo salvi in /html/editme. Quando hai finito 
di scrivere, esegui lo script staticizzatore, ed ello ti creerà, in
/html un file con lo stesso nome del tuo, ma con le sostituzioni 
corrette al posto dei comodi tag che hai usato.

## Estendere il sistema.

tutto il sistema è impostato con quei tag simpatici, tanto che basta
scrivere una funzione php che ritorni il contenuto di una pagina per
avere un sito con i vantaggi del dinamico e dello statico
contemporaneamente. ovviamente va pensato un po' come paginare le
proposte e creare le loro pagine una alla volta, chessò, la funzione
stampa pagina assemblea.html ed assemblea_2.html, linkandole
opportunamente, oltre a assemblea_proposta_xxxxx.html

ma, appunto, è un modulo da pensare a parte.

## CHANGELOG

r1 - draft0
puppa
puppa
