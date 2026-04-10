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
 * External function: check_status.
 *
 * @package    tool_consentwithdraw
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_consentwithdraw\external;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_system;

/**
 * Check whether a user has accepted the AI policy.
 */
class check_status extends external_api {

    /**
     * Parameter definitions.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID to check (0 = current user)', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Return the consent status for the given user.
     *
     * @param  int   $userid  0 means current user.
     * @return array
     */
    public static function execute(int $userid = 0): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);

        $context = context_system::instance();
        self::validate_context($context);

        $targetuserid = (int)$params['userid'] ?: (int)$USER->id;

        if ($targetuserid !== (int)$USER->id) {
            require_capability('tool/consentwithdraw:manage', $context);
        } else {
            require_login();
        }

        $record = $DB->get_record('ai_policy_register', ['userid' => $targetuserid]);

        return [
            'userid'   => $targetuserid,
            'accepted' => !empty($record),
            'recordid' => $record ? (int)$record->id : 0,
        ];
    }

    /**
     * Return value definitions.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'userid'   => new external_value(PARAM_INT,  'User ID'),
            'accepted' => new external_value(PARAM_BOOL, 'Whether AI policy has been accepted'),
            'recordid' => new external_value(PARAM_INT,  'Record ID, 0 if not found'),
        ]);
    }
}
