<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Professional;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use HttpResponses;
    public function getProfessionalsWithinRange(Request $request){
        // $latitude = $request->latitude ?? null;
        // $longitude = $request->longitude ?? null;

        $loggedInUserId = Auth::id();
        $business = Business::where('user_id', $loggedInUserId)->first();
        
        if (!$business) {
            return $this->error('Error', 'Unable to find businesses', 400);
        }

        $maxDistance = $business->max_distance ? $business->max_distance : 2;

        // if (!$latitude || !$longitude) {
            $latitude = $business->latitude;
            $longitude = $business->longitude;
        // }

        $professionalsAround = Professional::withinDistanceOf(
            $latitude, 
            $longitude, 
            $maxDistance
        )->with('user', 'profile')->get();

        return $this->success([
            'professionalsAround' => $professionalsAround->isEmpty() ? [] : $professionalsAround,
        ], 200);
    }

    public function getHealthCareProvidersWithinRange(Request $request){
        $latitude = $request->latitude ?? null;
        $longitude = $request->longitude ?? null;

        $loggedInUserId = Auth::id();
        $professional = Professional::where('user_id', $loggedInUserId)->first();

        if (!$professional) {
            return $this->error('Error', 'Unable to find professional', 400);
        }

        $maxDistance = $professional->max_distance ? $professional->max_distance : 2;

        if (!$latitude || !$longitude) {
            $latitude = $professional->latitude;
            $longitude = $professional->longitude;
        }

        $businessesAround = Business::withinDistanceOf(
            $latitude, 
            $longitude,
            $maxDistance
        )->with('user', 'profile')->get();

        return $this->success([
            'businessesAround' => $businessesAround->isEmpty() ? [] : $businessesAround,
        ], 200);
    }

    public function getUserProfile(){
        $loggedInUserId = Auth::id();
        
        $profile = Profile::where("user_id",$loggedInUserId)
        ->with("business","professional","user")
        ->first();

        if (Auth::user()->user_type_id == 2) {
            unset($profile['professional']);
        }
        if (Auth::user()->user_type_id == 1) {
            unset($profile['business']);
        }

        if (!$profile) {
            return $this->error('Error', 'Unable to find profile', 400);
        }

        return $this->success([
            'profile' => $profile ==null ? [] : $profile,
        ], 200);
    }

    public function userTypes(){
        return $this->success([
            UserType::all(),
        ], 200);
    }
    
    public function updateUserProfile(Request $request){
        try {
            $user_id = Auth::id();

            DB::transaction(function () use ($request, $user_id) {
                if ($profile = Profile::where('user_id', $user_id)->first()) {
                    $profile->update([
                        'address' => $request->address,
                        'profile_pic' => $request->profile_pic,
                        'phone_no' => $request->phone_no,
                        'about' => $request->about,
                        'country_abbr' => $request->countryAbbr,
                        'country_code' => $request->countryCode,
                        'max_distance' => $request->max_distance,
                    ]);
                }

                $userType = Auth::user()->user_type_id;

                if ($userType == 1) {
                    if ($professional = Professional::where('user_id', $user_id)->first()) {
                        $professional->update([
                            'profession_title' => $request->profession_title,
                            'years_of_experience' => $request->years_of_experience,
                            'wage' => $request->wage,
                            'status' => $request->status,
                            'specialities' => json_encode($request->specialities),
                            'longitude' => $request->longitude,
                            'latitude' => $request->latitude,
                        ]);
                    }
                } elseif ($userType == 2) {
                    if ($business = Business::where('user_id', $user_id)->first()) {
                        $business->update([
                            'company_name' => $request->company_name,
                            'max_distance' => $request->max_distance,
                            'longitude' => $request->longitude,
                            'latitude' => $request->latitude,
                        ]);
                    }
                }

                if ($user = User::where('id', $user_id)->first()) {
                    $user->update([
                        'fname' => $request->fname,
                        'lname' => $request->lname,
                    ]);
                }
            });

            return $this->success([
                'message' => 'Profile updated successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->error('Unable to update profile', $e, 404);
        } catch (\Exception $e) {
            return $this->error('Unable to update profile', $e, 500);
        }
    }

    // public function updateProfilePicture(Request $request){
    //     $user_id = Auth::id();

    //     if ($profile = Profile::where('user_id', $user_id)->first()) {
    //         $request->validate([
    //             'profile_pic' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         ]);

    //         if ($request->hasFile('profile_pic')) {
    //             // $path = $request->file('profile_pic')->store('public/images');
    //             $path = $request->file('profile_pic')->store('public/images');
                // $profile->profile_pic = $path;
                // $profile->update();

    //             // $imageUrl = Storage::url($path);
    //             $imageUrl = url('/storage/images/' . basename($path));

    //             return $this->success([
    //                 'message' => 'Profile picture updated successfully',
    //                 'image_url' => $imageUrl,
    //             ], 200);
    //         }

    //         return $this->error('Error', 'Unable to update profile picture', 400);
    //     }
    // }
    public function updateProfilePicture(Request $request) {
        $user_id = Auth::id();

        $profile = Profile::where('user_id', $user_id)->first();
        if ($profile) {
            $request->validate([
                'profile_pic' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('profile_pic')) {
                $imageName = $request->file('profile_pic')->getClientOriginalName();
                $request->file('profile_pic')->move(public_path('images'), $imageName);

                $path = 'images/' . $imageName;
                $profile->profile_pic = $path;
                $profile->save(); 
                // if ($profile->update()) {
                //     return 'iiiiii';
                // }

                $imageUrl = url('/images/' . basename($path));

                return $this->success([
                    'message' => "Profile picture updated successfully $profile->id",
                    'image_url' => $imageUrl,
                ], 200);
            }

            return $this->error('Error', 'Unable to update profile picture', 400);
        }
    }

    


    public function deleteUserAccount() {
        $userId = Auth::id();
        $user = User::find($userId);
    
        if ($user) {
    
            switch (Auth::user()->user_type_id) {
                case 1:
                    $user->deleteProfessionalAccount();
                    return $this->success(['message' => "User account deleted successfully"], 200);
                    break;
    
                case 2:
                    $user->deleteBusinessAccount();
                    return $this->success(['message' => "User account deleted successfully"], 200);
                    break;
            }
    
        }
    
        return $this->error('Error', 'Unable to delete user', 400);
    }

    public function test(){
        return ["Hello Dev Sam!!","I am up and running"];
    }

}
