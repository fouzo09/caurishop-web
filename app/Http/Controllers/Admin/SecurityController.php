<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'action'    => $request->get('action'),
            'user_id'   => $request->get('user_id'),
            'date_from' => $request->get('date_from'),
            'date_to'   => $request->get('date_to'),
            'search'    => $request->get('search'),
        ];

        $logs             = $this->activityLogService->paginate(30, $filters);
        $activeSessions   = $this->activityLogService->getActiveSessions();
        $recentFailed     = $this->activityLogService->getRecentFailedLogins(10);
        $availableActions = $this->activityLogService->availableActions();
        $users            = User::orderBy('name')->get(['id', 'name']);
        $activeTab        = $request->get('tab', 'logs');

        return view('admin.security.index', compact(
            'logs',
            'activeSessions',
            'recentFailed',
            'availableActions',
            'users',
            'filters',
            'activeTab',
        ));
    }
}
