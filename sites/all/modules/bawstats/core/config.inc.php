<?php
/**
 * betterawstats - an alternative display for awstats data
 *
 * @author      Oliver Spiesshofer, support at betterawstats dot com
 * @copyright   2008 Oliver Spiesshofer
 * @version     1.0
 * @link        http://betterawstats.com
 *
 * Based on the GPL AWStats Totals script by:
 * Jeroen de Jong <jeroen@telartis.nl>
 * copyright   2004-2006 Telartis
 * version 1.13 (http://www.telartis.nl/xcms/awstats)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


/**
 * File contents:
 *
 * This file contains only function related to the online config manager. It handles
 * editing & saving the config as well as logging the admin in and out.
 */

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'config_default.php') !== false) {
    die ('This file can not be used on its own!');
}
/*
* Saves the config settings to file
*
*/
function baw_action_save_config() {
    global $BAW_CONF, $BAW_CONF_DEF, $BAW_CONF_DIS_DEF, $BAW_CONF_DIS;
    global $BAW_MES, $BAW_CONF_DIS_TYP, $BAW_FILE_HEADER;
    @session_start();
    if (!baw_check_user()) {
        return baw_site_header() . baw_config_login_form();
    };

    if (isset($_POST['exit']) && ($_POST['exit'] == $BAW_MES['cfg_exit'])) {
        return baw_display_index();
    }
    if (isset($_POST['logout']) && ($_POST['logout'] == $BAW_MES['cfg_logout'])) {
        baw_logout();
        return baw_display_index();
    }
    if (!$BAW_CONF['online_config']) {
        return baw_raise_error('configdisabled');
    }
    $filename = $BAW_CONF['site_path'].'/config.php';
    if (is_writable($filename)) {
        // prepare config for writing
        $str = "<?php\r\n";
        $intro = $BAW_MES['config_intro'];
        $str .= $BAW_FILE_HEADER;
        $str .= wordwrap($intro, 75, "\r\n// ");

        foreach ($BAW_CONF_DEF as $sect => $sectdata) {
            $sect = strtoupper($sect);
            $str .= "\r\n\r\n//*********************************************************/\r\n"
                 . "//*          $sect\r\n"
                 . "//*********************************************************/\r\n\r\n";
            foreach ($sectdata as $setting => $misc) {
                if ($misc['type'] !== 'fixed') {
                    // set standard first
                    $allowed_values = '';
                    $val = $_POST[$setting];
                    $default = $misc['default'];
                    $val_str = "'$val'";
                    $default_str = "'$default'";
                    // now set the execptions
                    if ($val == 'true' or $val == 'false') {
                        $val_str = "$val";
                        if ($default == true) {
                            $default_str = 'true';
                        } else {
                            $default_str = 'false';
                        }
                        $allowed_values = "// POSSIBLE VALUES: true, false\r\n";
                    } else if ($val == "'"){
                        $val_str = '"\'"';
                    } else {
                        $val_str = "'$val'";
                    }
                    if ($default == "'"){
                        $default_str = '"\'"';
                    }

                    if ($setting == 'limit_server') { // dont print domain names into the config file
                        $allowed_values = "// POSSIBLE VALUES: 'sitename.org', 'show_all'\r\n";//
                    } else if ($misc['type'] == 'dropdown' && ($val !== 'true' && $val !== 'false')) {
                        $allowed_values = implode("', '", array_keys($misc['values']));
                        $allowed_values = wordwrap($allowed_values, 60, "\r\n//                  ");
                        $allowed_values = "// POSSIBLE VALUES: '$allowed_values'\r\n";
                    } else if ($misc['type'] == 'password') {
                        $val = $_POST[$setting];
                        if ($val[0] !== $val[1]) {
                            echo baw_raise_error('password_match');
                            exit;
                        } else if ($val[0] == '') { // password will not change if empty
                            $val_str = "'{$BAW_CONF['online_config_password']}'";
                        } else if (strlen($val[0]) < 5) { // password too short
                            echo baw_raise_error('password_short');
                            exit;
                        } else {
                            $val_str = "'{$val[0]}'";
                        }
                    }
                    $out_help = wordwrap(
                        strip_tags(html_entity_decode($misc['help'])),
                        68
                        , "\r\n//          "
                    );
                    $name = html_entity_decode($misc['name']);
                    $str .= "// {$BAW_MES['cfg_name']} $name\r\n"
                          . "// {$BAW_MES['cfg_info']} {$out_help}\r\n"
                          . "// {$BAW_MES['cfg_def']} $default_str\r\n"
                          . $allowed_values
                          . "\$BAW_CONF['$setting'] = $val_str;\r\n\r\n";
                }
            }
        }
        $str .= "\r\n\r\n//*********************************************************/\r\n"
             . "//*          {$BAW_MES['cfg_display']}\r\n"
             . "//*********************************************************/\r\n\r\n";

        foreach ($BAW_CONF_DIS_DEF as $setting => $misc) {
            $val_arr = $_POST[$setting];
            $name = html_entity_decode($misc['name']);
            $help = html_entity_decode($misc['help']);
            $str .= "// {$BAW_MES['cfg_name']} {$name}\r\n"
                  . "// {$BAW_MES['cfg_info']} {$help}\r\n";
            $this_conf = $BAW_CONF_DIS[$setting];
            $substr ='';
            foreach ($this_conf as $name => $oldval) {
                $type = $BAW_CONF_DIS_TYP[$name][1];
                $desc = $BAW_CONF_DIS_TYP[$name][0];
                $val = $val_arr[$name];
                if ($type == 'string') {
                    $val = "'$val'";
                } else if ($type == 'sorting1') {
                    $desc2 = $BAW_MES['cfg_possible_values'] ."\r\n    // ";
                    foreach ($misc['sorting'] as $key => $value) {
                        $desc2 .= "'$key'=$value, ";
                    }
                    $desc2 = substr($desc2, 0, -2);
                    $desc2 = wordwrap($desc2, 75, "\r\n    // ");
                    $desc .= $desc2;
                    $val = "'$val'";
                } else if ($type == 'sorting2') {
                    $desc .= $BAW_MES['cfg_possible_values'] . "\r\n"
                        . "    // " . $BAW_MES['cfg_type_sort_dir_opts'];
                } else {
                    $val = "$val";
                }
                $desc = strip_tags(html_entity_decode($desc));
                $substr .= "    '$name' => $val,    // $desc\r\n";
            }
            $str .= "\$BAW_CONF_DIS['$setting'] = array(\r\n$substr);\r\n\r\n";
        }
        $str .= "\r\n?>";
        if (!$handle = fopen($filename, 'w+')) {
            return baw_raise_error('configread');
        }
        if (fwrite($handle, $str) === FALSE) {
            return baw_raise_error('configwrite');
        }

        $out = $BAW_MES['cfg_saved'];
        $out = baw_site_header() . "<div class=\"errorbox\"><h1>BetterAWstats</h1>$out</div></body></html>";
        fclose($handle);
    } else {
        $out = baw_site_header() . baw_raise_error('configwrite');
    }
    return $out;
}

