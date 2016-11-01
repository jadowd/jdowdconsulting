<?php
// $Id: audio_itunes.admin.inc,v 1.1 2008/11/25 14:05:18 drewish Exp $

/**
 * Settings form.
 */
function audio_itunes_admin_settings() {
  $types = array();
  foreach (node_get_types() as $type) {
    $types[$type->type] = $type->name;
  }
  $form['audio_itunes_types'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Node types'),
    '#options' => $types,
    '#default_value' => variable_get('audio_itunes_types', array('audio')),
    '#description' => t('Associate iTunes feed item information with node types.'),
  );
  $form['#validate'][] = 'audio_itunes_admin_settings_validate';

  return system_settings_form($form);
}

function audio_itunes_admin_settings_validate($form, &$form_state) {
  // Retrieve selected types - Forms API sets the value of unselected checkboxes to 0.
  $form_state['values']['audio_itunes_types'] = array_keys(array_filter($form_state['values']['audio_itunes_types']));
}