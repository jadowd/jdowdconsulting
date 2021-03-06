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
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Core_DAO_UFField extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_uf_field';
    /**
     * static instance to hold the field values
     *
     * @var array
     * @static
     */
    static $_fields = null;
    /**
     * static instance to hold the FK relationships
     *
     * @var string
     * @static
     */
    static $_links = null;
    /**
     * static instance to hold the values that can
     * be imported / apu
     *
     * @var array
     * @static
     */
    static $_import = null;
    /**
     * static instance to hold the values that can
     * be exported / apu
     *
     * @var array
     * @static
     */
    static $_export = null;
    /**
     * static value to see if we should log any modifications to
     * this table in the civicrm_log table
     *
     * @var boolean
     * @static
     */
    static $_log = false;
    /**
     * Unique table ID
     *
     * @var int unsigned
     */
    public $id;
    /**
     * Which form does this field belong to.
     *
     * @var int unsigned
     */
    public $uf_group_id;
    /**
     * Name for CiviCRM field which is being exposed for sharing.
     *
     * @var string
     */
    public $field_name;
    /**
     * Is this field currently shareable? If false, hide the field for all sharing contexts.
     *
     * @var boolean
     */
    public $is_active;
    /**
     * the field is view only and not editable in user forms.
     *
     * @var boolean
     */
    public $is_view;
    /**
     * Is this field required when included in a user or registration form?
     *
     * @var boolean
     */
    public $is_required;
    /**
     * Controls field display order when user framework fields are displayed in registration and account editing forms.
     *
     * @var int
     */
    public $weight;
    /**
     * Description and/or help text to display after this field.
     *
     * @var text
     */
    public $help_post;
    /**
     * In what context(s) is this field visible.
     *
     * @var enum('User and User Admin Only', 'Public User Pages', 'Public User Pages and Listings')
     */
    public $visibility;
    /**
     * Is this field included as a column in the selector table?
     *
     * @var boolean
     */
    public $in_selector;
    /**
     * Is this field included search form of profile?
     *
     * @var boolean
     */
    public $is_searchable;
    /**
     * Location type of this mapping, if required
     *
     * @var int unsigned
     */
    public $location_type_id;
    /**
     * Phone type, if required
     *
     * @var string
     */
    public $phone_type;
    /**
     * To save label for fields.
     *
     * @var string
     */
    public $label;
    /**
     * This field saves field type (ie individual,household.. field etc).
     *
     * @var string
     */
    public $field_type;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_uf_field
     */
    function __construct() 
    {
        parent::__construct();
    }
    /**
     * return foreign links
     *
     * @access public
     * @return array
     */
    function &links() 
    {
        if (!(self::$_links)) {
            self::$_links = array(
                'uf_group_id' => 'civicrm_uf_group:id',
                'location_type_id' => 'civicrm_location_type:id',
            );
        }
        return self::$_links;
    }
    /**
     * returns all the column names of this table
     *
     * @access public
     * @return array
     */
    function &fields() 
    {
        if (!(self::$_fields)) {
            self::$_fields = array(
                'id' => array(
                    'name' => 'id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                ) ,
                'uf_group_id' => array(
                    'name' => 'uf_group_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                ) ,
                'field_name' => array(
                    'name' => 'field_name',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Field Name') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'is_active' => array(
                    'name' => 'is_active',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'is_view' => array(
                    'name' => 'is_view',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'is_required' => array(
                    'name' => 'is_required',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'weight' => array(
                    'name' => 'weight',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Weight') ,
                    'required' => true,
                ) ,
                'help_post' => array(
                    'name' => 'help_post',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Help Post') ,
                ) ,
                'visibility' => array(
                    'name' => 'visibility',
                    'type' => CRM_Utils_Type::T_ENUM,
                    'title' => ts('Visibility') ,
                ) ,
                'in_selector' => array(
                    'name' => 'in_selector',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('In Selector') ,
                ) ,
                'is_searchable' => array(
                    'name' => 'is_searchable',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'location_type_id' => array(
                    'name' => 'location_type_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'phone_type' => array(
                    'name' => 'phone_type',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Phone Type') ,
                    'maxlength' => 64,
                    'size' => CRM_Utils_Type::BIG,
                ) ,
                'label' => array(
                    'name' => 'label',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Label') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'field_type' => array(
                    'name' => 'field_type',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Field Type') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
            );
        }
        return self::$_fields;
    }
    /**
     * returns the names of this table
     *
     * @access public
     * @return string
     */
    function getTableName() 
    {
        global $dbLocale;
        return self::$_tableName . $dbLocale;
    }
    /**
     * returns if this table needs to be logged
     *
     * @access public
     * @return boolean
     */
    function getLog() 
    {
        return self::$_log;
    }
    /**
     * returns the list of fields that can be imported
     *
     * @access public
     * return array
     */
    function &import($prefix = false) 
    {
        if (!(self::$_import)) {
            self::$_import = array();
            $fields = &self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('import', $field)) {
                    if ($prefix) {
                        self::$_import['uf_field'] = &$fields[$name];
                    } else {
                        self::$_import[$name] = &$fields[$name];
                    }
                }
            }
        }
        return self::$_import;
    }
    /**
     * returns the list of fields that can be exported
     *
     * @access public
     * return array
     */
    function &export($prefix = false) 
    {
        if (!(self::$_export)) {
            self::$_export = array();
            $fields = &self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('export', $field)) {
                    if ($prefix) {
                        self::$_export['uf_field'] = &$fields[$name];
                    } else {
                        self::$_export[$name] = &$fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
    /**
     * returns an array containing the enum fields of the civicrm_uf_field table
     *
     * @return array (reference)  the array of enum fields
     */
    static function &getEnums() 
    {
        static $enums = array(
            'visibility',
        );
        return $enums;
    }
    /**
     * returns a ts()-translated enum value for display purposes
     *
     * @param string $field  the enum field in question
     * @param string $value  the enum value up for translation
     *
     * @return string  the display value of the enum
     */
    static function tsEnum($field, $value) 
    {
        static $translations = null;
        if (!$translations) {
            $translations = array(
                'visibility' => array(
                    'User and User Admin Only' => ts('User and User Admin Only') ,
                    'Public User Pages' => ts('Public User Pages') ,
                    'Public User Pages and Listings' => ts('Public User Pages and Listings') ,
                ) ,
            );
        }
        return $translations[$field][$value];
    }
    /**
     * adds $value['foo_display'] for each $value['foo'] enum from civicrm_uf_field
     *
     * @param array $values (reference)  the array up for enhancing
     * @return void
     */
    static function addDisplayEnums(&$values) 
    {
        $enumFields = &CRM_Core_DAO_UFField::getEnums();
        foreach($enumFields as $enum) {
            if (isset($values[$enum])) {
                $values[$enum . '_display'] = CRM_Core_DAO_UFField::tsEnum($enum, $values[$enum]);
            }
        }
    }
}
