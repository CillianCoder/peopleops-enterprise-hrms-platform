<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Company\CompleteCompanyOnboarding;
use App\Enums\CompanyIndustry;
use App\Http\Requests\CompanyOnboardingRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class CompanyOnboardingController extends Controller
{
    public function edit(): Response
    {
        $company = auth()->user()?->company;

        return Inertia::render('Company/Onboarding', [
            'company' => $company,
            'industries' => collect(CompanyIndustry::cases())->map(fn (CompanyIndustry $industry): array => [
                'value' => $industry->value,
                'label' => $industry->label(),
            ])->values(),
        ]);
    }

    public function update(CompanyOnboardingRequest $request, CompleteCompanyOnboarding $completeCompanyOnboarding): RedirectResponse
    {
        $data = collect($request->validated())->except('logo')->all();

        $completeCompanyOnboarding->handle($request->user(), $data, $request->file('logo'));

        return redirect()->route('dashboard')->with('success', 'Company profile completed. PeopleOps is ready to use.');
    }
}
