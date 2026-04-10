# tool_consentwithdraw — AI Policy Consent Withdraw

A Moodle 5.1 admin tool plugin that lets users revoke their own acceptance of the AI policy, and lets managers revoke consent on behalf of any user.

## Features

- **User self-service** — a "Withdraw AI policy consent" link appears in the user-settings navigation. Clicking it opens a modal showing the current consent status and, when accepted, a revoke button backed by a confirmation dialog.
- **Admin page** — `/admin/tool/consentwithdraw/index.php` (linked from *Site administration → Tools*) provides a user-search autocomplete and a per-user revoke action.
- **Three web-service functions** exposed over Ajax:

  | Function | Description |
  |---|---|
  | `tool_consentwithdraw_check_status` | Returns whether the given user has an `ai_policy_register` record. |
  | `tool_consentwithdraw_revoke_self` | Deletes the caller's own consent record. |
  | `tool_consentwithdraw_revoke_user` | Deletes any user's consent record (requires `tool/consentwithdraw:manage`). |

## Requirements

| Requirement | Version |
|---|---|
| Moodle | ≥ 4.5 (build 2024100700) |
| PHP | ≥ 8.1 |

The plugin reads from and deletes records in the `ai_policy_register` table, which is created by Moodle's core AI subsystem.

## Installation

1. Copy (or clone) this repository to `<moodle_root>/admin/tool/consentwithdraw/`.
2. Log in as an administrator and complete the upgrade at *Site administration → Notifications*.

## Capability

| Capability | Default role |
|---|---|
| `tool/consentwithdraw:manage` | Manager |

## Privacy

This plugin does not store personal data of its own. It operates exclusively on the `ai_policy_register` table owned by the core AI subsystem. See `classes/privacy/provider.php`.

## Running the unit tests

```bash
vendor/bin/phpunit admin/tool/consentwithdraw/tests/external_test.php
```

## Licence

GNU GPL v3 or later — see [COPYING](https://www.gnu.org/licenses/gpl-3.0.en.html).

Copyright © 2024 York University IT Innovation.