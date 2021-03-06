<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

class CRM_ACL_API {

    /**
     * The various type of permissions
     * 
     * @var int
     */
    const
        EDIT = 1,
        VIEW = 2;


    /**
     * given a permission string, check for access requirements
     *
     * @param string $str       the permission to check
     * @param int    $contactID the contactID for whom the check is made
     *
     * @return boolean true if yes, else false
     * @static
     * @access public
     */
    static function check( $str, $contactID = null ) {
        if ( $contactID == null ) {
            $session   =& CRM_Core_Session::singleton( );
            $contactID =  $session->get( 'userID' );
        }

        if ( ! $contactID ) {
            $contactID = 0; // anonymous user
        }

        require_once 'CRM/ACL/BAO/ACL.php';
        return CRM_ACL_BAO_ACL::check( $str, $contactID );
    }

    /**
     * Get the permissioned where clause for the user
     *
     * @param int $type the type of permission needed
     * @param  array $tables (reference ) add the tables that are needed for the select clause
     * @param  array $whereTables (reference ) add the tables that are needed for the where clause
     * @param int    $contactID the contactID for whom the check is made
     *
     * @return string the group where clause for this user
     * @access public
     */
    public static function whereClause( $type, &$tables, &$whereTables, $contactID = null ) {
        // first see if the contact has edit / view all contacts
        if ( CRM_Core_Permission::check( 'edit all contacts' ) ||
             ( $type == self::VIEW &&
               CRM_Core_Permission::check( 'view all contacts' ) ) ) {
            return ' ( 1 ) ';
        }

        if ( $contactID == null ) {
            $session   =& CRM_Core_Session::singleton( );
            $contactID =  $session->get( 'userID' );
        }

        if ( ! $contactID ) {
            $contactID = 0; // anonymous user
        }

        require_once 'CRM/ACL/BAO/ACL.php';
        return CRM_ACL_BAO_ACL::whereClause( $type, $tables, $whereTables, $contactID );
    }

    /**
     * get all the groups the user has access to for the given operation
     *
     * @param int $type the type of permission needed
     * @param int    $contactID the contactID for whom the check is made
     *
     * @return array the ids of the groups for which the user has permissions
     * @access public
     */
    public static function group( $type, $contactID = null, $tableName = 'civicrm_saved_search', $allGroups = null ) {
        if ( $contactID == null ) {
            $session   =& CRM_Core_Session::singleton( );
            $contactID =  $session->get( 'userID' );
        }

        if ( ! $contactID ) {
            $contactID = 0; // anonymous user
        }

        require_once 'CRM/ACL/BAO/ACL.php';
        return CRM_ACL_BAO_ACL::group( $type, $contactID, $tableName, $allGroups );
    }

    /**
     * check if the user has access to this group for operation $type
     *
     * @param int $type the type of permission needed
     * @param int    $contactID the contactID for whom the check is made
     *
     * @return array the ids of the groups for which the user has permissions
     * @access public
     */
    public static function groupPermission( $type, $groupID, $contactID = null,
                                            $tableName = 'civicrm_saved_search',
                                            $allGroups = null ) {
        static $cache = array( );

        $key = "{$tableName}_{$type}_{$contactID}";
        if ( array_key_exists( $key, $cache ) ) {
            $groups =& $cache[$key];
        } else {
            $groups =& self::group( $type, $contactID, $tableName, $allGroups );
            $cache[$key] = $groups;
        }

        return in_array( $groupID, $groups ) ? true : false;
    }
}


