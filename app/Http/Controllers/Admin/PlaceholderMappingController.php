<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlaceholderMappingRequest;
use App\Http\Requests\UpdatePlaceholderMappingRequest;
use App\Models\PlaceholderMapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\ExternalOpportunity;
use App\Models\ExternalRegistrationFieldConfiguration;

class PlaceholderMappingController extends Controller
{
    public function index(): View
    {
        // já traz oportunidade e field para não n+1
        $mappings = PlaceholderMapping::with(['opportunity','field'])
            ->orderBy('placeholder_key')
            ->paginate(20);

        return view('admin.placeholder-mappings.index', compact('mappings'));
    }

    public function create(): View
    {
        // Oportunidades-pai
        $opportunities = ExternalOpportunity::query()
            ->whereNull('parent_id')
            ->where('published_registrations', true)
            ->orderBy('name')
            ->get(['id','name']);

        // Campos para a primeira oportunidade
        $firstId = $opportunities->first()->id ?? null;
        $phaseIds = $firstId
            ? ExternalOpportunity::query()
                ->where(function($q) use($firstId){
                    $q->where('id', $firstId)
                      ->orWhere('parent_id', $firstId);
                })
                ->where('id','!=', $firstId + 1)
                ->pluck('id')
                ->toArray()
            : [];

        $dynamicFields = $firstId
            ? ExternalRegistrationFieldConfiguration::query()
                ->whereIn('opportunity_id', $phaseIds)
                ->orderBy('opportunity_id')
                ->orderBy('display_order')
                ->get(['id','title'])
            : collect();

        return view('admin.placeholder-mappings.create', compact(
            'opportunities','dynamicFields'
        ));
    }

    public function store(StorePlaceholderMappingRequest $request): RedirectResponse
    {
        PlaceholderMapping::create($request->validated());
        return redirect()
            ->route('admin.placeholder-mappings.index')
            ->with('success', 'Mapeamento criado com sucesso.');
    }

    public function show(PlaceholderMapping $placeholderMapping): View
    {
        return view('admin.placeholder-mappings.show', compact('placeholderMapping'));
    }

    public function edit(PlaceholderMapping $placeholderMapping): View
    {
        $opportunities = ExternalOpportunity::query()
            ->whereNull('parent_id')
            ->where('published_registrations', true)
            ->orderBy('name')
            ->get(['id','name']);

        $parentId = $placeholderMapping->opportunity_id;
        $phaseIds = ExternalOpportunity::query()
            ->where(function($q) use($parentId){
                $q->where('id', $parentId)
                  ->orWhere('parent_id', $parentId);
            })
            ->where('id','!=', $parentId + 1)
            ->pluck('id')
            ->toArray();

        $dynamicFields = ExternalRegistrationFieldConfiguration::query()
            ->whereIn('opportunity_id', $phaseIds)
            ->orderBy('opportunity_id')
            ->orderBy('display_order')
            ->get(['id','title']);

        return view('admin.placeholder-mappings.edit', compact(
            'placeholderMapping',
            'opportunities',
            'dynamicFields'
        ));
    }

    public function update(UpdatePlaceholderMappingRequest $request, PlaceholderMapping $placeholderMapping): RedirectResponse
    {
        $placeholderMapping->update($request->validated());
        return redirect()
            ->route('admin.placeholder-mappings.index')
            ->with('success', 'Mapeamento atualizado com sucesso.');
    }

    public function destroy(PlaceholderMapping $placeholderMapping): RedirectResponse
    {
        $placeholderMapping->delete();
        return redirect()
            ->route('admin.placeholder-mappings.index')
            ->with('success', 'Mapeamento removido com sucesso.');
    }
}
