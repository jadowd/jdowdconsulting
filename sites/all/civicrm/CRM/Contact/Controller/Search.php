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

require_once 'CRM/Core/Controller.php';
require_once 'CRM/Core/Session.php';

/**
 * This class is used by the Search functionality.
 *
 *  - the search controller is used for building/processing multiform
 *    searches.
 *
 * Typically the first form will display the search criteria and it's results
 *
 * The second form is used to process search results with the asscociated actions
 *
 */

class CRM_Contact_Controller_Search extends CRM_Core_Controller {

    /**
     * class constructor
     */
    function __construct( $title = null, $modal = true, $action = CRM_Core_Action::NONE ) {
        require_once 'CRM/Contact/StateMachine/Search.php';

        parent::__construct( $title, $modal );

        $this->_stateMachine =& new CRM_Contact_StateMachine_Search( $this, $action );

        // create and instantiate the pages
        $this->addPages( $this->_stateMachine, $action );

        require_once 'CRM/Core/BAO/File.php';

        $session =& CRM_Core_Session::singleton( );
        $uploadNames = $session->get( 'uploadNames' );
        if ( ! empty( $uploadNames ) ) {
            $uploadNames = array_merge( $uploadNames,
                                        CRM_Core_BAO_File::uploadNames( ) );
            
        } else {
            $uploadNames = CRM_Core_BAO_File::uploadNames( );
        }

        $config  =& CRM_Core_Config::singleton( );
        $uploadDir = $config->uploadDir;

        // add all the actions
        $this->addActions( $uploadDir, $uploadNames );
    }

    public function selectorName( ) {
        return $this->get( 'selectorName' );
    }

}


