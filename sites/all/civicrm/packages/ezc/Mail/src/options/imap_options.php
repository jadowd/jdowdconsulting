<?php
/**
 * File containing the ezcMailImapTransportOptions class
 *
 * @package Mail
 * @version 1.3
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Class containing the options for IMAP transport.
 *
 * The options from ezcMailTransportOptions are inherited.
 *
 * @package Mail
 * @version 1.3
 */
class ezcMailImapTransportOptions extends ezcMailTransportOptions
{
    /**
     * Constructs an object with the specified values.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if $options contains a property not defined
     * @throws ezcBaseValueException
     *         if $options contains a property with a value not allowed
     * @param array(string=>mixed) $options
     */
    public function __construct( array $options = array() )
    {
        parent::__construct( $options );
    }

    /**
     * Sets the option $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name is not defined
     * @throws ezcBaseValueException
     *         if $value is not correct for the property $name
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            default:
                parent::__set( $name, $value );
        }
    }
}
?>
