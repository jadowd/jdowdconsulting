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

require_once 'CRM/Core/DAO/UFField.php';
/**
 * This class contains function for UFField
 *
 */
class CRM_Core_BAO_UFField extends CRM_Core_DAO_UFField 
{

    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_BAO_UFField object
     * @access public
     * @static
     */
    static function retrieve(&$params, &$defaults)
    {
        return CRM_Core_DAO::commonRetrieve( 'CRM_Core_DAO_UFField', $params, $defaults );
    }
    
    /**
     * Get the form title.
     *
     * @param int $id id of uf_form
     * @return string title
     *
     * @access public
     * @static
     *
     */
    public static function getTitle( $id )
    {
        return CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFField', $groupId, 'title' );
    }
    /**
     * update the is_active flag in the db
     *
     * @param int      $id         id of the database record
     * @param boolean  $is_active  value we want to set the is_active field
     *
     * @return Object              DAO object on sucess, null otherwise
     * @access public
     * @static
     */
    static function setIsActive($id, $is_active) 
    {
        //check if custom data profile field is disabled
        if ($is_active) {
            if (CRM_Core_BAO_UFField::checkUFStatus($id)) {
                CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_UFField', $id, 'is_active', $is_active );
            } else {
                CRM_Core_Session::setStatus(ts('Cannot enable this UF field since the used custom field is disabled.'));
            }
        } else {
            return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_UFField', $id, 'is_active', $is_active );
        }
    }
    
    /**
     * Delete the profile Field.
     *
     * @param int  $id    Field Id 
     * 
     * @return boolean
     *
     * @access public
     * @static
     *
     */
    public static function del($id) 
    { 
        //delete  field field
        $field = & new CRM_Core_DAO_UFField();
        $field->id = $id; 
        $field->delete();
        return true;
    }
    
    /**
     * Function to check duplicate for duplicate field in a group
     * 
     * @param array $params an associative array with field and values
     * @ids   array $ids    array that containd ids 
     *
     *@access public
     *@static
     */
    public static function duplicateField($params, $ids)
    {
        $ufField                   =& new CRM_Core_DAO_UFField();
        $ufField->field_name       = $params['field_name'][0];
        $ufField->location_type_id = CRM_Utils_Array::value( 1, $params['field_name'] );
        $ufField->phone_type       = CRM_Utils_Array::value( 2, $params['field_name'] );
        $ufField->uf_group_id      = CRM_Utils_Array::value( 'uf_group', $ids );
        if (CRM_Utils_Array::value( 'uf_field', $ids )) {
            $ufField->whereAdd("id <> ".CRM_Utils_Array::value( 'uf_field', $ids ));
        }

        return $ufField->find(true);
    }

    
    /**
     * function to add the UF Field
     *
     * @param array $params (reference) array containing the values submitted by the form
     * @param array $ids    (reference) array containing the id
     * 
     * @return object CRM_Core_BAO_UFField object
     * 
     * @access public
     * @static 
     * 
     */
    static function add( &$params, &$ids) 
    {
        // set values for uf field properties and save
        $ufField                   =& new CRM_Core_DAO_UFField();
        $ufField->field_type       = $params['field_name'][0];
        $ufField->field_name       = $params['field_name'][1];
        
        //should not set location type id for Primary
        $locationTypeId = CRM_Utils_Array::value( 2, $params['field_name'] );
        if ( $locationTypeId ) {
            $ufField->location_type_id = $locationTypeId;
        } else {
            $ufField->location_type_id = 'null';
        }
        
        $ufField->phone_type      = CRM_Utils_Array::value( 3, $params['field_name'], 'NULL' );
        $ufField->listings_title  = CRM_Utils_Array::value( 'listings_title', $params );
        $ufField->visibility      = $params['visibility'];
        $ufField->help_post       = $params['help_post'];
        $ufField->label           = $params['label'];
        $ufField->is_required     = CRM_Utils_Array::value( 'is_required'    , $params, false );
        $ufField->is_active       = CRM_Utils_Array::value( 'is_active'      , $params, false );
        $ufField->in_selector     = CRM_Utils_Array::value( 'in_selector'    , $params, false );
        $ufField->is_view         = CRM_Utils_Array::value( 'is_view'        , $params, false );
        $ufField->is_registration = CRM_Utils_Array::value( 'is_registration', $params, false );
        $ufField->is_match        = CRM_Utils_Array::value( 'is_match'       , $params, false );
        $ufField->is_searchable   = CRM_Utils_Array::value( 'is_searchable'  , $params, false );
        
        // fix for CRM-316
        $oldWeight = null;
        if ($params['field_id']) {
            $oldWeight = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFField', $params['field_id'], 'weight', 'id' );
        }
        $fieldValues = array('uf_group_id' => $params['group_id']);
        require_once 'CRM/Utils/Weight.php';
        $ufField->weight = 
            CRM_Utils_Weight::updateOtherWeights('CRM_Core_DAO_UFField', $oldWeight, $params['weight'], $fieldValues);
        
        // need the FKEY - uf group id
        $ufField->uf_group_id = CRM_Utils_Array::value('uf_group', $ids , false );
        $ufField->id          = CRM_Utils_Array::value('uf_field', $ids , false ); 
        
        return $ufField->save();
    }
    
