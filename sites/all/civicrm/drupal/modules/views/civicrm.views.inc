<?php
  /**
   * @file
   * Provide views data and handlers for civicrm.module
   */

  /**
   * @defgroup views_civicrm_module civicrm.module handlers
   *
   * @{
   */

  /**
   * Implementation of hook_views_data()
   */
function civicrm_views_data() {
    // ----------------------------------------------------------------
    // civicrm_contact table

    // Define the base group of this table. Fields that don't
    // have a group defined will go into this field by default.

    $data = array( );

    civicrm_initialize( );
    require_once 'CRM/Core/Config.php';
    $config =& CRM_Core_Config::singleton( );

    civicrm_views_data_contact     ( $data, $config );

    civicrm_views_data_email       ( $data, $config );

    civicrm_views_data_address     ( $data, $config );

    civicrm_views_data_contribution( $data, $config );

    civicrm_views_data_activity    ( $data, $config );

    civicrm_views_data_event       ( $data, $config );

    return $data;
  }

/**
 * Implementation of hook_views_plugins
 */
function civicrm_views_plugins() {
    return array(
                 'module' => 'views', // This just tells our themes are elsewhere.
                 'argument default' => array(
                                             'user' => array(
                                                             'title' => t('Contact ID from URL'),
                                                             'handler' => 'civicrm_plugin_argument_default',
                                                             ),
                                             ),
                 );
}


/**
 * Allow replacement of current userid so we can cache these queries
 */
function civicrm_views_query_substitutions($view) {
    global $user;
    $uid = intval( $user->uid );
    civicrm_initialize( );
    require_once 'CRM/Core/BAO/UFMatch.php';
    $id  = CRM_Core_BAO_UFMatch::getContactId( $uid );
    return array('***CURRENT_CIVICRM***' => intval($id));
}

function civicrm_views_href( $text, $path, $query ) {
    civicrm_initialize( );
    require_once 'CRM/Utils/System.php';
    return CRM_Utils_System::href( $text, $path, $query );
}
 
function civicrm_views_data_contact( &$data, &$config ) {
    $data['civicrm_contact']['table']['group'] = t('CiviCRM Contact');

    $data['civicrm_contact']['table']['base'] = array(
                                                      'field' => 'id',
                                                      'title' => t('CiviCRM Contacts'),
                                                      'help'  => t('CiviCRM Contacts'),
                                                      );

    // id
    $data['civicrm_contact']['id'] = array(
                                           'title' => t('Contact ID'),
                                           'help'  => t('The contact ID'), // The help that appears on the UI,
                                           'field' => array(
                                                            'handler' => 'views_handler_field_numeric',
                                                            'click sortable' => TRUE,
                                                            ),
                                           'sort'  => array(
                                                            'handler' => 'views_handler_sort',
                                                            ),
                                           'argument' => array(
                                                               'handler' => 'views_handler_argument_numeric',
                                                               ),
                                           );

    // name
    $data['civicrm_contact']['name'] = array(
                                             'title' => t('Name'), // The item it appears as on the UI,
                                             'real field' => 'display_name',
                                             'help' => t('The display name of the contact.'), // The help that appears on the UI,
                                             'field' => array(
                                                              'handler' => 'views_handler_field',
                                                              'click sortable' => TRUE,
                                                              ),
                                             'sort' => array(
                                                             'handler' => 'views_handler_sort',
                                                             ),
                                             'argument' => array(
                                                                 'handler' => 'views_handler_argument_string',
                                                                 ),
                                             );

    // contact_type
    $data['civicrm_contact']['contact_type'] = array(
                                                     'title' => t('Contact Type'), // The item it appears as on the UI,
                                                     'help'  => t('Contact Type.'), // The help that appears on the UI,
                                                     'field' => array(
                                                                      'handler' => 'views_handler_field',
                                                                      'click sortable' => TRUE,
                                                                      ),
                                                     'sort' => array(
                                                                     'handler' => 'views_handler_sort',
                                                                     ),
                                                     'filter' => array(
                                                                       'handler' => 'civicrm_handler_filter_contact_type',
                                                                       ),
                                                     );

    $data['civicrm_contact']['edit_contact'] = array(
                                                     'field' => array(
                                                                      'title' => t('Edit link'),
                                                                      'help' => t('link to edit the contact.'),
                                                                      'handler' => 'civicrm_handler_field_link_edit',
                                                                      ),
                                                     );

    $data['civicrm_contact']['delete_contact'] = array(
                                                       'field' => array(
                                                                        'title' => t('Delete link'),
                                                                        'help' => t('link to delete the contact.'),
                                                                        'handler' => 'civicrm_handler_field_link_delete',
                                                                        ),
                                                       );

    // add the other generic fields
    civicrm_initialize( );
    $objType = 'Individual';
    require_once 'CRM/Contact/BAO/Contact.php';
    $fields = CRM_Contact_BAO_Contact::exportableFields( $objType );

    $skipFields = array( 'id' => 1,
                         'contact_type' => 1,
                         'contact_sub_type' => 1,
                         );
    civicrm_views_add_fields( $fields, $data['civicrm_contact'], $skipFields, 'civicrm_contact' );
}

