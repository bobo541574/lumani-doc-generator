<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('app', [
            'title' => 'Dashboard',
            'page' => [
                'name' => 'dashboard',
                'tenant' => $this->resolveTenant($request),
            ],
        ]);
    }

    private function resolveTenant(Request $request): ?string
    {
        $host = $request->getHost();
        $subdomain = $host && str_contains($host, '.') ? explode('.', $host)[0] : null;

        foreach ([$request->query('tenant'), $subdomain, config('app.default_tenant')] as $candidate) {
            if (is_string($candidate) && $candidate !== '' && $candidate !== 'www') {
                $org = Organization::where('slug', $candidate)->where('is_active', true)->first();
                if ($org) {
                    return $org->slug;
                }
            }
        }

        return Organization::where('is_active', true)->value('slug');
    }
}
