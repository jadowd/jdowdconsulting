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
 * Definition of CRM API for Participant.
 * More detailed documentation can be found 
 * {@link http://objectledge.org/confluence/display/CRM/CRM+v1.0+Public+APIs
 * here}
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v2/utils.php';
require_once 'CRM/Core/BAO/Note.php';
/**
 * Create Note
 *  
 * This API is used for creating a note.
 * Required parameters : entity_id AND note
 * 
 * @param   array  $params  an associative array of name/value property values of civicrm_note
 * 
 * @return array note id if note is created otherwise is_error = 1
 * @access public
 */
function &civicrm_note_create(&$params)
{
    _civicrm_initialize();

    if ( !is_array( $params ) ) {
        return civicrm_create_error( 'Params is not an array' );
    }
    
    if ( !isset($params['entity_table']) || 
         !isset($params['entity_id'])    || 
         !isset($params['note'])         || 
         !isset($params['contact_id'] ) ) {
        return civicrm_create_error( 'Required parameter missing' );
    }

    $contactID = CRM_Utils_Array::value( 'contact_id', $params );
    
    if ( !isset($params['modified_date']) ) {
        $params['modified_date']  = date("Ymd");
    }

    $ids = array( );
    $noteBAO = CRM_Core_BAO_Note::add( $params, $ids );
   
    if ( is_a( $noteBAO, 'CRM_Core_Error' ) ) {
        $error = civicrm_create_error( "Note could not be created" );
        return $error;
    } else {
        $note = array( );
        _civicrm_object_to_array( $noteBAO, $note );
        $note['is_error'] = 0;
    }
    return $note;
}

/**
 * Update existing note
 *
 * This api is used for updating an existing note
 * Required parrmeters : id of a note
 * 
 * @param  Array $params  an associative array of name/value property values of civicrm_note
 * 
 * @return array of updated note property values
 * @access public
 */

function &civicrm_note_update(&$params)
{
    _civicrm_initialize();
    if ( !is_array( $params ) ) {
        $error = civicrm_create_error( 'Parameters is not an array' );
        return $error;
    }
    
    if ( !isset( $params['id'] ) && !isset( $params['contact_id'] ) ) {
        $error = civicrm_create_error( 'Required parameter missing' );
        return $error;
    } else {
        $ids = array('id' => $params['id']);
        $noteBAO = CRM_Core_BAO_Note::add( $params, $ids );   

        $note = array( );
        _civicrm_object_to_array( $noteBAO, $note );
        return $note;
    }
}

/**
 * Deletes an existing note
 * 
 * This API is used for deleting a note
 * 
 * @param  Int  $noteID   Id of the note to be deleted
 * 
 * @return null
 * @access public
 */
function &civicrm_note_delete( &$params )
{
    _civicrm_initialize();
    
    if ( !is_array( $params ) ) {
        $error = civicrm_create_error( 'Params is not an array' );
        return $error;
    }
    
    if ( ! CRM_Utils_Array::value( 'id', $params ) ) {
        $error = civicrm_create_error( 'Invalid or no value for Note ID' );
        return $error;
    }

    $result = new CRM_Core_BAO_Note();
    return $result->del( $params['id'] ) ? civicrm_create_success( ) : civicrm_create_error('Error while deleting Note');
}

/**
 * Retrieve a specific note, given a set of input params
 *
 * @param  array   $params (reference ) input parameters
 *
 * @return array (reference ) array of properties, 
 * if error an array with an error id and error message
 * 
 * @static void
 * @access public
 */

function &civicrm_note_get( &$params ) {
    _civicrm_initialize( );
 
    $values = array( );
    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'No input parameters present' ) );
    }

    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameters is not an array' ) );
    }
   
    if ( !is_numeric( $params['entity_id'] ) ) {
        return civicrm_create_error( ts ( "Invalid entity ID" ) );
    }

    if ( ! isset( $params['entity_id'] ) && ! isset( $params['entity_table'] ) ) {
        return civicrm_create_error( 'Required parameters missing.' );
    }

    $note = CRM_Core_BAO_Note::getNote($params['entity_id'],$params['entity_table']);
    
    if ( civicrm_error( $note ) ) {
        return $note;
    }

    if ( count( $note ) < 1 ) {
        return civicrm_create_error( ts( '%1 notes matching the input parameters', array( 1 => count( $note ) ) ) );
    }
    
    $note = array_values( $note );
    $note['is_error'] = 0;
    return $note;
}

