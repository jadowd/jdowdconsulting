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
 * @copyright CiviCRM LLC (c) 2004-2008
 * $Id$
 *
 */

class CRM_Mailing_MailStore
{
    // flag to decide whether to print debug messages
    var $_debug = true;

    /**
     * Return the proper mail store implementation, based on config settings
     *
     * @return object  mail store implementation for processing CiviMail-bound emails
     */
    function getStore()
    {
        // FIXME: get the params from the config
        $class = 'IMAP';

        switch ($class) {

        case 'IMAP':
            $server = 'server';
            $user   = 'username';
            $pass   = 'password';
            $ssl    = true;
            $folder = 'Inbox';
            require_once 'CRM/Mailing/MailStore/Imap.php';
            return new CRM_Mailing_MailStore_Imap($server, $user, $pass, $ssl, $folder);

        case 'POP3':
            $server = 'server';
            $user   = 'username';
            $pass   = 'password';
            $ssl    = true;
            require_once 'CRM/Mailing/MailStore/Pop3.php';
            return new CRM_Mailing_MailStore_Pop3($server, $user, $pass, $ssl);

        case 'Maildir':
            $path = '/proper/directory';
            require_once 'CRM/Mailing/MailStore/Maildir.php';
            return new CRM_Mailing_MailStore_Maildir($path);

        // DO NOT USE the mbox transport for anything other than testing
        // in particular, it does not clear the mbox afterwards
        case 'mbox':
            $file = '/proper/file';
            require_once 'CRM/Mailing/MailStore/Mbox.php';
            return new CRM_Mailing_MailStore_Mbox($file);
        }
    }

    /**
     * Return all emails in the mail store
     *
     * @return array  array of ezcMail objects
     */
    function allMails()
    {
        $set = $this->_transport->fetchAll();
        $mails = array();
        $parser = new ezcMailParser;
        foreach ($set->getMessageNumbers() as $nr) {
            if ($this->_debug) print "retrieving message $nr\n";
            $single = $parser->parseMail($this->_transport->fetchByMessageNr($nr));
            $mails[$nr] = $single[0];
        }
        return $mails;
    }

    /**
     * Point to (and create if needed) a local Maildir for storing retrieved mail
     *
     * @param string $name  name of the Maildir
     * @return string       path to the Maildir's cur directory
     */
    function maildir($name)
    {
        $config =& CRM_Core_Config::singleton();
        $dir = $config->customFileUploadDir . DIRECTORY_SEPARATOR . $name;
        foreach (array('cur', 'new', 'tmp') as $sub) {
            if (!file_exists($dir . DIRECTORY_SEPARATOR . $sub)) {
                if ($this->_debug) print "creating $dir/$sub\n";
                if (!mkdir($dir . DIRECTORY_SEPARATOR . $sub, 0700, true)) {
                    throw new Exception('Could not create ' . $dir . DIRECTORY_SEPARATOR . $sub);
                }
            }
        }
        return $dir . DIRECTORY_SEPARATOR . 'cur';
    }
}
