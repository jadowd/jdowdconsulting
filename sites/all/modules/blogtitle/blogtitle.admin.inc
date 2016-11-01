<?php

// $Id: blogtitle.admin.inc,v 1.1 2009/07/06 11:00:43 remi Exp $

/**
 * @file
 * Administration include file.
 */
 
/**
 * Get user profile fields defined by the Profile module.
 *
 * @return
 *   array An array with profile names as keys and titles as values, ready to be used in a form array.
 */
function blogtitle_get_profile_fields($type = null)
{
	$options = array();
	if(is_null($type))
	{
		$result = db_query('SELECT title, name, category, weight FROM {profile_fields} ORDER BY category, weight');
	}
	else
	{
		$result = db_query("SELECT title, name, category, weight FROM {profile_fields} WHERE type = '%s' ORDER BY category, weight", array($type));
	}
	while($field = db_fetch_object($result))
	{
		$options[$field->category][$field->name] = $field->title;
	}
	return $options;
}

/**
 * Returns form to be displayed on module's settings page.
 *
 * @return
 *   array A form processed by system_settings_form().
 */
function blogtitle_settings_form()
{

	$form = array();
	
	$form['blogtitle_profile_field'] = array(
	    '#default_value' => variable_get('blogtitle_profile_field', ''),
		'#description' => t("Select the user profile field which contains the custom title for the user's blog. To add profile fields, see the <a href=\"@url\">@page</a> settings page.", array('@page' => t('Profiles'), '@url' => url('admin/user/profile'))),
		'#options' => array('' => t('None')) + blogtitle_get_profile_fields('textfield'),
	    '#title' => t('Profile field containing blog title'),
		'#type' => 'select',
	);
	
	$form['blogtitle_remove_blog_link'] = array(
	    '#default_value' => variable_get('blogtitle_remove_blog_link', 0),
		'#description' => t("Remove the link to the user's blog when viewing one of its blog entries."),
	    '#title' => t('Remove blog link in nodes'),
		'#type' => 'checkbox',
	);

	$form['blogtitle_remove_front_page_title'] = array(
	    '#default_value' => variable_get('blogtitle_remove_front_page_title', 0),
		'#description' => t("Remove the title if a blog is set as the front page of the site. To set the site's front page, see the <a href=\"@url\">@page</a> settings page.", array('@page' => t('Site information'), '@url' => url('admin/settings/site-information'))),
	    '#title' => t('Remove title if blog is set as front page'),
		'#type' => 'checkbox',
	);

	$form['blogtitle_replace_rss_feed_title'] = array(
	    '#default_value' => variable_get('blogtitle_replace_rss_feed_title', 0),
		'#description' => t("Replace the titles of the RSS feeds of every blog like the way it's done on normal pages. Changing this option may require clearing the site's caches which can be done on the <a href=\"@url\">@page</a> settings page.", array('@page' => t('Performance'), '@url' => url('admin/settings/performance'))),
	    '#title' => t('Replace blog RSS feed titles'),
		'#type' => 'checkbox',
	);

	return system_settings_form($form);

}