<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Settings for 'local_harpiaajax'.
 *
 * @package    local_harpiaajax
 * @copyright  2024 C4AI - USP
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if ($hassiteconfig) { 
    $settings = new admin_settingpage('local_harpiaajax', get_string('pluginname', 'local_harpiaajax'));
    $ADMIN->add('localplugins', $settings);

    $setting = new admin_setting_configtext(
        // setting id:
        'local_harpiaajax/answerprovideraddress', 
        // setting name:                                        
        get_string('answer_provider_address', 'local_harpiaajax'),     
        // setting description: 
        get_string('answer_provider_address_desc', 'local_harpiaajax'),
        // default value: 
        '',
        // type:                                                             
        PARAM_URL                                                    
    );

    $settings->add($setting);

}
