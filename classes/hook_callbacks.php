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
 * Hook callbacks for tool_consentwithdraw.
 *
 * @package    tool_consentwithdraw
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_consentwithdraw;

/**
 * Hook callbacks.
 */
class hook_callbacks {

    /**
     * Adds the consent-withdraw link to the top-right user menu.
     *
     * @param \core_user\hook\extend_user_menu $hook
     */
    public static function extend_user_menu(\core_user\hook\extend_user_menu $hook): void {
        if (!isloggedin() || isguestuser()) {
            return;
        }

        $item = new \stdClass();
        $item->itemtype = 'link';
        $item->url = new \moodle_url('/user/preferences.php', ['consentwithdraw' => 1]);
        $item->title = get_string('withdraw_ai_consent', 'tool_consentwithdraw');
        $item->titleidentifier = 'withdraw_ai_consent,tool_consentwithdraw';
        $hook->add_navitem($item);
    }

    /**
     * Ensures the consent modal AMD bootstrap is loaded for logged-in users.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE;

        if (!isloggedin() || isguestuser()) {
            return;
        }

        $PAGE->requires->js_call_amd('tool_consentwithdraw/consent_modal', 'init');
    }
}
