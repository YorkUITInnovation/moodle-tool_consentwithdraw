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

define(['core/ajax'], function(Ajax) {

    'use strict';

    return {
        /**
         * Autocomplete transport function called by core/form-autocomplete.
         *
         * @param {string}   selector   CSS selector of the enhanced element (unused).
         * @param {string}   query      The search string typed by the user.
         * @param {Function} callback   Must be called with an array of {value, label} objects.
         */
        transport: function(selector, query, callback) {
            if (!query || query.length < 2) {
                callback([]);
                return;
            }

            Ajax.call([{
                methodname: 'core_user_get_users',
                args: {
                    criteria: [
                        {key: 'search', value: query},
                    ],
                },
            }])[0].then(function(result) {
                const suggestions = (result.users || []).map(function(user) {
                    return {
                        value: String(user.id),
                        label: user.fullname + ' (' + user.email + ')',
                    };
                });
                callback(suggestions);
                return suggestions;
            }).catch(function() {
                callback([]);
            });
        },

        /**
         * Process callback — return the raw value unchanged.
         *
         * @param  {string} value
         * @return {Promise<string>}
         */
        processResults: function(value) {
            return Promise.resolve(value);
        },
    };
});
