<?php
/**
 * betterawstats - an alternative display for awstats data
 *
 * @author      Oliver Spiesshofer, support at betterawstats dot com
 * @copyright   2008 Oliver Spiesshofer
 * @version     1.0
 * @link        http://betterawstats.com
 * Based on the GPL AWStats Totals script by:
 * Jeroen de Jong <jeroen@telartis.nl>
 * copyright   2004-2006 Telartis
 * version 1.13 (http://www.telartis.nl/xcms/awstats)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * File contents:
 *
 * This file is the central gateway for all other files. It includes all required
 * files and handles in/output from GET /POST as well as preparation of default values
 */

// var declaration
$BAW_LOGTYPE = 'W'; // fix to weblog only for now, no email / FTP
$BAW_CURR = array(); // holds all global values of the current session
$BAW_CURR['thismonth'] = array('hits'=> 0);
$BAW_CONF['max_percent'] = 'layout_percent';
$BAW_CONF['max_bytes'] = 'layout_bytes';
$BAW_D = array();
$BAW_MES = array();
$BAW_DFILES = array(); // data files
$tmp = null; // Temp variable for all kinds of things

include_once('./config.php');

if ($BAW_CONF['debug']) {
    error_reporting(E_ALL);
    // ini_set('memory_limit','25M');
}

if (!defined ('XHTML')) {
    if ($BAW_CONF['xhtml']) {
        define('XHTML', ' /');
    } else {
        define('XHTML', '');
    }
}
if (!defined ('BR')) {
    define('BR', '<br'.XHTML.'>');
}
include_once('./core/helpers.inc.php');
// define when the script was started
if (!defined ('START_TIME')) {
    define('START_TIME', baw_mtime());
}
if ($BAW_CONF['online_config'] && baw_is_writable($BAW_CONF['site_path'] . '/config.php')) {
    include_once('./core/config.inc.php');
}
// start time calculation and save the first one for reference

include_once('./modules/render_htmlchart.inc.php');
include_once('./modules/render_table.inc.php');
include_once('./modules/render_jpgraph.inc.php');
include_once('./modules/render_map.inc.php');
include_once('./core/data.inc.php');
include_once('./core/language.inc.php');
baw_language(); // this has to be b/f library & b/f baw_match_files()
// find all the data, populate $BAW_DFILES
$BAW_SERVERS = array(); // this will be populates with the servers for the config
// editor. Since we need that in the config_default.inc.php, we need it even if online config is off


include_once('./core/display_helpers.php');
baw_match_files(); // we read all files here so we have the array for config and normal

if (!isset($BAW_MES['e'])) {
    // if the awstats language file does not indicate a
    // encoding, it means its Latin/ISO-8859-1
    $BAW_MES['e'] = 'iso-8859-1';
}

baw_check_config();
include_once('./core/library.inc.php');

include_once('./core/display.inc.php');
include_once('./core/config_default.inc.php');
include_once('./core/extras.inc.php');


// get input from forms
// it is questionable if this needs to be slashed or filtered since we dont write any data
if (isset($_GET['year'])) {
    $BAW_CURR['year'] = $_GET['year'];
} else {
    $BAW_CURR['year'] = date('Y');
}
if (isset($_GET['month'])) {
    $BAW_CURR['month'] = $_GET['month'];
} else {
    $BAW_CURR['month'] = date('m');
}
$BAW_CURR['day'] = date('d');
$BAW_CURR['yearmonth'] = $BAW_CURR['year'] . $BAW_CURR['month'];
if (isset($_GET['site'])) {
    $BAW_CURR['site_name'] = $_GET['site'];
}


// add language-dependent library values
$BAW_LIB['domains']['unknown'] = $BAW_MES[0];
$BAW_LIB['domains']['ip'] = $BAW_MES[0];
$BAW_LIB['os']['list']['Unknown'] = $BAW_MES[0];
$BAW_LIB['browser']['names']['Unknown'] = $BAW_MES[0];
$BAW_LIB['browser']['icons']['Unknown'] = 'unknown';

if (isset($_REQUEST['action'])) { // used by poll and config editor
    $action = 'baw_action_' . $_REQUEST['action'];
    $out = $action();
} else if (@$BAW_CURR['site_name'] == 'all_months') {
    $settings['section'] = "months";
    $out = baw_display_index($settings);
} else if (@$BAW_CURR['site_name'] == 'all_days') {
    $settings['section'] = "days";
    $out = baw_display_index($settings);
} else {
    $out = baw_display_index();
}

echo $out;
?>