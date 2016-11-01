<?php
// $Id: active_translation.admin.inc,v 1.4 2008/11/17 08:42:56 drewish Exp $

/**
 * Settings form.
 */
function active_translation_settings_form() {
  $form['active_translation_show_status'] = array(
    '#type' => 'radios',
    '#title' => t('Show Translation Status Message'),
    '#options' => array(
      1 => t('Yes, inform visitors when they are viewing the node in another language because it has not been translated into their language.'),
      0 => t("No, don't bother them."),
    ),
    '#default_value' => variable_get('active_translation_show_status', 1),
  );
  $form['active_translation_hide_node_links'] = array(
    '#type' => 'radios',
    '#title' => t("Hide Node Translation Links"),
    '#options' => array(
      1 => t("Yes, hide the local module's links to other translations."),
      0 => t('No, leave the links alone.'),
    ),
    '#default_value' => variable_get('active_translation_hide_node_links', 0),
    '#description' => t("This can be useful the locale module's language switcher block is being used to select the language making the node links redundant."),
  );

  return system_settings_form($form);
}

/**
 * Rebuild translation table form.
 */
function active_translation_rebuild_form() {
  $form['help'] = array(
    '#type' => 'item',
    '#value' => t('If you are experiencing problems with missing translations you should try rebuilding the Active Translation table.'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Rebuild'),
  );
  $form['#submit'] = array('active_translation_rebuild_on_submit');
  return $form;
}
