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
 * External function: revoke_user.
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
 * Allow a manager to revoke the AI policy consent of any user.
 */
class revoke_user extends external_api {

    /**
     * Parameter definitions.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID whose consent records should be deleted'),
        ]);
    }

    /**
     * Delete all consent records for the given user (requires manage capability).
     *
     * @param  int   $userid
     * @return array
     */
    public static function execute(int $userid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['userid' => $userid]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('tool/consentwithdraw:manage', $context);

        $records = $DB->get_records('ai_policy_register', ['userid' => $params['userid']]);
        if (empty($records)) {
            throw new \moodle_exception('error_no_record', 'tool_consentwithdraw');
        }

        $DB->delete_records('ai_policy_register', ['userid' => $params['userid']]);

        // Invalidate the Moodle 5.1 core AI policy cache so the policy popup
        // is shown immediately on the user's next page load — not after cache expiry.
        try {
            $policycache = \cache::make('core', 'ai_policy');
            $policycache->delete((int)$params['userid']);
        } catch (\Throwable $e) {
            // Cache may not exist in all environments — safe to ignore.
        }

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
