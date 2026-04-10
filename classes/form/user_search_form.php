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
 * User-search form for the consent-withdraw admin tool.
 *
 * @package    tool_consentwithdraw
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_consentwithdraw\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form with a single user-autocomplete field backed by core_user/form_user_selector.
 */
class user_search_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition(): void {
        $mform = $this->_form;

        $context = \context_system::instance();

        $attributes = [
            'multiple'         => false,
            'ajax'             => 'core_user/form_user_selector',
            'valuehtmlcallback' => function($userid) use ($context) {
                global $OUTPUT;

                $fields = \core_user\fields::for_name()->with_identity($context, false);
                $record = \core_user::get_user($userid, 'id ' . $fields->get_sql()->selects, MUST_EXIST);

                $user = (object)[
                    'id'          => $record->id,
                    'fullname'    => fullname($record, has_capability('moodle/site:viewfullnames', $context)),
                    'extrafields' => [],
                ];

                foreach ($fields->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]) as $field) {
                    $user->extrafields[] = (object)['name' => $field, 'value' => s($record->$field)];
                }

                return $OUTPUT->render_from_template('core_user/form_user_selector_suggestion', $user);
            },
        ];

        $mform->addElement(
            'autocomplete',
            'userid',
            get_string('admin_search_label', 'tool_consentwithdraw'),
            [],
            $attributes
        );
        $mform->setType('userid', PARAM_INT);

        $this->add_action_buttons(false, get_string('admin_search_button', 'tool_consentwithdraw'));
    }
}
