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
class CRM_Event_DAO_Event extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_event';
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
     * Event
     *
     * @var int unsigned
     */
    public $id;
    /**
     * Event Title (e.g. Fall Fundraiser Dinner)
     *
     * @var string
     */
    public $title;
    /**
     * Brief summary of event. Text and html allowed. Displayed on Event Registration form and can be used on other CMS pages which need an event summary.
     *
     * @var text
     */
    public $summary;
    /**
     * Full description of event. Text and html allowed. Displayed on built-in Event Information screens.
     *
     * @var text
     */
    public $description;
    /**
     * Event Type ID.Implicit FK to civicrm_option_value where option_group = event_type.
     *
     * @var int unsigned
     */
    public $event_type_id;
    /**
     * Should we expose the participant list? Implicit FK to civicrm_option_value where option_group = participant_listing.
     *
     * @var int unsigned
     */
    public $participant_listing_id;
    /**
     * Public events will be included in the iCal feeds. Access to private event information may be limited using ACLs.
     *
     * @var boolean
     */
    public $is_public;
    /**
     * Date and time that event starts.
     *
     * @var datetime
     */
    public $start_date;
    /**
     * Date and time that event ends. May be NULL if no defined end date/time
     *
     * @var datetime
     */
    public $end_date;
    /**
     * If true, include registration link on Event Info page.
     *
     * @var boolean
     */
    public $is_online_registration;
    /**
     * Text for link to Event Registration form which is displayed on Event Information screen when is_online_registration is true.
     *
     * @var string
     */
    public $registration_link_text;
    /**
     * Date and time that online registration starts.
     *
     * @var datetime
     */
    public $registration_start_date;
    /**
     * Date and time that online registration ends.
     *
     * @var datetime
     */
    public $registration_end_date;
    /**
     * Maximum number of registered participants to allow. After max is reached, a custom Event Full message is displayed. If NULL, allow unlimited number of participants.
     *
     * @var int unsigned
     */
    public $max_participants;
    /**
     * Message to display on Event Information page and INSTEAD OF Event Registration form if maximum participants are signed up. Can include email address/info about getting on a waiting list, etc. Text and html allowed.
     *
     * @var text
     */
    public $event_full_text;
    /**
     * Is this a PAID event? If true, one or more fee amounts must be set and a Payment Processor must be configured for Online Event Registration.
     *
     * @var boolean
     */
    public $is_monetary;
    /**
     * Contribution type assigned to paid event registrations for this event. Required if is_monetary is true.
     *
     * @var int unsigned
     */
    public $contribution_type_id;
    /**
     * Payment Processor for this Event (if is_monetary is true)
     *
     * @var int unsigned
     */
    public $payment_processor_id;
    /**
     * Include a map block on the Event Information page when geocode info is available and a mapping provider has been specified?
     *
     * @var boolean
     */
    public $is_map;
    /**
     * Is this Event enabled or disabled/cancelled?
     *
     * @var boolean
     */
    public $is_active;
    /**
     *
     * @var string
     */
    public $fee_label;
    /**
     * If true, show event location.
     *
     * @var boolean
     */
    public $is_show_location;
    /**
     * FK to Location Block ID
     *
     * @var int unsigned
     */
    public $loc_block_id;
    /**
     * Participant role ID. Implicit FK to civicrm_option_value where option_group = participant_role.
     *
     * @var int unsigned
     */
    public $default_role_id;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_event
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
                'payment_processor_id' => 'civicrm_payment_processor:id',
                'loc_block_id' => 'civicrm_loc_block:id',
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
                'event_title' => array(
                    'name' => 'title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Event Title') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'import' => true,
                    'where' => 'civicrm_event.title',
                    'headerPattern' => '/^title$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'summary' => array(
                    'name' => 'summary',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Event Summary') ,
                    'rows' => 4,
                    'cols' => 60,
                ) ,
                'event_description' => array(
                    'name' => 'description',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Event Description') ,
                    'rows' => 8,
                    'cols' => 60,
                ) ,
                'event_type_id' => array(
                    'name' => 'event_type_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Event Type ID') ,
                ) ,
                'participant_listing_id' => array(
                    'name' => 'participant_listing_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Participant Listing ID') ,
                ) ,
                'is_public' => array(
                    'name' => 'is_public',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Is Event Public') ,
                ) ,
                'event_start_date' => array(
                    'name' => 'start_date',
                    'type' => CRM_Utils_Type::T_DATE+CRM_Utils_Type::T_TIME,
                    'title' => ts('Event Start Date') ,
                    'import' => true,
                    'where' => 'civicrm_event.start_date',
                    'headerPattern' => '/^start|(s(tart\s)?date)$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'event_end_date' => array(
                    'name' => 'end_date',
                    'type' => CRM_Utils_Type::T_DATE+CRM_Utils_Type::T_TIME,
                    'title' => ts('Event End Date') ,
                    'import' => true,
                    'where' => 'civicrm_event.end_date',
                    'headerPattern' => '/^end|(e(nd\s)?date)$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'is_online_registration' => array(
                    'name' => 'is_online_registration',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Is Online Registration') ,
                ) ,
                'registration_link_text' => array(
                    'name' => 'registration_link_text',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Event Registration Link Text') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'registration_start_date' => array(
                    'name' => 'registration_start_date',
                    'type' => CRM_Utils_Type::T_DATE+CRM_Utils_Type::T_TIME,
                    'title' => ts('Registration Start Date') ,
                ) ,
                'registration_end_date' => array(
                    'name' => 'registration_end_date',
                    'type' => CRM_Utils_Type::T_DATE+CRM_Utils_Type::T_TIME,
                    'title' => ts('Registration End Date') ,
                ) ,
                'max_participants' => array(
                    'name' => 'max_participants',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Max Participants') ,
                ) ,
                'event_full_text' => array(
                    'name' => 'event_full_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Event Information') ,
                    'rows' => 4,
                    'cols' => 60,
                ) ,
                'is_monetary' => array(
                    'name' => 'is_monetary',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'contribution_type_id' => array(
                    'name' => 'contribution_type_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'payment_processor_id' => array(
                    'name' => 'payment_processor_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'is_map' => array(
                    'name' => 'is_map',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'is_active' => array(
                    'name' => 'is_active',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'fee_label' => array(
                    'name' => 'fee_label',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Fee Label') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'import' => true,
                    'where' => 'civicrm_event.fee_label',
                    'headerPattern' => '/^fee|(f(ee\s)?label)$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'is_show_location' => array(
                    'name' => 'is_show_location',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('show location') ,
                ) ,
                'loc_block_id' => array(
                    'name' => 'loc_block_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'default_role_id' => array(
                    'name' => 'default_role_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Participant Role') ,
                    'import' => true,
                    'where' => 'civicrm_event.default_role_id',
                    'headerPattern' => '',
                    'dataPattern' => '',
                    'export' => true,
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
                        self::$_import['event'] = &$fields[$name];
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
                        self::$_export['event'] = &$fields[$name];
                    } else {
                        self::$_export[$name] = &$fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
}
