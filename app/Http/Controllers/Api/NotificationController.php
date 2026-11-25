<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notes = $request->user()
            ->notifications()
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn ($note) => [
                'id' => $note->id,
                'title' => $note->data['title'] ?? '',
                'body' => $note->data['body'] ?? '',
                'url' => $note->data['url'] ?? null,
                'read_at' => $note->read_at,
                'created_at' => $note->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'unread' => $request->user()->unreadNotifications()->count(),
            'notifications' => $notes,
        ]);
    }
}
