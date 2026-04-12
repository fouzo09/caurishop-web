<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(): View
    {
        $notifications = $this->notificationService->forUser(auth()->id(), 30);
        $unreadCount   = $this->notificationService->unreadCount(auth()->id());

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(int $id): RedirectResponse
    {
        $this->notificationService->markRead($id, auth()->id());

        return redirect()->back();
    }

    public function markAllRead(): RedirectResponse
    {
        $this->notificationService->markAllRead(auth()->id());

        return redirect()->back()->with('success', 'Toutes les notifications marquées comme lues.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->notificationService->delete($id, auth()->id());

        return redirect()->back()->with('success', 'Notification supprimée.');
    }

    /**
     * API JSON pour le dropdown du header.
     */
    public function unreadJson(): JsonResponse
    {
        $userId        = auth()->id();
        $unreadCount   = $this->notificationService->unreadCount($userId);
        $recent        = $this->notificationService->recentUnread($userId, 5);

        return response()->json([
            'count'         => $unreadCount,
            'notifications' => $recent->map(fn ($n) => [
                'id'      => $n->id,
                'title'   => $n->title,
                'message' => $n->message,
                'icon'    => $n->icon,
                'color'   => $n->color,
                'link'    => $n->link,
                'ago'     => $n->created_at->diffForHumans(),
            ]),
        ]);
    }
}
