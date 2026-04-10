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
 * Handles the confirmation modal for the admin consent-revoke button.
 * User search is handled entirely by the Moodle form (user_search_form.php).
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

/**
 * Initialise delegated click handler for the revoke button.
 */
export const init = () => {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="admin-revoke"]');
        if (!btn) {
            return;
        }
        e.preventDefault();
        const userid = parseInt(btn.dataset.userid, 10);
        const username = btn.dataset.username ?? '';
        const sesskey = btn.dataset.sesskey ?? '';
        void confirmRevoke(userid, username, sesskey);
    });
};

/**
 * Show a confirmation modal, then POST the revoke action.
 *
 * @param {number} userid
 * @param {string} username
 * @param {string} sesskey
 */
const confirmRevoke = async(userid, username, sesskey) => {
    const strings = await getStrings([
        {key: 'confirm_title',         component: 'tool_consentwithdraw'},
        {key: 'confirm_admin_message', component: 'tool_consentwithdraw'},
        {key: 'btn_confirm',           component: 'tool_consentwithdraw'},
    ]);

    const modal = await ModalSaveCancel.create({
        title: strings[0],
        body: strings[1],
        removeOnClose: true,
    });
    modal.setSaveButtonText(strings[2]);
    modal.show();

    modal.getRoot().on(ModalEvents.save, () => {
        void doRevoke(userid, sesskey, modal);
    });
    modal.getRoot().on(ModalEvents.hidden, () => modal.destroy());
};

const doRevoke = async(userid, sesskey, modal) => {
    try {
        const result = await Ajax.call([{
            methodname: 'tool_consentwithdraw_revoke_user',
            args: {userid},
        }])[0];

        modal.destroy();

        if (result.success) {
            Notification.addNotification({
                type: 'success',
                message: result.message,
            });
            // Reload the page so the form reflects the updated state.
            window.location.reload();
        }
    } catch (error) {
        modal.destroy();
        Notification.exception(error);
    }
};
