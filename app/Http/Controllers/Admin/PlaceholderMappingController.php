<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlaceholderMappingRequest;
use App\Http\Requests\UpdatePlaceholderMappingRequest;
use App\Models\PlaceholderMapping;
use App\Models\TemplatePlaceholder;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
        $placeholders = TemplatePlaceholder::all();
        $opportunities = Opportunity::orderBy('name')->get();
        $sourceTypes = ['meta' => 'Meta', 'registration' => 'Registration', 'agent' => 'Agent'];

        return view('admin.placeholder-mappings.create', compact(
            'placeholders',
            'opportunities',
            'sourceTypes'
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
        $placeholders = TemplatePlaceholder::all();
        $opportunities = Opportunity::orderBy('name')->get();
        $sourceTypes = ['meta' => 'Meta', 'registration' => 'Registration', 'agent' => 'Agent'];

        return view('admin.placeholder-mappings.edit', compact(
            'placeholderMapping',
            'placeholders',
            'opportunities',
            'sourceTypes'
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
