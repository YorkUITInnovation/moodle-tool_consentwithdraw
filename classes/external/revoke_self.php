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
 * External function: revoke_self.
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
 * Allow a logged-in user to revoke their own AI policy consent.
 */
class revoke_self extends external_api {

    /**
     * Parameter definitions.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'recordid' => new external_value(PARAM_INT, 'ai_policy_register record ID to delete'),
        ]);
    }

    /**
     * Delete the consent record, verifying it belongs to the current user.
     *
     * @param  int   $recordid
     * @return array
     */
    public static function execute(int $recordid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['recordid' => $recordid]);

        $context = context_system::instance();
        self::validate_context($context);
        require_login();

        $record = $DB->get_record('ai_policy_register', ['id' => $params['recordid']], '*', MUST_EXIST);

        if ((int)$record->userid !== (int)$USER->id) {
            throw new \moodle_exception('error_permission', 'tool_consentwithdraw');
        }

        $DB->delete_records('ai_policy_register', ['id' => $params['recordid']]);

        return [
            'success' => true,
            'message' => get_string('success_revoked', 'tool_consentwithdraw'),
        ];
    }

    /**
     * Return value definitions.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the operation succeeded'),
            'message' => new external_value(PARAM_TEXT, 'Result message'),
        ]);
    }
}
