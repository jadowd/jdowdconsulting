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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Contact/BAO/Contact.php';


/**
 * This class is used to browse past mailings.
 */
class CRM_Mailing_Selector_Browse   extends CRM_Core_Selector_Base 
                                    implements CRM_Core_Selector_API 
{
    /**
     * array of supported links, currenly null
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;

    protected $_parent;

    /**
     * Class constructor
     *
     * @param
     *
     * @return CRM_Contact_Selector_Profile
     * @access public
     */
    function __construct( )
    {
    }//end of constructor


    /**
     * This method returns the links that are given for each search row.
     *
     * @return array
     * @access public
     *
     */
    static function &links()
    {
        return self::$_links;
    } //end of function


    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;
        $params['status']       = ts('Mailings %%StatusMessage%%');
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }//end of function


    /**
     * returns the column headers as an array of tuples:
     * (name, sortName (key to the sort array))
     *
     * @param string $action the action being performed
     * @param enum   $output what should the result set include (web/email/csv)
     *
     * @return array the column headers that need to be displayed
     * @access public
     */
    function &getColumnHeaders($action = null, $output = null) 
    {
        require_once 'CRM/Mailing/BAO/Mailing.php';
        require_once 'CRM/Mailing/BAO/Job.php';
        $mailing = CRM_Mailing_BAO_Mailing::getTableName();
        $job = CRM_Mailing_BAO_Job::getTableName();
        if ( ! isset( self::$_columnHeaders ) ) {
            
            self::$_columnHeaders = array( 
                                          array(
                                                'name'  => ts('Mailing Name'),
                                                'sort'      => 'name',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ), 
                                          array(
                                                'name' => ts('Status'),
                                                'sort'      => 'status',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ), 
                                          array(
                                                'name' => ts('Scheduled Date'),
                                                'sort'      => 'scheduled_date',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ), 
                                          array(
                                                'name' => ts('Start Date'),
                                                'sort'      => 'start_date',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ), 
                                          array(
                                                'name' => ts('Completed Date'),
                                                'sort'      => 'end_date',
                                                'direction' => CRM_Utils_Sort::DESCENDING,
                                                ), 
            );
            if ($output != CRM_Core_Selector_Controller::EXPORT) {
                self::$_columnHeaders[] = array('name' => ts('Action'));
            }
        }
        return self::$_columnHeaders;
    }


    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        $params      = array( );
        $whereClause = $this->whereClause( $params );
        $query = "