/*
* Shows the login form
*
*/
function baw_config_login_form(){
    global $BAW_MES, $BAW_CONF;
    $out = "<form class=\"login_form\" action=\"{$BAW_CONF['site_url']}/index.php\" method=\"post\">\n"
        ."<p>"
        . $BAW_MES['require_password'] . "<br" . XHTML . ">". "<br" . XHTML . ">"
        . "<input type=\"password\" name=\"password\" value=\"\" size=\"20\"" . XHTML . "> "
        . "<input type=\"hidden\" name=\"action\" value=\"do_login\"" . XHTML . "> "
        . "<input type=\"submit\" name=\"submit\" value=\"Ok\"" . XHTML . ">\n"
        . "<input type=\"submit\" name=\"exit\" value=\"{$BAW_MES['cfg_exit']}\"" . XHTML . ">\n"
        ."</p>"
        . "</form>";
    return $out;
}

/*
* Checks the password and logs the user in if OK
*
*/
function baw_action_do_login() {
    global $BAW_CONF, $BAW_MES;
    if (isset($_POST['exit']) && ($_POST['exit'] == $BAW_MES['cfg_exit'])) {
        return baw_display_index();
    }
    if (isset($_POST['password'])) {
        if ($_POST['password'] == $BAW_CONF['online_config_password']) {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['password'] = MD5($_POST['password']);
            $out = baw_action_config_editor();
        } else {
            $out = baw_site_header()
                . baw_raise_error('password_incorrect')
                . baw_config_login_form();
        }
    } else {
        $out = baw_site_header()
            . baw_config_login_form();
    }
    echo $out;
}

