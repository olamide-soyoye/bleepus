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

        if ($userType == Constants::$business) {
            $userLoggedIn = Business::where('user_id', $userId)->first()->id;
            if ($userLoggedIn === null) {
                return $this->error('Error', 'User type not recognized', 400);
            }
            //set Notification as Read
            $updateToRead = Notification::where('business_id', $userLoggedIn)->update(['read'=> true]);
            if ($updateToRead) {
                $notifications = Notification::with('professional')->where('business_id', $userLoggedIn)
                ->get();
            }
        }else{
            $userLoggedIn = Professional::where('user_id', $userId)->first()->id;
            if ($userLoggedIn === null) {
                return $this->error('Error', 'User type not recognized', 400);
            }
            $updateToRead = Notification::where('professional_id', $userLoggedIn)
            ->whereNull('business_id')
            ->update(['read'=> true]);
            if ($updateToRead) {
                $notifications = Notification::where('professional_id', $userLoggedIn)
                ->whereNull('business_id')
                ->select('id','professional_id','subject','body','read','job_id','created_at','updated_at')
                ->get();
            }
            
        }

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

    public function readNotification($notificationId = null) {
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
