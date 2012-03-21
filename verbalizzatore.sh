for i in ./html/*.html
do
j=`echo $i | sed 's/\.html$/.txt/g' | sed 's/html\///g'`
#lynx -dump $i > ./vdump/$j
php extrar.php $i | lynx -dump -stdin > ./html/vdump/$j
done
