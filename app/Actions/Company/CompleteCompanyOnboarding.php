<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final readonly class CompleteCompanyOnboarding
{
    public function handle(User $admin, array $data, ?UploadedFile $logo): Company
    {
        return DB::transaction(function () use ($admin, $data, $logo): Company {
            $logoPath = $logo?->store('company-logos', [
                'disk' => config('filesystems.default'),
                'visibility' => 'private',
            ]);

            if ($admin->company?->logo_path && $logoPath) {
                Storage::disk(config('filesystems.default'))->delete($admin->company->logo_path);
            }

            $company = Company::query()->updateOrCreate(
                ['id' => $admin->company_id],
                [
                    ...$data,
                    'logo_path' => $logoPath ?? $admin->company?->logo_path,
                    'onboarding_completed_at' => now(),
                ],
            );

            $admin->forceFill(['company_id' => $company->id])->save();

            activity('companies')
                ->causedBy($admin)
                ->performedOn($company)
                ->event('company_onboarding_completed')
                ->log('Company onboarding details were completed.');

            return $company;
        });
    }
}
