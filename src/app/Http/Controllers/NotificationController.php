<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Notification;
use App\Models\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HttpResponses;
use Constants;

class NotificationController extends Controller
{
    use HttpResponses;
    
    public function getAllNotifications() {
        $userId = Auth::id();
        $userType = Auth::user()->user_type_id;

        $userLoggedIn = ($userType == Constants::$business)
            ? Business::where('user_id', $userId)->first()->id
            : ($userType == Constants::$professional
                ? Professional::where('user_id', $userId)->first()->id
                : null);

        if ($userLoggedIn === null) {
            return $this->error('Error', 'User type not recognized', 400);
        }

        $notifications = Notification::where('business_id', $userLoggedIn)->get();

        return $this->success([
            'data' => $notifications ?? [],
        ], 200);
    }

    public function showSingleNotification($notificationId = null) {
        if (!$notificationId) {
            return $this->error('Error', 'Please send a Notification Id', 400);
        }

        $notification = Notification::find($notificationId);

        if (!$notification) {
            return $this->error('Error', 'Notification not found', 404);
        }

        $read = $notification->update(['read' => true]);

        if (!$read) {
            return $this->error('Error', 'Failed to mark notification as read', 500);
        }

        return $this->success([
            'data' => $notification,
        ], 200);
    }

}
