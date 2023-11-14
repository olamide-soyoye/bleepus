<?php

// namespace App\Http\Controllers\Api;
namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PhpParser\Parser\Tokens;
use App\Enums\TokenAbility;
use App\Models\Business;
use App\Models\Professional;
use App\Http\Requests\RegisterRequest;
// use App\Http\Requests\StoreUserRequest;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
// use App\Http\Controllers\Api\ValidationException;


class AuthController extends Controller
{
    use HttpResponses;
/**
 * @OA\Post(
 *     path="/api/auth/register",
 *     operationId="Register",
 *     summary="Register a new user",
 *     description="Register a new user",
 *     tags={"Register"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"fname", "lname", "email", "password"},
 *                 @OA\Property(
 *                     property="fname",
 *                     type="string",
 *                     description="The user's first name"
 *                 ),
 *                 @OA\Property(
 *                     property="lname",
 *                     type="string",
 *                     description="The user's last name"
 *                 ),
 *                 @OA\Property(
 *                     property="email",
 *                     type="string",
 *                     format="email",
 *                     description="The user's email address"
 *                 ),
 *                  @OA\Property(
 *                     property="user_type_id",
 *                     type="integer",
 *                     description="User type Id"
 *                 ),
 *                  @OA\Property(
 *                     property="longitude",
 *                     type="string",
 *                     description="User Longitude"
 *                 ),
 *                  @OA\Property(
 *                     property="latitude",
 *                     type="string",
 *                     description="User latitude"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     type="string",
 *                     minLength=8,
 *                     description="The user's password"
 *                 ),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response="201",
 *         description="A successful response.",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response="422",
 *         description="Unprocessable Entry",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Bad Request",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response="404",
 *         description="Resource not found",
 *         @OA\JsonContent()
 *     )
 * )
 */



    public function register(Request $request) { 

        $validator = $this->validator($request);
        if ($validator !== true){
            return $validator;
        }
        
        $user = User::create([
            'fname' => $request->fname,
            'lname' => $request->lname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $additionalData = [];

        if ($user) {
            $token = $user->createToken('API TOKEN')->plainTextToken;
            if ($request->user_type_id === 1 ) {
                $professional = Professional::create([
                    'user_id' => $user->id,
                    'longitude' => $request->longitude,
                    'latitude' => $request->latitude, 
                ]);
                $additionalData = ['professional' => $professional];
            }

            if ($request->user_type_id === 2 ) {
                $business = Business::create([
                    'user_id' => $user->id,
                    'longitude' => $request->longitude,
                    'latitude' => $request->latitude, 
                    'company_name' => $request->company_name, 
                ]);
                $additionalData = ['business' => $business];
            }
    
            return $this->success([
                'user' => $user,
                'additional_data' => $additionalData,
                'token' => $token,
            ],201);
            
        } else {
            return $this->error('Error', "User registration failed", 400);
        }
    }
    // public function register(RegisterRequest $request){
    //     return $request;
    //     $validatedData = $request->validated();

    //     return DB::transaction(function () use ($validatedData) {
    //         $user = User::create([
    //             'fname' => $validatedData['fname'],
    //             'lname' => $validatedData['lname'],
    //             'email' => $validatedData['email'],
    //             'password' => Hash::make($validatedData['password']),
    //         ]);

    //         if (!$user) {
    //             return $this->error('Error', "User registration failed", 400);
    //         }
    //         return $user;
    //         $token = $user->createToken('API TOKEN')->plainTextToken;
    //         $additionalData = null;

    //         if ($validatedData['user_type_id'] === 1) {
    //             $professional = Professional::create([
    //                 'user_id' => $user->id,
    //                 'longitude' => $validatedData['longitude'],
    //                 'latitude' => $validatedData['latitude'],
    //             ]);

    //             $additionalData = ['professional' => $professional];
    //         }

    //         if ($validatedData['user_type_id'] === 2) {
    //             $business = Business::create([
    //                 'user_id' => $user->id,
    //                 'longitude' => $validatedData['longitude'],
    //                 'latitude' => $validatedData['latitude'],
    //                 'company_name' => $validatedData['company_name'],
    //             ]);

    //             $additionalData = ['business' => $business];
    //         }

    //         return $this->success([
    //             'user' => $user,
    //             'additional_data' => $additionalData,
    //             'token' => $token,
    //         ], 201);
    //     });
    // }



    private function validator($request){
        try {
            $request->validate([
                'fname' => ['required', 'string', 'max:255'],
                'lname' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email:rfc', 'unique:users'],
                'password' => ['required', 'min:8'],
            ]);
            return true;
        } catch (ValidationException $exception) {
            $errors = [];
    
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[$field] = $message;
                }
            }
    
            return $this->error($errors, "User registration failed", 400);
        }
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => ['required'],
        ]);
        if(!Auth::attempt($request->only(['email', 'password']))){
            return response()->json('Email or Password does not match with our record.');
        }

        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            return response()->json(
                    [
                        'user' => $user, 
                        'access_token' => $user->createToken("API TOKEN")->plainTextToken,
                        'message' => 'Logged In Successfully'
                    ], 
                );
        } else {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    public function logout() {
        Auth::user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return $this->success([
            'message' => 'Successfully logged out',
        ]);
        // return response()->json(['message' => 'Successfully logged out']);
    }
    
    public function refreshToken(Request $request)
{
    return $request;
    // $refreshToken = $request->input('refresh_token');

    // // Validate the refresh token
    // $refreshToken = Tokens::where('token', $refreshToken)->first();

    // if (!$refreshToken || $refreshToken->isExpired()) {
    //     return response()->json(['message' => 'Invalid refresh token'], 401);
    // }

    // // Generate a new access token
    // $accessToken = $refreshToken->user->createToken('API TOKEN')->accessToken;

    // // Revoke the old access token
    // $refreshToken->revoke();

    // // Return the new access token
    // return response()->json([
    //     'access_token' => $accessToken->plainTextToken,
    //     'expires_at' => $accessToken->expires_at,
    // ]);
}

    public function getLocationsAround(){
        $merchantALongitude = 3.9213518654370594; 
        $merchantALatitude = 6.83631976776148; // Ibadan garage or fontana

        // Gets all jobs within 2 miles of the given latitude and longitude (professional)
        $jobs = User::withinDistanceOf(4.033783866199626, 6.778648608484787, 2)->get();
        // Gets all jobs within 2 miles of the given latitude and longitude (professional)
        return $jobs;
        // $merchants = User::all(); // Assuming you have a Merchant model

        // $filteredMerchants = $merchants->filter(function ($merchant) use ($merchantALatitude, $merchantALongitude) {
        //     $merchantLatitude = $merchant->latitude; // Replace with actual latitude column name
        //     $merchantLongitude = $merchant->longitude; // Replace with actual longitude column name

        //     $distance = Distance::between(
        //         [$merchantLatitude, $merchantLongitude],
        //         [$merchantALatitude, $merchantALongitude]
        //     )->in('miles');

        //     return $distance <= 3;
        // });
    }
}
