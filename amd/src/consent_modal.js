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
 * AMD module: consent_modal
 *
 * Provides a modal allowing a logged-in user to view and withdraw their own
 * AI policy consent from the user-settings navigation.
 *
 * @module     tool_consentwithdraw/consent_modal
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/ajax',
    'core/modal_factory',
    'core/modal_events',
    'core/notification',
    'core/str',
], function(Ajax, ModalFactory, ModalEvents, Notification, Str) {

    'use strict';

    /** @type {object} Public API */
    const MODULE = {

        /**
         * Attach a delegated click listener for the consent modal trigger.
         */
        init: function() {
            document.addEventListener('click', function(e) {
                const trigger = e.target.closest('[data-action="open-consent-modal"]');
                if (trigger) {
                    e.preventDefault();
                    MODULE.openStatusModal();
                }
            });
        },

        /**
         * Fetch the current user's consent status then open the status modal.
         */
        openStatusModal: function() {
            Ajax.call([{
                methodname: 'tool_consentwithdraw_check_status',
                args: {userid: 0},
            }])[0].then(function(result) {
                return MODULE.showModal(result);
            }).catch(Notification.exception);
        },

        /**
         * Build and display the status modal.
         *
         * @param  {object} statusData  Result from check_status external function.
         * @return {Promise}
         */
        showModal: function(statusData) {
            return Str.get_strings([
                {key: 'modal_title',        component: 'tool_consentwithdraw'},
                {key: statusData.accepted ? 'policy_accepted' : 'policy_not_accepted', component: 'tool_consentwithdraw'},
                {key: 'btn_withdraw',       component: 'tool_consentwithdraw'},
                {key: 'btn_cancel',         component: 'tool_consentwithdraw'},
            ]).then(function(strings) {
                let bodyHtml = '<p>' + strings[1] + '</p>';
                if (statusData.accepted) {
                    bodyHtml += '<button class="btn btn-danger" id="consent-withdraw-btn"'
                        + ' data-recordid="' + statusData.recordid + '">'
                        + strings[2] + '</button>';
                }

                return ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: strings[0],
                    body: bodyHtml,
                    footer: '<button class="btn btn-secondary" data-action="cancel">' + strings[3] + '</button>',
                });
            }).then(function(modal) {
                modal.show();

                modal.getRoot().on('click', '#consent-withdraw-btn', function(e) {
                    const recordid = parseInt(e.currentTarget.dataset.recordid, 10);
                    MODULE.confirmRevoke(modal, recordid);
                });

                modal.getRoot().on('click', '[data-action="cancel"]', function() {
                    modal.hide();
                });

                modal.getRoot().on(ModalEvents.hidden, function() {
                    modal.destroy();
                });

                return modal;
            });
        },

        /**
         * Show a confirmation modal before revoking consent.
         *
         * @param  {object} parentModal  The status modal to destroy on success.
         * @param  {number} recordid     The consent record ID to delete.
         * @return {Promise}
         */
        confirmRevoke: function(parentModal, recordid) {
            return Str.get_strings([
                {key: 'confirm_title',   component: 'tool_consentwithdraw'},
                {key: 'confirm_message', component: 'tool_consentwithdraw'},
                {key: 'btn_confirm',     component: 'tool_consentwithdraw'},
            ]).then(function(strings) {
                return ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: strings[0],
                    body: strings[1],
                });
            }).then(function(confirmModal) {
                return Str.get_string('btn_confirm', 'tool_consentwithdraw').then(function(btnLabel) {
                    confirmModal.setSaveButtonText(btnLabel);
                    confirmModal.show();

                    confirmModal.getRoot().on(ModalEvents.save, function() {
                        MODULE.doRevoke(recordid, parentModal, confirmModal);
                    });

                    confirmModal.getRoot().on(ModalEvents.hidden, function() {
                        confirmModal.destroy();
                    });

                    return confirmModal;
                });
            });
        },

        /**
         * Call the revoke_self web service and update the UI.
         *
         * @param {number} recordid
         * @param {object} parentModal
         * @param {object} confirmModal
         */
        doRevoke: function(recordid, parentModal, confirmModal) {
            Ajax.call([{
                methodname: 'tool_consentwithdraw_revoke_self',
                args: {recordid: recordid},
            }])[0].then(function(result) {
                confirmModal.destroy();
                parentModal.destroy();
                if (result.success) {
                    Notification.addNotification({
                        type: 'success',
                        message: result.message,
                    });
                    // Re-open the modal to reflect the updated state.
                    MODULE.openStatusModal();
                }
                return result;
            }).catch(function(err) {
                Notification.exception(err);
            });
        },
    };

    return MODULE;
});