function civicrm_views_data_email( &$data, &$config ) {
    // ----------------------------------------------------------------------
    // CiviCRM email table

    $data['civicrm_email']['table']['group']  = t('CiviCRM Contact');

    // Explain how this table joins to others.
    $data['civicrm_email']['table']['join'] = array(
                                                    // Directly links to contact table.
                                                    'civicrm_contact' => array(
                                                                               'left_field' => 'id',
                                                                               'field' => 'contact_id',
                                                                               ),
                                                    );

    $data['civicrm_email']['email'] = array(
                                            'title' => t('Email'),
                                            'help' => t('Email address of the contact'),
                                            'field' => array(
                                                             'handler' => 'civicrm_handler_field_email',
                                                             ),
                                            'filter' => array(
                                                              'handler' => 'views_handler_filter_string',
                                                              ),
                                            );

    $data['civicrm_email']['email_location'] =
        civicrm_views_location_type( t('Email Location'),
                                     t('Email location of the contact (home,work...)') );

    $data['civicrm_email']['email_is_primary'] = array(
                                                       'title' => t('Is Primary Email?'),
                                                       'real field' => 'is_primary',
                                                       'help' => t('Is this the primary email?'),
                                                       'field' => array(
                                                                        'handler' => 'views_handler_field_boolean',
                                                                        ),
                                                       'filter' => array(
                                                                         'handler' => 'views_handler_filter_boolean_operator',
                                                                         ),
                                                       );
}

function civicrm_views_data_address( &$data, &$config ) {
    // ----------------------------------------------------------------------
    // CiviCRM email table

    $data['civicrm_address']['table']['group']  = t('CiviCRM Contact');

    // Explain how this table joins to others.
    $data['civicrm_address']['table']['join'] = array(
                                                      // Directly links to contact table.
                                                      'civicrm_contact' => array(
                                                                                 'left_field' => 'id',
                                                                                 'field' => 'contact_id',
                                                                                 ),
                                                      );

    require_once 'CRM/Core/DAO/Address.php';
    $fields = CRM_Core_DAO_Address::export( );

    $skipFields = array( 'id'               => 1,
                         'contact_id'       => 1,
                         'location_type_id' => 1,
                         'geo_code_1'       => 1,
                         'geo_code_2'       => 1 );
    civicrm_views_add_fields( $fields, $data['civicrm_address'], $skipFields, 'civicrm_address' );

    $data['civicrm_address']['address_location'] =
        civicrm_views_location_type( t('Address Location'),
                                     t('Address location of the contact (home,work...)') );

    $data['civicrm_address']['state_province'] =
        civicrm_views_state_province( );

    $data['civicrm_address']['country'] =
        civicrm_views_country( );

}

