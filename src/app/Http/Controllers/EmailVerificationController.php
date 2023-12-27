<?php

namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use DateTime;

class EmailVerificationController extends Controller
{
    use HttpResponses;

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);
        $user_id = $request->user_id;

        $user = User::where('id',$user_id)->first();
        $emailVerification = EmailVerification::where(['user_id' => $user_id, 'used'=>false])->first();
        // return $emailVerification
        if (!$emailVerification) {
            return $this->error('Error', 'OTP no longer active', 400);
        }
        $originalDateTimeString = now();

        $dateTime = new DateTime($originalDateTimeString);
        $now = $dateTime->format('Y-m-d H:i:s');

        if (Hash::check($request->otp, $emailVerification->otp)) { 

            if ($emailVerification->expiry >= $now ) {
                $user->update(['email_verified_at' => $now]);
                $emailVerification->update(['used'=>true]);

                return $this->success([
                    'message' => 'Email Verification Successful',
                ], 200);
            }
            return $this->error('Error', 'OTP has expired', 400);
        }

        return $this->error('Error', 'Incorrect OTP', 400);
    }

}