<?php
 // $Id: bawstats.config.inc,v 1.2 2008/03/24 21:58:17 anaru Exp $

/**
 * BAWstats drupal module
 *
 * BAWstats is a drupal module written in php for integration of
 * the BetterAWstats presentation and processing of AWstats data into drupal.
 * Copyright (C) 2008  Andrew Gillies (anaru@equivocation.org)
 *
 * For more information about BetterAWstats see:
 * http://betterawstats.com/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or, at
 * your option, any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

$bawstats_path = drupal_get_path('module', 'bawstats');

/**
 * beterawstats globals and libraries
 *
 */

// global vars
global $BAW_LOGTYPE, $BAW_CURR, $BAW_MES, $BAW_LIB, $BAW_CONF_DEF, $BAW_CONF_DIS_DEF, $BAW_D;
global $BAW_CONF, $BAW_CONF_DIS, $BAW_DFILES, $BAW_SERVERS;

$BAW_CONF = array();        // holds all the configuration info

include_once($bawstats_path . '/config.php');
$BAW_CONF['site_path'] = $bawstats_path;
$BAW_CONF['max_percent'] = 'layout_percent';
$BAW_CONF['max_bytes'] = 'layout_bytes';
$BAW_CONF['online_config'] = false;
$BAW_CONF['drupal_base'] = 'admin/reports/bawstats';

// get configuration from drupal variables (not from the config.php) for key locations
$BAW_CONF['path_data'] = variable_get('bawstats_data','');
$BAW_CONF['path_lib'] = variable_get('bawstats_lib','');
$BAW_CONF['path_lang'] = variable_get('bawstats_lang','');
$BAW_CONF['icons_url'] = variable_get('bawstats_icon','/' . $bawstats_path . '/icon');

// drupal map
$BAW_CONF_DIS['domains']['map'] = true;

$BAW_LOGTYPE = 'W';
$BAW_D = array();
$BAW_CURR = array();
$BAW_CURR['thismonth'] = array('hits'=> 0);
$BAW_MES = array();
$BAW_DFILES = array(); // data files
$BAW_SERVERS = array(); // this will be populates with the servers for the config

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

// check we are configured...
if ((strlen($BAW_CONF['path_data'])>0)&&(strlen($BAW_CONF['path_lib'])>0)&&(strlen($BAW_CONF['path_lang'])>0)) {
  include_once($bawstats_path . '/core/helpers.inc.php');
  include_once($bawstats_path . '/modules/render_htmlchart.inc.php');
  include_once($bawstats_path . '/modules/render_table.inc.php');
  include_once($bawstats_path . '/modules/render_jpgraph.inc.php');
  include_once($bawstats_path . '/modules/render_map.inc.php');
  include_once($bawstats_path . '/core/data.inc.php');
  include_once($bawstats_path . '/core/language.inc.php');
  baw_language();
  include_once($bawstats_path . '/core/display_helpers.php');

  if (!isset($BAW_MES['e'])) {
    $BAW_MES['e'] = 'utf-8';
  }

  //baw_check_config();
  include_once($bawstats_path . '/core/library.inc.php');

  include_once($bawstats_path . '/core/display.inc.php');
  include_once($bawstats_path . '/core/extras.inc.php');

  // add language-dependent library values
  $BAW_LIB['domains']['unknown'] = $BAW_MES[0];
  $BAW_LIB['domains']['ip'] = $BAW_MES[0];
  $BAW_LIB['os']['list']['Unknown'] = $BAW_MES[0];
  $BAW_LIB['browser']['names']['Unknown'] = $BAW_MES[0];
  $BAW_LIB['browser']['icons']['Unknown'] = 'unknown';
  include_once($bawstats_path . '/core/config_default.inc.php');
}

$BAW_CONF['site_url'] = '/admin/bawstats';

// ensure the baw backend knows we are drupal
$BAW_CONF['module'] = 'drupal';

unset($bawstats_path);
