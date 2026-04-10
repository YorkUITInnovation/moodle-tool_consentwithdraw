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
 * PHPUnit tests for tool_consentwithdraw external functions.
 *
 * @package    tool_consentwithdraw
 * @copyright  2024 York University IT Innovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_consentwithdraw\external\check_status
 * @covers     \tool_consentwithdraw\external\revoke_self
 * @covers     \tool_consentwithdraw\external\revoke_user
 */

namespace tool_consentwithdraw\tests;

defined('MOODLE_INTERNAL') || die();

use tool_consentwithdraw\external\check_status;
use tool_consentwithdraw\external\revoke_self;
use tool_consentwithdraw\external\revoke_user;

/**
 * External API test cases.
 */
class external_test extends \advanced_testcase {

    /**
     * Reset the database after every test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Insert a fake AI policy register record for the given user.
     *
     * @param  int $userid
     * @return int  Inserted record ID.
     */
    protected function create_policy_record(int $userid): int {
        global $DB;
        $record = (object)[
            'userid'       => $userid,
            'contextid'    => 1,
            'usermodified' => $userid,
            'timecreated'  => time(),
            'timemodified' => time(),
        ];
        return (int)$DB->insert_record('ai_policy_register', $record);
    }

    // -------------------------------------------------------------------------
    // check_status tests
    // -------------------------------------------------------------------------

    /**
     * check_status returns accepted=false when no record exists.
     */
    public function test_check_status_no_record(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $result = check_status::execute(0);

        $this->assertFalse($result['accepted']);
        $this->assertEquals(0, $result['recordid']);
        $this->assertEquals((int)$user->id, $result['userid']);
    }

    /**
     * check_status returns accepted=true when a record exists.
     */
    public function test_check_status_with_record(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $recordid = $this->create_policy_record((int)$user->id);
        $result = check_status::execute(0);

        $this->assertTrue($result['accepted']);
        $this->assertEquals($recordid, $result['recordid']);
    }

    /**
     * check_status throws when a regular user tries to check another user.
     */
    public function test_check_status_other_user_requires_capability(): void {
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user2);

        $this->create_policy_record((int)$user1->id);

        $this->expectException(\required_capability_exception::class);
        check_status::execute((int)$user1->id);
    }

    /**
     * A manager can check another user's status.
     */
    public function test_check_status_other_user_as_manager(): void {
        $manager = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign(
            $this->getDataGenerator()->create_role(['archetype' => 'manager']),
            $manager->id,
            \context_system::instance()->id
        );
        // Use site admin for simplicity.
        $admin = get_admin();
        $this->setUser($admin);

        $user = $this->getDataGenerator()->create_user();
        $recordid = $this->create_policy_record((int)$user->id);

        $result = check_status::execute((int)$user->id);
        $this->assertTrue($result['accepted']);
        $this->assertEquals($recordid, $result['recordid']);
    }

    // -------------------------------------------------------------------------
    // revoke_self tests
    // -------------------------------------------------------------------------

    /**
     * A user can revoke their own consent.
     */
    public function test_revoke_self_success(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $recordid = $this->create_policy_record((int)$user->id);
        $result = revoke_self::execute($recordid);

        $this->assertTrue($result['success']);

        // Record must be gone.
        $check = check_status::execute(0);
        $this->assertFalse($check['accepted']);
    }

    /**
     * revoke_self throws when a different user owns the record.
     */
    public function test_revoke_self_wrong_user(): void {
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $recordid = $this->create_policy_record((int)$user1->id);
        $this->setUser($user2);

        $this->expectException(\moodle_exception::class);
        revoke_self::execute($recordid);
    }

    // -------------------------------------------------------------------------
    // revoke_user tests
    // -------------------------------------------------------------------------

    /**
     * revoke_user throws for a regular user (no manage capability).
     */
    public function test_revoke_user_requires_capability(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $otheruser = $this->getDataGenerator()->create_user();
        $this->create_policy_record((int)$otheruser->id);

        $this->expectException(\required_capability_exception::class);
        revoke_user::execute((int)$otheruser->id);
    }

    /**
     * Site admin can revoke any user's consent.
     */
    public function test_revoke_user_as_admin(): void {
        global $DB;

        $admin = get_admin();
        $this->setUser($admin);

        $user = $this->getDataGenerator()->create_user();
        $this->create_policy_record((int)$user->id);

        $result = revoke_user::execute((int)$user->id);
        $this->assertTrue($result['success']);
        $this->assertFalse($DB->record_exists('ai_policy_register', ['userid' => $user->id]));
    }

    /**
     * revoke_user throws when the target user has no consent record.
     */
    public function test_revoke_user_no_record(): void {
        $admin = get_admin();
        $this->setUser($admin);

        $user = $this->getDataGenerator()->create_user();

        $this->expectException(\moodle_exception::class);
        revoke_user::execute((int)$user->id);
    }
}
