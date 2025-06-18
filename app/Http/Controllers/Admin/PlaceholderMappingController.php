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
        $mappings = PlaceholderMapping::with('placeholder')
            ->orderBy('template_placeholder_id')
            ->paginate(20);

        return view('admin.placeholder-mappings.index', compact('mappings'));
    }

    public function create(): View
    {
        // 2) só as oportunidades-pai publicadas
        $opportunities = ExternalOpportunity::query()
            ->whereNull('parent_id')
            ->where('published_registrations', true)
            ->orderBy('name')
            ->get(['id','name']);

        // 3) pegue os campos da 1ª oportunidade (para popular no load inicial)
        $firstId = $opportunities->first()->id ?? null;
        $phaseIds = $firstId
            ? ExternalOpportunity::query()
                ->where(function($q) use($firstId){
                $q->where('id',$firstId)
                    ->orWhere('parent_id',$firstId);
                })
                ->where('id','!=', $firstId+1)     // mesma lógica do código Node
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
        'placeholders','opportunities','dynamicFields'
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

        // 2) só as oportunidades-pai publicadas
        $opportunities = ExternalOpportunity::query()
            ->whereNull('parent_id')
            ->where('published_registrations', true)
            ->orderBy('name')
            ->get(['id','name']);

        // 3) identifica a oportunidade selecionada no mapeamento
        $parentId = $placeholderMapping->opportunity_id;

        // 4) determina fases-filhas relevantes (pai + filhos, excluindo parentId+1)
        $phaseIds = ExternalOpportunity::query()
            ->where(function($q) use($parentId) {
                $q->where('id', $parentId)
                ->orWhere('parent_id', $parentId);
            })
            ->where('id', '!=', $parentId + 1)
            ->pluck('id')
            ->toArray();

        // 5) carrega todos os campos dinâmicos dessas fases
        $dynamicFields = ExternalRegistrationFieldConfiguration::query()
            ->whereIn('opportunity_id', $phaseIds)
            ->orderBy('opportunity_id')
            ->orderBy('display_order')
            ->get(['id','title']);

        return view('admin.placeholder-mappings.edit', compact(
            'placeholderMapping',
            'placeholders',
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