function civicrm_views_data_activity    ( &$data, &$config ) {
    $data['civicrm_activity']['table']['group']  = t('CiviCRM Activities');

    $data['civicrm_activity']['table']['base'] =
        array(
              'field' => 'id',
              'title' => t('CiviCRM Activities'),
              'help'  => t('CiviCRM Activities' ), 
              );

    // Explain how this table joins to others.
    $data['civicrm_activity']['table']['join'] = array(
                                                       // Directly links to contact table.
                                                       'civicrm_contact' => array(
                                                                                  'left_field' => 'id',
                                                                                  'field' => 'source_contact_id',
                                                                                  ),
//                                                        'civicrm_contact' => array(
//                                                                                   'left_field' => 'id',
//                                                                                   'field' => 'source_record_id',
//                                                                                   ),
                                                       );
    
    $data['civicrm_activity']['id'] = array(
                                            'title' => t('Activity ID'),
                                            'help'  => t('The activity ID'),
                                            'field' => array(
                                                             'handler' => 'views_handler_field_numeric',
                                                             'click sortable' => TRUE,
                                                             ),
                                            'filter' => array(
                                                              'handler' => 'views_handler_filter_numeric',
                                                              ),
                                            'sort'   => array(
                                                              'handler' => 'views_handler_sort',
                                                              ),
                                            'argument' => array(
                                                                'handler' => 'views_handler_argument_numeric',
                                                                ),
                                            );

    $data['civicrm_activity']['source_contact_id'] = array(
                                                           'title' => t('Source Contact'),
                                                           'help'  => t('The contact who scheduled this activity'),
                                                           'relationship' => array(
                                                                                   'base' => 'civicrm_contact',
                                                                                   'field' => 'id',
                                                                                   'handler' => 'views_handler_relationship',
                                                                                   'label' => t('CiviCRM Contact'),
                                                                                   ),
                                                           );

    $data['civicrm_activity']['source_record_id'] = array(
                                                          'title' => t('Source Record ID'),
                                                          'help'  => t('The contact who made this activity'),
                                                          'relationship' => array(
                                                                                  'base' => 'civicrm_contact',
                                                                                  'field' => 'id',
                                                                                  'handler' => 'views_handler_relationship',
                                                                                  'label' => t('CiviCRM Contact'),
                                                                                  ),
                                                          );

    $data['civicrm_activity']['activity_type'] =
        civicrm_views_activity_type( t('Activity Type'),
                                     t('Activity Types') );
    
    // add the other generic fields
    civicrm_initialize( );
    require_once 'CRM/Activity/BAO/Activity.php';
    $fields = CRM_Activity_BAO_Activity::export( );
    
    $skipFields = array( 'id' => 1,
                         'source_contact_id' => 1,
                         'source_record_id'  => 1,
                         'activity_type_id'  => 1,
                         'phone_id'    => 1,
                         'status_id'   => 1,
                         'priority_id' => 1,
                         'parent_id'   => 1,
                         );

    civicrm_views_add_fields( $fields, $data['civicrm_activity'], $skipFields, 'civicrm_activity' );
}

