<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Support\Audit\AuditActivityFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

final class AuditLogController extends Controller
{
    public function __invoke(Request $request, AuditActivityFormatter $formatter): Response
    {
        $user = $request->user();
        $companyUserIds = User::query()
            ->where('company_id', $user?->company_id)
            ->pluck('id');

        $search = trim($request->string('search')->toString());
        $module = $request->string('module')->toString();
        $event = $request->string('event')->toString();
        $actor = $request->string('actor')->toString();
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');
        $perPage = min(max((int) $request->integer('per_page', 12), 5), 30);

        $scopedQuery = Activity::query()
            ->with(['causer', 'subject'])
            ->where(function (Builder $query) use ($user, $companyUserIds): void {
                $query->where(function (Builder $query) use ($companyUserIds): void {
                    $query->where('causer_type', User::class)
                        ->whereIn('causer_id', $companyUserIds);
                })
                    ->orWhere(function (Builder $query) use ($companyUserIds): void {
                        $query->where('subject_type', User::class)
                            ->whereIn('subject_id', $companyUserIds);
                    })
                    ->orWhere(function (Builder $query) use ($user): void {
                        $query->where('subject_type', Company::class)
                            ->where('subject_id', $user?->company_id);
                    })
                    ->orWhere(function (Builder $query) use ($user): void {
                        $query->where('causer_type', User::class)
                            ->where('causer_id', $user?->id);
                    });
            });

        $optionBase = clone $scopedQuery;

        $query = (clone $scopedQuery)
            ->when($module !== '', fn (Builder $query) => $query->where('log_name', $module))
            ->when($event !== '', fn (Builder $query) => $query->where('event', $event))
            ->when($actor !== '', function (Builder $query) use ($actor): void {
                $needle = mb_strtolower($actor);

                $query->whereHasMorph('causer', [User::class], function (Builder $query) use ($needle): void {
                    $query->whereRaw('lower(name) like ?', ["%{$needle}%"])
                        ->orWhereRaw('lower(email) like ?', ["%{$needle}%"]);
                });
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $needle = mb_strtolower($search);

                $query->where(function (Builder $query) use ($needle): void {
                    $query->whereRaw('lower(coalesce(description, \'\')) like ?', ["%{$needle}%"])
                        ->orWhereRaw('lower(coalesce(event, \'\')) like ?', ["%{$needle}%"])
                        ->orWhereRaw('lower(coalesce(log_name, \'\')) like ?', ["%{$needle}%"]);
                });
            })
            ->when($dateFrom !== null, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo));

        $summaryBase = clone $query;

        $activities = $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Activity $activity): array => $formatter->format($activity));

        return Inertia::render('Admin/Audit/Index', [
            'activities' => $activities,
            'filters' => [
                'search' => $search,
                'module' => $module,
                'event' => $event,
                'actor' => $actor,
                'date_from' => $dateFrom?->toDateString(),
                'date_to' => $dateTo?->toDateString(),
                'per_page' => $perPage,
            ],
            'moduleOptions' => $this->moduleOptions($optionBase),
            'eventOptions' => $this->eventOptions($optionBase, $formatter),
            'summary' => [
                'total' => (clone $summaryBase)->count(),
                'today' => (clone $summaryBase)->whereDate('created_at', today())->count(),
                'security' => (clone $summaryBase)->where('log_name', 'security')->count(),
                'writes' => (clone $summaryBase)->where('log_name', 'requests')->count(),
            ],
        ]);
    }

    private function moduleOptions(Builder $query): array
    {
        return (clone $query)
            ->select('log_name')
            ->whereNotNull('log_name')
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name')
            ->map(fn (string $module): array => [
                'value' => $module,
                'label' => match ($module) {
                    'users' => 'Users',
                    'roles' => 'Roles',
                    'companies' => 'Company',
                    'security' => 'Security',
                    'requests' => 'System requests',
                    default => str($module)->headline()->toString(),
                },
            ])
            ->values()
            ->all();
    }

    private function eventOptions(Builder $query, AuditActivityFormatter $formatter): array
    {
        return (clone $query)
            ->select('event')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->map(function (string $event) use ($formatter): array {
                $activity = new Activity;
                $activity->event = $event;

                return [
                    'value' => $event,
                    'label' => $formatter->eventLabel($activity),
                ];
            })
            ->values()
            ->all();
    }
}