    /**
     * Function to enable/disable profile field given a custom field id
     *
     * @param int      $customFieldId     custom field id
     * @param boolean  $is_active         set the is_active field
     *
     * @return void
     * @static
     * @access public
     */
    static function setUFField($customFieldId, $is_active) 
    {
        //find the profile id given custom field
        $ufField =& new CRM_Core_DAO_UFField();
        $ufField->field_name = "custom_".$customFieldId;
        
        $ufField->find();
        while ($ufField->fetch()) {
            //enable/ disable profile
            CRM_Core_BAO_UFField::setIsActive($ufField->id, $is_active);
        }
    }

  /**
     * Function to copy exisiting profile fields to 
     * new profile from the already built profile
     *
     * @param int      $old_id  from which we need to copy     
     * @param boolean  $new_id  in which to copy  
     *
     * @return void
     * @static
     * @access public
     */
    static function copy( $old_id, $new_id ) 
    {
        $ufField =& new CRM_Core_DAO_UFField( );
        $ufField->uf_group_id = $old_id;
        $ufField->find( );
        while( $ufField->fetch( ) ) {
            //copy the field records as it is on new ufgroup id
            $ufField->uf_group_id = $new_id;
            $ufField->id          = null;
            $ufField->save();
        }
    }

    /**
     * Function to delete profile field given a custom field
     *
     * @param int   $customFieldId      ID of the custom field to be deleted
     *
     * @return void
     * 
     * @static
     * @access public
     */
    function delUFField($customFieldId)
    {
        //find the profile id given custom field id
        $ufField =& new CRM_Core_DAO_UFField();
        $ufField->field_name = "custom_".$customFieldId;
        
        $ufField->find();
        while ($ufField->fetch()) {
            //enable/ disable profile
            CRM_Core_BAO_UFField::del($ufField->id);
        }
    }

