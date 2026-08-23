<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')->orderByDesc('created_at')->get();

        return view('central.dashboard', [
            'tenants' => $tenants,
            'baseDomain' => config('tenancy.base_domain'),
        ]);
    }

    public function store(Request $request, TenantProvisioningService $provisioning)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'subdominio' => 'required|string|max:63',
        ]);

        try {
            $provisioning->create($data['nombre'], $data['subdominio']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('central.dashboard')->with('status', "Tenant '{$data['nombre']}' creado correctamente.");
    }
}
