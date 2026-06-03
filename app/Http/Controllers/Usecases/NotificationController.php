<?php

namespace App\Http\Controllers\Usecases;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'project_id' => 'required|integer',
            'project_title' => 'nullable|string',
            'message' => 'required|string',
            'recipient_id' => 'nullable|integer',
        ]);

        $recipient = null;
        if ($request->filled('recipient_id')) {
            $recipient = User::find($request->input('recipient_id'));
            if (!$recipient) {
                return response()->json(['message' => 'Destinataire introuvable.'], 404);
            }
        }

        if (!$recipient) {
            $recipient = auth()->user();
        }

        $recipient->notify(new GenericNotification(
            $request->input('type'),
            $request->input('project_id'),
            $request->input('project_title'),
            $request->input('message')
        ));

        return response()->json(['message' => 'Notification envoyée.']);
    }

    public function getNotifications()
    {
        $user = auth()->user();
        $notifications = $user->unreadNotifications;

        return response()->json($notifications);
    }

    public function markAsRead($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marquée comme lue.']);
        }

        return response()->json(['message' => 'Notification introuvable.'], 404);
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }


}
