<?php
 // $Id: bawstats.admin.inc,v 1.2 2008/03/24 21:58:17 anaru Exp $

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

include(drupal_get_path('module', 'bawstats') . '/includes/bawstats.config.inc');

/**
 * Menu callback; presents the bawstats admin settings page.
 */

function bawstats_admin_settings() {
  global $BAW_CONF,$BAW_LIB;
  $form = array();

  // build the form for the settings

  $form['bawstats_config'] = array(
    '#type' => 'fieldset',
    '#title' => t('AWstats configuration'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#weight' => -1
  );
  $form['bawstats_config']['bawstats_data'] = array(
    '#type' => 'textfield',
    '#size' => 40,
    '#maxlength' => 80,
    '#title' => t('AWstats data directory'),
    '#default_value' => variable_get('bawstats_data',''),
    '#description' => t('Set this value to the directory where AWStats saves its database files into. No trailing slash.'),
    '#weight' => -3
  );
  $form['bawstats_config']['bawstats_lib'] = array(
    '#type' => 'textfield',
    '#title' => t('AWstats library directory'),
    '#size' => 40,
    '#maxlength' => 80,
    '#default_value' => variable_get('bawstats_lib',''),
    '#description' => t('Set this value to the directory where the AWStats library files are located (the "lib" directory under the AWstats tree). No trailing slash.'),
    '#weight' => -2
  );
  $form['bawstats_config']['bawstats_lang'] = array(
    '#type' => 'textfield',
    '#title' => t('AWstats language directory'),
    '#size' => 40,
    '#maxlength' => 80,
    '#default_value' => variable_get('bawstats_lang',''),
    '#description' => t('Set this value to the directory where the AWStats language files are located (the "lang" directory under the AWstats tree). No trailing slash.'),
    '#weight' => -1
  );
  $bawstats_path = drupal_get_path('module', 'bawstats');
  $form['bawstats_config']['bawstats_icon'] = array(
    '#type' => 'textfield',
    '#title' => t('AWstats icon directory'),
    '#size' => 40,
    '#maxlength' => 80,
    '#default_value' => variable_get('bawstats_icon','/' . $bawstats_path . '/icon'),
    '#description' => t('Set this value to the URL where AWStats icons can be obtained. Note, as this is used for icon URLs, this must be in web file space.  The default is to copy the icons to a directory called "icon" in the toplevel of the BAWstats module directory') . ' (' . $bawstats_path . '/icon).',
    '#weight' => 0
  );
  $form['bawstats_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('BAWstats general settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#weight' => 0
  );
  $form['bawstats_settings']['bawstats_page_view'] = array(
    '#type' => 'checkbox',
    '#title' => t('Split page view'),
    '#default_value' => variable_get('bawstats_page_view',0),
    '#description' => t('By default statistics for a single month are displayed on a single large page. Selecting this option splits the page into multiple sub-views.'),
    '#weight' => 1
  );
  // turn off display controle if we are not yet configured
  //if ($configured==0) $form['bawstats_settings']['bawstats_page_view']['#disabled']=TRUE;

  $form['buttons']['submit'] = array('#type' => 'submit', '#value' => t('Save configuration') );
  $form['buttons']['reset'] = array('#type' => 'submit', '#value' => t('Reset to defaults') );

  if (!empty($_POST) && form_get_errors()) {
    drupal_set_message(t('The settings have not been saved because of the errors.'), 'error');
  }

  $form['#submit'][] = 'bawstats_admin_settings_submit';
  $form['#theme'] = 'system_settings_form';

  return $form;
}

function _bawstats_remove_slash($path) {
  if (substr($path,strlen($path)-1,1)=='/') return substr($path,0,strlen($path)-1);
  return $path;
}

function bawstats_admin_settings_validate($form, &$form_state) {
  global $BAW_CONF,$BAW_LIB,$BAW_CONF_DIS_DEF;
  $op = isset($form_state['values']['op']) ? $form_state['values']['op'] : '';

  if ($op!=t('Reset to defaults')) {
    // check the key paths

    $settingOK = TRUE;
    $data_dir = _bawstats_remove_slash($form_state['values']['bawstats_data']);

    if (is_dir($data_dir)) {
      if (!is_readable($data_dir)) {
	form_set_error('bawstats_data', t('AWstats data directory "') . $data_dir . t('" is not accessible.'));
	$settingOK = FALSE;
      }
    } else {
      form_set_error('bawstats_data', t('AWstats data location "') . $data_dir . t('" is not a directory.'));
      $settingOK = FALSE;
    }

    if (!$settingOK) variable_del('bawstats_data');  // this is evil doing this in validate, but ensures clean fall-over

    $settingOK = TRUE;
    $lib_dir = _bawstats_remove_slash($form_state['values']['bawstats_lib']);

    if (is_dir($lib_dir)) {
      if (is_readable($lib_dir)) {
	// directory is there and readable, so check it contains the AWstats lib stuff
	if (!is_readable($lib_dir.'/domains.pm')) {
	  form_set_error('bawstats_lib', t('AWstats library directory "') . $lib_dir . t('" does not contain the necessary library files?'));
	  $settingOK = FALSE;
	}
      } else {
	form_set_error('bawstats_lib', t('AWstats library directory "') . $lib_dir . t('" is not accessible.'));
	$settingOK = FALSE;
      }
    } else {
      form_set_error('bawstats_lib', t('AWstats library location "') . $lib_dir . t('" is not a directory.'));
      $settingOK = FALSE;
    }

    if (!$settingOK) variable_del('bawstats_lib');  // this is evil doing this in validate, but ensures clean fall-over

    $settingOK = TRUE;
    $lang_dir = _bawstats_remove_slash($form_state['values']['bawstats_lang']);

    if (is_dir($lang_dir)) {
      if (is_readable($lang_dir)) {
	// directory is there and readable, so check it contains the AWstats lib stuff
	if (!is_readable($lang_dir.'/awstats-en.txt')) {
	  form_set_error('bawstats_lang', t('AWstats library directory "') . $lang_dir . t('" does not contain the necessary language files?'));
	  $settingOK = FALSE;
	}
      } else {
	form_set_error('bawstats_lang', t('AWstats language directory "') . $lang_dir . t('" is not accessible.'));
	$settingOK = false;
      }
    } else {
      form_set_error('bawstats_lang', t('AWstats language location "') . $lang_dir . t('" is not a directory.'));
      $settingOK = FALSE;
    }

    if (!$settingOK) variable_del('bawstats_lang');  // this is evil doing this in validate, but ensures clean fall-over
  }
}


function bawstats_admin_settings_submit($form, &$form_state) {
  global $BAW_CONF,$BAW_LIB,$BAW_CONF_DIS_DEF;
  $op = isset($form_state['values']['op']) ? $form_state['values']['op'] : '';

  // Exclude unnecessary elements.
  unset($form_state['values']['submit'], $form_state['values']['reset'], $form_state['values']['form_id'], $form_state['values']['op'], $form_state['values']['form_token']);

  foreach ($form_state['values'] as $key => $value) {
    if ($op == t('Reset to defaults')) {
      variable_del($key);
    }
    else {
      if (is_array($value) && isset($form_state['values']['array_filter'])) {
        $value = array_keys(array_filter($value));
      }
      // Test the key is correct:

      if ($key == 'bawstats_data') {
	// assume validation has taken care of testing for directory/readable
        $value = _bawstats_remove_slash($value);
	// do some further checking
	$count = count(glob($value . "/*.txt"));
	if ($count==0) drupal_set_message(t('Warning, the AWstats data directory "') . $value . t('" does not contain any text data files'),'warning');
      }

      if ($key == 'bawstats_lib') {
	// assume validation has taken care of testing for directory/readable
	$value = _bawstats_remove_slash($value);
      }

      if ($key == 'bawstats_lang') {
	// assume validation has taken care of testing for directory/readable
	$value = _bawstats_remove_slash($value);
      }

      if ($key == 'bawstats_icon') {
	// assume validation has taken care of testing for directory/readable
        $value = _bawstats_remove_slash($value);
	$testvalue = substr($value,1);
	// do some further checking
	if (is_dir($testvalue)) {
	  if (is_readable($testvalue)) {
	    // directory is there and readable, so check it contains the AWstats lib stuff
	    if (!is_readable($testvalue.'/browser')) {
	      drupal_set_message(t('Warning, the AWstats icon path "') . $value . t('" does not appear to contain icon directories.'),'warning');
	    }
	  } else {
	    drupal_set_message(t('Warning, the AWstats icon path "') . $value . t('" is not accessible.'),'warning');
	  }
	} else {
	  drupal_set_message(t('Warning, the AWstats icon path "') . $value . t('" is not a directory.'),'warning');
	}
      }

      // set the actual variable
      variable_set($key, $value);
    }
  }

  if ($op == t('Reset to defaults')) {
    drupal_set_message(t('The configuration options have been reset to their default values.'));
  }
  else {
    drupal_set_message(t('The configuration options have been saved.'));
  }

  // can only get this if configured
  if ((strlen($BAW_CONF['path_data'])>0)&&(strlen($BAW_CONF['path_lib'])>0)&&(strlen($BAW_CONF['path_lang'])>0)) {
    // create the stat groups var in case they are wanted post configuration!
    $statgroups = array();
    foreach ($BAW_LIB['item_groups'] as $group => $group_details) {
      $statgroups[check_plain($group)] = check_plain($group_details['title']);
    }
    variable_set('bawstats_groups',$statgroups);

    // similarly with the stat sections
    $statsections = array();
    foreach ($BAW_CONF_DIS_DEF as $key => $value) {
      $statgroups[] = $key;
    }
    variable_set('bawstats_sections',$statsections);
  }

  menu_rebuild();
}
