<?php
// $Id: project_forecast.views.inc,v 1.1 2008/05/27 02:41:04 jpetso Exp $
/**
 * @file
 * Project Forecast - Estimated completion dates for tasks and milestones.
 *
 * This file contains Views integration functionality.
 *
 * Copyright 2007 by Jakob Petsovits ("jpetso", http://drupal.org/user/56020)
 */


function project_forecast_views_data() {
  $data = array();

  // ----------------------------------------------------------------------
  // project_forecast_user_tasks table

  $data['project_forecast_user_tasks']['table']['group']  = t('Project forecast');

  $data['project_forecast_user_tasks']['table']['join'] = array(
    'node' => array(
      'left_field' => 'nid',
      'field' => 'nid',
    ),
  );

  $data['project_forecast_user_tasks']['unit'] = array(
    'title' => t('Estimated task completion date'),
    'field' => array(
      'handler' => 'views_handler_field_time_unit',
      'help' => t('The time when this task is estimated to be accomplished by all participating users. Will stay empty if the time budget has not been specified far enough to include this task in the estimated target date calculation.'),
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_boolean_operator',
      'title' => t('Has an estimated completion date'),
      'help' => t('Only display items where an estimated completion date has been determined. If this is not the case, then the user has not (yet) allocated enough time in the time budget to complete the task.'),
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_time_unit',
      'help' => t('Sort by estimated completion date.'),
    ),
  );

  // ----------------------------------------------------------------------
  // project_forecast_milestones table

  $data['project_forecast_milestones']['table']['group']  = t('Project forecast');

  $data['project_forecast_milestones']['table']['join'] = array(
    'node' => array(
      'left_field' => 'nid',
      'field' => 'nid',
    ),
  );

  $data['project_forecast_milestones']['unit'] = array(
    'title' => t('Estimated milestone completion date'),
    'field' => array(
      'handler' => 'views_handler_field_time_unit',
      'help' => t('The time when this milestone is estimated to be accomplished by all participating users. Will stay empty if the time budget has not been specified far enough to include all tasks for this milestone.'),
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_boolean_operator',
      'title' => t('Has an estimated completion date'),
      'help' => t('Only display items where an estimated completion date has been determined. If this is not the case, then the users responsible for this milestone have not (yet) allocated enough time in the time budget to complete all the tasks in there.'),
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_time_unit',
      'help' => t('Sort by estimated completion date.'),
    ),
  );

  return $data;
}

/**
 * A handler to provide proper displays for time units (i.e. calendar weeks).
 */
class views_handler_field_time_unit extends views_handler_field {
  function init(&$view, &$data) {
    parent::init($view, $data);
    $this->additional_fields['completed'] = 'completed';
  }

  function render($values) {
    $completed = $values->{$this->aliases['completed']};
    if (!$completed) {
      return '';
    }
    $date = date_make_date($values->{$this->field_alias}, 'UTC', DATE_UNIX);
    if (is_object($date)) {
      return timebudget_format_unit($date);
    }
    return '';
  }

  function click_sort($order) {
    $order = strtolower($order);
    $opposite_order = ($order == 'desc') ? 'asc' : 'desc';
    $this->query->add_orderby($this->table, $this->field, $opposite_order, $this->aliases['completed']);
    $this->query->add_orderby($this->table, $this->field, $order, $this->field_alias);
  }
}

/**
 * A sort handler that properly takes non-completed targets into account.
 */
class views_handler_sort_time_unit extends views_handler_sort {
  function query() {
    $this->ensure_my_table();
    $order = strtolower($this->options['order']);

    $opposite_order = ($order == 'desc') ? 'asc' : 'desc';
    $this->query->add_orderby($this->table_alias, 'completed', $opposite_order);
    $this->query->add_orderby($this->table_alias, $this->real_field, $order);
  }
}
