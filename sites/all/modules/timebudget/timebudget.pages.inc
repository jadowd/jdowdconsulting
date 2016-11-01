<?php
// $Id: timebudget.pages.inc,v 1.1 2008/05/27 02:36:24 jpetso Exp $
/**
 * @file
 * Time Budget - Provides means for sufficiently privileged users
 * to specify their time budget for all of their projects.
 *
 * This file contains the administrative user interface for managing
 * a user's time budget.
 *
 * Copyright 2008 by Jakob Petsovits ("jpetso", http://drupal.org/user/56020)
 */


/**
 * Retrieve the default ratio values for new budget entries for a given user.
 *
 * @param $uid
 *   The Drupal user id of the user whose default budget ratio values
 *   should be retrieved.
 * @param $project_nodes
 *   The list of project nodes between which the ratio will be distributed
 *   amongst.
 *
 * @return
 *   An array of percentage values (between 0.0 and 1.0), with the nid of the
 *   project node as array key. There will be exactly one value for each
 *   given project node, and database entries without a corresponding node
 *   in the @p $project_nodes array will be deleted.
 */
function _timebudget_defaults_for_user($uid, $project_nodes) {
  $result = db_query("SELECT nid, ratio FROM {timebudget_user_defaults}
                      WHERE uid = %d", $uid);
  $ratio = array();
  $total_percentage = 0.0;

  while ($row = db_fetch_object($result)) {
    if (isset($project_nodes[$row->nid])) {
      $ratio[$row->nid] = $row->ratio;
      $total_percentage += $row->ratio;
    }
    else {
      // Stale entry (without corresponding project), delete from the database.
      db_query("DELETE FROM {timebudget_user_defaults}
                WHERE uid = %d AND nid = %d", $uid, $row->nid);
    }
  }

  if (empty($project_nodes)) {
    return array();
  }

  // Fill in values for nodes that are not (yet) in the database.
  foreach ($project_nodes as $nid => $node) {
    if (!isset($ratio[$nid])) {
      $ratio[$nid] = 0.0;
    }
  }

  // Now that we've got the entries, let's make sure they add up to 100%.
  if ($total_percentage < 0.9999 || $total_percentage > 1.0001) {
    foreach ($ratio as $nid => $percentage) {
      if ($total_percentage > 0.0001) {
        $ratio[$nid] = $percentage / $total_percentage;
      }
      else {
        $ratio[$nid] = 1.0 / count($ratio);
      }
    }
  }

  return $ratio;
}

/**
 * Page callback for "timebudget". Take that, sucker!
 */
function timebudget_redirect() {
  global $user;
  drupal_goto('timebudget/'. $user->uid);
}

/**
 * Form callback for "timebudget/%uid[/edit]":
 * The form where the user can configure the time budget for the upcoming
 * days, weeks or months.
 */
function timebudget_form($form_state, $uid = NULL) {
  if (!isset($uid)) {
    global $user;
    $uid = $user->uid;
  }
  $form = array();
  $form['uid'] = array(
    '#type' => 'value',
    '#value' => $uid,
  );

  // Retrieve the time tracked projects and current time budgets for this user.
  $project_nodes = timebudget_get_projects($uid);
  $user_budget = timebudget_by_project($uid);

  if (empty($project_nodes)) {
    $form['no_projects'] = array(
      '#type' => 'markup',
      '#value' => t('There are no projects available to be scheduled.'),
    );
    return $form;
  }
  $budgeted_units = array();

  foreach ($user_budget as $nid => $unit_hours) {
    // Ignore budget entries for (stale?) projects that are not tracked.
    if (!isset($project_nodes[$nid])) {
      unset($user_budget[$nid]);
      continue;
    }
    // Stick all timestamps that we got into a single array.
    $budgeted_units = array_unique(array_merge($budgeted_units, array_keys($unit_hours)));
  }
  sort($budgeted_units); // put the timestamps in chrologic order

  // Fill up missing weeks in the budget with empty entries.
  $date = timebudget_current_unit();
  $timestamp = date_format_date($date, 'custom', 'U');
  $units = array();

  while (!empty($budgeted_units)) {
    $units[$timestamp] = clone($date);

    // Remove all units from $budgeted_units up to (and including) $timestamp.
    while (!empty($budgeted_units) && reset($budgeted_units) <= $timestamp) {
      array_shift($budgeted_units); // remove first element
    }
    timebudget_increment_unit($date);
    $timestamp = date_format_date($date, 'custom', 'U');
  }

  // The first unit that is not budgeted will be shown with an "Add" button.
  $next_date = clone($date);
  $next_timestamp = $timestamp;

  $form['#timebudget_project_nodes'] = $project_nodes;
  $form['#timebudget_timestamps'] = array_keys($units);
  $form['#timebudget_timestamps'][] = $next_timestamp;

  // Render a time budget table, with projects as columns and units as rows.
  foreach ($units as $timestamp => $date) {
    $unit_string = timebudget_format_unit($date);
    $total_hours = 0.0;

    $form['timebudget_unit_'. $timestamp] = array(
      '#type' => 'markup',
      '#value' => $unit_string,
    );
    foreach ($project_nodes as $nid => $node) {
      $hours = isset($user_budget[$nid][$timestamp])
        ? $user_budget[$nid][$timestamp]
        : 0;
      $form['timebudget_value_'. $nid .'_'. $timestamp] = array(
        '#type' => 'duration_combo',
        '#largest_metric' => 'hours',
        '#smallest_metric' => 'hours',
        '#default_value' => duration_create('PT'. $hours .'H'),
      );
      $total_hours += $hours;
    }
    $form['timebudget_total_'. $timestamp] = array(
      '#type' => 'markup',
      '#value' => t('!thismany hours', array('!thismany' => $total_hours)),
    );
  }

  $form['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save time budget'),
    '#weight' => 100,
    '#validate' => array('timebudget_form_save_validate'),
    '#submit' => array('timebudget_form_save'),
  );

  $unit_string = timebudget_format_unit($next_date);

  $form['timebudget_unit_'. $next_timestamp] = array(
    '#type' => 'markup',
    '#value' => $unit_string,
  );
  $form['timebudget_total_'. $next_timestamp] = array(
    '#type' => 'duration_combo',
    '#largest_metric' => 'hours',
    '#smallest_metric' => 'hours',
  );
  $form['timebudget_actions_'. $next_timestamp]['add'] = array(
    '#type' => 'submit',
    '#value' => t('Add new entry'),
    '#validate' => array('timebudget_form_save_validate', 'timebudget_form_add_validate'),
    '#submit' => array('timebudget_form_save', 'timebudget_form_add'),
  );

  return $form;
}

/**
 * "Save time budget" validation handler for the time budget edit form.
 */
function timebudget_form_save_validate($form, &$form_state) {
  foreach ($form_state['values'] as $key => $value) {
    if (strpos($key, 'timebudget_value_') === 0 /* found at the beginning of the key */) {
      if (!is_numeric($value->get_hours()) || $value->get_hours() < 0.0) {
        form_set_error($key, t('Each given time entry has to be a number greater than or equal to 0.'));
      }
    }
  }
}

/**
 * "Save time budget" submit handler for the time budget edit form.
 */
function timebudget_form_save($form, &$form_state) {
  $budget = array();
  $uid = $form_state['values']['uid'];

  foreach ($form_state['values'] as $key => $value) {
    if (strpos($key, 'timebudget_value_') === 0 /* found at the beginning of the key */) {
      $id = substr($key, strlen('timebudget_value_')); // get rid of the prefix
      $id = explode('_', $id);
      if (count($id) != 2) {
        continue; // safety check
      }

      $nid = (int) array_shift($id);
      $timestamp = array_shift($id);

      $budget[$timestamp][$nid] = $value->get_hours();
    }
  }
  // No need to keep old entries, so let's just wipe out all of them
  // in favor of the new ones.
  db_query("DELETE FROM {timebudget_user} WHERE uid = %d", $uid);

  $granularity = variable_get('timebudget_granularity', 0.5);

  foreach ($budget as $unit => $hours_per_project) {
    $hours_per_project = _timebudget_round($hours_per_project, $granularity);
    foreach ($hours_per_project as $nid => $hours) {
      db_query("INSERT INTO {timebudget_user} (uid, nid, unit, hours)
                VALUES (%d, %d, %d, %f)", $uid, $nid, $unit, $hours);
    }
  }

  // Let the world know that the user changed her time budget.
  module_invoke_all('timebudget_updated', $uid);
}

/**
 * "Add budget entry" validation handler for the time budget edit form.
 */
function timebudget_form_add_validate($form, &$form_state) {
  foreach ($form_state['values'] as $key => $value) {
    if (strpos($key, 'timebudget_total_') === 0 /* found at the beginning of the key */) {
      if (!is_numeric($value->get_hours()) || $value->get_hours() < 0.0) {
        form_set_error($key, t('The total time for the new week has to be a number greater than or equal to 0.'));
      }
    }
  }
}

/**
 * "Add budget entry" submit handler for the time budget edit form.
 */
function timebudget_form_add($form, &$form_state) {
  $uid = $form_state['values']['uid'];

  foreach ($form_state['values'] as $key => $value) {
    if (strpos($key, 'timebudget_total_') === 0 /* found at the beginning of the key */) {
      $unit = (int) substr($key, strlen('timebudget_total_')); // get rid of the prefix
      $total_hours = $value->get_hours();
      break; // there's only one "total" in there that is an input value
    }
  }
  // Make sure the new entry hasn't been added in the meantime.
  db_query("DELETE FROM {timebudget_user}
            WHERE uid = %d AND unit = %d", $uid, $unit);

  $project_nodes = $form['#timebudget_project_nodes'];

  if (empty($project_nodes)) {
    return; // safety check, should not happen
  }
  $ratio = _timebudget_defaults_for_user($uid, $project_nodes);
  $hours_per_project = array();

  foreach ($project_nodes as $nid => $node) {
    $hours_per_project[$nid] = $total_hours * $ratio[$nid];
  }
  $granularity = variable_get('timebudget_granularity', 0.5);
  $hours_per_project = _timebudget_round($hours_per_project, $granularity);

  foreach ($hours_per_project as $nid => $hours) {
    db_query("INSERT INTO {timebudget_user} (uid, nid, unit, hours)
              VALUES (%d, %d, %d, %f)", $uid, $nid, $unit, $hours);
  }

  // Let the world know that the user changed his time budget.
  module_invoke_all('timebudget_updated', $uid);
}

/**
 * Theme the time budget form into a table.
 *
 * @ingroup themeable
 */
function theme_timebudget_form($form) {
  $tables = '';

  $project_nodes = $form['#timebudget_project_nodes'];
  if (!isset($project_nodes)) {
    return drupal_render($form);
  }
  $header = array(t('Date'), t('Total'));
  $rows = array();

  foreach ($project_nodes as $nid => $node) {
    $header[] = check_plain($node->title);
  }
  $i = 0;

  foreach ($form['#timebudget_timestamps'] as $timestamp) {
    $columns = array();
    $columns[] = drupal_render($form['timebudget_unit_'. $timestamp]);
    $columns[] = drupal_render($form['timebudget_total_'. $timestamp]);
    foreach ($project_nodes as $nid => $node) {
      if (isset($form['timebudget_value_'. $nid .'_'. $timestamp])) {
        $columns[] = drupal_render($form['timebudget_value_'. $nid .'_'. $timestamp]);
      }
    }
    if (isset($form['timebudget_actions_'. $timestamp])) {
      $columns[] = array(
        'data' => drupal_render($form['timebudget_actions_'. $timestamp]),
        'colspan' => count($project_nodes),
      );
    }
    $rows[] = $columns;
  }

  if (!empty($rows)) {
    $tables .= theme('table', $header, $rows, array('id' => 'timebudget'));
  }
  return $tables . drupal_render($form);
}


/**
 * Form callback for "timebudget/%/defaults":
 * The form where the user can configure the time budget for the upcoming
 * days, weeks or months.
 */
function timebudget_defaults_form($form_state, $uid = NULL) {
  if (!isset($uid)) {
    global $user;
    $uid = $user->uid;
  }
  $form = array();
  $form['uid'] = array(
    '#type' => 'value',
    '#value' => $uid,
  );

  // Retrieve the time tracked projects and default time ratio for this user.
  $project_nodes = timebudget_get_projects($uid);
  if (empty($project_nodes)) {
    $form['no_projects'] = array(
      '#type' => 'markup',
      '#value' => t('There are no projects available to be scheduled.'),
    );
    return $form;
  }

  $ratio = _timebudget_defaults_for_user($uid, $project_nodes);
  $ratio = _timebudget_round($ratio, 0.001);

  foreach ($project_nodes as $nid => $node) {
    $form['timebudget_ratio_'. $nid] = array(
      '#type' => 'textfield',
      '#title' => $node->title,
      '#description' => t('Percentage of time that will be assigned to @node for newly scheduled weeks.', array('@node' => $node->title)),
      '#default_value' => ($ratio[$nid] * 100) .'%',
      '#size' => 5,
    );
  }

  $form['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save defaults'),
    '#weight' => 100,
    '#validate' => array('timebudget_default_form_save_validate'),
    '#submit' => array('timebudget_default_form_save'),
  );
  return $form;
}

/**
 * "Save defaults" validation handler for the time budget defaults form.
 */
function timebudget_default_form_save_validate($form, &$form_state) {
  foreach ($form_state['values'] as $key => $value) {
    if (strpos($key, 'timebudget_ratio_') === 0 /* found at the beginning of the key */) {
      $percentage = strtr($value, array('%' => ''));
      if (!is_numeric($percentage) || $percentage < -0.001 || $percentage > 100.001) {
        form_set_error($key, t('Each project must be assigned a value between 0% and 100%.'));
      }
    }
  }
}

/**
 * "Save defaults" submit handler for the time budget defaults form.
 */
function timebudget_default_form_save($form, &$form_state) {
  $uid = $form_state['values']['uid'];
  $ratio_per_project = array();

  foreach ($form_state['values'] as $key => $value) {
    if (strpos($key, 'timebudget_ratio_') === 0 /* found at the beginning of the key */) {
      $nid = (int) substr($key, strlen('timebudget_ratio_')); // get rid of the prefix
      $percentage = strtr($value, array('%' => ''));
      $ratio_per_project[$nid] = $percentage / 100.0;
    }
  }
  // Make sure we can insert a new set of defaults.
  db_query("DELETE FROM {timebudget_user_defaults} WHERE uid = %d", $uid);

  foreach ($ratio_per_project as $nid => $ratio) {
    db_query("INSERT INTO {timebudget_user_defaults} (uid, nid, ratio)
              VALUES (%d, %d, %f)", $uid, $nid, $ratio);
  }
}
