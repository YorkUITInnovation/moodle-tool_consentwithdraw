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
 * AMD datasource: user_search_datasource
 *
 * Provides autocomplete suggestions for the admin user-search field by calling
 * the Moodle core user-selector web service.
 *
 * @module     tool_consentwithdraw/user_search_datasource
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString} from 'core/str';

/**
 * Autocomplete transport function called by core/form-autocomplete.
 *
 * @param {string} selector CSS selector of the enhanced element.
 * @param {string} query The search string typed by the user.
 * @param {Function} callback Must be called with an array of {value, label} objects.
 * @param {Function} failure Called with an error if the request fails.
 */
export const transport = async(selector, query, callback, failure) => {
    if (!query || query.length < 2) {
        callback([]);
        return;
    }

    try {
        const response = await Ajax.call([{
            methodname: 'core_user_search_identity',
            args: {query: query},
        }])[0];

        if (response.overflow) {
            const msg = await getString('toomanyuserstoshow', 'core', '>' + response.maxusersperpage);
            callback(msg);
            return;
        }

        const suggestions = (response.list || []).map((user) => ({
            value: String(user.id),
            label: user.extrafields
                ? `${user.fullname} (${user.extrafields.map(f => f.value).join(', ')})`
                : user.fullname,
        }));
        callback(suggestions);
    } catch (error) {
        failure(error);
    }
};

/**
 * Process callback for autocomplete options.
 *
 * @param {string} selector
 * @param {Array} results
 * @return {Array}
 */
export const processResults = (selector, results) => {
    if (!Array.isArray(results)) {
        return results;
    }
    return results;
};
