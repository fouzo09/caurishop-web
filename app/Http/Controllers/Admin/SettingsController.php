<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index(Request $request): View
    {
        $grouped     = $this->settingService->grouped();
        $activeGroup = $request->get('group', 'general');

        if (!array_key_exists($activeGroup, $grouped)) {
            $activeGroup = 'general';
        }

        return view('admin.settings.index', compact('grouped', 'activeGroup'));
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        $allowed = ['general', 'credit', 'stock', 'notifications'];

        if (!in_array($group, $allowed)) {
            abort(404);
        }

        $this->settingService->updateGroup($group, $request->all());

        // Log
        app(\App\Services\Admin\ActivityLogService::class)->log(
            'settings_updated',
            'Paramètres mis à jour — groupe : ' . $group,
        );

        return redirect()->route('admin.settings.index', ['group' => $group])
            ->with('success', 'Paramètres enregistrés avec succès.');
    }
}
