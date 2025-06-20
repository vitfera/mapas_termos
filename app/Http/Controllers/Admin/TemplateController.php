<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(): View
    {
        $templates = Template::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.templates.create');
    }

    public function store(StoreTemplateRequest $request): RedirectResponse
    {
        Template::create($request->validated());
        return redirect()
            ->route('admin.templates.index')
            ->with('success', 'Template criado com sucesso.');
    }

    public function show(Template $template): View
    {
        return view('admin.templates.show', compact('template'));
    }

    public function edit(Template $template): View
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(UpdateTemplateRequest $request, Template $template): RedirectResponse
    {
        $template->update($request->validated());
        return redirect()
            ->route('admin.templates.index')
            ->with('success', 'Template atualizado com sucesso.');
    }

    public function destroy(Template $template): RedirectResponse
    {
        $template->delete();
        return redirect()
            ->route('admin.templates.index')
            ->with('success', 'Template removido com sucesso.');
    }
}
