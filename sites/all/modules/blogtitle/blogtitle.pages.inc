<?php

// $Id: blogtitle.pages.inc,v 1.1.2.2 2010/04/21 15:42:35 remi Exp $

/**
 * Menu callback; displays a Drupal page containing recent blog entries of a given user.
 *
 * Call blog.module's blog_page_user() to create the page, but adjust
 * the page title and RSS feed title.
 */
function blogtitle_blog_page_user($account) {

	$title = blogtitle_get_blog_title($account->uid);
	
	if(variable_get('blogtitle_replace_rss_feed_title', 0))
	{
		// add feed URL before calling blog_page_user(), so that our title takes precedence
		drupal_add_feed(url('blog/'. $account->uid .'/feed'), t('RSS - !title', array('!title' => $title)));
	}
	
	// build the page
	module_load_include('inc', 'blog', 'blog.pages');
	$output = blog_page_user($account);

	// fix the page title
	drupal_set_title($title);

	return $output;
}

/**
 * Menu callback.  Displays RSS feed with the latest entries of a user's blog.
 *
 * This function is based on blog.module's blog_feed_user().
 */
function blogtitle_blog_feed_user($account)
{
	$result = db_query_range(db_rewrite_sql("SELECT n.nid, n.created FROM {node} n  WHERE n.type = 'blog' AND n.uid = %d AND n.status = 1 ORDER BY n.created DESC"), $account->uid, 0, variable_get('feed_default_items', 10));
	$channel['title'] = blogtitle_get_blog_title($account->uid);
	$channel['link'] = url('blog/'. $account->uid, array('absolute' => TRUE));
	
	$items = array();
	while ($row = db_fetch_object($result))
	{
		$items[] = $row->nid;
	}
	node_feed($items, $channel);
}
