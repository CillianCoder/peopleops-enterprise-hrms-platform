<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CompanyLogoController extends Controller
{
    public function __invoke(): StreamedResponse|Response
    {
        $company = auth()->user()?->company;

        abort_if($company?->logo_path === null, 404);

        $disk = Storage::disk(config('filesystems.default'));

        abort_unless($disk->exists($company->logo_path), 404);

        return $disk->response($company->logo_path);
    }
}
