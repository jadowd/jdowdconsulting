README.txt
==========
A module that displays a personalized XML feed from weather.com, weatherfacti.
Inspired by Will Ballantyne - facti.net, this module features automatic IP to city
detection.  From the first visit the web viewer will see a location specific
weather report. Later on, the user will be able to personalize the number of
days to display a weather report, the specific location via postal code, and
a personalized city name.
The report is seen through a block. Make certain to enable that block and place
it in your prefered place. 
Please note that your server should be hardend since the caching feature creates
a 0777 directory in the module's directory
Also if your host doesn't support file_get_contents or cURL, you will have
issues with this module. Please contact me jfrancis@c-sgroup.com with your
hosting company and I'll see if I can resolve this issue for you.

Other features:
	IP caching, XML feed caching, HTML caching. 
This module new, be careful when deploying to a production site.
Make certain to enable the profile's module for maximum customazability

AUTHOR/MAINTAINER
======================
-Jonathan T. Francis
jon@jonfrancis.com
Inspired from: Will Ballantyne - facti.net
