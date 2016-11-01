<?php
// $Id: phpbb_api.php,v 1.12 2008/05/21 18:31:34 vb Exp $
/**
 * Copyright (Ñ) 2007-2008 by Vadim G.B. (http://vgb.org.ru)
 */

define('IN_PHPBB', true);
define('PHPBB_API_EMBEDDED', true);
define('PHPBB_EMBEDDED', true);
define('PHPBB_API_DEBUG', 0);

//@define('PHPBB_DB_PCONNECT', true);
@define('PHPBB_DB_PCONNECT', false);
@define('PHPBB_DB_NEW_LINK', true);

define('PHPBB_BOARD_URL_WITHOUT_PORT', true);
//define('PHPBB_BOARD_URL_WITHOUT_PORT', false);

// Report all errors, except notices
if (PHPBB_API_DEBUG)
  error_reporting(E_ALL);
else
  error_reporting(E_ALL ^ E_NOTICE);

global $phpbb_config, $phpbb_user,
$user, $config, $cache, $auth, $template, $db,
$phpbb_root_path, $phpEx,
$phpbb_db_type, $phpbb_db, $phpbb_connection, $phpbb_db_prefix, $table_prefix,
$site_base_url, $site_phpbb_page, $site_forum_url, $phpbb_integration_mode, $phpbb_output,
$site_user, $site_error_handler, $_site_context_saved,
$phpbb_hook;


if (!isset($phpbb_root_path) || empty($phpbb_root_path))
  $phpbb_root_path = dirname(__FILE__) .'/';

$phpEx = substr(strrchr(__FILE__, '.'), 1);

// If we are on PHP >= 6.0.0 we do not need some code
if (version_compare(PHP_VERSION, '6.0.0-dev', '>='))
{
	define('STRIP', false);
}
else
{
	set_magic_quotes_runtime(0);

	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
}

if (!file_exists($phpbb_root_path . 'config.' . $phpEx))
{
	die("<p>The config.$phpEx file could not be found.</p><p><a href=\"{$phpbb_root_path}install/index.$phpEx\">Click here to install phpBB</a></p>");
}

require_once($phpbb_root_path . 'config.' . $phpEx);

// If PHPBB isn't defined, config.php is missing or corrupt
if (!defined('PHPBB_INSTALLED'))
{
	die("<p>The config.$phpEx file is not valid.</p><p><a href=\"{$phpbb_root_path}install/index.$phpEx\">Click here to install phpBB</a></p>");
}

$phpbb_db_type = $dbms;
$phpbb_db_prefix = $table_prefix;
//$phpbb_language_dir = $phpbb_root_path . 'language/';

// Load Extensions
if (!empty($load_extensions))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}