function civicrm_views_data_contribution( &$data, &$config ) {
    // ----------------------------------------------------------------------
    $data['civicrm_contribution']['table']['group']  = t('CiviCRM Contributions');

    $data['civicrm_contribution']['table']['base'] =
        array(
              'field' => 'id',
              'title' => t('CiviCRM Contributions'),
              'help' => t('CiviCRM Contributions' ), 
             );

    // Explain how this table joins to others.
    $data['civicrm_contribution']['table']['join'] = array(
                                                           // Directly links to contact table.
                                                           'civicrm_contact' => array(
                                                                                      'left_field' => 'id',
                                                                                      'field' => 'contact_id',
                                                                                      ),
                                                           );


    $data['civicrm_contribution']['id'] = array(
                                                'title' => t('Contribution ID'),
                                                'help'  => t('The contribution ID'),
                                                'field' => array(
                                                                 'handler' => 'views_handler_field_numeric',
                                                                 'click sortable' => TRUE,
                                                                 ),
                                                'filter' => array(
                                                                  'handler' => 'views_handler_filter_numeric',
                                                                  ),
                                                'sort'   => array(
                                                                  'handler' => 'views_handler_sort',
                                                                  ),
                                                'argument' => array(
                                                                    'handler' => 'views_handler_argument_numeric',
                                                                    ),
                                                );

    $data['civicrm_contribution']['contact_id'] = array(
                                                        'title' => t('Contact ID'),
                                                        'help' => t('The contact who made this contribution'),
                                                        'relationship' => array(
                                                                                'base' => 'civicrm_contact',
                                                                                'field' => 'id',
                                                                                'handler' => 'views_handler_relationship',
                                                                                'label' => t('CiviCRM Contact'),
                                                                                ),
                                                        );

    // add the other generic fields
    civicrm_initialize( );
    require_once 'CRM/Contribute/BAO/Contribution.php';
    $fields = CRM_Contribute_BAO_Contribution::exportableFields( );

    $skipFields = array( 'id' => 1,
                         'contribution_id' => 1,
                         'contribution_contact_id' => 1,
                         'contact_id' => 1 );

    civicrm_views_add_fields( $fields, $data['civicrm_contribution'], $skipFields, 'civicrm_contribution' );
}

function civicrm_views_data_event(&$data, &$config) {
    require_once 'CRM/Event/DAO/Event.php';
    $fields = CRM_Event_DAO_Event::export();

    $skipFields = array( 'id'                     => 1,
                         'title'                  => 1,
                       );

    civicrm_views_add_fields( $fields, $data['civicrm_event'], $skipFields, 'civicrm_event' );

    $data['civicrm_event']['table']['group'] = t('CiviCRM Event');

    $data['civicrm_event']['table']['base'] = array(
                                                    'field' => 'id',
                                                    'title' => t('CiviCRM Events'),
                                                    'help'  => t('CiviCRM Events')
                                                   );

    $data['civicrm_event']['id'] = array(
                                         'title'    => t('Event ID'),
                                         'help'     => t('The event id.'),
                                         'field'    => array(
                                                             'handler' => 'views_handler_field_numeric',
                                                             'click sortable' => TRUE
                                                            ),
                                         'argument' => array(
                                                             'handler' => 'views_handler_field_numeric'
                                                            ),
                                         'filter'   => array(
                                                             'handler' => 'views_handler_filter_numeric'
                                                            ),
                                         'sort'     => array(
                                                             'handler' => 'views_handler_sort'
                                                            )
                                         );

    $data['civicrm_event']['title'] = array(
                                         'title'    => t('Event Title'),
                                         'help'     => t('Event Title'),
                                         'field'    => array(
                                                             'handler' => 'civicrm_handler_field_event',
                                                             'format' => FILTER_FORMAT_DEFAULT,
                                                             'click sortable' => TRUE
                                                            ),
                                         );

    $data['civicrm_event']['description'] = array(
                                         'title'    => t('Description'),
                                         'help'     => t('Full description of event'),
                                         'field'    => array(
                                                             'handler' => 'civicrm_handler_field_event',
                                                             'format' => FILTER_FORMAT_DEFAULT,
                                                             'click sortable' => TRUE
                                                            ),
                                         );

    $data['civicrm_event']['summary'] = array(
                                         'title'    => t('Summary'),
                                         'help'     => t('Brief summary of event'),
                                         'field'    => array(
                                                             'handler' => 'civicrm_handler_field_event',
                                                             'format' => FILTER_FORMAT_DEFAULT,
                                                             'click sortable' => TRUE
                                                            ),
                                         );

    $data['civicrm_event']['link'] = array(
                                         'title'      => t('Link'),
                                         'help'       => t('Link to event page'),
                                         'field'      => array(
                                                              'handler' => 'civicrm_handler_field_event_link',
                                                              'real field' => 'id',
                                                              'click sortable' => FALSE
                                                             ),
                                         );
}

