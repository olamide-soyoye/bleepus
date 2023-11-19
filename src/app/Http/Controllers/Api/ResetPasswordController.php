<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResetPasswordController
{
    public function reset(Request $request): JsonResponse
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($response)
            : $this->sendResetFailedResponse($request, $response);
    }

    protected function broker()
    {
        return Password::broker();
    }

    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);
        $user->save();
    }

    protected function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }

    protected function validationErrorMessages(): array
    {
        return [];
    }

    protected function sendResetResponse($response): JsonResponse
    {
        return response()->json(['message' => trans($response)], 200);
    }

    protected function sendResetFailedResponse(Request $request, $response): JsonResponse
    {
        return response()->json(['error' => trans($response)], 422);
    }
}
