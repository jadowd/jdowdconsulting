<?php
// $Id: project_forecast.timeneed.inc,v 1.1 2008/05/27 02:41:04 jpetso Exp $
/**
 * @file
 * Project Forecast - Estimated completion dates for tasks and milestones.
 *
 * This file contains Project Forecast's own implementation of
 * hook_project_forecast_timeneed_field_info().
 *
 * Copyright 2007 by Jakob Petsovits ("jpetso", http://drupal.org/user/56020)
 */


/**
 * Implementation of hook_project_forecast_timeneed_field_info():
 * Return information about which fields can represent time need values,
 * and how to parse them into the number of hours required for this task.
 */
function project_forecast_project_forecast_timeneed_field_info() {
  $fields['duration'] = array(
    'parse callback' => 'project_forecast_timeneed_parse_duration',
  );
  $fields['number_float'] =
  $fields['number_integer'] =
  $fields['number_decimal'] = array(
    'parse callback' => 'project_forecast_timeneed_parse_number',
  );
  return $fields;
}

/**
 * Transform a duration field into the corresponding number of hours.
 */
function project_forecast_timeneed_parse_duration($timeneed_result_values) {
  $iso_string = $timeneed_result_values['iso8601'];
  if (empty($iso_string)) {
    return 0;
  }
  $duration = duration_create($iso_string);
  if (isset($duration)) {
    $duration->set_conversion_factors(array(
      'days/months' => variable_get('project_forecast_days_per_month', 22),
      'days/weeks' => variable_get('project_forecast_days_per_week', 5),
      'hours/days' => variable_get('project_forecast_hours_per_day', 8),
    ));
    return $duration->to_single_metric('hours');
  }
  return 0;
}

/**
 * Transform a numeric value field into the corresponding number of hours.
 */
function project_forecast_timeneed_parse_number($timeneed_result_values) {
  $value = $timeneed_result_values['value'];
  if (empty($value)) {
    return 0;
  }
  return $value;
}
