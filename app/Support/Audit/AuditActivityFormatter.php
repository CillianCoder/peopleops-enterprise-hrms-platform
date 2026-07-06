<?php

declare(strict_types=1);

namespace App\Support\Audit;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

final class AuditActivityFormatter
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'temporary_password',
        'current_password',
    ];

    public function format(Activity $activity): array
    {
        $properties = $activity->properties instanceof Collection
            ? $activity->properties
            : collect($activity->properties ?? []);

        return [
            'id' => $activity->id,
            'module' => $this->moduleLabel($activity->log_name),
            'moduleKey' => $activity->log_name,
            'event' => $activity->event,
            'eventLabel' => $this->eventLabel($activity),
            'description' => $this->description($activity),
            'actor' => $this->actor($activity),
            'subject' => $this->subject($activity),
            'changes' => $this->changes($properties),
            'meta' => $this->meta($properties),
            'status' => $this->status($properties),
            'occurredAt' => $activity->created_at?->toIso8601String(),
            'occurredAtDisplay' => $activity->created_at?->format('M j, Y, g:i A'),
            'relativeTime' => $activity->created_at?->diffForHumans(),
        ];
    }

    public function eventLabel(Activity $activity): string
    {
        return match ($activity->event) {
            'created' => 'Record created',
            'updated' => 'Record updated',
            'deleted' => 'Record deleted',
            'initial_admin_created' => 'Initial administrator created',
            'login_succeeded' => 'User signed in',
            'login_failed' => 'Sign-in failed',
            'login_blocked' => 'Sign-in blocked',
            'logout_succeeded' => 'User signed out',
            'password_reset_link_requested' => 'Password reset requested',
            'password_reset_completed' => 'Password reset completed',
            'email_verified' => 'Email address verified',
            'verification_link_requested' => 'Verification link requested',
            'company_onboarding_completed' => 'Company profile saved',
            'managed_user_created' => 'User login created',
            'managed_user_updated' => 'User account updated',
            'managed_user_deleted' => 'User account deleted',
            'role_created' => 'Role created',
            'role_updated' => 'Role updated',
            'role_deleted' => 'Role deleted',
            'request_completed' => $this->requestEventLabel($activity),
            default => str((string) $activity->event)->replace('_', ' ')->headline()->toString(),
        };
    }

    private function description(Activity $activity): string
    {
        if ($activity->event === 'request_completed') {
            $properties = $activity->properties instanceof Collection
                ? $activity->properties
                : collect($activity->properties ?? []);
            $route = $properties->get('route_name') ?: $properties->get('path');
            $method = $properties->get('method');
            $status = $properties->get('status_code');

            return trim("{$method} {$route} completed with status {$status}.");
        }

        return $activity->description;
    }

    private function requestEventLabel(Activity $activity): string
    {
        $properties = $activity->properties instanceof Collection
            ? $activity->properties
            : collect($activity->properties ?? []);
        $method = $properties->get('method');
        $route = $properties->get('route_name');

        return trim("{$method} ".str((string) ($route ?: 'request'))->replace('.', ' ')->headline()->toString());
    }

    private function actor(Activity $activity): array
    {
        $causer = $activity->causer;

        if ($causer instanceof User) {
            return [
                'name' => $causer->name,
                'email' => $causer->email,
                'type' => 'User',
            ];
        }

        return [
            'name' => 'System',
            'email' => null,
            'type' => $activity->causer_type ? class_basename($activity->causer_type) : 'System',
        ];
    }

    private function subject(Activity $activity): array
    {
        $subject = $activity->subject;

        if ($subject instanceof User) {
            return [
                'label' => $subject->name,
                'detail' => $subject->email,
                'type' => 'User',
            ];
        }

        if ($subject instanceof Company) {
            return [
                'label' => $subject->name,
                'detail' => $subject->legal_name,
                'type' => 'Company',
            ];
        }

        if ($subject instanceof Role) {
            return [
                'label' => str($subject->name)->replace('_', ' ')->headline()->toString(),
                'detail' => $subject->name,
                'type' => 'Role',
            ];
        }

        if ($subject instanceof Model) {
            return [
                'label' => class_basename($subject).' #'.$subject->getKey(),
                'detail' => null,
                'type' => class_basename($subject),
            ];
        }

        return [
            'label' => $activity->subject_type ? class_basename($activity->subject_type).' #'.$activity->subject_id : 'No subject',
            'detail' => null,
            'type' => $activity->subject_type ? class_basename($activity->subject_type) : 'System',
        ];
    }

    private function changes(Collection $properties): array
    {
        $attributes = collect($properties->get('attributes', []));
        $old = collect($properties->get('old', []));

        return $attributes
            ->reject(fn (mixed $value, string $key): bool => in_array($key, self::SENSITIVE_KEYS, true))
            ->map(function (mixed $value, string $key) use ($old): array {
                return [
                    'field' => str($key)->replace('_', ' ')->headline()->toString(),
                    'from' => $this->displayValue($old->get($key)),
                    'to' => $this->displayValue($value),
                ];
            })
            ->values()
            ->all();
    }

    private function meta(Collection $properties): array
    {
        return $properties
            ->except(['attributes', 'old'])
            ->reject(fn (mixed $value, string $key): bool => in_array($key, self::SENSITIVE_KEYS, true) || $value === null || $value === '')
            ->map(fn (mixed $value, string $key): array => [
                'label' => str($key)->replace('_', ' ')->headline()->toString(),
                'value' => $this->displayValue($value),
            ])
            ->values()
            ->all();
    }

    private function status(Collection $properties): string
    {
        if ($properties->get('validation_errors') === true) {
            return 'warning';
        }

        $statusCode = (int) $properties->get('status_code', 200);

        if ($statusCode >= 500) {
            return 'failed';
        }

        if ($statusCode >= 400) {
            return 'warning';
        }

        return 'success';
    }

    private function moduleLabel(?string $module): string
    {
        return match ($module) {
            'users' => 'Users',
            'roles' => 'Roles',
            'companies' => 'Company',
            'security' => 'Security',
            'requests' => 'System requests',
            default => str((string) ($module ?: 'peopleops'))->replace('_', ' ')->headline()->toString(),
        };
    }

    private function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Empty';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return collect($value)->map(fn (mixed $item): string => $this->displayValue($item))->join(', ');
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
