<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyIndustry;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'industry',
        'registration_number',
        'tax_number',
        'email',
        'phone',
        'website',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'timezone',
        'currency',
        'logo_path',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'industry' => CompanyIndustry::class,
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('companies')
            ->logOnly([
                'name',
                'legal_name',
                'industry',
                'registration_number',
                'email',
                'phone',
                'website',
                'country',
                'timezone',
                'currency',
                'logo_path',
                'onboarding_completed_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
