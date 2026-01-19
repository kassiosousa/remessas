<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartnerStoreRequest;
use App\Http\Requests\PartnerUpdateRequest;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $partners = Partner::query()
            ->when($q, fn($qb) => $qb->where('name', 'like', "%{$q}%"))
            ->orderByDesc('id', 'name')
            ->paginate(20);

        return response()->json($partners);

    }

    public function store(PartnerStoreRequest $request)
    {
        $partner = Partner::create($request->validated());
        return response()->json($partner, 201);
    }

    public function show(Partner $partner)
    {
        // se quiser trazer projetos do sócio:
        $partner->load('projects');
        return response()->json($partner);
    }

    public function update(PartnerUpdateRequest $request, Partner $partner)
    {
        $partner->update($request->validated());
        return response()->json($partner);
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        return response()->json([], 204);
    }
}
