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
 * Language strings for tool_consentwithdraw.
 *
 * @package    tool_consentwithdraw
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']               = 'AI Policy Consent Withdraw';
$string['withdraw_ai_consent']      = 'Withdraw AI policy consent';
$string['modal_title']              = 'AI Policy Consent';
$string['policy_accepted']          = 'AI Policy accepted.';
$string['policy_not_accepted']      = 'AI Policy has not been accepted.';
$string['btn_withdraw']             = 'Withdraw/Revoke';
$string['confirm_title']            = 'Confirm Consent Withdrawal';
$string['confirm_message']          = 'Are you sure you want to withdraw your AI policy consent? This action cannot be undone.';
$string['confirm_admin_message']    = 'Are you sure you want to withdraw AI policy consent for this user? This action cannot be undone.';
$string['btn_confirm']              = 'Confirm';
$string['btn_cancel']               = 'Cancel';
$string['success_revoked']          = 'AI policy consent has been successfully withdrawn.';
$string['error_no_record']          = 'No AI policy consent record found.';
$string['error_permission']         = 'You do not have permission to perform this action.';
$string['error_general']            = 'An error occurred. Please try again.';
$string['admin_page_title']         = 'Withdraw AI Policy Consent';
$string['admin_search_label']            = 'Search user';
$string['admin_search_label_help']       = 'Search by firstname, lastname, email, username, or idnumber.';
$string['admin_search_button']           = 'Check consent status';
$string['admin_search_help']             = 'Search by firstname, lastname, email, username, or idnumber.';
$string['admin_no_user_selected']        = 'Please search for and select a user.';
$string['admin_user_status']        = 'User consent status';
$string['consentwithdraw_manage']   = 'Manage AI policy consent withdrawal';
$string['privacy:metadata:core_ai'] = 'This plugin reads and deletes consent records stored by the core AI subsystem.';
