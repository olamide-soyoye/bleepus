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
use App\Models\EmailVerification;
use App\Models\Notification;
use App\Models\Profile;
use App\Traits\HttpResponses;
use Constants;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;
use Illuminate\Support\Str;

// Swagger Link:  http://127.0.0.1:8000/api/documentation#/Register/Register
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

    public function register(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator !== true) {
            return $validator;
        }

        try {
            DB::beginTransaction();

            // Check if the email already exists
            if (User::where('email', $request->email)->exists()) {
                return $this->error('Validation Error', 'Email already exists', 422);
            }

            $user = User::create([
                'fname' => $request->fname,
                'lname' => $request->lname,
                'email' => $request->email,
                'user_type_id' => $request->user_type_id,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('API TOKEN')->plainTextToken;

            $profile = Profile::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'total_earnings' => 0,
                'pending_payment' => 0,
                'phone_no' => $request->phone_no,
                'agency_code' => $request->agency_code,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
            ]);

            $additionalData = ['profile' => $profile];

            if ($request->user_type_id === Constants::$professional) {
                $professional = Professional::create([
                    'user_id' => $user->id,
                    'profile_id' => $profile->id,
                ]);

                $additionalData['professional'] = $professional;
            } elseif ($request->user_type_id === Constants::$business) {
                $business = Business::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'profile_id' => $profile->id,
                ]);

                $additionalData['business'] = $business;
            }

            // DB::commit();
            $createOtp = $this->createOtp($user->id);

            if ($createOtp === 'OTP Not Created') {
                DB::rollBack();
                return $this->error('Error', 'OTP Not Created', 500);
            }

            $sendOtp= $this->sendOtp($user, $createOtp);

            if ($sendOtp !== true) {
                DB::rollBack();
                return $sendOtp; //$this->error('Error', 'Error sending OTP email', 500);
            }

            DB::commit(); 

            return $this->success([
                'user' => $user,
                'additional_data' => $additionalData,
                'token' => $token,
            ], 201);
            
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error('Error', "An error occurred: " . $e->getMessage(), 500);
        }
    } 

    private function createOtp($userId) {
        $otp = random_int(100000, 999999);
        $expiry = now()->addMinutes(30);

        $verification = EmailVerification::firstOrNew(['user_id' => $userId]);
        $verification->otp = Hash::make($otp);
        $verification->expiry = $expiry;

        if ($verification->save()) {
            return $otp;
        }

        return "OTP Not Created";
    } 
    
    private function sendOtp($user, $otp) {
        try {
            Mail::to($user->email)->send(new \App\Mail\VerificationEmail($user->fname, $otp));
            return true;
        } catch (\Exception $e) { 
            return $e;
        }
    }

    
    public function resendOtp(Request $request) { 
        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error('Error', 'User not found', 400);
        }

        $otp = $this->createAndSendOtp($user);

        if ($otp === 'OTP Not Created') {
            return $this->error('Error', $otp, 400);
        }

        if ($otp === 'OTP not sent. Retry') {
            return $this->error('Error', $otp, 400);
        }

        return $this->success(['message' => 'Email Verification Email Sent Successfully'], 200);
    }

    private function createAndSendOtp(User $user) {
        try {
            $otp = $this->createOtp($user->id);
            // return $otp;
            if ($otp === 'OTP Not Created') {
                return $otp;
            }

            $sendOtp = $this->sendOtp($user, $otp);
            // return $sendOtp;
            if (!$sendOtp) {
                return 'OTP not sent. Retry!';
            }
            // Indicates success
            return null;
        } catch (\Exception $e) {
            // Handle exceptions (e.g., log, report, etc.)
            return 'An error occurred during OTP creation and sending.';
        }
    }



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
            return $this->error('Error', "Email or Password does not match with our record.", 500);
            // return response()->json('Email or Password does not match with our record.');
        }

        $user = User::with("business","professional","profile")->where('email', $request->email)->first();
        if ($user->user_type_id == Constants::$business) {
            unset($user['professional']);
        }
        if ($user->user_type_id == Constants::$professional) {
            unset($user['business']);
        }
        if ($user) {
            // return $user['business']['id'];
            if ($user->user_type_id == Constants::$business){
                try {
                    $unreadNotification = Notification::where('business_id', $user['business']['id'])
                        ->where('read', false)
                        ->count();
                } catch (\Exception $e) {
                    $unreadNotification = 0;
                }
            }
            if ($user->user_type_id == Constants::$professional) {
                
                 try {
                    $unreadNotification = Notification::where('professional_id',$user['professional']['id'])
                    ->where('read',false)
                    ->whereNull('business_id')
                    ->count();
                } catch (\Exception $e) {
                    $unreadNotification = 0;
                }
            }

            return response()->json(
                [
                    'user' => $user, 
                    'unreadMessages'=>$unreadNotification,
                    'access_token' => $user->createToken("API TOKEN")->plainTextToken,
                    'message' => 'Logged In Successfully'
                ]
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
        ],200);
    }
    
    

    public function getLocationsAround(){
        $merchantALongitude = 3.9213518654370594; 
        $merchantALatitude = 6.83631976776148; // Ibadan garage or fontana

        // Gets all jobs within 2 miles of the given latitude and longitude (professional)
        // $jobs = User::withinDistanceOf(4.033783866199626, 6.778648608484787, 2)->get();
        // Gets all jobs within 2 miles of the given latitude and longitude (professional)
        // return $jobs;
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
