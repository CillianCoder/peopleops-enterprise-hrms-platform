# Access Control

PeopleOps access control is managed from `/admin/users`.

## Purpose

Administrators use this workspace to create managed user logins, assign roles, maintain unique identity data, and manage role permissions.

## User Management

- Managed users require name, unique email, unique NIC, and role.
- Optional fields are job title and phone.
- New users receive an active login with a generated temporary password.
- The temporary password is flashed once to the browser so an administrator can copy it through an approved private channel.
- Super administrator accounts are protected from managed-user edits and deletes.
- Managed user deletion uses soft deletes.

## RBAC Management

- Roles are stored with Spatie Permission.
- `super_admin` is protected and cannot be edited, deleted, recreated, or assigned through managed-user forms.
- Recruitment access belongs to the HR Manager role; there is no separate hiring access role.
- Custom role names are normalized to lowercase snake case.
- Roles cannot be deleted while users are assigned to them.
- Permissions are seeded by the platform and grouped in the UI for assignment.

## Permissions

Relevant permissions:

- `users.view`
- `users.create`
- `users.update`
- `users.delete`
- `roles.view`
- `roles.create`
- `roles.update`
- `roles.delete`
- `permissions.view`

## Validation

Backend validation covers required fields, unique email, unique NIC, role assignment restrictions, and permission existence. The UI adds server-backed search, filters, pagination, and confirmation dialogs for destructive actions.
