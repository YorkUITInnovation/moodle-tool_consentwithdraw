<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External functions and services.
 *
 * @package    tool_consentwithdraw
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'tool_consentwithdraw_check_status' => [
        'classname'     => 'tool_consentwithdraw\external\check_status',
        'description'   => 'Check AI policy consent status for a user',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_consentwithdraw_revoke_self' => [
        'classname'     => 'tool_consentwithdraw\external\revoke_self',
        'description'   => 'Revoke own AI policy consent',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_consentwithdraw_revoke_user' => [
        'classname'     => 'tool_consentwithdraw\external\revoke_user',
        'description'   => 'Admin: revoke AI policy consent for any user',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
];
