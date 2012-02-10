# -- 
# http://gnuplot-surprising.blogspot.com/2011/09/gnuplot-background-image.html

reset
set term png nocrop enhanced size 477,599 truecolor
set output "piratemap_italy.png"
set multiplot
set xrange [0:476]	
set yrange [0:598]
#As the background picture's size is 800x410,
#we choose xrange and yrange of these values
unset tics
unset border
unset key

set lmargin at screen 0
set rmargin at screen 1
set bmargin at screen 0
set tmargin at screen 1

#Plot the background image
plot "equirectangular_italy_477x599_physical.png" binary filetype=png w rgbimage


#The x and y range of the population data file
set xrange [6.2:19]
set yrange [35.3:47.4]
set border
#set tics out nomirror scale 2
#set mxtics 5

set lmargin at screen 0
set rmargin at screen 1
set bmargin at screen 0
set tmargin at screen 1

unset tics
unset border
unset key

set style fill transparent solid 0.35 noborder

plot "italian_top1000_cities_coordinates_census2001.tsv" using 3:2:(sqrt($4)/3000) with circles;

unset multiplot