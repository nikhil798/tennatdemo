<?php

namespace App\Http\Controllers;

use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function createForm()
    {
        return view('tenant.create');
    }

    public function store(Request $request, TenantService $tenantService)
    {
        $request->validate([
            'name' => 'required',
            'domain' => 'required|unique:tenants,domain',
        ]);

        $tenant = $tenantService->create($request->all());

        return redirect()->back()->with('success', 'Tenant created successfully');
    }
}
