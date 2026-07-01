<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CompanyIndustry;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
final class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Northstar People Solutions',
            'legal_name' => 'Northstar People Solutions Pvt Ltd',
            'industry' => CompanyIndustry::Software,
            'email' => 'hello@example.test',
            'phone' => '+94112345678',
            'address_line_1' => '14 Lake Drive',
            'city' => 'Colombo',
            'country' => 'LK',
            'timezone' => 'Asia/Colombo',
            'currency' => 'LKR',
            'onboarding_completed_at' => now(),
        ];
    }
}
