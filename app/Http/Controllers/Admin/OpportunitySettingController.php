<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOpportunitySettingRequest;
use App\Models\OpportunitySetting;
use App\Models\ExternalOpportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OpportunitySettingController extends Controller
{
    public function index(): View
    {
        $settings = OpportunitySetting::with('opportunity')
            ->orderBy('opportunity_id')
            ->paginate(15);
        return view('admin.opportunity-settings.index', compact('settings'));
    }

    // Sincroniza editais sem formulário manual
    public function sync(): RedirectResponse
    {
        $external = ExternalOpportunity::whereNull('parent_id')
            ->where('published_registrations', true)
            ->pluck('id');

        $created = 0;
        foreach ($external as $oppId) {
            $setting = OpportunitySetting::firstOrCreate(
                ['opportunity_id' => $oppId],
                ['category' => 'execucao', 'start_number' => 1]
            );
            if ($setting->wasRecentlyCreated) {
                $created++;
            }
        }

        return redirect()
            ->route('admin.opportunity-settings.index')
            ->with('success', 'Sincronizados ' . $created . ' editais.');
    }

    public function edit(OpportunitySetting $opportunitySetting): View
    {
        return view('admin.opportunity-settings.edit', compact('opportunitySetting'));
    }

    public function update(UpdateOpportunitySettingRequest $request, OpportunitySetting $opportunitySetting): RedirectResponse
    {
        $opportunitySetting->update($request->validated());
        return redirect()
            ->route('admin.opportunity-settings.index')
            ->with('success', 'Configuração atualizada com sucesso.');
    }

    public function destroy(OpportunitySetting $opportunitySetting): RedirectResponse
    {
        $opportunitySetting->delete();
        return redirect()
            ->route('admin.opportunity-settings.index')
            ->with('success', 'Configuração removida.');
    }
}