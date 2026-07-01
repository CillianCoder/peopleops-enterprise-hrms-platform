<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanyIndustry: string
{
    case Software = 'software';
    case Bpo = 'bpo';
    case Education = 'education';
    case Healthcare = 'healthcare';
    case Finance = 'finance';
    case Manufacturing = 'manufacturing';
    case ProfessionalServices = 'professional_services';
    case Other = 'other';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
