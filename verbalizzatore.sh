for i in ./html/verbale_*.html
do
j=`echo $i | sed 's/\.html$/.txt/g' | sed 's/html\///g'`
#lynx -dump $i > ./vdump/$j
php extrar.php $i | lynx -dump -stdin > ./vdump/$j
done
