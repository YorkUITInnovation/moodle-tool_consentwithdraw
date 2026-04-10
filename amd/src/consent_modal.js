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

import Ajax from 'core/ajax';
import Modal from 'core/modal';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import {getStrings} from 'core/str';

let listenerAttached = false;

/**
 * Attach a delegated click listener for the consent modal trigger.
 */
export const init = () => {
    if (listenerAttached) {
        return;
    }
    listenerAttached = true;

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest(
            '[data-action="open-consent-modal"], a[href*="consentwithdraw=1"], a[href*="consentwithdraw%3D1"]'
        );
        if (!trigger) {
            return;
        }

        e.preventDefault();
        void openStatusModal();
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('consentwithdraw') === '1') {
        void openStatusModal();
        params.delete('consentwithdraw');
        const cleanurl = `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}${window.location.hash}`;
        window.history.replaceState({}, document.title, cleanurl);
    }
};

/**
 * Fetch the current user's consent status then open the status modal.
 */
const openStatusModal = async() => {
    try {
        const result = await Ajax.call([{
            methodname: 'tool_consentwithdraw_check_status',
            args: {userid: 0},
        }])[0];
        await showModal(result);
    } catch (error) {
        Notification.exception(error);
    }
};

/**
 * Build and display the status modal.
 *
 * @param {object} statusData Result from check_status external function.
 * @return {Promise<void>}
 */
const showModal = async(statusData) => {
    const strings = await getStrings([
        {key: 'modal_title', component: 'tool_consentwithdraw'},
        {key: statusData.accepted ? 'policy_accepted' : 'policy_not_accepted', component: 'tool_consentwithdraw'},
        {key: 'btn_withdraw', component: 'tool_consentwithdraw'},
        {key: 'btn_cancel', component: 'tool_consentwithdraw'},
    ]);

    let bodyHtml = `<p>${strings[1]}</p>`;
    if (statusData.accepted) {
        bodyHtml += `<button class="btn btn-danger" id="consent-withdraw-btn" data-recordid="${statusData.recordid}">${strings[2]}</button>`;
    }

    const modal = await Modal.create({
        title: strings[0],
        body: bodyHtml,
        footer: `<button class="btn btn-secondary" data-action="cancel">${strings[3]}</button>`,
        removeOnClose: true,
    });

    modal.getRoot().on('click', '#consent-withdraw-btn', (e) => {
        const recordid = parseInt(e.currentTarget.dataset.recordid, 10);
        void confirmRevoke(modal, recordid);
    });

    modal.getRoot().on('click', '[data-action="cancel"]', () => {
        modal.hide();
    });

    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });

    modal.show();
};

/**
 * Show a confirmation modal before revoking consent.
 *
 * @param {object} parentModal The status modal to destroy on success.
 * @param {number} recordid The consent record ID to delete.
 * @return {Promise<void>}
 */
const confirmRevoke = async(parentModal, recordid) => {
    const strings = await getStrings([
        {key: 'confirm_title', component: 'tool_consentwithdraw'},
        {key: 'confirm_message', component: 'tool_consentwithdraw'},
        {key: 'btn_confirm', component: 'tool_consentwithdraw'},
    ]);

    const confirmModal = await ModalSaveCancel.create({
        title: strings[0],
        body: strings[1],
        removeOnClose: true,
    });

    confirmModal.setSaveButtonText(strings[2]);

    confirmModal.getRoot().on(ModalEvents.save, () => {
        void doRevoke(recordid, parentModal, confirmModal);
    });

    confirmModal.getRoot().on(ModalEvents.hidden, () => {
        confirmModal.destroy();
    });

    confirmModal.show();
};

/**
 * Call the revoke_self web service and update the UI.
 *
 * @param {number} recordid
 * @param {object} parentModal
 * @param {object} confirmModal
 */
const doRevoke = async(recordid, parentModal, confirmModal) => {
    try {
        const result = await Ajax.call([{
            methodname: 'tool_consentwithdraw_revoke_self',
            args: {recordid: recordid},
        }])[0];

        confirmModal.destroy();
        parentModal.destroy();

        if (result.success) {
            Notification.addNotification({
                type: 'success',
                message: result.message,
            });
            await openStatusModal();
        }
    } catch (error) {
        Notification.exception(error);
    }
};
