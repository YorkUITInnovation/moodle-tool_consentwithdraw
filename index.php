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

$form = new \tool_consentwithdraw\form\user_search_form();

// Status section — populated after the form is submitted.
$statushtml = '';
$selecteduserid = 0;

if ($data = $form->get_data()) {
    $selecteduserid = (int)$data->userid;
}

if ($selecteduserid > 0) {
    $record = $DB->get_record('ai_policy_register', ['userid' => $selecteduserid]);
    $selecteduser = core_user::get_user($selecteduserid, 'id, firstname, lastname, email', MUST_EXIST);

    if ($record) {
        $revokeurl = new moodle_url('/admin/tool/consentwithdraw/index.php', [
            'userid'  => $selecteduserid,
            'action'  => 'revoke',
            'sesskey' => sesskey(),
        ]);
        $statushtml = $OUTPUT->notification(
            get_string('policy_accepted', 'tool_consentwithdraw'),
            \core\output\notification::NOTIFY_INFO,
            false
        );
        $statushtml .= html_writer::tag('p',
            html_writer::link(
                '#',
                get_string('btn_withdraw', 'tool_consentwithdraw'),
                [
                    'class'           => 'btn btn-danger',
                    'data-action'     => 'admin-revoke',
                    'data-userid'     => $selecteduserid,
                    'data-username'   => fullname($selecteduser),
                    'data-sesskey'    => sesskey(),
                ]
            )
        );
    } else {
        $statushtml = $OUTPUT->notification(
            get_string('policy_not_accepted', 'tool_consentwithdraw'),
            \core\output\notification::NOTIFY_WARNING,
            false
        );
    }
}

// Handle revoke action (POST via AMD ajax stays separate; this handles a direct link fallback).
$action = optional_param('action', '', PARAM_ALPHA);
if ($action === 'revoke') {
    require_sesskey();
    $revokeuserid = required_param('userid', PARAM_INT);
    $DB->delete_records('ai_policy_register', ['userid' => $revokeuserid]);
    redirect(
        new moodle_url('/admin/tool/consentwithdraw/index.php'),
        get_string('success_revoked', 'tool_consentwithdraw'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();

$form->display();

if ($statushtml) {
    echo html_writer::div($statushtml, 'mt-3');
}

// Load the confirmation-modal JS only (no datasource or autocomplete wiring needed).
$PAGE->requires->js_call_amd('tool_consentwithdraw/admin_tool', 'init');

echo $OUTPUT->footer();
