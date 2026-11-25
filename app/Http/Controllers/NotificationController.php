<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notes' => $request->user()->notifications()->latest()->paginate(15),
        ]);
    }

    public function poll(Request $request): JsonResponse
    {
        $latest = $request->user()
            ->unreadNotifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DatabaseNotification $note) => [
                'id' => $note->id,
                'title' => $note->data['title'] ?? 'መልዕክት',
                'body' => $note->data['body'] ?? '',
            ]);

        return response()->json([
            'unread' => $request->user()->unreadNotifications()->count(),
            'latest' => $latest,
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $note = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $note->markAsRead();

        return redirect($this->target($note));
    }

    private function target(DatabaseNotification $note): string
    {
        $url = (string) ($note->data['url'] ?? '');

        if ($url !== '' && str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        return route('notifications.index');
    }
}
