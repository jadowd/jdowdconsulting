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


require_once('CRM/Core/Payment/Google.php');
require_once('Google/library/googlecart.php');
require_once('Google/library/googleitem.php');

class CRM_Event_Payment_Google extends CRM_Core_Payment_Google { 
    /** 
     * We only need one instance of this object. So we use the singleton 
     * pattern and cache the instance in this variable 
     * 
     * @var object 
     * @static 
     */ 
    static private $_singleton = null; 
    
    /** 
     * Constructor 
     *
     * @param string $mode the mode of operation: live or test
     * 
     * @return void 
     */ 
    function __construct( $mode, &$paymentProcessor ) {
        parent::__construct( $mode, $paymentProcessor );
    }

    /** 
     * singleton function used to manage this object 
     * 
     * @param string $mode the mode of operation: live or test
 
     * @return object 
     * @static 
     * 
     */ 
    static function &singleton( $mode, &$paymentProcessor ) {
        if (self::$_singleton === null ) { 
            self::$_singleton =& new CRM_Event_Payment_Google( $mode, $paymentProcessor );
        } 
        return self::$_singleton; 
    } 

    /**  
     * Sets appropriate parameters for checking out to google
     *  
     * @param array $params  name value pair of contribution datat
     *  
     * @return void  
     * @access public 
     *  
     */  
    function doTransferCheckout( &$params ) {
        parent::doTransferCheckout( $params, 'event' );
    }

}


