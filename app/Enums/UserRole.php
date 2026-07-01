<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case HrManager = 'hr_manager';
    case Recruiter = 'recruiter';
    case DepartmentManager = 'department_manager';
    case FinanceOfficer = 'finance_officer';
    case Employee = 'employee';
    case Auditor = 'auditor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'System Administrator',
            self::HrManager => 'HR Manager',
            self::Recruiter => 'Recruiter',
            self::DepartmentManager => 'Department Manager',
            self::FinanceOfficer => 'Finance Officer',
            self::Employee => 'Employee',
            self::Auditor => 'Auditor',
        };
    }

    public static function assignableValues(): array
    {
        return collect(self::cases())
            ->reject(fn (self $role): bool => $role === self::SuperAdmin)
            ->map(fn (self $role): string => $role->value)
            ->values()
            ->all();
    }
}
