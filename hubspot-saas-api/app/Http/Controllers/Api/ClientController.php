<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        return Client::with('company')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'hubspot_id' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        return Client::create($data);
    }

    public function show(Client $client)
    {
        return $client->load(['company', 'deals']);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|email|unique:clients,email,{$client->id}",
            'hubspot_id' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $client->update($data);

        return $client->refresh();
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'message' => 'Cliente removido com sucesso'
        ]);
    }
}
