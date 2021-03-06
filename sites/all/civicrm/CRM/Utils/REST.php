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
 * This class handles all REST client requests.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 *
 */

class CRM_Utils_REST
{
    /**
     * Number of seconds we should let a soap process idle
     * @static
     */
    static $rest_timeout = 0;
    
    /**
     * Cache the actual UF Class
     */
    public $ufClass;

    /**
     * Class constructor.  This caches the real user framework class locally,
     * so we can use it for authentication and validation.
     *
     * @param  string $uf       The userframework class
     */
    public function __construct() {
        // any external program which call SoapServer is responsible for
        // creating and attaching the session
        $args = func_get_args( );
        $this->ufClass = array_shift( $args );
    }

    /**
     * Simple ping function to test for liveness.
     *
     * @param string $var   The string to be echoed
     * @return string       $var
     * @access public
     */
    public function ping($var) {
        $session =& CRM_Core_Session::singleton();
        $key = $session->get('key');
        $session->set( 'key', $var );
        return self::simple( array( 'message' => "PONG: $var ($key)" ) );
    }


    /**
     * Verify a REST key
     *
     * @param string $key   The soap key generated by authenticate()
     * @return none
     * @access public
     */
    public function verify($key) {
        $session =& CRM_Core_Session::singleton();

        $rest_key = $session->get('rest_key');
        
        if ( $key !== sha1($rest_key) ) {
            return false;
        }
        
        $t = time();
        if ( self::$rest_timeout && 
             $t > ($session->get('rest_time') + self::$rest_timeout)) {
            return false;
        }
        
        /* otherwise, we're ok.  update the timestamp */
        $session->set('rest_time', $t);
        return true;
    }
    
    /**
     * Authentication wrapper to the UF Class
     *
     * @param string $name      Login name
     * @param string $pass      Password
     * @return string           The REST Client key
     * @access public
     * @static
     */
    public function authenticate($name, $pass) {
        require_once 'CRM/Utils/System.php';
        
        // first check for civicrm site key
        if ( ! CRM_Utils_System::authenticateKey( false ) ) {
            return self::error( 'Could not authenticate user, invalid site key. More info at: http://wiki.civicrm.org/confluence/display/CRMDOC/Command-line+Script+Configuration.' );
        }

        $result =& CRM_Utils_System::authenticate($name, $pass);
        
        if (empty($result)) {
            return self::error( 'Could not authenticate user, invalid name / password.' );
        }
        
        $session =& CRM_Core_Session::singleton();
        $session->set('rest_key', $result[2]);
        $session->set('rest_time', time());
        
        return self::simple( array( 'key' => sha1( $result[2] ) ) );
    }

    function error( $message = 'Unknown Error' ) {
        $values =
            array( 'error_message' => $message,
                   'is_error'      => 1 );
        return $values;
    }

    function simple( $params ) {
        $values  = array( 'is_error' => 0 );
        $values += $params;
        return $values;
    }

    function run( &$config ) {
        $result = self::handle( $config );

        return self::output( $config, $result );
    }

    function output( &$config, &$result ) {
        $hier = false;
        if ( is_scalar( $result ) ) {
            if ( ! $result ) {
                $result = 0;
            }
            $result = self::simple( array( 'result' => $result ) );
        } else if ( is_array( $result ) ) {
            if ( CRM_Utils_Array::isHierarchical( $result ) ) {
                $hier = true;
            } else if ( ! array_key_exists( 'is_error', $result ) ) {
                $result['is_error'] = 0;
            }
        } else {
            $result = self::error( 'Could not interpret return values from function.' );
        }

        if ( CRM_Utils_Array::value( 'json', $_GET ) ) {
            require_once 'Services/JSON.php';
            $json =& new Services_JSON( );
            return $json->encode( $result ) . "\n";
        }
        
        $xml = "<?xml version=\"1.0\"?>
<ResultSet xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
";
        // check if this is a single element result (contact_get etc)
        // or multi element
        if ( $hier ) {
            foreach ( $result as $n => $v ) {
                $xml .= "<Result>\n" . CRM_Utils_Array::xml( $v ) . "</Result>\n";
            }
        } else {
            $xml .= "<Result>\n" . CRM_Utils_Array::xml( $result ) . "</Result>\n";
        }

        $xml .= "</ResultSet>\n";
        return $xml;
    }

    function handle( $config ) {

        $q = CRM_Utils_array::value( 'q', $_GET );
        $args = explode( '/', $q );
        if ( $args[0] != 'civicrm' ) {
            return self::error( 'Unknown function invocation.' );
        }

        if ( ( count( $args ) != 3 ) && ( $args[1] != 'login' ) ) {
            return self::error( 'Unknown function invocation.' );
        }

        require_once 'CRM/Utils/Request.php';

        $store = null;
        if ( $args[1] == 'login' ) {
            $name = CRM_Utils_Request::retrieve( 'name', 'String', $store, false, 'GET' );
            $pass = CRM_Utils_Request::retrieve( 'pass', 'String', $store, false, 'GET' );
            if ( empty( $name ) ||
                 empty( $pass ) ) {
                return self::error( 'Invalid name / password.' );
            }
            return self::authenticate( $name, $pass );
        } else {
            $key = CRM_Utils_Request::retrieve( 'key', 'String', $store, false, null, 'GET' );
            if ( ! self::verify( $key ) ) {
                return self::error( 'Session keys do not match, please re-auth.' );
            }
        }

        $params =& self::buildParamList( );

        $fnGroup = ucfirst($args[1]);
        if ( strpos( $fnGroup, '_' ) ) {
            $fnGroup    = explode( '_', $fnGroup );
            $fnGroup[1] = ucfirst( $fnGroup[1] );
            $fnGroup    = implode( '', $fnGroup );
        }

        $apiPath = substr( $_SERVER['SCRIPT_FILENAME'] , 0 ,-15 ) . 'api/v2/';
        $apiFile = "{$fnGroup}.php";
        
        // check to ensure file exists, else die
        if ( file_exists( $apiPath . $apiFile ) ) {
            require_once $apiPath . $apiFile;
        } else {
            return self::error( 'Unknown function invocation.' );
        }

        $fnName = "civicrm_{$args[1]}_{$args[2]}";
        if ( ! function_exists( $fnName ) ) {
            return self::error( "Unknown function called: $fnName" );
        }

        // trap all fatal errors
        CRM_Core_Error::setCallback( array( 'CRM_Utils_REST', 'fatal' ) );
        $result = $fnName( $params );
        CRM_Core_Error::setCallback( );

        if ( $result === false ) {
            return self::error( 'Unknown error.' );
        }
        return $result;
    }

    function &buildParamList( ) {
        $params = array( );

        $skipVars = array( 'q'   => 1,
                           'key' => 1 );

        foreach ( $_GET as $n => $v ) {
            if ( ! array_key_exists( $n, $skipVars ) ) {
                $params[$n] = $v;
            }
        }

        return $params;
    }

    static function fatal( $pearError ) {
        header( 'Content-Type: text/xml' );
        $error = array();
        $error['code']          = $pearError->getCode();
        $error['error_message'] = $pearError->getMessage();
        $error['mode']          = $pearError->getMode();
        $error['debug_info']    = $pearError->getDebugInfo();
        $error['type']          = $pearError->getType();
        $error['user_info']     = $pearError->getUserInfo();
        $error['to_string']     = $pearError->toString();
        $error['is_error']      = 1;

        $config = CRM_Core_Config::singleton( );
        echo self::output( $config, $error );
        exit( );
    }
}
