# Audit Logs

PeopleOps records audit activity through Spatie Activitylog and exposes the company-scoped ledger at `/admin/audit`.

## What Is Logged

- Authentication events: sign in, blocked sign in, sign out, password reset requests, password resets, and email verification events.
- Company profile changes and onboarding completion.
- Managed user creation, update, suspension, soft deletion, and model-level user changes.
- Role creation, permission changes, and role deletion.
- Every authenticated write request through the `audit.write` middleware, including route name, method, status code, IP, user agent, and duration.

Passwords, reset tokens, remember tokens, and temporary passwords must never be logged.

## Access

Only users with `audit.view` can open the audit workspace. Audit rows are scoped to the current company by actor or subject so company data is not mixed across tenants.

## Future Modules

Every new sensitive workflow should add a domain-specific activity event with:

- A clear `log_name`, such as `employees`, `leave`, `payroll`, or `documents`.
- A clear `event`, such as `leave_request_approved`.
- `causedBy()` when an authenticated user performs the action.
- `performedOn()` when a model is affected.
- Safe properties only. Do not include passwords, tokens, salary details, bank details, document contents, or private file URLs.

The write-request middleware is a safety net, not a replacement for workflow-specific audit events.