// Include files
require_once($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.' . $phpEx);
require_once($phpbb_root_path . 'includes/cache.' . $phpEx);
require_once($phpbb_root_path . 'includes/template.' . $phpEx);
require_once($phpbb_root_path . 'includes/session.' . $phpEx);
require_once($phpbb_root_path . 'includes/auth.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);
require_once($phpbb_root_path . 'includes/constants.' . $phpEx);
require_once($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
require_once($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);
require_once($phpbb_root_path . 'includes/hooks/index.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
/////////////////////////////////////////
require_once(dirname(__FILE__) . '/phpbb_api_subs.php');

// Set PHP error handler to ours
//if (PHPBB_API_DEBUG)
//set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');
/*
if (
//!
defined('PHPBB_ERROR_HANDLER')) 
{
  set_error_handler('msg_handler');
}
else {
  //define('PHPBB_ERROR_HANDLER', 'phpbbforum_error_handler');
  if (!isset($site_error_handler) || empty($site_error_handler))
    $site_error_handler = set_error_handler(PHPBB_ERROR_HANDLER);
}
*/

$site_user = $user;
$_site_context_saved = true;

// Instantiate some basic classes
$user	= new user();
$auth	= new auth();
$template	= new template();
$cache = new cache();

if (!is_object($db))
  $db	= new $sql_db();

if (!$phpbb_connection)
{
// Connect to DB
  $db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, 
    defined('PHPBB_DB_PCONNECT') ? PHPBB_DB_PCONNECT : false, 
    defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);
  if ($db->db_connect_id)
    $phpbb_connection = $db->db_connect_id;
}

// We do not need this any longer, unset for safety purposes
unset($dbpasswd);

// Grab global variables, re-cache if necessary
$config = $cache->obtain_config();
$phpbb_config = $config;

if (empty($phpbb_hook))
{
  $phpbb_hook = new phpbb_hook(array('exit_handler', 'phpbb_user_session_handler', 'append_sid', array('template', 'display')));

  foreach ($cache->obtain_hooks() as $hook)
  {
  	@include_once($phpbb_root_path . 'includes/hooks/' . $hook . '.' . $phpEx);
  }
}

function phpbb_hook_register(&$hook)
{
	$hook->register('append_sid', 'phpbb_hook_append_sid');
  $hook->register('exit_handler', 'phpbb_hook_exit_handler');
}

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$phpbb_config['forum_url'] = phpbb_generate_board_url(false, defined('PHPBB_BOARD_URL_WITHOUT_PORT') ? PHPBB_BOARD_URL_WITHOUT_PORT : false);
//if (PHPBB_MODULE_DEBUG)
//  drupal_set_message('phpbb_url='.$phpbb_config['forum_url']);

$phpbb_user	= $user;
$user = $site_user;
$_site_context_saved = false;
////////////////////////////////////////////////////////////////////////////////

function phpbb_api_get_user_name($user_id)
{
	global $phpbb_connection, $db;

  $username = "";
	if (!empty($user_id) && is_integer($user_id))
	{
		if (!$phpbb_connection)
			return $username;

		$sql = 'SELECT username
  		FROM ' . USERS_TABLE . '
  		WHERE user_id = ' . $user_id;
  	$result = $db->sql_query($sql);
  	list ($username) = $db->sql_fetchrow($result);
  	$db->sql_freeresult($result);
	}
	return $username;
}

function phpbb_api_get_user_id($username)
{
	global $phpbb_connection, $db;

  $user_id = 0;
	if (!empty($username))
	{
		if (!$phpbb_connection)
			return $user_id;

  	$sql = 'SELECT user_id
  		FROM ' . USERS_TABLE . "
  		WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
  	$result = $db->sql_query($sql);
  	list ($user_id) = $db->sql_fetchrow($result);
  	$db->sql_freeresult($result);
	}
	return $user_id;
}

function phpbb_api_authenticate_user()
{
	global $phpbb_connection, $phpbb_config, $phpbb_user;
	global $db, $user, $template, $auth, $phpEx, $phpbb_root_path, $config;

  if (!$phpbb_connection)
	  return false;

  phpbb_save();

  // Start session management
  $user->session_begin();
  $auth->acl($user->data);
  $user->setup();

  $phpbb_user->data['is_registered'] = $user->data['is_registered'];
  $phpbb_user->data['is_bot'] = $user->data['is_bot'];
  
  phpbb_load();

	return ($phpbb_user->data['user_id'] != ANONYMOUS);
}

function phpbb_api_get_user($username, $password)
{
	global $phpbb_connection, $phpbb_db_prefix, $phpbb_config, $phpbb_user;

	$username_clean = utf8_clean_string($username);

	// authentication!
	if (phpbb_api_authenticate_user() &&
		!empty($username) && $username_clean == utf8_clean_string($phpbb_user->data['username'])) {

		$phpbb_config['user'] =	array(
			'status'		=> LOGIN_SUCCESS,
			'error_msg'		=> false,
		);
		return true;
	}

  if (!$phpbb_connection)
	  return false;

	if (empty($username))
	  return false;

	global $db, $config;

	$sql = 'SELECT *
		FROM ' . USERS_TABLE . "
		WHERE username_clean = '" . $db->sql_escape($username_clean) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

 	if (!$row)
	{
		$phpbb_config['user'] =	array(
			'status'	=> LOGIN_ERROR_USERNAME,
			'error_msg'	=> 'LOGIN_ERROR_USERNAME',
			'user_row'	=> array('user_id' => ANONYMOUS),
		);
		$phpbb_user->data['user_id'] = ANONYMOUS;
    return false;
	}

  // Check password ...
	if (!empty($password)) {
		if (/*!$row['user_pass_convert'] &&*/ phpbb_check_hash($password, $row['user_password'])/*md5($password) == $row['user_password']*/)
		{
			// Check for old password hash...
      /*
  		if (strlen($row['user_password']) == 32)
  		{
  			$hash = phpbb_hash($password);

  			// Update the password in the users table to the new format
  			$sql = 'UPDATE ' . USERS_TABLE . "
  				SET user_password = '" . $db->sql_escape($hash) . "',
  					user_pass_convert = 0
  				WHERE user_id = {$row['user_id']}";
  			$db->sql_query($sql);

  			$row['user_password'] = $hash;
  		}
      */
      /*
			if ($row['user_login_attempts'] != 0)
			{
				// Successful, reset login attempts (the user passed all stages)
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_login_attempts = 0
					WHERE user_id = ' . $row['user_id'];
				$db->sql_query($sql);
			}
			*/
			// User inactive...
			if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE)
			{
				$phpbb_config['user'] =	array(
					'status'		=> LOGIN_ERROR_ACTIVE,
					'error_msg'		=> 'ACTIVE_ERROR',
					'user_row'		=> $row,
				);
			}
			else {
				// Successful login... set user_login_attempts to zero...
				$phpbb_config['user'] =	array(
					'status'		=> LOGIN_SUCCESS,
					'error_msg'		=> false,
					'user_row'		=> $row,
				);
			}
			$phpbb_user->data = $row;
		}
		else {
			// Give status about wrong password...
			$phpbb_config['user'] =	array(
				'status'		=> LOGIN_ERROR_PASSWORD,
				'error_msg'		=> 'LOGIN_ERROR_PASSWORD',
				'user_row'		=> $row,
			);
			/*
			// Password incorrect - increase login attempts
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_login_attempts = user_login_attempts + 1
				WHERE user_id = ' . $row['user_id'];
			$db->sql_query($sql);
			*/
			$phpbb_user->data = $row;
      return false;
		}
	}
  else {
    $phpbb_config['user'] =	array(
			'status'		=> LOGIN_ERROR_ACTIVE,
			'error_msg'		=> 'ACTIVE_ERROR',
			'user_row'		=> $row,
		);
		$phpbb_user->data = $row;
		$phpbb_user->data['user_password'] = "";
  }

	return true;
}

function phpbb_api_index($str)
{
	global $phpbb_connection, $phpbb_config, $phpbb_user, $phpbb_output;
	global $user, $config, $cache, $auth, $template, $db, $phpbb_root_path, $phpEx;

  if (!$phpbb_connection)
	  return false;
  //return $phpbb_root_path . $str;
  phpbb_save();

  //include_once($phpbb_root_path . '/index.' . $phpEx);
  include_once($phpbb_root_path . $str);

  phpbb_load();

	return $phpbb_output;
}

function phpbb_api_posting($str)
{
	global $phpbb_connection, $phpbb_config, $phpbb_user, $phpbb_output;
	global $user, $config, $cache, $auth, $template, $db, $phpbb_root_path, $phpEx;

  if (!$phpbb_connection)
	  return false;
  //return $phpbb_root_path . $str;
  phpbb_save();

  //include_once($phpbb_root_path . '/index.' . $phpEx);
  include_once($phpbb_root_path . $str);

  phpbb_load();

	return $phpbb_output;
}

function phpbb_api_viewforum()
{
	global $phpbb_connection, $phpbb_config, $phpbb_user, $phpbb_output;
	global $user, $config, $cache, $auth, $template, $db, $phpbb_root_path, $phpEx;

  if (!$phpbb_connection)
	  return false;

  phpbb_save();

  include_once($phpbb_root_path . '/viewforum.' . $phpEx);

  phpbb_load();

	return $phpbb_output;
}

function phpbb_api_viewtopic()
{
	global $phpbb_connection, $phpbb_config, $phpbb_user, $phpbb_output;
	global $user, $config, $cache, $auth, $template, $db, $phpbb_root_path, $phpEx;

  if (!$phpbb_connection)
	  return false;

  phpbb_save();

  include_once($phpbb_root_path . '/viewtopic.' . $phpEx);

  phpbb_load();

	return $phpbb_output;
}

// Recent post list
function phpbb_api_recent_posts($num_recent = 10, $search_id = 'newposts', $output_tag = "<ul>", $output_br = "<br />", $display_mode = 0, $output_method = '')
{
	global $phpbb_connection, $phpbb_db_prefix, $phpbb_config, $phpbb_user;

	if (!$phpbb_connection)
		return false;

  require_once(dirname(__FILE__) . '/phpbb_api_recent.php');
  
  if ($num_recent < 0)
    $num_recent = 0;

  $show_results = 'posts';
  //$search_id = 'unanswered';
  //$search_id = 'egosearch';
	$search_id = 'newposts';
  $topic_id = 0;
  $post_id = 0;

	$phpbb_url = $phpbb_config['forum_url'];

	$posts = phpbb_api_search($num_recent, $show_results, $search_id, $topic_id, $post_id);

	if ($output_method == 'array')
		return $posts;

	if (empty($posts))
  {
		if (PHPBB_MODULE_DEBUG && $phpbb_config['error_msg'] != 'NO_ERRORS')
		  return "empty posts error=" . $phpbb_config['error_msg'];
		else
		  return "";
	}

	//if (!empty($phpbb_config['number_recent_posts'])) {

  //$output_tag = "<ul>" "<div>" "<pre>"
  $output_tag = strtolower($output_tag);
  $output_end_tag = "";
  $output_tag2 = "";
  $output_end_tag2 = "";
  if (substr($output_tag, 0, 3) == "<ul" || substr($output_tag, 0, 3) == "<ol") {
  	$output_end_tag = "</" . substr($output_tag, 1, 2) . ">";
  	$output_tag2 = "<li>";
  	$output_end_tag2 = "</li>";
  }
  elseif (substr($output_tag, 0, 4) == "<div") {
  	$output_end_tag = "</div>";
  }
  elseif (substr($output_tag, 0, 4) == "<pre") {
  	$output_end_tag = "</pre>";
  }
  elseif (substr($output_tag, 0, 2) == "<p") {
  	$output_end_tag = "</p>";
  }

  $strposts = $str = '';
  //$str = '<a href="'. $phpbb_url .'"><img src="' . $.gif" alt="' . $phpbb_ . '" /></a>';
  //$strposts = $str . ' ';
  //$str = '<a href="' . $phpbb_url .'">' .$phpbb_ . '</a>';
  //$strposts .= $str;

  $strposts .= $output_tag;
 	foreach ($posts as $post) {
    //$strnew = $post['new'] ? '' : '<a href="' . $phpbb_url . '?topic=' . $post['topic'] . '.msg' . $post['new_from'] . ';topicseen#new"><img src="' . $phpbb_config['theme']['images_url'] . '/' . $phpbb_user['language'] . '/new.gif" alt="' . $phpbb_txt[302] . '" /></a>';
    //$strnew .= ' ';
		$strnew = '';
    $str = 	'<a href="' . $post['U_VIEW_POST'] . '">' . phpbb_shorten_subject($post['POST_SUBJECT'], 45) . '</a>';
    //$strposts .= $str . $output_br;
		$strposts .= $output_tag2 . $strnew . $str;
    
    if ($display_mode == 0) {
      $strposts .= $output_br;
      $posterlink = //empty($post['POST_AUTHOR_FULL']) ? $post['POST_AUTHOR_FULL'] :
  		$post['POST_AUTHOR_FULL'];
      $strnew = '<a href="' . $post['U_VIEW_POST'] . '"><img src="' . $post['U_LAST_POST_IMG'] . '" alt="' . $post['LAST_POST_IMG_ALT'] . '" title="' . $post['LAST_POST_IMG_ALT'] . '" /></a>';
  		$str = 	$posterlink . ' ' . $strnew . '<br />' . $post['POST_DATE'];
      $strposts .= $str . $output_br;
  		//$str = 	$post['U_VIEW_FORUM'];
  		$str = 	'<a href="' . $post['U_VIEW_FORUM'] . '">' . phpbb_shorten_subject($post['FORUM_TITLE'], 45) . '</a>';
      $strposts .= $str;
    }
    $strposts .= $output_end_tag2;
  }
  $strposts .= $output_end_tag;

  if ($output_method == 'echo')
    echo $strposts;

  return $strposts;
}

// Recent topic list
function phpbb_api_recent_topics($num_recent = 10, $search_id = 'newposts', $output_tag = "<ul>", $output_br = "<br />", $display_mode = 0, $output_method = '')
{
	global $phpbb_connection, $phpbb_db_prefix, $phpbb_config, $phpbb_user;

	if (!$phpbb_connection)
		return false;

  require_once(dirname(__FILE__) . '/phpbb_api_recent.php');

  if ($num_recent < 0)
    $num_recent = 0;

  $show_results = 'topics';
  //$search_id = 'unanswered';
  $search_id = 'active_topics';
	//$search_id = 'newposts';
  $topic_id = 0;
  $post_id = 0;

	$phpbb_url = $phpbb_config['forum_url'];

	$posts = phpbb_api_search($num_recent, $show_results, $search_id, $topic_id, $post_id);

	if ($output_method == 'array')
		return $posts;

	if (empty($posts))
  {
		if (PHPBB_MODULE_DEBUG && $phpbb_config['error_msg'] != 'NO_ERRORS')
		  return "empty topics error=" . $phpbb_config['error_msg'];
		else
		  return "";
	}

	//if (!empty($phpbb_config['number_recent_posts'])) {

  //$output_tag = "<ul>" "<div>" "<pre>"
  $output_tag = strtolower($output_tag);
  $output_end_tag = "";
  $output_tag2 = "";
  $output_end_tag2 = "";
  if (substr($output_tag, 0, 3) == "<ul" || substr($output_tag, 0, 3) == "<ol") {
  	$output_end_tag = "</" . substr($output_tag, 1, 2) . ">";
  	$output_tag2 = "<li>";
  	$output_end_tag2 = "</li>";
  }
  elseif (substr($output_tag, 0, 4) == "<div") {
  	$output_end_tag = "</div>";
  }
  elseif (substr($output_tag, 0, 4) == "<pre") {
  	$output_end_tag = "</pre>";
  }
  elseif (substr($output_tag, 0, 2) == "<p") {
  	$output_end_tag = "</p>";
  }

  $strposts = $str = '';

	//$strposts = $phpbb_config['error_msg'];

  //$str = '<a href="'. $phpbb_url . '"><img src="' . $.gif" alt="' . $phpbb_ . '" /></a>';
  //$strposts = $str . ' ';
  //$str = '<a href="' . $phpbb_url . '">' .$phpbb_ . '</a>';
  //$strposts .= $str;

  $strposts .= $output_tag;
 	foreach ($posts as $post) {
    $strnew = '<a href="' . $post['U_NEWEST_POST'] . '"><img src="' . $post['U_NEWEST_POST_IMG'] . '" alt="' . $post['NEWEST_POST_IMG_ALT'] . '" title="' . $post['NEWEST_POST_IMG_ALT'] . '" /></a>';
    $strnew .= ' ';
    $str = 	'<a href="' . $post['U_VIEW_TOPIC'] . '">' . phpbb_shorten_subject($post['TOPIC_TITLE'], 45) . '</a>';
    //$strposts .= $str . $output_br; $post['U_LAST_POST'] LAST_POST_SUBJECT
    $strposts .= $output_tag2 . $strnew . $str;
    
    if ($display_mode == 0) {
      $strposts .= $output_br;
      $posterlink = //empty($post['POST_AUTHOR_FULL']) ? $post['POST_AUTHOR_FULL'] :
  		$post['LAST_POST_AUTHOR_FULL'];
      //'<a href="' . $post['POST_AUTHOR_FULL'] . '">' . $post['POST_AUTHOR'] . '</a>';
      $strnew = '<a href="' . $post['U_LAST_POST'] . '"><img src="' . $post['U_LAST_POST_IMG'] . '" alt="' . $post['LAST_POST_IMG_ALT'] . '" title="' . $post['LAST_POST_IMG_ALT'] . '" /></a>';
  		$str = 	$posterlink . ' ' . $strnew . '<br />' . $post['LAST_POST_TIME'];
      //$str = 	 . ' ' . $post['POST_AUTHOR_FULL'] . ' (' . $post['FORUM_TITLE'] . ')';
      $strposts .= $str . $output_br;
  		//$str = 	$post['U_VIEW_FORUM'];
  		$str = 	'<a href="' . $post['U_VIEW_FORUM'] . '">' . phpbb_shorten_subject($post['FORUM_TITLE'], 45) . '</a>';
      $strposts .= $str;
    }
    $strposts .= $output_end_tag2;
  }
  $strposts .= $output_end_tag;

  if ($output_method == 'echo')
    echo $strposts;

  return $strposts;
}

// Show the top posters
function phpbb_api_topposter($num_top = 1, $output_method = '')
{
	global $phpbb_connection, $phpbb_config, $phpbb_user;
	global $db, $config, $template, $user, $auth, $phpEx, $phpbb_root_path;

  $strreturn = "";
	if (!$phpbb_connection)
		return $strreturn;

  if ($num_top <= 0)
    $num_top = 1;

  phpbb_save();

  // Start session management
  $user->session_begin();
  $auth->acl($user->data);
  $user->setup();

	$phpbb_url = $phpbb_config['forum_url'];

	// Find the latest poster.
	$sql = 'SELECT user_id, username, user_posts, user_colour
		FROM ' . USERS_TABLE . '
		WHERE user_type <> 2
		AND user_posts <> 0
		ORDER BY user_posts DESC';

	$result = $db->sql_query_limit($sql, $num_top);

	if ($result === false) {
		phpbb_load();
		return $strreturn;
	}

	$posters = array();
	while( ($row = $db->sql_fetchrow($result)) && ($row['username'] != '') )
	{
		$posters[] = array(
			'user_id' => $row['user_id'],
			//'S_SEARCH_ACTION'=> append_sid("{$phpbb_url}/search.$phpEx", 'author_id=' . $row['user_id'] . '&amp;sr=posts'),
			'username'		=> $row['username'], //censor_text($row['username']),
			'USERNAME_COLOR'=> ($row['user_colour']) ? ' style="color:#' . $row['user_colour'] .'"' : '',
			//'U_USERNAME'	=> append_sid("{$phpbb_url}/memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']),
			'link' => '<a href="' . append_sid("{$phpbb_url}/memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']) . '">' . $row['username'] . '</a>',
			'user_posts'	=> $row['user_posts'],
		);
	}
	$db->sql_freeresult($result);

	if ($output_method == 'array') {
		phpbb_load();
		return $posters;
	}

	$poster_array = array();
	foreach ($posters as $poster)
		$poster_array[] = $poster['link'] . ': ' .  '<strong>' . $poster['user_posts'] . '</strong>';

  $strreturn = implode(', ', $poster_array);

	if ($output_method == 'echo')
  	echo $strreturn;

  phpbb_load();

  return $strreturn;
}

// Shows a list of online users...
function phpbb_api_whos_online($num_top = 9999, $output_method = '')
{
	global $phpbb_connection, $phpbb_db_prefix, $phpbb_config, $phpbb_user;
	global $db, $config, $template, $SID, $_SID, $user, $auth, $phpEx, $phpbb_root_path;

	$strreturn = "";
	if (!$phpbb_connection)
		return $strreturn;

  phpbb_save();

  if ($num_top <= 0)
    $num_top = 9999;

	$phpbb_url = $phpbb_config['forum_url'];

  // Start session management
  $user->session_begin();
  $auth->acl($user->data);
  $user->setup();

/*
	// Generate logged in/logged out status
	if ($user->data['user_id'] != ANONYMOUS)
	{
		$u_login_logout = append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=logout', true, $user->session_id);
		$l_login_logout = sprintf($user->lang['LOGOUT_USER'], $user->data['username']);
	}
	else
	{
		$u_login_logout = append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login');
		$l_login_logout = $user->lang['LOGIN'];
	}

	// Last visit date/time
	$s_last_visit = ($user->data['user_id'] != ANONYMOUS) ? $user->format_date($user->data['session_last_visit']) : '';
*/
	// Get users online list ... if required
	$l_online_users = $online_userlist = $l_online_record = '';

	if ($config['load_online'] && $config['load_online_time']/* && $display_online_list*/)
	{
		$logged_visible_online = $logged_hidden_online = $guests_online = $prev_user_id = 0;
		$prev_session_ip = $reading_sql = '';

		$f = 0;
    /*
		if (!empty($_REQUEST['f']))
		{
			$f = request_var('f', 0);
			//$f = 0;

			$reading_sql = ' AND s.session_page ' . $db->sql_like_expression("{$db->any_char}_f_={$f}x{$db->any_char}");
		}
    */
		// Get number of online guests
		if (!$config['load_online_guests'])
		{
			if ($db->sql_layer === 'sqlite')
			{
				$sql = 'SELECT COUNT(session_ip) as num_guests
					FROM (
						SELECT DISTINCT s.session_ip
							FROM ' . SESSIONS_TABLE . ' s
							WHERE s.session_user_id = ' . ANONYMOUS . '
								AND s.session_time >= ' . (time() - ($config['load_online_time'] * 60)) .
								$reading_sql .
					')';
			}
			else
			{
				$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests
					FROM ' . SESSIONS_TABLE . ' s
					WHERE s.session_user_id = ' . ANONYMOUS . '
						AND s.session_time >= ' . (time() - ($config['load_online_time'] * 60)) .
					$reading_sql;
			}
			$result = $db->sql_query($sql);
			$guests_online = (int) $db->sql_fetchfield('num_guests');
			$db->sql_freeresult($result);
		}

		$sql = 'SELECT u.username, u.username_clean, u.user_id, u.user_type, u.user_allow_viewonline, u.user_colour, s.session_ip, s.session_viewonline
			FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . ' s
			WHERE s.session_time >= ' . (time() - (intval($config['load_online_time']) * 60)) .
				$reading_sql .
				((!$config['load_online_guests']) ? ' AND s.session_user_id <> ' . ANONYMOUS : '') . '
				AND u.user_id = s.session_user_id
			ORDER BY u.username_clean ASC, s.session_ip ASC';

		//$result = $db->sql_query($sql);
		$result = $db->sql_query_limit($sql, $num_top);

		while ($row = $db->sql_fetchrow($result))
		{
			// User is logged in and therefore not a guest
			if ($row['user_id'] != ANONYMOUS)
			{
				// Skip multiple sessions for one user
				if ($row['user_id'] != $prev_user_id)
				{
					if ($row['user_colour'])
					{
						$user_colour = ' style="color:#' . $row['user_colour'] . '"';
						$row['username'] = '<strong>' . $row['username'] . '</strong>';
					}
					else
					{
						$user_colour = '';
					}

					if ($row['session_viewonline'])
					{
						$user_online_link = $row['username'];
						$logged_visible_online++;
					}
					else
					{
						$user_online_link = '<em>' . $row['username'] . '</em>';
						$logged_hidden_online++;
					}

					if (($row['session_viewonline']) || $auth->acl_get('u_viewonline'))
					{
						if ($row['user_type'] <> USER_IGNORE)
						{
							$user_online_link = '<a href="' . append_sid("{$phpbb_url}/memberlist.$phpEx", 'mode=viewprofile&amp;u=' . $row['user_id']) . '"' . $user_colour . '>' . $user_online_link . '</a>';
						}
						else
						{
							$user_online_link = ($user_colour) ? '<span' . $user_colour . '>' . $user_online_link . '</span>' : $user_online_link;
						}

						$online_userlist .= ($online_userlist != '') ? ', ' . $user_online_link : $user_online_link;
					}
				}

				$prev_user_id = $row['user_id'];
			}
			else
			{
				// Skip multiple sessions for one user
				if ($row['session_ip'] != $prev_session_ip)
				{
					$guests_online++;
				}
			}

			$prev_session_ip = $row['session_ip'];
		}
		$db->sql_freeresult($result);

		if (!$online_userlist)
		{
			$online_userlist = $user->lang['NO_ONLINE_USERS'];
		}

		//if (empty($_REQUEST['f']))
		//if (empty($f))
		//{
			$online_userlist = $user->lang['REGISTERED_USERS'] . ' ' . $online_userlist;
		//}
		//else
		//{
			//$l_online = ($guests_online == 1) ? $user->lang['BROWSING_FORUM_GUEST'] : $user->lang['BROWSING_FORUM_GUESTS'];
			//$online_userlist = sprintf($l_online, $online_userlist, $guests_online);
		//}

		$total_online_users = $logged_visible_online + $logged_hidden_online + $guests_online;

		if ($total_online_users > $config['record_online_users'])
		{
			set_config('record_online_users', $total_online_users, true);
			set_config('record_online_date', time(), true);
		}

		// Build online listing
		$vars_online = array(
			'ONLINE'	=> array('total_online_users', 'l_t_user_s'),
			'REG'		=> array('logged_visible_online', 'l_r_user_s'),
			'HIDDEN'	=> array('logged_hidden_online', 'l_h_user_s'),
			'GUEST'		=> array('guests_online', 'l_g_user_s')
		);

		foreach ($vars_online as $l_prefix => $var_ary)
		{
			switch (${$var_ary[0]})
			{
				case 0:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_ZERO_TOTAL'];
				break;

				case 1:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USER_TOTAL'];
				break;

				default:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_TOTAL'];
				break;
			}
		}
		unset($vars_online);

		$l_online_users = sprintf($l_t_user_s, $total_online_users);
		$l_online_users .= sprintf($l_r_user_s, $logged_visible_online);
		$l_online_users .= sprintf($l_h_user_s, $logged_hidden_online);
		$l_online_users .= sprintf($l_g_user_s, $guests_online);

		$l_online_record = sprintf($user->lang['RECORD_ONLINE_USERS'], $config['record_online_users'], $user->format_date($config['record_online_date']));

		$l_online_time = ($config['load_online_time'] == 1) ? 'VIEW_ONLINE_TIME' : 'VIEW_ONLINE_TIMES';
		$l_online_time = sprintf($user->lang[$l_online_time], $config['load_online_time']);
	}
	else
	{
		$l_online_time = '';
	}

	//if ($output_method == 'array') {
	//	phpbb_load();
	//	return $return;
	//}

	$phpbb_site_img_path = $phpbb_url .'/styles/' . $user->theme['theme_path'] . '/theme/images/';
	$phpbb_site_img_file = $phpbb_site_img_path . 'icon_members.gif';

	$str = '<a href="'. $phpbb_url . '/viewonline.php"><img src="' . $phpbb_site_img_file . '" alt="' . $user->lang['WHO_IS_ONLINE'] . '" title="' . $user->lang['WHO_IS_ONLINE'] . '" /></a>';
  $strreturn = $str . ' ';

  $strreturn .= $l_online_users;
  $strreturn .= '<br />';
  $strreturn .= $online_userlist;

	//$strreturn .=  '<br />';
  /*
	foreach ($return['users'] as $user)
	{
    $str = $user['hidden'] ? '<i>' . $user['link'] . '</i>' : $user['link'];
    $str .= $user['is_last'] ? '' : ', ';
    $strreturn .= $str;
  }
  */

	if ($output_method == 'echo')
  	echo $strreturn;

  phpbb_load();

  return $strreturn;
}

// Show some basic stats
function phpbb_api_board_stats($display_mode = 0, $output_method = '')
{
	global $phpbb_connection, $phpbb_db_prefix, $phpbb_config, $phpbb_user, $phpbb_func, $phpbb_txt;
  global $db, $config, $template, $SID, $_SID, $user, $auth, $phpEx, $phpbb_root_path;

  $strreturn = "";
	if (!$phpbb_connection)
		return $strreturn;

  phpbb_save();

  // Start session management
  $user->session_begin();
  $auth->acl($user->data);
  $user->setup();

	// Generate logged in/logged out status
	/*
	if ($user->data['user_id'] == ANONYMOUS) {
    phpbb_load();
    return $strreturn;
  }
  */
	$phpbb_url = $phpbb_config['forum_url'];

  // Set some stats, get posts count from forums data if we... hum... retrieve all forums data
  $total_posts	= $config['num_posts'];
  $total_topics	= $config['num_topics'];
  $total_users	= $config['num_users'];

  $l_total_user_s = ($total_users == 0) ? 'TOTAL_USERS_ZERO' : 'TOTAL_USERS_OTHER';
  $l_total_post_s = ($total_posts == 0) ? 'TOTAL_POSTS_ZERO' : 'TOTAL_POSTS_OTHER';
  $l_total_topic_s = ($total_topics == 0) ? 'TOTAL_TOPICS_ZERO' : 'TOTAL_TOPICS_OTHER';

  $total_posts = sprintf($user->lang[$l_total_post_s], $total_posts);
	$total_topics	= sprintf($user->lang[$l_total_topic_s], $total_topics);
	$total_users = sprintf($user->lang[$l_total_user_s], $total_users);
	$newest_user = sprintf($user->lang['NEWEST_USER'], get_username_string('full', $config['newest_user_id'], $config['newest_username'], $config['newest_user_colour']));

	$phpbb_site_img_path = $phpbb_url .'/styles/' . $user->theme['theme_path'] . '/theme/images/';
	$phpbb_site_img_file = $phpbb_site_img_path . 'icon_home.gif';
  $str = '<a href="'. $phpbb_url . '/index.php"><img src="' . $phpbb_site_img_file . '" alt="' . $user->lang['HOME'] . '" title="' . $user->lang['HOME'] . '" /></a>';

  $strreturn = $str . ' ';
  $strreturn .= $total_posts;
  $strreturn .= '<br />';
  $strreturn .= $total_topics;
  $strreturn .= '<br />';

	$phpbb_site_img_file = $phpbb_site_img_path . 'icon_members.gif';
  $str = '<a href="'. $phpbb_url . '/memberlist.php"><img src="' . $phpbb_site_img_file . '" alt="' . $user->lang['MEMBERLIST'] . '" title="' . $user->lang['MEMBERLIST_EXPLAIN'] . '" /></a>';
	$strreturn .= $str . ' ';

	$strreturn .= $total_users;
  $strreturn .= '<br />';
  $strreturn .= $newest_user;

	if ($output_method == 'echo')
  	echo $strreturn;

  phpbb_load();

  return $strreturn;
}

function phpbb_api_pm($output_br = "<br />", $display_mode = 0, $output_method = '')
{
	global $phpbb_connection, $phpbb_config, $phpbb_user, $phpbb_func, $phpbb_txt;
	global $db, $config, $template, $SID, $_SID, $user, $auth, $phpEx, $phpbb_root_path;

  $strreturn = "";
	if (!$phpbb_connection)
		return $strreturn;

  phpbb_save();

  // Start session management
  $user->session_begin();
  $auth->acl($user->data);
  $user->setup();

	// Generate logged in/logged out status
	if ($user->data['user_id'] == ANONYMOUS || !isset($user->data['is_registered']) || !$user->data['is_registered']) {
    phpbb_load();
    return $strreturn;
  }

	$phpbb_url = $phpbb_config['forum_url'];

  //include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);

  $l_privmsgs_text = $l_privmsgs_text_unread = '';
	$s_privmsg_new = false;

	// Obtain number of new private messages if user is logged in
	if ($user->data['user_new_privmsg'])
	{
		$l_message_new = ($user->data['user_new_privmsg'] == 1) ? $user->lang['NEW_PM'] : $user->lang['NEW_PMS'];
		$l_privmsgs_text = sprintf($l_message_new, $user->data['user_new_privmsg']);

		if (!$user->data['user_last_privmsg'] || $user->data['user_last_privmsg'] > $user->data['session_last_visit'])
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_last_privmsg = ' . $user->data['session_last_visit'] . '
				WHERE user_id = ' . $user->data['user_id'];
			$db->sql_query($sql);

			$s_privmsg_new = true;
		}
		else
		{
			$s_privmsg_new = false;
		}
	}
	else
	{
		$l_privmsgs_text = $user->lang['NO_NEW_PM'];
		$s_privmsg_new = false;
	}

	//$tz = ($user->data['user_id'] != ANONYMOUS) ? strval(doubleval($user->data['user_timezone'])) : strval(doubleval($config['board_timezone']));
  //$current_time = sprintf($user->lang['CURRENT_TIME'], $user->format_date(time(), false, true));

	$phpbb_site_img_path = $phpbb_url .'/styles/' . $user->theme['theme_path'] . '/theme/images/';
	$phpbb_site_img_file = $phpbb_site_img_path . 'icon_ucp.gif';
  $str = '<a href="'. $phpbb_url . '/ucp.php"><img src="' . $phpbb_site_img_file . '" alt="' . $user->lang['PROFILE'] . '" title="' . $user->lang['PROFILE'] . '" /></a>';

	$strreturn = $str . ' ';

  //$str = $l_privmsgs_text;
  //$strreturn .= $str;

  $str = '<a href="'. $phpbb_url .'/ucp.php?i=pm&folder=inbox">'. $l_privmsgs_text .'</a>';
  $strreturn .= $str;
  //$str = $phpbb_txt['hello_member_ndt'] .' <b>'. $phpbb_user['name'] .'</b><br />';
  //$strreturn .= $str;

	$l_privmsgs_text_unread = '';

	if ($user->data['user_unread_privmsg'] && $user->data['user_unread_privmsg'] != $user->data['user_new_privmsg'])
	{
		$l_message_unread = ($user->data['user_unread_privmsg'] == 1) ? $user->lang['UNREAD_PM'] : $user->lang['UNREAD_PMS'];
		$l_privmsgs_text_unread = sprintf($l_message_unread, $user->data['user_unread_privmsg']);

	  //$str = '<br />'. $l_privmsgs_text_unread;
    $str = '<br />'.'<a href="'. $phpbb_url .'/ucp.php?i=pm&folder=inbox">'. $l_privmsgs_text_unread .'</a>';
    $strreturn .= $str;
	}

  if ($display_mode != 1) {
    $str = "<br />";
    $str .= "&bull; ";    
    // View your posts
    $l_pm_text = $user->lang['SEARCH_SELF'];
    $str .= '<a href="'. $phpbb_url .'/search.php?search_id=egosearch">'. $l_pm_text .'</a>';
    $strreturn .= $str;

    if ($display_mode != 2) {
      $str = $output_br;
      $str .= "&bull; ";    
      // View unanswered posts
      $l_pm_text = $user->lang['SEARCH_UNANSWERED'];
      $str .= '<a href="'. $phpbb_url .'/search.php?search_id=unanswered">'. $l_pm_text .'</a>';
      $strreturn .= $str;
      

      $str = $output_br;    
      $str .= "&bull; ";
      // View new posts
      $l_pm_text = $user->lang['SEARCH_NEW'];
      $str .= '<a href="'. $phpbb_url .'/search.php?search_id=newposts">'. $l_pm_text .'</a>';
      $strreturn .= $str;
      
      $str = $output_br;
      $str .= "&bull; ";
      // View active topics
      $l_pm_text = $user->lang['SEARCH_ACTIVE_TOPICS'];
      $str .= '<a href="'. $phpbb_url .'/search.php?search_id=active_topics">'. $l_pm_text .'</a>';
      $strreturn .= $str;
    }  
  }
  
	if ($output_method == 'echo')
  	echo $strreturn;

  phpbb_load();

  return $strreturn;
}


function phpbb_api_register($username, $password, $email, $data = array())
{
	global $phpbb_connection, $phpbb_config, $phpbb_user;

  if (!$phpbb_connection)
	  return false;

	if (empty($username) || empty($password) || empty($email) || strlen($username) > 128) {
		$phpbb_config['error_msg'] = "bad username";
		return false;
  }

	$email = strtolower($email);

	$username = utf8_normalize_nfc($username);

	phpbb_save();

	$rc = phpbb_register($username, $password, $email, $data);

	phpbb_load();

	return $rc;
}


function phpbb_api_update_user($user_id, $username = '', $password = '', $email = '', $data = array())
{
	global $phpbb_connection, $phpbb_config, $phpbb_user;

  $rc = false;

  if (!$phpbb_connection || 
      empty($user_id) || empty($phpbb_user->data['user_id']) || 
      $user_id == ANONYMOUS || $phpbb_user->data['is_bot'])
    return $rc;

  if (!empty($username))
  {
		$username = utf8_normalize_nfc($username);
    if (isset($data['username']))
   	  $data['username'] = $username;
 	  else
      $data += array('username' => $username);
  }

  if (!empty($password))
  {
 	  //$password = md5($password);

 	  if (isset($data['user_password']))
   	  $data['user_password'] = $password;
 	  else
      $data += array('user_password' => $password);
 	}

  if (!empty($email))
  {
  	$email = strtolower($email);
    
    if (isset($data['user_email']))
   	  $data['user_email'] = $email;
 	  elseif ($phpbb_user->data['user_email'] != $email)
      $data += array('user_email' => $email);
  }

  if (!empty($data))
	{
   	phpbb_save();

		$rc = phpbb_update_user_data($user_id, $data);

		phpbb_load();
	}

	return $rc;
}

function phpbb_api_login($username = '', $password = '', $autologin = true, $viewonline = 1, $admin = false)
{
	global $phpbb_connection, $phpbb_config, $user;
	global $db, $user, $template, $auth, $phpEx, $phpbb_root_path, $config;

  if (!$phpbb_connection)
	  return false;

  phpbb_save();

	$err = '';

	// Make sure user->setup() has been called
	if (empty($user->lang))
	{
		$user->setup();
	}

	// Print out error if user tries to authenticate as an administrator without having the privileges...
	if ($admin && !$auth->acl_get('a_'))
	{
		// Not authd
		// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
		if ($user->data['is_registered'])
		{
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
		}
		//trigger_error('NO_AUTH_ADMIN');
		phpbb_load();
		return false;
	}

	if (!empty($username) && !empty($password))
	{
		$viewonline = (!$viewonline) ? 0 : 1;
		$admin 		= ($admin) ? 1 : 0;
		$viewonline = ($admin) ? $user->data['session_viewonline'] : $viewonline;

		// Check if the supplied username is equal to the one stored within the database if re-authenticating
		if ($admin && utf8_clean_string($username) != utf8_clean_string($user->data['username']))
		{
			// We log the attempt to use a different username...
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
			//trigger_error('NO_AUTH_ADMIN_USER_DIFFER');
			phpbb_load();
  		return false;
		}

		// do not allow empty password
		//if (!$password)
		//{
		//	trigger_error('NO_PASSWORD_SUPPLIED');
		//}

		$result = $auth->login($username, $password, $autologin, $viewonline, $admin);

		// If admin authentication and login, we will log if it was a success or not...
		// We also break the operation on the first non-success login - it could be argued that the user already knows
		if ($admin)
		{
			if ($result['status'] == LOGIN_SUCCESS)
			{
				add_log('admin', 'LOG_ADMIN_AUTH_SUCCESS');
			}
			else
			{
				// Only log the failed attempt if a real user tried to.
				// anonymous/inactive users are never able to go to the ACP even if they have the relevant permissions
				if ($user->data['is_registered'])
				{
					add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
				}
			}
		}
		// Special cases... determine
		switch ($result['status'])
		{
			case LOGIN_SUCCESS:
				// Special case... the user is effectively banned, but we allow founders to login
				//if (defined('IN_CHECK_BAN') && $result['user_row']['user_type'] != USER_FOUNDER)
				//{
				//}

			break;

			case LOGIN_ERROR_ATTEMPTS:
/*
				// Show confirm image
				$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
					WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
						AND confirm_type = " . CONFIRM_LOGIN;
				$db->sql_query($sql);

				// Generate code
				$code = gen_rand_string(mt_rand(5, 8));
				$confirm_id = md5(unique_id($user->ip));
				$seed = hexdec(substr(unique_id(), 4, 10));

				// compute $seed % 0x7fffffff
				$seed -= 0x7fffffff * floor($seed / 0x7fffffff);

				$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'confirm_id'	=> (string) $confirm_id,
					'session_id'	=> (string) $user->session_id,
					'confirm_type'	=> (int) CONFIRM_LOGIN,
					'code'			=> (string) $code,
					'seed'			=> (int) $seed)
				);
				$db->sql_query($sql);

				$template->assign_vars(array(
					'S_CONFIRM_CODE'			=> true,
					'CONFIRM_ID'				=> $confirm_id,
					'CONFIRM_IMAGE'				=> '<img src="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=confirm&amp;id=' . $confirm_id . '&amp;type=' . CONFIRM_LOGIN) . '" alt="" title="" />',
					'L_LOGIN_CONFIRM_EXPLAIN'	=> sprintf($user->lang['LOGIN_CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>'),
				));
*/
				$err = $user->lang[$result['error_msg']];

			break;

			case LOGIN_ERROR_PASSWORD_CONVERT:
				$err = sprintf(
					$user->lang[$result['error_msg']],
					($config['email_enable']) ? '<a href="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=sendpassword') . '">' : '',
					($config['email_enable']) ? '</a>' : '',
					($config['board_contact']) ? '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">' : '',
					($config['board_contact']) ? '</a>' : ''
				);
			break;

			case LOGIN_BREAK:
				//trigger_error($result['error_msg'], E_USER_ERROR);
				phpbb_load();
    		return false;
			//break;

			// Username, password, etc...
			default:
				$err = $user->lang[$result['error_msg']];

				// Assign admin contact to some error messages
				if ($result['error_msg'] == 'LOGIN_ERROR_USERNAME' || $result['error_msg'] == 'LOGIN_ERROR_PASSWORD')
				{
					$err = (!$config['board_contact']) ? sprintf($user->lang[$result['error_msg']], '', '') : sprintf($user->lang[$result['error_msg']], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>');
				}

			break;
		}
	}
	else {
		$result = array(
			'status'	=> LOGIN_ERROR_USERNAME,
			'error_msg'	=> 'LOGIN_ERROR_USERNAME',
			'user_row'	=> array('user_id' => ANONYMOUS),
		);
		$phpbb_user->data['user_id'] = ANONYMOUS;
	}

	$phpbb_config['user'] =	$result;
	$phpbb_config['error_msg'] = $err;

  phpbb_load();

	return ($result['status'] == LOGIN_SUCCESS);
}

function phpbb_api_logout()
{
	global $phpbb_connection, $phpbb_config, $user, $auth;

  if (!$phpbb_connection)
	  return false;

  phpbb_save();

	if ($user->data['user_id'] != ANONYMOUS)
	{
		$user->session_kill();
		$user->session_begin();
    $auth->acl($user->data);
    $user->setup();
	}

  phpbb_load();
}


function phpbb_api_user_delete($user_id, $mode = 'retain', $post_username = false)
{
	global $phpbb_config, $phpbb_connection, $user;

  $return = false;

	if (!$phpbb_connection || !$user_id)
		return $return;

  phpbb_save();

  if ($user->data['user_id'] != ANONYMOUS && $user->data['user_id'] != 2 && !$user->data['is_bot'] && $user->data['user_type'] != USER_IGNORE)
	{
  	$return = phpbb_user_delete($mode, $user_id, $post_username);
	}

	phpbb_load();
	return $return;
}

function phpbb_api_user_name_validate($username)
{
	global $phpbb_config, $phpbb_connection, $config;

	if (!$phpbb_connection || empty($username))
		return false;

  phpbb_save();

  $check_data = array('username' => $username);
  $check_ary['username'] = array(
    array('string', false, $config['min_name_chars'], $config['max_name_chars']),
    array('username'),
  );
  $error = validate_data($check_data, $check_ary);
	
  phpbb_load();
	
  return !sizeof($error);
}

function phpbb_api_user_password_validate($password)
{
	global $phpbb_config, $phpbb_connection, $config;

	if (!$phpbb_connection || empty($password))
		return false;

  phpbb_save();

  $check_data = array('user_password' => $password);
  $check_ary = array(
    'user_password'				=> array(
    array('string', true, $config['min_pass_chars'], $config['max_pass_chars']),
    array('password')),
  );
  $error = validate_data($check_data, $check_ary);
	
  phpbb_load();
	
  return !sizeof($error);
}

function phpbb_api_user_email_validate($email)
{
	global $phpbb_config, $phpbb_connection, $config;

	if (!$phpbb_connection || empty($email))
		return false;

  phpbb_save();

  $check_data = array('user_email' => $email);
  $check_ary = array(
    'user_email'				=> array(
      array('string', false, 6, 60),
      array('email')),
  );
  $error = validate_data($check_data, $check_ary);
	
  phpbb_load();
	
  return !sizeof($error);
}


?>