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
 * A web service plug-in that interacts with an external language model.
 *
 * @package    local_harpiaajax
 * @copyright  2024 C4AI - USP
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$plugin->version = 2025031903;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires = 2024100701;        // Requires this Moodle version.
$plugin->component = 'local_harpiaajax';
$plugin->maturity = MATURITY_ALPHA;