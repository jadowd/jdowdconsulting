<?php
// $Id: timebudget.admin.inc,v 1.1 2008/05/27 02:36:24 jpetso Exp $
/**
 * @file
 * Time Budget - Provides means for sufficiently privileged users
 * to specify their time budget for all of their projects.
 *
 * This file contains the admin settings forms.
 *
 * Copyright 2008 by Jakob Petsovits ("jpetso", http://drupal.org/user/56020)
 */


/**
 * Form callback for "admin/project/timebudget":
 * Let the admin select a view of project nodes (plus arguments).
 */
function timebudget_admin() {
  $views = array('' => t('please select a view'));
  $all_views = views_get_all_views();
  foreach ($all_views as $view) {
    $views[$view->name] = $view->name;
  }

  $form = array();

  $form['timebudget_projects_view'] = array(
    '#type' => 'select',
    '#title' => t('View for projects'),
    '#options' => $views,
    '#required' => TRUE,
    '#default_value' => variable_get('timebudget_projects_view', ''),
    '#description' => t('Choose the "Views module" view that selects the projects for which time budgets can be specified. Use the view\'s "sort criteria" section to determine the order in which project nodes will be displayed in the time budget management form.'),
  );
  $form['timebudget_projects_view_args'] = array(
    '#type' => 'textfield',
    '#title' => t('View arguments'),
    '#default_value' => variable_get('timebudget_projects_view_args', ''),
    '#required' => FALSE,
    '#description' => t('Provide a comma separated list of arguments to pass to the view. You will probably want to provide the user id as argument (in order to provide each user with the appropriate set of projects), use the %uid placeholder for that purpose.'),
  );

  return system_settings_form($form);
}
