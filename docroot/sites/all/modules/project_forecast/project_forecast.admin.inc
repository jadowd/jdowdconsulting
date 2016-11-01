<?php
// $Id: project_forecast.admin.inc,v 1.1 2008/05/27 02:41:04 jpetso Exp $
/**
 * @file
 * Project Forecast - Estimated completion dates for tasks and milestones.
 *
 * This file contains the administrative user interface.
 *
 * Copyright 2007 by Jakob Petsovits ("jpetso", http://drupal.org/user/56020)
 */

/**
 * Form callback for "admin/project/forecast[/relationships]":
 * Let the admin select views of issues (plus arguments)
 * and other node relationships.
 */
function project_forecast_admin() {
  $views = array();
  $all_views = views_get_all_views();
  foreach ($all_views as $view) {
    $views[$view->name] = $view->name;
  }

  $timeneed_field_info = _project_forecast_timeneed_field_info();
  $timeneed_fields = array();
  $nodereference_fields = array();

  foreach (content_fields() as $field) {
    if ($field['type'] == 'nodereference') {
      $nodereference_fields[$field['field_name']] = _project_forecast_field_option($field);
    }
    if (array_key_exists($field['type'], $timeneed_field_info)) {
      $timeneed_fields[$field['field_name']] = _project_forecast_field_option($field);
    }
  }

  $form = array();

  // Required settings
  $form['required'] = array(
    '#type' => 'fieldset',
    '#title' => t('Required task settings'),
    '#description' => t('Settings related to tasks (...issues, cases, bugs) that are required to make the forecast work at all.'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['required']['project_forecast_timeneed_field'] = array(
    '#type' => 'select',
    '#title' => t('CCK field for the task\'s time need'),
    '#options' => $timeneed_fields,
    '#required' => TRUE,
    '#default_value' => variable_get('project_forecast_timeneed_field', ''),
    '#description' => t('The project forecast needs to know how many time is still required for a given task, and this information is extracted from a Duration type CCK field that you need to specify here. Tasks without this field are assumed to have a zero-length time need.'),
  );
  $form['required']['project_forecast_project_field'] = array(
    '#type' => 'select',
    '#title' => t('CCK nodereference for the project node that contains the task'),
    '#options' => $nodereference_fields,
    '#required' => TRUE,
    '#default_value' => variable_get('project_forecast_timeneed_field', ''),
    '#description' => t('The project forecast needs to know how many time is still required for a given task, and this information is extracted from a Duration type CCK field that you need to specify here. Tasks without this field are assumed to have a zero-length time need.'),
  );
  $form['required']['project_forecast_open_tasks_view'] = array(
    '#type' => 'select',
    '#title' => t('View of a user\'s open tasks'),
    '#options' => array_merge(array('' => t('please select a view')), $views),
    '#required' => TRUE,
    '#default_value' => variable_get('project_forecast_open_tasks_view', ''),
    '#description' => t('Choose the "Views module" view that selects the open tasks of each user. The view\'s "sort criteria" section will be used to determine in which order the user is expected to complete these tasks.'),
  );
  $form['required']['project_forecast_open_tasks_view_args'] = array(
    '#type' => 'textfield',
    '#title' => t('View arguments'),
    '#default_value' => variable_get('project_forecast_open_tasks_view_args', '%uid'),
    '#required' => TRUE,
    '#description' => t('Provide a comma separated list of arguments to pass to the "open tasks" view. Use the %uid placeholder for the argument of the user whose cases are being queried.'),
  );
  $form['required']['project_forecast_users_view'] = array(
    '#type' => 'select',
    '#title' => t('View of users with assigned tasks'),
    '#options' => array_merge(array('' => t('please select a view')), $views),
    '#required' => TRUE,
    '#default_value' => variable_get('project_forecast_users_view', ''),
    '#description' => t('Choose the "Views module" view of users that have tasks assigned. Required for the initial calculation of all estimated completion dates.'),
  );
  $form['required']['project_forecast_users_view_args'] = array(
    '#type' => 'textfield',
    '#title' => t('View arguments'),
    '#default_value' => variable_get('project_forecast_users_view_args', ''),
    '#description' => t('Provide a comma separated list of arguments to pass to the "users with assigned tasks" view.'),
  );

  // Optional settings.
  $form['optional'] = array(
    '#type' => 'fieldset',
    '#title' => t('Optional task settings'),
    '#description' => t('Settings related to tasks (...issues, cases, bugs) that are not required to make the forecast work, but provide additional features that you might want to use as well.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['optional']['project_forecast_milestone_field'] = array(
    '#type' => 'select',
    '#title' => t('CCK nodereference for a task\'s milestone'),
    '#options' => array_merge(array('' => t('no milestones')), $nodereference_fields),
    '#required' => FALSE,
    '#default_value' => variable_get('project_forecast_milestone_field', ''),
    '#description' => t('If you use milestones in your project management solution, you can specify the field that holds a reference to the milestone node. '),
  );
  $form['optional']['project_forecast_continuous_tasks_view'] = array(
    '#type' => 'select',
    '#title' => t('View of a user\'s continuous tasks'),
    '#options' => array_merge(array('' => t('no continuous tasks')), $views),
    '#required' => FALSE,
    '#default_value' => variable_get('project_forecast_continuous_tasks_view', ''),
    '#description' => t('You can specify a list of tasks that the user is expected to allocate for every week (like meetings or recurring administrative tasks. The time need of these issues will be subtracted from each week\'s allocated time budget, and for obvious reasons they should not include tasks from the "open tasks" view as the time need would incorrectly be used twice. Choose the "Views module" view that selects the continuous tasks of each user, or leave the selection at "no continuous tasks" to ignore this feature.'),
  );
  $form['optional']['project_forecast_continuous_tasks_view_args'] = array(
    '#type' => 'textfield',
    '#title' => t('View arguments'),
    '#default_value' => variable_get('project_forecast_continuous_tasks_view_args', '%uid'),
    '#required' => FALSE,
    '#description' => t('Provide a comma separated list of arguments to pass to the view. Use the %uid placeholder for the argument of the user whose cases are being queried.'),
  );

  $form = system_settings_form($form);
  $form['#submit'][] = 'project_forecast_admin_submit';

  return $form;
}

function project_forecast_admin_submit($form, &$form_state) {
  project_forecast_recalculate_all();
}

/**
 * Form callback for "admin/project/forecast":
 * Provide an admin settings form.
 */
function project_forecast_admin_settings() {
  $form = array();
  $form['#id'] = 'project-forecast-settings-form';

  $case_statuses = casetracker_case_state_load('status');
  $case_types = casetracker_case_state_load('type');
  $completion_ratios = project_forecast_completion_ratios();

  $form['continuous_cases'] = array(
    '#type' => 'checkboxes',
    '#title' => 'Continuous cases',
    '#description' => 'Specify which case types should be considered continuous, that is, the assumption will be that the case\'s time need is required every week.',
    '#default_value' => project_forecast_continuous_case_types(),
    '#options' => $case_types,
    '#weight' => -1,
  );

  foreach ($case_statuses as $csid => $status_name) {
    $form['completion_ratio_'. $csid] = array(
      '#type' => 'textfield',
      '#title' => t('Completion ratio for %status', array('%status' => $status_name)),
      '#default_value' => $completion_ratios[$csid],
      '#description' => t('The estimated percentage of how far the case has already progressed at this state, given as a number from 0.0 (not yet done) to 1.0 (completed).'),
    );
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
    '#weight' => 100,
  );

  return $form;
}

/**
 * Validation handler for the settings form.
 */
function project_forecast_admin_settings_validate($form_id, $form_values) {
  foreach ($form_values as $key => $value) {
    if (strpos($key, 'completion_ratio') !== FALSE) {
      if (!is_numeric($value) || $value < 0.0 || $value > 1.0) {
        form_set_error($key, t('The completion ratio has to be given as a number from 0.0 to 1.0.'));
      }
    }
  }
}

/**
 * Submit handler for the settings form.
 */
function project_forecast_admin_settings_submit($form_id, $form_values) {
  $continuous_case_types = array_values(array_filter($form_values['continuous_cases']));
  variable_set('project_forecast_continuous_case_types', $continuous_case_types);

  $completion_ratios = array();
  foreach ($form_values as $key => $value) {
    if (strpos($key, 'completion_ratio') !== FALSE) {
      $csid = (int) substr($key, strlen('completion_ratio_'));
      $completion_ratios[$csid] = $value;
    }
  }
  variable_set('project_forecast_completion_ratios', $completion_ratios);
}

/**
 * Return a user visible string representation of the given field.
 */
function _project_forecast_field_option($field) {
  $label = $field['widget']['label'];
  if (empty($label)) {
    return $field['field_name'];
  }
  else {
    return t('@label (@fieldname)', array(
      '@label' => $label, '@fieldname' => $field['field_name']
    ));
  }
}
