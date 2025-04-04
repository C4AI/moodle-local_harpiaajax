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
 * Definition of the service provided by this plugin.
 *
 * @package    local_harpiaajax
 * @copyright  2024 C4AI - USP
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$functions = [
    'local_harpiaajax_send_message_to_datafield_harpiainteraction' => [
        'classname' => 'send_message',
        'classpath' => 'local/harpiaajax/send_message.php',
        'methodname' => 'execute_to_datafield_harpiainteraction',
        'description' => 'Send a message and obtain its output (used by the HarpIA Interaction database plugin)',
        'type' => 'write',
        'ajax' => true,
    ]
];