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
 * Admin page for managing AI policy consent withdrawal.
 *
 * @package    tool_consentwithdraw
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();
$context = context_system::instance();
require_capability('tool/consentwithdraw:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/admin/tool/consentwithdraw/index.php'));
$PAGE->set_title(get_string('admin_page_title', 'tool_consentwithdraw'));
$PAGE->set_heading(get_string('admin_page_title', 'tool_consentwithdraw'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

$templatedata = [
    'sesskey'          => sesskey(),
    'userid'           => 0,
    'search_label'     => get_string('admin_search_label', 'tool_consentwithdraw'),
    'search_help'      => get_string('admin_search_help', 'tool_consentwithdraw'),
    'no_user_selected' => get_string('admin_no_user_selected', 'tool_consentwithdraw'),
];

echo $OUTPUT->render_from_template('tool_consentwithdraw/admin_page', $templatedata);

$PAGE->requires->js_call_amd('tool_consentwithdraw/admin_tool', 'init');

echo $OUTPUT->footer();
