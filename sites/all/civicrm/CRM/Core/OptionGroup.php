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

class CRM_Core_OptionGroup 
{
    static $_values = array( );

    static function &valuesCommon( $dao, $flip = false, $grouping = false,
                                   $localize = false, $valueColumnName = 'label' ) 
    {
        self::$_values = array( );

        while ( $dao->fetch( ) ) {
            if ( $flip ) {
                if ( $grouping ) {
                    self::$_values[$dao->value] = $dao->grouping;
                } else {
                    self::$_values[$dao->{$valueColumnName}] = $dao->value;
                }
            } else {
                if ( $grouping ) {
                    self::$_values[$dao->{$valueColumnName}] = $dao->grouping;
                } else {
                    self::$_values[$dao->value] = $dao->{$valueColumnName};
                }
            }
        }
        if ($localize) {
            $i18n =& CRM_Core_I18n::singleton();
            $i18n->localizeArray(self::$_values);
        }
        return self::$_values;
    }

    static function &values( $name, $flip = false, $grouping = false,
                             $localize = false, $condition = null,
                             $valueColumnName = 'label' ) 
    {
        $query = "
SELECT  v.{$valueColumnName} as {$valueColumnName} ,v.value as value, v.grouping as grouping
FROM   civicrm_option_value v,
       civicrm_option_group g
WHERE  v.option_group_id = g.id
  AND  g.name            = %1
  AND  v.is_active       = 1 
  AND  g.is_active       = 1 ";
        
        if ( $condition ) {
            $query .= $condition;
        } 
        
        $query .= "  ORDER BY v.weight"; 

        $p = array( 1 => array( $name, 'String' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
        
        return self::valuesCommon( $dao, $flip, $grouping, $localize, $valueColumnName );
    }

    static function &valuesByID( $id, $flip = false, $grouping = false, $localize = false, $valueColumnName = 'label' ) 
    {
        $query = "
SELECT  v.{$valueColumnName} as {$valueColumnName} ,v.value as value, v.grouping as grouping
FROM   civicrm_option_value v,
       civicrm_option_group g
WHERE  v.option_group_id = g.id
  AND  g.id              = %1
  AND  v.is_active       = 1 
  AND  g.is_active       = 1 
  ORDER BY v.weight; 
";
        $p = array( 1 => array( $id, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
           
        return self::valuesCommon( $dao, $flip, $grouping, $localize, $valueColumnName );
    }
    
    /**
     * Function to lookup titles OR ids for a set of option_value populated fields. The retrieved value
     * is assigned a new fieldname by id or id's by title  
     * (each within a specificied option_group)
     *
     * @param  array   $params   Reference array of values submitted by the form. Based on
     *                           $flip, creates new elements in $params for each field in
     *                           the $names array.
     *                           If $flip = false, adds     root field name     => title
     *                           If $flip = true, adds      actual field name   => id                                                                     
     * 
     * @param  array   $names    Reference array of fieldnames we want transformed.
     *                           Array key = 'postName' (field name submitted by form in $params).
     *                           Array value = array('newName' => $newName, 'groupName' => $groupName).
     *                           
     *
     * @param  boolean $flip
     *
     * @return void     
     * 
     * @access public
     * @static
     */
    static function lookupValues( &$params, &$names, $flip = false ) 
    {
        require_once "CRM/Core/BAO/CustomOption.php";
        foreach ($names as $postName => $value) {
            // See if $params field is in $names array (i.e. is a value that we need to lookup)
            if ( CRM_Utils_Array::value( $postName, $params ) ) {
                // params[$postName] may be a Ctrl+A separated value list
                if ( strpos( $params[$postName], CRM_Core_BAO_CustomOption::VALUE_SEPERATOR ) ) {
                    // eliminate the ^A frm the beginning and end if present
                    if ( substr( $params[$postName], 0, 1 ) == CRM_Core_BAO_CustomOption::VALUE_SEPERATOR ) {
                        $params[$postName] = substr( $params[$postName], 1, -1 );
                    }
                }
                $postValues = explode(CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $params[$postName]);
                $newValue = array( );
                foreach ($postValues as $postValue) {
                    if ( ! $postValue ) {
                        continue;
                    }

                    if ( $flip ) {
                        $p = array( 1 => array( $postValue, 'String' ) );
                        $lookupBy = 'v.label= %1';
                        $select   = "v.value";
                    } else {
                        $p = array( 1 => array( $postValue, 'Integer' ) );
                        $lookupBy = 'v.value = %1';
                        $select   = "v.label";
                    }
                    
                    $p[2] = array( $value['groupName'], 'String' );
                    $query = "
                        SELECT $select
                        FROM   civicrm_option_value v,
                               civicrm_option_group g
                        WHERE  v.option_group_id = g.id
                        AND    g.name            = %2
                        AND    $lookupBy";

                    $newValue[] = CRM_Core_DAO::singleValueQuery( $query, $p );
                    $newValue = str_replace( ',', '_', $newValue );
                }
                $params[$value['newName']] = implode(', ', $newValue);
            }
        }
    }

    static function getLabel( $groupName, $value ) 
    {
        $query = "
SELECT  v.label as label ,v.value as value
FROM   civicrm_option_value v, 
       civicrm_option_group g 
WHERE  v.option_group_id = g.id 
  AND  g.name            = %1 
  AND  v.is_active       = 1  
  AND  g.is_active       = 1  
  AND  v.value           = %2
";

        $p = array( 1 => array( $groupName , 'String' ),
                    2 => array( $value, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
        if ( $dao->fetch( ) ) {
            return $dao->label;
        }
        return null;
    }

    static function getValue( $groupName, $label, $labelField = 'label' ) 
    {
        if ( empty( $label ) ) {
            return null;
        }

        $query = "
SELECT  v.label as label ,v.value as value
FROM   civicrm_option_value v, 
       civicrm_option_group g 
WHERE  v.option_group_id = g.id 
  AND  g.name            = %1 
  AND  v.is_active       = 1  
  AND  g.is_active       = 1  
  AND  v.$labelField     = %2
";

        $p = array( 1 => array( $groupName , 'String' ),
                    2 => array( $label     , 'String' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $p );
        if ( $dao->fetch( ) ) {
            return $dao->value;
        }
        return null;
    }

    static function createAssoc( $groupName, &$values, &$defaultID, $groupLabel = null ) 
    {
        self::deleteAssoc( $groupName );
        if ( ! empty( $values ) ) {
            require_once 'CRM/Core/DAO/OptionGroup.php';
            $group = new CRM_Core_DAO_OptionGroup( );
            $group->name        = $groupName;
            $group->label       = $groupLabel;
            $group->is_reserved = 1;
            $group->is_active   = 1;
            $group->save( );
            
            require_once 'CRM/Core/DAO/OptionValue.php';
            foreach ( $values as $v ) {
                $value = new CRM_Core_DAO_OptionValue( );
                $value->option_group_id = $group->id;
                $value->label           = $v['label'];
                $value->value           = $v['value'];
                $value->name            = $v['name'];
                $value->description     = CRM_Utils_Array::value( 'description', $v );
                $value->weight          = $v['weight'];
                $value->is_default      = $v['is_default'];
                $value->is_active       = $v['is_active'];
                $value->save( );
                
                if ( $value->is_default ) {
                    $defaultID = $value->id;
                }
            }
        } else {
            $defaultID = 'null';   
        }
        
        return $group->id;
    }
    
    static function getAssoc( $groupName, &$values, $field = 'name' ) 
    {
        $query = "
SELECT v.id, v.value, v.label, v.name, v.description, v.weight
  FROM civicrm_option_group g,
       civicrm_option_value v
 WHERE g.id = v.option_group_id
   AND g.$field = %1
ORDER BY v.weight
";
        $params = array( 1 => array( $groupName, 'String' ) );
       	$dao = CRM_Core_DAO::executeQuery( $query, $params );

        // now extract the amount 
        $values['value'] = $values['label'] = $values['name'] = array( );
        $values['description'] = array( ); 
        $index  = 1; 
         
        while ( $dao->fetch( ) ) { 
            $values['value'      ][$index] = $dao->value; 
            $values['label'      ][$index] = $dao->label; 
            $values['name'       ][$index] = $dao->name; 
            $values['description'][$index] = $dao->description; 
            $values['amount_id'  ][$index] = $dao->id;
            $values['weight'     ][$index] = $dao->weight;
            $index++; 
        } 
    }

    static function deleteAssoc( $groupName , $operator = "=" ) 
    {        
        $query = "
DELETE g, v
  FROM civicrm_option_group g,
       civicrm_option_value v
 WHERE g.id = v.option_group_id
   AND g.name {$operator} %1";

        $params = array( 1 => array( $groupName, 'String' ) );

        $dao = CRM_Core_DAO::executeQuery( $query, $params );
    }

    static function optionLabel( $groupName, $value ) 
    {
        $query = "
SELECT v.label
  FROM civicrm_option_group g,
       civicrm_option_value v
 WHERE g.id = v.option_group_id
   AND g.name  = %1
   AND v.value = %2";
        $params = array( 1 => array( $groupName, 'String' ),
                         2 => array( $value    , 'String' ) );
        return CRM_Core_DAO::singleValueQuery( $query, $params );

    }
}
