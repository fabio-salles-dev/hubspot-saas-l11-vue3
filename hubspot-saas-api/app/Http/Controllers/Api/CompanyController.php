<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        return Company::with(['clients', 'deals'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                          ->orWhere('hubspot_id', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'hubspot_id' => 'nullable|string|max:255',
        ]);

        return Company::create($data);
    }

    public function show(Company $company)
    {
        return $company->load(['clients', 'deals']);
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'hubspot_id' => 'nullable|string|max:255',
        ]);

        $company->update($data);

        return $company->refresh();
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json([
            'message' => 'Empresa removida com sucesso'
        ]);
    }
}
