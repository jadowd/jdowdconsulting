<?php

/**
 * Implementation of hook_views_plugins
 */
function audio_itunes_views_plugins() {
  return array(
    'module' => 'audio_itunes', // This just tells our themes are elsewhere.
    'style' => array(
      'itunes_rss' => array(
        'title' => t('iTunes RSS Feed'),
        'help' => t('Generates an RSS feed from a view with extra iTunes specific attributes.'),
        'handler' => 'audio_itunes_plugin_style_rss',
        'theme' => 'views_view_rss',
        'parent' => 'rss',
        'uses row plugin' => TRUE,
        'uses options' => TRUE,
        'type' => 'feed',
      ),
    ),
  );
}