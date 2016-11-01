<?php
// $Id: drupalvb.module,v 1.11 2008/04/19 02:58:47 sun Exp $

/**
 * @file
 * Drupal vB module database functions.
 *
 * Forked from Migrator module, http://drupal.org/project/migrator
 */

/**
 * Add vB database connection and connect to remote system database.
 *
 * If remote system database connection is identical to Drupal's database
 * connection, we skip switching the connection and alter the prefix only.
 *
 * @see drupalvb_db_disconnect(), drupalvb_get()
 */
function drupalvb_db_connect() {
  global $db_url, $db_prefix;
  if (drupalvb_db_is_valid()) {
    if (!variable_get('drupalvb_db_is_default', TRUE)) {
      $drupalvb_db_url = variable_get('drupalvb_db', '');
      if (!is_array($db_url)) {
        $db_url = array('default' => $db_url);
      }
      if (!empty($drupalvb_db_url)) {
        $db_url['drupalvb'] = $drupalvb_db_url;
        db_set_active('drupalvb');
      }
    }
    drupalvb_get_default_db_prefix();
    $db_prefix = variable_get('drupalvb_db_prefix', 'vb_');
  }
}

/**
 * Disconnect from remote system database.
 *
 * @see drupalvb_db_connect(), drupalvb_get()
 */
function drupalvb_db_disconnect() {
  if (drupalvb_db_is_valid()) {
    if (!variable_get('drupalvb_db_is_default', TRUE)) {
      db_set_active();
    }
    drupalvb_set_default_db_prefix();
  }
}

/**
 * Helper function to recall Drupal's default database table prefix.
 *
 * @see drupalvb_set_default_prefix()
 */
function drupalvb_get_default_db_prefix() {
  global $db_prefix;
  static $drupal_db_prefix;
  if (!isset($drupal_db_prefix)) {
    $drupal_db_prefix = $db_prefix;
  }
  return $drupal_db_prefix;
}

/**
 * Helper function to reset Drupal's default database table prefix.
 *
 * @see drupalvb_get_default_prefix()
 */
function drupalvb_set_default_db_prefix() {
  global $db_prefix;
  $db_prefix = drupalvb_get_default_db_prefix();
}

/**
 * Check if a configured remote system database connection is valid.
 *
 * @see drupalvb_settings_system()
 */
function drupalvb_db_is_valid() {
  static $valid;

  if (isset($valid)) {
    return $valid;
  }

  $valid = FALSE;
  $connection_string = variable_get('drupalvb_db', '');
  $db = parse_url($connection_string);
  if (!empty($db['scheme']) && !empty($db['host']) && !empty($db['user']) && !empty($db['pass']) && !empty($db['path'])) {
    foreach (array('user', 'pass', 'host', 'path') as $value) {
      $db[$value] = urldecode($db[$value]);
    }
    // Drupal can't switch database layers, so we fix it if it differs from the
    // globally used type.
    if ($db['scheme'] != $GLOBALS['db_type']) {
      variable_set('drupalvb_db', preg_replace('/^'. $db['scheme'] .'/', $GLOBALS['db_type'], $connection_string));
    }
    switch ($GLOBALS['db_type']) {
      case 'mysql':
        $connection = @mysql_connect($db['host'], $db['user'], $db['pass'], TRUE, 2);
        if ($connection && mysql_select_db(substr($db['path'], 1))) {
          $valid = TRUE;
        }
        @mysql_close($connection);
        break;

      case 'mysqli':
        $connection = mysqli_init();
        @mysqli_real_connect($connection, $db['host'], $db['user'], $db['pass'], substr($db['path'], 1), $db['port'], NULL, MYSQLI_CLIENT_FOUND_ROWS);
        if ($connection) {
          $valid = TRUE;
        }
        @mysqli_close($connection);
        break;
    }
  }
  if (!$valid && user_access('administer drupalvb')) {
    drupal_set_message(t('Invalid database connection for vBulletin. Please configure the connection in <a href="!settings">Drupal vB\'s settings</a>', array('!settings' => url('admin/settings/drupalvb/database'))), 'error');
  }
  return $valid;
}

/**
 * Generic database callback for querying pre-defined data from remote system.
 *
 * @param string $op
 *   The get-operation to perform in the remote system.
 */
function drupalvb_get($op) {
  $function = 'drupalvb_get_'. $op;
  if (!function_exists($function)) {
    return FALSE;
  }
  drupalvb_db_connect();
  $args = func_get_args();
  array_shift($args);
  $result = call_user_func_array($function, $args);
  drupalvb_db_disconnect();
  return $result;
}

/**
 * Query remote system database.
 *
 * @see db_query()
 */
function drupalvb_db_query($query) {
  drupalvb_db_connect();

  $args = func_get_args();
  array_shift($args);
  $query = db_prefix_tables($query);
  if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
    $args = $args[0];
  }
  _db_query_callback($args, TRUE);
  $query = preg_replace_callback(DB_QUERY_REGEXP, '_db_query_callback', $query);
  $result = _db_query($query);
  
  drupalvb_db_disconnect();
  return $result;
}

/**
 * Query remote system database with range.
 *
  * @see db_query_range
 */
function drupalvb_db_query_range($query) {
  drupalvb_db_connect();
  
  $args = func_get_args();
  $count = array_pop($args);
  $from = array_pop($args);
  array_shift($args);

  $query = db_prefix_tables($query);
  if (isset($args[0]) and is_array($args[0])) { // 'All arguments in one array' syntax
    $args = $args[0];
  }
  _db_query_callback($args, TRUE);
  $query = preg_replace_callback(DB_QUERY_REGEXP, '_db_query_callback', $query);
  $query .= ' LIMIT '. (int)$count .' OFFSET '. (int)$from;
  $result = _db_query($query);
  
  drupalvb_db_disconnect();
  return $result;
}

/**
 * Return the last insert id.
 *
 * Borrowed from Drupal 6.
 */
function drupalvb_db_last_insert_id($table, $field) {
  return db_result(drupalvb_db_query('SELECT LAST_INSERT_ID()'));
}

/**
 * Initialize DrupalvB's user mapping table upon installation.
 *
 * Note: We can't do this in a single query, because Drupal's and vB's tables
 * need not to be in the same database.
 *
 * @see drupalvb.admin-pages.inc
 */
function _drupalvb_init_user_map() {
  $users = $vbusers = array();
  // Fetch all users in Drupal.
  $result = db_query("SELECT uid, name FROM {users}");
  while ($user = db_fetch_array($result)) {
    $users[$user['name']] = $user['uid'];
  }
  // Fetch all users in vBulletin.
  $result = drupalvb_db_query("SELECT userid, username FROM {user}");
  // Insert all vB users who already exist in Drupal with corresponding username
  // into our mapping table.
  while ($vbuser = db_fetch_array($result)) {
    if (isset($users[$vbuser['username']])) {
      db_query("INSERT INTO {drupalvb_users} (uid, userid) VALUES (%d, %d)", $users[$vbuser['username']], $vbuser['userid']);
    }
  }
}

