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
 * Library functions for tool_consentwithdraw.
 *
 * @package    tool_consentwithdraw
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extend user settings navigation with a consent withdraw link.
 *
 * This callback is invoked by Moodle's navigation system and adds a link to
 * the user's account settings navigation block.
 *
 * @param settings_navigation $settingsnav The settings navigation object.
 * @param context             $context     Current context.
 */
function tool_consentwithdraw_extend_navigation_user_settings(settings_navigation $settingsnav, context $context) {
    global $PAGE;

    if (!isloggedin() || isguestuser()) {
        return;
    }

    $usernode = $settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
    if ($usernode) {
        $url = new moodle_url('#', ['data-action' => 'open-consent-modal']);
        $usernode->add(
            get_string('withdraw_ai_consent', 'tool_consentwithdraw'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'consentwithdraw',
            new pix_icon('i/lock', '')
        );

        // Initialise the AMD consent modal module so the link is functional.
        $PAGE->requires->js_call_amd('tool_consentwithdraw/consent_modal', 'init');
    }
}