function civicrm_views_add_fields( &$fields, &$data, &$skipFields, $tableName ) {
    foreach ( $fields as $name => $value ) {
        if ( CRM_Utils_Array::value( $name, $skipFields ) ||
             substr( $value['where'], 0, strlen( $tableName ) + 1 ) != "{$tableName}." ) {
            continue;
        }

        $data[$value['name']] = array(
                             'title' => $value['title'],
                             'help'  => $value['title'],
                             'field' => array( 
                                              'handler' => civicrm_views_get_field_handler  ( $value['type'] ),
                                              'click sortable' => true,
                                              ),
                             'sort'  => array(
                                              'handler' => civicrm_views_get_sort_handler   ( $value['type'] ),
                                              ),
                             'filter' => array(
                                               'handler' => civicrm_views_get_filter_handler( $value['type'] ),
                                               ),
                             );
    }

}

function civicrm_views_get_field_handler( $type ) {
    switch ( $type ) {
    case CRM_Utils_Type::T_INT   :
    case CRM_Utils_Type::T_CCNUM :
    case CRM_Utils_Type::T_FLOAT :
    case CRM_Utils_Type::T_MONEY :
        return 'views_handler_field_numeric';

    case CRM_Utils_Type::T_URL :
        return 'views_handler_field_url';
        
    case CRM_Utils_Type::T_DATE :
    case CRM_Utils_Type::T_TIME :
    case CRM_Utils_Type::T_TIMESTAMP :
    case CRM_Utils_Type::T_DATE | CRM_Utils_Type::T_TIME :
        return 'civicrm_handler_field_datetime';

    case CRM_Utils_Type::T_BOOL :
    case CRM_Utils_Type::T_BOOLEAN :
        return 'views_handler_field_boolean';
        
    default:
        return 'views_handler_field';
    }
}

function civicrm_views_get_filter_handler( $type ) {
    switch ( $type ) {
    case CRM_Utils_Type::T_INT   :
    case CRM_Utils_Type::T_CCNUM :
    case CRM_Utils_Type::T_FLOAT :
    case CRM_Utils_Type::T_MONEY :
        return 'views_handler_filter_numeric';

    case CRM_Utils_Type::T_ENUM :
        return 'views_handler_filter_in_operator';
        
    case CRM_Utils_Type::T_TEXT :
    case CRM_Utils_Type::T_LONGTEXT :
    case CRM_Utils_Type::T_STRING :
    case CRM_Utils_Type::T_BLOB :
    case CRM_Utils_Type::T_EMAIL :
    case CRM_Utils_Type::T_MEDIUMBLOB :
    case CRM_Utils_Type::T_URL :
        return 'views_handler_filter_string';
        
    case CRM_Utils_Type::T_DATE :
    case CRM_Utils_Type::T_TIME :
    case CRM_Utils_Type::T_TIMESTAMP :
    case CRM_Utils_Type::T_DATE | CRM_Utils_Type::T_TIME :
        return 'civicrm_handler_filter_datetime';

    case CRM_Utils_Type::T_BOOL :
    case CRM_Utils_Type::T_BOOLEAN :
        return 'views_handler_filter_boolean_operator';
        
    default:
        return 'views_handler_filter';
    }
}

function civicrm_views_get_sort_handler( $type ) {
    switch ( $type ) {
    case CRM_Utils_Type::T_DATE :
    case CRM_Utils_Type::T_TIME :
    case CRM_Utils_Type::T_DATE | CRM_Utils_Type::T_TIME :
    case CRM_Utils_Type::T_TIMESTAMP :
        return 'views_handler_sort_date';
        
    default:
        return 'views_handler_sort';
    }
}

/**
 * Implementation of hook_views_handlers() to register all of the basic handlers
 * views uses.
 */