    /**
     * Function to enable/disable profile field given a custom group id
     *
     * @param int      $customGroupId custom group id
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return void
     * @static
     * @access public
     */
    function setUFFieldStatus ($customGroupId, $is_active) 
    {
        //find the profile id given custom group id
        $queryString = "SELECT civicrm_custom_field.id as custom_field_id
                        FROM   civicrm_custom_field, civicrm_custom_group
                        WHERE  civicrm_custom_field.custom_group_id = civicrm_custom_group.id
                          AND  civicrm_custom_group.id = %1";
        $p = array( 1 => array( $customGroupId, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery($queryString, $p);
        
        while ($dao->fetch()) {
            //enable/ disable profile
            CRM_Core_BAO_UFField::setUFField($dao->custom_field_id, $is_active);
        }
    }
    
    /**
     * Function to check the status of custom field used in uf fields
     *
     * @params  int $UFFieldId     uf field id 
     *
     * @return boolean   false if custom field are disabled else true
     * @static
     * @access public
     */
    static function checkUFStatus($UFFieldId) 
    {
        $fieldName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFField', $UFFieldId, 'field_name' );
        
        $customFieldId = explode('_', $fieldName);
        
        require_once "CRM/Core/DAO/CustomField.php";
        $customField =& new CRM_Core_DAO_CustomField();
        $customField->id = $customFieldId[1];

        if ($customField->find(true) ) { // if uf field is custom field
            if ( !$customField->is_active) {
                return false;
            } else {
                return true;
            }
        } else { // this is if field is not a custom field
            return true;
        }
    }


    /**
     * function to check for mix profile fields (eg: individual + other contact types)
     *
     * @params int     $ufGroupId  uf group id 
     * @params boolean $check      this is to check mix profile (if true it will check if profile is
     *                             pure ie. it contains only one contact type)
     *
     * @return  true for mix profile else false
     * @acess public
     * @static
     */
    static function checkProfileType( $ufGroupId ) 
    {
        $ufGroup =& new CRM_Core_DAO_UFGroup();
        $ufGroup->id = $ufGroupId;
        $ufGroup->find( true );
        
        $profileTypes = array( );
        if ( $ufGroup->group_type ) {
            $profileTypes = explode( ',',  $ufGroup->group_type );
        }
        
        //we need to unset Contact
        if ( count( $profileTypes ) > 1 ) {
            $index = array_search( 'Contact', $profileTypes );
            if ( $index !== false ) {
                unset( $profileTypes[$index] );
            }
        }

        $contactTypes = array( 'Contact', 'Individual', 'Household', 'Organization' );
        $components   = array( 'Contribution', 'Participant', 'Membership' );
        $fields = array( );

        // check for mix profile condition
        if ( count( $profileTypes ) > 1 ) {
            //check the there are any components include in profile
            foreach ( $components as $value ) {
                if ( in_array( $value, $profileTypes ) ) {
                    return true;
                }
            }
            //check if there are more than one contact types included in profile
            if ( count( $profileTypes ) > 1 ) {
                return true;
            }
        } else {
            $profileTypes = array_values( $profileTypes );
            if ( !in_array( $profileTypes[0], $contactTypes ) ) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * function to get the profile type (eg: individual/organization/household)
     *
     * @param int      $ufGroupId     uf group id 
     * @param boolean  $returnMixType this is true, then field type of  mix profile field is returned
     * @param boolean  $onlyPure      true if only pure profiles are required
     *
     * @return  profile group_type
     * @acess public
     * @static
     */
    static function getProfileType( $ufGroupId, $returnMixType = true, $onlyPure = false ) 
    {
        // profile types
        $contactTypes = array( 'Contact', 'Individual', 'Household', 'Organization', 'Student' );
        $components   = array( 'Contribution', 'Participant', 'Membership' );

        require_once 'CRM/Core/DAO/UFGroup.php';
        $ufGroup =& new CRM_Core_DAO_UFGroup( );
        $ufGroup->id          = $ufGroupId;
        $ufGroup->is_active   = 1;        

        $ufGroup->find( true );
        
        $profileTypes = array( );
        if ( $ufGroup->group_type ) {
            $profileTypes = explode( ',',  $ufGroup->group_type );
        }
        
        if ( $onlyPure ) {
            if ( count( $profileTypes ) == 1 ) {
                return $profileTypes[0]; 
            } else {
                return null;
            }
        }

        //we need to unset Contact
        if ( count( $profileTypes ) > 1 ) {
            $index = array_search( 'Contact', $profileTypes );
            if ( $index !== false ) {
                unset( $profileTypes[$index] );
            }
        }

        $profileType = $mixProfileType = null;

        if ( count( $profileTypes ) == 1 ) { // this case handles pure profile
            $profileType = array_pop( $profileTypes ); 
        } else {
            //check the there are any components include in profile
            $componentCount = array( );
            foreach ( $components as $value ) {
                if ( in_array( $value, $profileTypes ) ) {
                    $componentCount[] = $value;
                }
            }          

            //check contact type included in profile
            $contactTypeCount = array( );
            foreach ( $contactTypes as $value ) {
                if ( in_array( $value, $profileTypes ) ) {
                    $contactTypeCount[] = $value;
                }
            }

            if ( count( $componentCount ) == 1 && count( $contactTypeCount ) == 1 ) { 
                // this case if valid profile contact + component
                 $profileType = $componentCount[0];
            } elseif( count( $componentCount ) > 1 ) { 
                // this is mix component profiles
                $mixProfileType = $componentCount[1];
            } else if( count( $contactTypeCount ) > 1 ) {
                // this is mix contact profiles
                $mixProfileType = $contactTypeCount[1];
            }
        }

        if ( $mixProfileType ) {
            if ( $returnMixType ) {
                return $mixProfileType;
            } else {
                return 'Mixed';
                }
        } else {
            return $profileType;
        }
    }

    /**
     * function to check for mix profiles groups (eg: individual + other contact types)
     *
     * @return  true for mix profile group else false
     * @acess public
     * @static
     */
    static function checkProfileGroupType( $ctype ) 
    {
        $ufGroup =& new CRM_Core_DAO_UFGroup();

        $query = "
SELECT ufg.id as id
  FROM civicrm_uf_group as ufg, civicrm_uf_join as ufj
 WHERE ufg.id = ufj.uf_group_id
   AND ufj.module = 'User Registration'
   AND ufg.is_active = 1 ";

        $ufGroup =& CRM_Core_DAO::executeQuery( $query );
        
        $fields = array( );
        $validProfiles = array( 'Individual', 'Organization', 'Household', 'Contribution' );
        while ( $ufGroup->fetch() ) {
            $profileType = self::getProfileType($ufGroup->id);
            if ( in_array( $profileType, $validProfiles ) ) {
                continue;
            } else if ( $profileType ) {
                return false;
            }
        }

        return true;
    }

}

