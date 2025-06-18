<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplatePlaceholderRequest;
use App\Http\Requests\UpdateTemplatePlaceholderRequest;
use App\Models\Template;
use App\Models\TemplatePlaceholder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TemplatePlaceholderController extends Controller
{
    public function index(): View
    {
        $placeholders = TemplatePlaceholder::with('template')
            ->orderBy('template_id')
            ->paginate(20);

        return view('admin.template-placeholders.index', compact('placeholders'));
    }

    public function create(): View
    {
        $templates = Template::all();
        return view('admin.template-placeholders.create', compact('templates'));
    }

    public function store(StoreTemplatePlaceholderRequest $request): RedirectResponse
    {
        TemplatePlaceholder::create($request->validated());
        return redirect()
            ->route('admin.template-placeholders.index')
            ->with('success', 'Placeholder criado com sucesso.');
    }

    public function show(TemplatePlaceholder $templatePlaceholder): View
    {
        return view('admin.template-placeholders.show', compact('templatePlaceholder'));
    }

    public function edit(TemplatePlaceholder $templatePlaceholder): View
    {
        $templates = Template::all();
        return view('admin.template-placeholders.edit', compact('templatePlaceholder', 'templates'));
    }

    public function update(UpdateTemplatePlaceholderRequest $request, TemplatePlaceholder $templatePlaceholder): RedirectResponse
    {
        $templatePlaceholder->update($request->validated());
        return redirect()
            ->route('admin.template-placeholders.index')
            ->with('success', 'Placeholder atualizado com sucesso.');
    }

    public function destroy(TemplatePlaceholder $templatePlaceholder): RedirectResponse
    {
        $templatePlaceholder->delete();
        return redirect()
            ->route('admin.template-placeholders.index')
            ->with('success', 'Placeholder removido com sucesso.');
    }
}
