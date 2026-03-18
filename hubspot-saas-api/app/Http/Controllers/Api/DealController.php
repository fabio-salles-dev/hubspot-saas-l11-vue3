<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        return Deal::with(['client', 'company'])
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->company_id, fn ($q) =>
                $q->where('company_id', $request->company_id)
            )
            ->when($request->client_id, fn ($q) =>
                $q->where('client_id', $request->client_id)
            )
            ->orderBy(
                $request->get('sort', 'created_at'),
                $request->get('direction', 'desc')
            )
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'status' => 'required|in:open,won,lost',
            'client_id' => 'required|exists:clients,id',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        return Deal::create($data);
    }

    public function show(Deal $deal)
    {
        return $deal->load(['client', 'company']);
    }

    public function update(Request $request, Deal $deal)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:open,won,lost',
            'client_id' => 'sometimes|required|exists:clients,id',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $deal->update($data);

        return $deal->refresh();
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return response()->json([
            'message' => 'Negócio removido com sucesso'
        ]);
    }
}