/*
* Check if the user is logged in and if the password has changed
*
*/
function baw_check_user() {
    global $BAW_CONF;
    if (isset($_SESSION['loggedin']) &&
        $_SESSION['loggedin'] == true &&
        isset ($_SESSION['password']) &&
        strlen($BAW_CONF['online_config_password']) > 5 &&
        $_SESSION['password'] == MD5($BAW_CONF['online_config_password'])) {
        return true;
    }
    if (strlen($BAW_CONF['online_config_password']) <= 5) {
        echo "The password has to be longer than 5 letters!";
    }
    return false;
}

/*
* This logs the user out and displays the index.
*
*/
function baw_action_do_logout() {
    baw_logout();
    echo baw_display_index();
}

/*
* This logs the user out physically.
*
*/
function baw_logout() {
    @session_start();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
    }
    @session_destroy();
}

/*
* Displays the config editor.
*
*/
function baw_action_config_editor() {
    global $BAW_CONF, $BAW_CONF_DEF, $BAW_CONF_DIS_DEF, $BAW_CONF_DIS;
    global $BAW_MES, $BAW_CONF_DIS_TYP, $BAW_DFILES;
    if (!$BAW_CONF['online_config']) {
        return baw_raise_error('configdisabled');
    }
    @session_start();
    if(!baw_check_user()) {
        return baw_site_header() . baw_config_login_form();
    };
    $top = baw_site_header();
    $top .= "\n<form action=\"{$BAW_CONF['site_url']}/index.php\" method=\"post\">\n"
        . "  <div class=\"clearfix\">\n"
        . "    <input type=\"hidden\" name=\"action\" value=\"save_config\"" . XHTML . ">\n";
    $submit = "    <div class=\"conf_button_active\">\n      <input type=\"submit\" name=\"submit\" value=\"{$BAW_MES['cfg_save']}\"" . XHTML . ">\n"
        . "      <input type=\"reset\" name=\"reset\" value=\"{$BAW_MES['cfg_reset']}\"" . XHTML . ">\n"
        . "      <input type=\"submit\" name=\"exit\" value=\"{$BAW_MES['cfg_exit']}\"" . XHTML . ">\n"
        . "      <input type=\"submit\" name=\"logout\" value=\"{$BAW_MES['cfg_logout']}\"" . XHTML . ">\n"
        . "    </div>\n";
    $i = 0;
    $out = '';
    $section_count = count($BAW_CONF_DEF) + 1;
    foreach ($BAW_CONF_DEF as $section => $set_arr) {
        $out .= "<!-- CONF SECTION $i -->\n\n";
        if ($i >0) {
            $top .= "    <div class=\"conf_button_inactive\" id=\"button_1_$i\" onclick=\"toggleBox($i, 1, $section_count, 'conf');\">$section</div>\n";
            $out .= "  <div class=\"conf_section_inactive\" id=\"box_1_$i\">\n";
        } else {
            $top .= "    <div class=\"conf_button_active\" id=\"button_1_$i\" onclick=\"toggleBox($i, 1, $section_count, 'conf');\">$section</div>\n";
            $out .= "  <div class=\"conf_section_active\" id=\"box_1_$i\">\n";
        }
        foreach ($set_arr as $cfgname => $cfgdata) {
            $default = $cfgdata['default'];
            if (isset($BAW_CONF[$cfgname])) {
                $val = $BAW_CONF[$cfgname];
            } else {
                $val = $BAW_CONF_DEF[$section][$cfgname]['default'];
            }
            if ($cfgdata['type'] == 'fixed') {
                $input = $val;
            } else if ($cfgdata['type'] == 'string') {
                $input = "<input type=\"text\" name=\"$cfgname\" value=\"$val\" size=\"40\"" . XHTML . ">";
            } else if ($cfgdata['type'] == 'password') {
                $input = "<input type=\"password\" name=\"{$cfgname}[]\" value=\"\" size=\"40\"" . XHTML . "><br" . XHTML . ">"
                    . $BAW_MES['confirm_password']
                    . "<input type=\"password\" name=\"{$cfgname}[]\" value=\"\" size=\"40\"" . XHTML . ">";
            } else if ($cfgdata['type'] == 'dropdown') {
                $input = baw_generic_dropdown($cfgname, $cfgdata['values'],$val);
                $default = $cfgdata['values'][$cfgdata['default']];
            } else if ($cfgdata['type'] == 'server_select') {
                $input = baw_generic_dropdown($cfgname, $servers_arr, $BAW_DFILES['cfg_server_drop']);
            }
        $out .= "    <div class=\"conf_setting\"><h3>{$cfgdata['name']}</h3>\n"
            . $cfgdata['help'] . "<br " . XHTML . ">"
            . "$cfgname = $input"
            . "<br " . XHTML . ">(Default: \"<span class=\"default\">$default</span>\")\n"
            . "    </div>\n";
        }
        $out .= "  </div>\n";
        $i++;

    }
    $top .= "    <div class=\"conf_button_inactive\" id=\"button_1_$i\" onclick=\"toggleBox($i, 1, $section_count, 'conf');\">{$BAW_MES['cfg_display2']}</div>\n";
    $out .= "<!-- CONF SECTION $i -->\n\n";
    $out .= "  <div class=\"conf_section_inactive\" id=\"box_1_$i\">\n";
    $BAW_CONF_DIS = baw_array_sorting($BAW_CONF_DIS, 'order', SORT_ASC);
    foreach ($BAW_CONF_DIS as $cfgname => $cfgdata) {
        $input = '';
        $default = '';
        $name = $BAW_CONF_DIS_DEF[$cfgname]['name'];
        $help = $BAW_CONF_DIS_DEF[$cfgname]['help'];

        foreach ($BAW_CONF_DIS_TYP as $setname => $setdata) {
            if (isset($cfgdata[$setname])) {
                $val = $BAW_CONF_DIS[$cfgname][$setname];
                if ($setdata[1] == 'bool') {
                    if ($val == true) {
                        $selyes = ' checked="checked"';
                        $selno = "";
                    } else {
                        $selno = ' checked="checked"';
                        $selyes = "";
                    }
                    $input .= "<fieldset><legend>{$setdata[0]}</legend>\n"
                        ."{$BAW_MES[112]} <input type=\"radio\" name=\"".$cfgname."[".$setname."]\" value=\"true\"$selyes" . XHTML . "> "
                        . "{$BAW_MES[113]} <input type=\"radio\" name=\"".$cfgname."[".$setname."]\" value=\"false\"$selno" . XHTML . "> "
                        . "</fieldset>\n";
                } else if ($setdata[1] == 'string') {
                    $input .= "<fieldset><legend>{$setdata[0]}</legend>\n"
                        ."<input type=\"text\" name=\"".$cfgname."[".$setname."]\" value=\"$val\" size=\"5\"" . XHTML . ">"
                        . "</fieldset>\n";
                } else if ($setdata[1] == 'sorting1') {
                    $sort_arr = $BAW_CONF_DIS_DEF[$cfgname]['sorting'];
                    $input .= "<fieldset><legend>{$setdata[0]}</legend>\n"
                        . baw_generic_dropdown($cfgname."[".$setname."]", $sort_arr,$val)
                        . "</fieldset>\n";
                } else if ($setdata[1] == 'sorting2') {
                    if ($val == 4) {
                        $selyes = ' checked="checked"';
                        $selno = '';
                    } else {
                        $selno = ' checked="checked"';
                        $selyes = '';
                    }
                    $input .= "<fieldset><legend>{$setdata[0]}</legend>\n"
                        ."{$BAW_MES['cfg_asc']} <input type=\"radio\" name=\"".$cfgname."[".$setname."]\" value=\"SORT_ASC\"$selyes" . XHTML . "> "
                        . "{$BAW_MES['cfg_desc']} <input type=\"radio\" name=\"".$cfgname."[".$setname."]\" value=\"SORT_DESC\"$selno" . XHTML . "> "
                        . "</fieldset>\n";
                }
            }
        }
        $out .= "<div class=\"conf_setting\"><h3>$name</h3>\n"
            . $help . "<br " . XHTML . ">"
            . "<div class=\"clearfix\">$input</div>"
            . $default
            . "</div>\n";
    }

    $out .= "</div>\n"
        . "</form>"
        . baw_site_footer();
    return $top . "$submit  </div>\n". $out;
}

$BAW_FILE_HEADER = "/**
 * betterawstats - an alternative display for awstats data
 *
 * @author      Oliver Spiesshofer, support at betterawstats dot com
 * @copyright   2008 Oliver Spiesshofer
 * @version     1.0
 * @link        http://betterawstats.com
 *
 * Based on the GPL AWStats Totals script by:
 * Jeroen de Jong <jeroen@telartis.nl>
 * copyright   2004-2006 Telartis
 * version 1.13 (http://www.telartis.nl/xcms/awstats)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// this file can't be used on its own - do not change these 3 lines
if (strpos (\$_SERVER['PHP_SELF'], 'config.php') !== false) {
    die ('This file can not be used on its own!');
}
";


?>