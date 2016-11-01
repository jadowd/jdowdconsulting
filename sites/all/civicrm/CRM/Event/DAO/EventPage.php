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
class CRM_Event_DAO_EventPage extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_event_page';
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
     * Event Page ID
     *
     * @var int unsigned
     */
    public $id;
    /**
     * Event which this page belongs to.
     *
     * @var int unsigned
     */
    public $event_id;
    /**
     * Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.
     *
     * @var text
     */
    public $intro_text;
    /**
     * Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.
     *
     * @var text
     */
    public $footer_text;
    /**
     * Title for Confirmation page.
     *
     * @var string
     */
    public $confirm_title;
    /**
     * Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.
     *
     * @var text
     */
    public $confirm_text;
    /**
     * Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.
     *
     * @var text
     */
    public $confirm_footer_text;
    /**
     * If true, confirmation is automatically emailed to contact on successful registration.
     *
     * @var boolean
     */
    public $is_email_confirm;
    /**
     * text to include above standard event info on confirmation email. emails are text-only, so do not allow html for now
     *
     * @var text
     */
    public $confirm_email_text;
    /**
     * FROM email name used for confirmation emails.
     *
     * @var string
     */
    public $confirm_from_name;
    /**
     * FROM email address used for confirmation emails.
     *
     * @var string
     */
    public $confirm_from_email;
    /**
     * comma-separated list of email addresses to cc each time a confirmation is sent
     *
     * @var string
     */
    public $cc_confirm;
    /**
     * comma-separated list of email addresses to bcc each time a confirmation is sent
     *
     * @var string
     */
    public $bcc_confirm;
    /**
     * FK to civicrm_option_value.
     *
     * @var int unsigned
     */
    public $default_fee_id;
    /**
     * FK to civicrm_option_value.
     *
     * @var int unsigned
     */
    public $default_discount_id;
    /**
     * Title for ThankYou page.
     *
     * @var string
     */
    public $thankyou_title;
    /**
     * ThankYou Text.
     *
     * @var text
     */
    public $thankyou_text;
    /**
     * Footer message.
     *
     * @var text
     */
    public $thankyou_footer_text;
    /**
     * if true - allows the user to send payment directly to the org later
     *
     * @var boolean
     */
    public $is_pay_later;
    /**
     * The text displayed to the user in the main form
     *
     * @var text
     */
    public $pay_later_text;
    /**
     * The receipt sent to the user instead of the normal receipt text
     *
     * @var text
     */
    public $pay_later_receipt;
    /**
     * if true - allows the user to register multiple participants for event
     *
     * @var boolean
     */
    public $is_multiple_registrations;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_event_page
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
                'event_id' => 'civicrm_event:id',
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
                'event_id' => array(
                    'name' => 'event_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Event ID') ,
                    'required' => true,
                ) ,
                'intro_text' => array(
                    'name' => 'intro_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Introductory Message') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'footer_text' => array(
                    'name' => 'footer_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Footer Message') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'confirm_title' => array(
                    'name' => 'confirm_title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Confirmation Title') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'confirm_text' => array(
                    'name' => 'confirm_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Confirm Text') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'confirm_footer_text' => array(
                    'name' => 'confirm_footer_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Footer Text') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'is_email_confirm' => array(
                    'name' => 'is_email_confirm',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Is confirm email') ,
                ) ,
                'confirm_email_text' => array(
                    'name' => 'confirm_email_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Confirmation Email Text') ,
                    'rows' => 4,
                    'cols' => 50,
                ) ,
                'confirm_from_name' => array(
                    'name' => 'confirm_from_name',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Confirm From Name') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'confirm_from_email' => array(
                    'name' => 'confirm_from_email',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Confirm From Email') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'cc_confirm' => array(
                    'name' => 'cc_confirm',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Cc Confirm') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'bcc_confirm' => array(
                    'name' => 'bcc_confirm',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Bcc Confirm') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'default_fee_id' => array(
                    'name' => 'default_fee_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'default_discount_id' => array(
                    'name' => 'default_discount_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'thankyou_title' => array(
                    'name' => 'thankyou_title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('ThankYou Title') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'thankyou_text' => array(
                    'name' => 'thankyou_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('ThankYou Text') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'thankyou_footer_text' => array(
                    'name' => 'thankyou_footer_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Footer Text') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'is_pay_later' => array(
                    'name' => 'is_pay_later',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'pay_later_text' => array(
                    'name' => 'pay_later_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Pay Later Text') ,
                ) ,
                'pay_later_receipt' => array(
                    'name' => 'pay_later_receipt',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Pay Later Receipt') ,
                ) ,
                'is_multiple_registrations' => array(
                    'name' => 'is_multiple_registrations',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
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
                        self::$_import['event_page'] = &$fields[$name];
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
                        self::$_export['event_page'] = &$fields[$name];
                    } else {
                        self::$_export[$name] = &$fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
}