function civicrm_views_handlers() {
    return array(
                 'info' => array(
                                 'path' => drupal_get_path('module', 'civicrm') . '/modules/views/civicrm',
                                 ),
                 'handlers' =>
                 array(
                       'civicrm_handler_field' => array(
                                                        'parent' => 'views_handler_field',
                                                        ),
                       'civicrm_handler_field_link' => array(
                                                             'parent' => 'civicrm_handler_field',
                                                             ),
                       'civicrm_handler_field_link_edit' => array(
                                                                  'parent' => 'civicrm_handler_field_link',
                                                                  ),
                       'civicrm_handler_field_link_delete' => array(
                                                                    'parent' => 'civicrm_handler_field_link',
                                                                    ),
                       'civicrm_handler_field_mail' => array(
                                                             'parent' => 'civicrm_handler_field',
                                                             ),
                       'civicrm_handler_field_email' => array(
                                                              'parent' => 'civicrm_handler_field',
                                                              ),
                       'civicrm_handler_field_option' => array(
                                                               'parent' => 'civicrm_handler_field',
                                                               ),
                       'civicrm_handler_field_location' => array(
                                                                 'parent' => 'civicrm_handler_field',
                                                                 ),
                       'civicrm_handler_field_activityType' => array(
                                                                     'parent' => 'civicrm_handler_field_option',
                                                                     ),
                       'civicrm_handler_field_state' => array(
                                                              'parent' => 'civicrm_handler_field',
                                                              ),
                       'civicrm_handler_field_country' => array(
                                                                'parent' => 'civicrm_handler_field',
                                                                ),
                       'civicrm_handler_field_datetime' => array(
                                                            'parent' => 'views_handler_field_date',
                                                           ),
                       'civicrm_handler_filter_datetime' => array(
                                                            'parent' => 'views_handler_filter_date',
                                                           ),
                       'civicrm_handler_filter_contact_type' => array(
                                                                      'parent' => 'views_handler_filter_in_operator',
                                                                      ),
                       'civicrm_handler_filter_option' => array(
                                                                'parent' => 'views_handler_filter_in_operator',
                                                                ),
                       'civicrm_handler_filter_state' => array(
                                                               'parent' => 'views_handler_filter_in_operator',
                                                               ),
                       'civicrm_handler_filter_country' => array(
                                                                 'parent' => 'views_handler_filter_in_operator',
                                                                 ),
                       'civicrm_handler_filter_location' => array(
                                                                  'parent' => 'views_handler_filter_in_operator',
                                                                  ),
                       'civicrm_handler_filter_activityType' => array(
                                                                      'parent' => 'civicrm_handler_filter_option',
                                                                      ),
                       'civicrm_handler_field_event' => array(
                                                            'parent' => 'views_handler_field_markup',
                                                           ),
                       'civicrm_handler_field_event_link' => array(
                                                            'parent' => 'civicrm_handler_field_link',
                                                           ),
                       ),
                    );
}

function civicrm_views_location_type( $title, $help ) {
    return array( 
                 'title' => $title,
                 'real field' => 'location_type_id',
                 'help' => $help,
                 'field' => array(
                                  'handler' => 'civicrm_handler_field_location',
                                  ),
                 'filter' => array(
                                   'handler' => 'civicrm_handler_filter_location',
                                   ),
                  );
}

function civicrm_views_activity_type( $title, $help ) {
    return array( 
                 'title'      => $title,
                 'real field' => 'activity_type_id',
                 'help' => $help,
                 'field' => array(
                                  'handler' => 'civicrm_handler_field_activityType',
                                  ),
                 'filter' => array(
                                   'handler' => 'civicrm_handler_filter_activityType',
                                   ),
                 );
}

function civicrm_views_state_province( ) {
    return array(
                 'title' => t('State'),
                 'help'  => t('State'),
                 'real field' => 'state_province_id',
                 'field' => array(
                                  'handler' => 'civicrm_handler_field_state',
                                  ),
                 'filter' => array(
                                   'handler' => 'civicrm_handler_filter_state',
                                   ),
                 );
}

function civicrm_views_country( ) {
    return array(
                 'title' => t('Country'),
                 'help'  => t('Country'),
                 'real field' => 'country_id',
                 'field' => array(
                                  'handler' => 'civicrm_handler_field_country',
                                  ),
                 'filter' => array(
                                   'handler' => 'civicrm_handler_filter_country',
                                   ),
                 );
}
