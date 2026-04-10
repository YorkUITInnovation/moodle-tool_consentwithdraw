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
 * Extend the global navigation tree.
 *
 * This fires on every page load and is used to register the AMD consent-modal
 * module for logged-in non-guest users so the trigger link in user settings is
 * always functional.
 *
 * Note on user-menu integration: Moodle 5.x does not expose a standard plugin
 * callback to inject items directly into the top-bar user dropdown. The most
 * maintainable extension point is therefore the user-account settings navigation
 * node (see {@see tool_consentwithdraw_extend_navigation_user_settings}).  The AMD
 * module is loaded here so it handles the click from that node on every page.
 *
 * @param \global_navigation $navigation The global navigation object.
 */
function tool_consentwithdraw_extend_navigation(\global_navigation $navigation) {
    global $PAGE;

    if (!isloggedin() || isguestuser()) {
        return;
    }

    $PAGE->requires->js_call_amd('tool_consentwithdraw/consent_modal', 'init');
}

/**
 * Extend user settings navigation with a consent withdraw link.
 *
 * This callback is invoked by Moodle's navigation system and adds a link to
 * the user's account settings navigation block.
 *
 * @param \navigation_node $navigation    The user settings navigation node.
 * @param stdClass         $user          The user whose settings are being built.
 * @param \context_user   $usercontext   The user's context.
 * @param stdClass         $course        The current course.
 * @param \context_course $coursecontext The course context.
 */
function tool_consentwithdraw_extend_navigation_user_settings(
    \navigation_node $navigation,
    stdClass $user,
    $usercontext,
    stdClass $course,
    $coursecontext
) {
    if (!isloggedin() || isguestuser()) {
        return;
    }

    $url = new \moodle_url('/user/preferences.php', ['consentwithdraw' => 1]);

    $usernode = $navigation->find('useraccount', \navigation_node::TYPE_CONTAINER);
    if ($usernode) {
        $usernode->add(
            get_string('withdraw_ai_consent', 'tool_consentwithdraw'),
            $url,
            \navigation_node::TYPE_SETTING,
            null,
            'consentwithdraw',
            new \pix_icon('i/lock', '')
        );
        return;
    }

    $navigation->add(
        get_string('withdraw_ai_consent', 'tool_consentwithdraw'),
        $url,
        \navigation_node::TYPE_SETTING,
        null,
        'consentwithdraw',
        new \pix_icon('i/lock', '')
    );
}
