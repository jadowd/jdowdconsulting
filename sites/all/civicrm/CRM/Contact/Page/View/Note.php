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

require_once 'CRM/Contact/Page/View.php';

/**
 * Main page for viewing Notes.
 *
 */
class CRM_Contact_Page_View_Note extends CRM_Contact_Page_View 
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * View details of a note
     *
     * @return void
     * @access public
     */
    function view( ) {
      
        $note =& new CRM_Core_DAO_Note( );
        $note->id = $this->_id;
        if ( $note->find( true ) ) {
            $values = array( );
            CRM_Core_DAO::storeValues( $note, $values );
            $this->assign( 'note', $values );
        }
       
    }

    /**
     * This function is called when action is browse
     *
     * return null
     * @access public
     */
    function browse( ) {
        $note =& new CRM_Core_DAO_Note( );
        $note->entity_table = 'civicrm_contact';
        $note->entity_id    = $this->_contactId;

        $note->orderBy( 'modified_date desc' );

        $values =  array( );
        $links  =& self::links( );
        $action = array_sum(array_keys($links)) & CRM_Core_Action::mask( $this->_permission );
        
        $note->find( );
        while ( $note->fetch( ) ) {
          
            $values[$note->id] = array( );
            CRM_Core_DAO::storeValues( $note, $values[$note->id] );
            $values[$note->id]['action'] = CRM_Core_Action::formLink( $links,
                                                                      $action,
                                                                      array( 'id'  => $note->id,
                                                                             'cid' => $this->_contactId ) );
            $contact =  new CRM_Contact_DAO_Contact( );
            $contact->id = $note->contact_id;
            $contact->find();
            $contact->fetch();
            $values[$note->id]['createdBy'] = $contact->display_name;
            
        }
       
        $this->assign( 'notes', $values );
    }

    /**
     * This function is called when action is update or new
     * 
     * return null
     * @access public
     */
    function edit( ) {
       

        $controller =& new CRM_Core_Controller_Simple( 'CRM_Note_Form_Note', ts('Contact Notes'), $this->_action );
        $controller->setEmbedded( true );

        // set the userContext stack
        $session =& CRM_Core_Session::singleton();
        $url = CRM_Utils_System::url('civicrm/contact/view', 'action=browse&selectedChild=note&cid=' . $this->_contactId );
        $session->pushUserContext( $url );

        if (CRM_Utils_Request::retrieve('confirmed', 'Boolean',
                                        CRM_Core_DAO::$_nullObject )) {
            CRM_Core_BAO_Note::del( $this->_id);
            CRM_Utils_System::redirect($url);
        }

        $controller->reset( );
        $controller->set( 'entityTable', 'civicrm_contact' );
        $controller->set( 'entityId'   , $this->_contactId );
        $controller->set( 'id'         , $this->_id );

        $controller->process( );
        $controller->run( );
    }

    /**
     * This function is the main function that is called when the page loads,
     * it decides the which action has to be taken for the page.
     *
     * return null
     * @access public
     */
    function run( ) {
        $this->preProcess( );

        if ( $this->_action & CRM_Core_Action::VIEW ) {
            $this->view( );
        } else if ( $this->_action & ( CRM_Core_Action::UPDATE | CRM_Core_Action::ADD ) ) {
            $this->edit( );
        } else if ( $this->_action & CRM_Core_Action::DELETE ) {
            // we use the edit screen the confirm the delete
            $this->edit( );
        }

        $this->browse( );
        return parent::run( );
    }

    /**
     * delete the note object from the db
     *
     * @return void
     * @access public
     */
    function delete( ) {
        CRM_Core_BAO_Note::del( $this->_id );
    }

    /**
     * Get action links
     *
     * @return array (reference) of action links
     * @static
     */
    static function &links()
    {
        if (!(self::$_links)) {
            $deleteExtra = ts('Are you sure you want to delete this note?');

            self::$_links = array(
                                  CRM_Core_Action::VIEW    => array(
                                                                    'name'  => ts('View'),
                                                                    'url'   => 'civicrm/contact/view/note',
                                                                    'qs'    => 'action=view&reset=1&cid=%%cid%%&id=%%id%%&selectedChild=note',
                                                                    'title' => ts('View Note')
                                                                    ),
                                  CRM_Core_Action::UPDATE  => array(
                                                                    'name'  => ts('Edit'),
                                                                    'url'   => 'civicrm/contact/view/note',

                                                                    'qs'    => 'action=update&reset=1&cid=%%cid%%&id=%%id%%&selectedChild=note',
                                                                    'title' => ts('Edit Note')
                                                                    ),
                                  CRM_Core_Action::DELETE  => array(
                                                                    'name'  => ts('Delete'),
                                                                    'url'   => 'civicrm/contact/view/note',
                                                                    'qs'    => 'action=delete&reset=1&cid=%%cid%%&id=%%id%%&selectedChild=note',
                                                                    'extra' => 'onclick = "if (confirm(\'' . $deleteExtra . '\') ) this.href+=\'&amp;confirmed=1\'; else return false;"',                                                                    
                                                                    'title' => ts('Delete Note')
                                                                    ),
                                  );
        }
        return self::$_links;
    }
                                  

}