SELECT count(civicrm_mailing.id)
  FROM civicrm_mailing
     LEFT JOIN civicrm_mailing_job ON (civicrm_mailing.id = civicrm_mailing_job.mailing_id AND civicrm_mailing_job.is_test = 0)
   AND $whereClause";
        return CRM_Core_DAO::singleValueQuery( $query, $params );
    }

    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $output   what should the result set include (web/email/csv)
     *
     * @return int   the total number of rows for this action
     */
    function &getRows($action, $offset, $rowCount, $sort, $output = null) {
        static $actionLinks = null;
        if (empty($actionLinks)) {
            $cancelExtra = ts('Are you sure you want to cancel this mailing?');
            $deleteExtra = ts('Are you sure you want to delete this mailing?');
            $actionLinks = array(
                CRM_Core_Action::VIEW => array(
                    'name'  => ts('Report'),
                    'url'   => 'civicrm/mailing/report',
                    'qs'    => 'mid=%%mid%%&reset=1',
                    'title' => ts('View Mailing Report')
                    ),
                CRM_Core_Action::UPDATE => array(
                    'name'  => ts('Re-Use'),
                    'url'   => 'civicrm/mailing/send',
                    'qs'    => 'mid=%%mid%%&reset=1',
                    'title' => ts('Re-Send Mailing')
                    ),
                CRM_Core_Action::DISABLE => array(
                    'name'  => ts('Cancel'),
                    'url'   => 'civicrm/mailing/browse',
                    'qs'    => 'action=disable&mid=%%mid%%&reset=1',
                    'extra' => 'onclick="if (confirm(\''. $cancelExtra .'\')) this.href+=\'&amp;confirmed=1\'; else return false;"',
                    'title' => ts('Cancel Mailing')
                    ),
                CRM_Core_Action::PREVIEW => array(
                    'name'  => ts('Continue'),
                    'url'   => 'civicrm/mailing/send',
                    'qs'    => 'mid=%%mid%%&continue=true&reset=1',
                    'title' => ts('Continue Mailing')                    
                    ),
                CRM_Core_Action::DELETE => array(
                    'name'  => ts('Delete'),
                    'url'   => 'civicrm/mailing/browse',
                    'qs'    => 'action=delete&mid=%%mid%%&reset=1',
                    'extra' => 'onclick="if (confirm(\''. $deleteExtra .'\')) this.href+=\'&amp;confirmed=1\'; else return false;"',
                    'title' => ts('Delete Mailing')                    
                    )
            );
        }

        
        $mailing =& new CRM_Mailing_BAO_Mailing();
        
        $params = array( );
        $whereClause = ' AND ' . $this->whereClause( $params );
        $rows =& $mailing->getRows($offset, $rowCount, $sort, $whereClause, $params );

        if ($output != CRM_Core_Selector_Controller::EXPORT) {
            foreach ($rows as $key => $row) {
                if (!($row['status'] == 'Not scheduled')) {
                    $actionMask = CRM_Core_Action::VIEW;
                    $actionMask |= CRM_Core_Action::UPDATE;
                } else {
                    $actionMask = CRM_Core_Action::PREVIEW;
                }
                if (in_array($row['status'], array('Scheduled', 'Running', 'Paused'))) {
                    $actionMask |= CRM_Core_Action::DISABLE;
                }
                $actionMask |= CRM_Core_Action::DELETE;
               
                $rows[$key]['action'] = 
                    CRM_Core_Action::formLink(  $actionLinks,
                                                $actionMask,
                                                array('mid' => $row['id']));
                //unset($rows[$key]['id']);
                // if the scheduled date is 0, replace it with an empty string
                if ($rows[$key]['scheduled_iso'] == '0000-00-00 00:00:00') {
                    $rows[$key]['scheduled'] = '';
                }
                unset($rows[$key]['scheduled_iso']);
            }
        }

        // also initialize the AtoZ pager
        $this->pagerAtoZ( );
        return $rows;
        
    }

    /**
     * name of export file.
     *
     * @param string $output type of output
     * @return string name of the file
     */
    function getExportFileName( $output = 'csv') {
        return ts('CiviMail Mailings');
    }

    function setParent( $parent ) {
        $this->_parent = $parent;
    }

    function whereClause( &$params, $sortBy = true ) {
        $values =  array( );

        $clauses = array( );
        $title   = $this->_parent->get( 'mailing_name' );

        if ( $title ) {
            $clauses[] = 'name LIKE %1';
            if ( strpos( $title, '%' ) !== false ) {
                $params[1] = array( $title, 'String', false );
            } else {
                $params[1] = array( $title, 'String', true );
            }
        }

        require_once 'CRM/Utils/Date.php';

        $from = $this->_parent->get( 'mailing_from' );
        if ( ! CRM_Utils_System::isNull( $from ) ) {
            $from = CRM_Utils_date::format( $from );
            $from .= '000000';
            $clauses[] = 'start_date >= %2';
            $params[2] = array( $from, 'String' );
        }

        $to = $this->_parent->get( 'mailing_to' );
        if ( ! CRM_Utils_System::isNull( $to ) ) {
            $to = CRM_Utils_date::format( $to );
            $to .= '235959';
            $clauses[] = 'start_date <= %3';
            $params[3] = array( $to, 'String' );
        }

        if ( $sortBy &&
             $this->_parent->_sortByCharacter ) {
            $clauses[] = 'name LIKE %3';
            $params[3] = array( $this->_parent->_sortByCharacter . '%', 'String' );
        }

        // dont do a the below assignement when doing a 
        // AtoZ pager clause
        if ( $sortBy ) {
            if ( count( $clauses ) > 1 ) {
                $this->_parent->assign( 'isSearch', 1 );
            } else {
                $this->_parent->assign( 'isSearch', 0 );
            }
        }
 
        if ( empty( $clauses ) ) {
            return 1;
        }

        return implode( ' AND ', $clauses );
    }

    function pagerAtoZ( ) {
        require_once 'CRM/Utils/PagerAToZ.php';
        
        $params      = array( );
        $whereClause = $this->whereClause( $params, false );
        
        $query = "
   SELECT DISTINCT UPPER(LEFT(name, 1)) as sort_name
     FROM civicrm_mailing, civicrm_mailing_job
    WHERE civicrm_mailing.id = civicrm_mailing_job.mailing_id
      AND $whereClause
 ORDER BY LEFT(name, 1)
";
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        
        $aToZBar = CRM_Utils_PagerAToZ::getAToZBar( $dao, $this->_parent->_sortByCharacter, true );
        $this->_parent->assign( 'aToZ', $aToZBar );
    }
    
}//end of class


