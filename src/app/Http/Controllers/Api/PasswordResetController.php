<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    use HttpResponses;

    public function __invoke(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['status' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    // public function resetPassword(Request $request){
    //     $request->validate([
    //         'token' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required|confirmed|min:8',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         return $this->error('Error', 'User not found', 404);
    //     }

    //     if ($user->reset_token !== $request->token) {
    //         return $this->error('Error', 'Invalid token', 401);
    //     }

    //     $user->password = Hash::make($request->password);
    //     $user->save();

    //     $user->update(['reset_token' => null]);

    //     return $this->success([
    //         'message' => 'Password reset successful'
    //     ], 200);
    // }


}
