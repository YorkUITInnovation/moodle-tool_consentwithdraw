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
 * AMD module: admin_tool
 *
 * Provides user-search autocomplete and consent-revoke functionality for the
 * admin page at /admin/tool/consentwithdraw/index.php.
 *
 * @module     tool_consentwithdraw/admin_tool
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/ajax',
    'core/modal_factory',
    'core/modal_events',
    'core/notification',
    'core/str',
    'core/form-autocomplete',
], function(Ajax, ModalFactory, ModalEvents, Notification, Str, Autocomplete) {

    'use strict';

    /** @type {object} Public API */
    const AdminTool = {

        /**
         * Initialise the admin page: wire up autocomplete and event delegation.
         */
        init: function() {
            const container = document.getElementById('tool-consentwithdraw-admin');
            if (!container) {
                return;
            }

            // Enhance the plain text input into a user-search autocomplete.
            Autocomplete.enhance(
                '#tool-consentwithdraw-usersearch',
                false,
                'tool_consentwithdraw/user_search_datasource',
                '',
                false,
                true,
                '',
                true
            );

            // Listen for autocomplete selection changes.
            container.addEventListener('change', function(e) {
                if (e.target && e.target.id === 'tool-consentwithdraw-usersearch') {
                    const userid = parseInt(e.target.value, 10);
                    if (userid > 0) {
                        AdminTool.checkUserStatus(userid);
                    }
                }
            });

            // Delegated click handler for the revoke button rendered into the status div.
            container.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-action="admin-revoke"]');
                if (btn) {
                    e.preventDefault();
                    const userid = parseInt(btn.dataset.userid, 10);
                    AdminTool.confirmRevoke(userid);
                }
            });
        },

        /**
         * Fetch and render consent status for the given user.
         *
         * @param {number} userid
         */
        checkUserStatus: function(userid) {
            const statusDiv = document.getElementById('tool-consentwithdraw-status');
            if (!statusDiv) {
                return;
            }

            Ajax.call([{
                methodname: 'tool_consentwithdraw_check_status',
                args: {userid: userid},
            }])[0].then(function(result) {
                return Str.get_strings([
                    {
                        key: result.accepted ? 'policy_accepted' : 'policy_not_accepted',
                        component: 'tool_consentwithdraw',
                    },
                    {key: 'btn_withdraw', component: 'tool_consentwithdraw'},
                ]).then(function(strings) {
                    let html = '<div class="col"><p>' + strings[0] + '</p>';
                    if (result.accepted) {
                        html += '<button class="btn btn-danger"'
                            + ' data-action="admin-revoke"'
                            + ' data-userid="' + userid + '">'
                            + strings[1]
                            + '</button>';
                    }
                    html += '</div>';
                    statusDiv.innerHTML = html;
                    statusDiv.setAttribute('aria-live', 'polite');
                    return strings;
                });
            }).catch(Notification.exception);
        },

        /**
         * Show a confirmation modal before revoking the selected user's consent.
         *
         * @param  {number} userid
         * @return {Promise}
         */
        confirmRevoke: function(userid) {
            return Str.get_strings([
                {key: 'confirm_title', component: 'tool_consentwithdraw'},
                {key: 'confirm_admin_message', component: 'tool_consentwithdraw'},
                {key: 'btn_confirm', component: 'tool_consentwithdraw'},
            ]).then(function(strings) {
                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: strings[0],
                    body: strings[1],
                }).then(function(modal) {
                    modal.setSaveButtonText(strings[2]);
                    modal.show();

                    modal.getRoot().on(ModalEvents.save, function() {
                        AdminTool.doRevoke(userid, modal);
                    });

                    modal.getRoot().on(ModalEvents.hidden, function() {
                        modal.destroy();
                    });

                    return modal;
                });
            });
        },

        /**
         * Call the revoke_user web service and update the status panel.
         *
         * @param {number} userid
         * @param {object} modal
         */
        doRevoke: function(userid, modal) {
            Ajax.call([{
                methodname: 'tool_consentwithdraw_revoke_user',
                args: {userid: userid},
            }])[0].then(function(result) {
                modal.destroy();
                if (result.success) {
                    Notification.addNotification({
                        type: 'success',
                        message: result.message,
                    });
                    AdminTool.checkUserStatus(userid);
                }
                return result;
            }).catch(function(err) {
                modal.destroy();
                Notification.exception(err);
            });
        },
    };

    return AdminTool;
});
