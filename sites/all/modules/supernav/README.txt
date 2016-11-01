/* $Id: README.txt,v 1.1.2.3 2008/03/09 20:58:48 stompeers Exp $ */

Super Nav module:
-------------------------
Author - Chris Shattuck (www.impliedbydesign.com)
License - GPL


Overview:
-------------------------
The Super Nav module is a powerful navigation and productivity tool. The 
central concepts behind it is to 1) isolate administrative tools to an area 
outside of the theme so that themers don't have to worry about accomodating for administrative menus and tools, and 2) To make navigation as intuitive as possible 
for administrators as well as content managers, so they can easily find what they 
need.


Demo:
-------------------------
http://www.impliedbydesign.com/superior-performance-navigation-drupal-super-nav.html


Problems?
-------------------------
- Does your page.tpl.php have the <?php echo $closure;?> in it? If not, you'll need
add it in since that's where Super Nav slips in some of its features.
- If you're having any other problems, please post an issue in the Super Nav
issue queue.


Somewhat Hidden Features:
-------------------------
Most Super Nav features are very visible. However, there are a couple items that
are a bit under the surface:

KEYBOARD SHORTCUTS
There are a few keyboard shortcuts you can use when Super Nav is enabled:
SPACE+S: Moves the focus to the search bar so you can start a search
TAB (while in the search bar): Automatically loads the first search result
B+(1-9): Loads the corresponding bookmark, up to 9

ADVANCED SEARCH
By default, the Super Nav search bar will search the navigation and nodes.
To make your search more specific, click the magnifying glass option.

CSS, JS AND IMAGE FILE REFRESHING
Super Nav uses frames, and one problem with testing pages in frames is that .css, .js  and image files get cached, so when you refresh a frameset, they don't get refreshed. To get around this, you can add a couple of lines to your page.tpl.php
file and your Super Nav users will have the option to refresh the CSS and JS and image files each time there is a page load or reload. Super Nav accomplishes this by 
adding a timestamp query string to the end of each file name, which will fool
the browser into thinking it's a new file. To enable it, add the following two
functions to the top and bottom of your theme's page.tpl.php file:

<?php supernav_refresh_top(); ?>
Content of php.tpl.php
<?php supernav_refresh_bottom(); ?>

HIDE LOCAL TASKS
One feature that will help isolate administrative tasks is
the 'Remove local tasks in content' feature. This will take the local navigation
out of the content frame, leaving it as a non-administrative user might see
it.

TOOL HOOK
Modules can add their own features to Super Nav by using hook_snavtool().
These hooks should return a single array with two items, 'content' (required) and 
'js' (optional). The content will be added to an area visible when the tool
icon is clicked (the tool icon is hidden if there are no hooks), and the js will
be added to the page.


Installation:
-------------------------
- Download the Super Nav module and copy it into your 'modules'
directory. 
- Go to Administer >> Modules and enable the module
- You should now see the Super Nav. To turn of forcing Super Nav display,
visit the supernav options in admin/settings/supernav. Here you can also brand
your installation.


Last updated:
------------
; $Id: README.txt,v 1.1.2.3 2008/03/09 20:58:48 stompeers Exp $