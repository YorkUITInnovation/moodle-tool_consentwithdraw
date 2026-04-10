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

import Ajax from 'core/ajax';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Notification from 'core/notification';
import {getStrings} from 'core/str';
import Autocomplete from 'core/form-autocomplete';

/**
 * Initialise the admin page: wire up autocomplete and event delegation.
 */
export const init = () => {
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
    container.addEventListener('change', (e) => {
        if (e.target && e.target.id === 'tool-consentwithdraw-usersearch') {
            const userid = parseInt(e.target.value, 10);
            if (userid > 0) {
                void checkUserStatus(userid);
            }
        }
    });

    // Delegated click handler for the revoke button rendered into the status div.
    container.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="admin-revoke"]');
        if (!btn) {
            return;
        }

        e.preventDefault();
        const userid = parseInt(btn.dataset.userid, 10);
        void confirmRevoke(userid);
    });
};

/**
 * Fetch and render consent status for the given user.
 *
 * @param {number} userid
 */
const checkUserStatus = async(userid) => {
    const statusDiv = document.getElementById('tool-consentwithdraw-status');
    if (!statusDiv) {
        return;
    }

    try {
        const result = await Ajax.call([{
            methodname: 'tool_consentwithdraw_check_status',
            args: {userid: userid},
        }])[0];

        const strings = await getStrings([
            {
                key: result.accepted ? 'policy_accepted' : 'policy_not_accepted',
                component: 'tool_consentwithdraw',
            },
            {key: 'btn_withdraw', component: 'tool_consentwithdraw'},
        ]);

        let html = `<div class="col"><p>${strings[0]}</p>`;
        if (result.accepted) {
            html += `<button class="btn btn-danger" data-action="admin-revoke" data-userid="${userid}">${strings[1]}</button>`;
        }
        html += '</div>';
        statusDiv.innerHTML = html;
        statusDiv.setAttribute('aria-live', 'polite');
    } catch (error) {
        Notification.exception(error);
    }
};

/**
 * Show a confirmation modal before revoking the selected user's consent.
 *
 * @param {number} userid
 */
const confirmRevoke = async(userid) => {
    const strings = await getStrings([
        {key: 'confirm_title', component: 'tool_consentwithdraw'},
        {key: 'confirm_admin_message', component: 'tool_consentwithdraw'},
        {key: 'btn_confirm', component: 'tool_consentwithdraw'},
    ]);

    const modal = await ModalSaveCancel.create({
        title: strings[0],
        body: strings[1],
        removeOnClose: true,
    });

    modal.setSaveButtonText(strings[2]);

    modal.getRoot().on(ModalEvents.save, () => {
        void doRevoke(userid, modal);
    });

    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });

    modal.show();
};

/**
 * Call the revoke_user web service and update the status panel.
 *
 * @param {number} userid
 * @param {object} modal
 */
const doRevoke = async(userid, modal) => {
    try {
        const result = await Ajax.call([{
            methodname: 'tool_consentwithdraw_revoke_user',
            args: {userid: userid},
        }])[0];

        modal.destroy();
        if (result.success) {
            Notification.addNotification({
                type: 'success',
                message: result.message,
            });
            await checkUserStatus(userid);
        }
    } catch (error) {
        modal.destroy();
        Notification.exception(error);
    }
};
