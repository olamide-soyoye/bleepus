<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Notification;
use App\Models\Professional;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Constants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use HttpResponses;
    // public function getProfessionalsWithinRange(Request $request){
    //     $distance = $request->distance;
    //     $rating = $request->rating;
    //     $experience = $request->experience;
    //     $availability = $request->availability;

    //     $loggedInUserId = Auth::id();
    //     $business = Business::where('user_id', $loggedInUserId)->first();
        
    //     if (!$business) {
    //         return $this->error('Error', 'Unable to find businesses', 400);
    //     }

    //     $maxDistance = $business->max_distance ? $business->max_distance : 2;

    //     $latitude = $business->latitude;
    //     $longitude = $business->longitude;

    //     $professionalsAround = Professional::withinDistanceOf(
    //         $latitude, 
    //         $longitude, 
    //         $maxDistance
    //     )->with('user', 'profile')->get();

    //     return $this->success([
    //         'professionalsAround' => $professionalsAround->isEmpty() ? [] : $professionalsAround,
    //     ], 200);
    // }
    public function getProfessionalsWithinRange(Request $request){ 
        $loggedInUserId = Auth::id();
        $business = Business::with("profile")->where('user_id', $loggedInUserId)->first();
        
        if (!$business) {
            return $this->error('Error', 'Unable to find businesses', 400);
        }
        
        $maxDistance = $business->max_distance ? $business->max_distance  : Constants::$defaultDistance;
        
        $latitude = (float)$business['profile']->latitude;
        $longitude = (float)$business['profile']->longitude;

        if ($request->has('distance')) {
            $distance = $request->input('distance');
            $rawQuery = "ST_Distance_Sphere(point(profiles.longitude, profiles.latitude),  point($longitude, $latitude))/". Constants::$mileConversion ."<= $distance";
        }else{
            $rawQuery = "ST_Distance_Sphere(point(profiles.longitude, profiles.latitude),  point($longitude, $latitude))/". Constants::$mileConversion ."<= $maxDistance";
        }

        $query = Professional::join('profiles', 'professionals.profile_id', '=', 'profiles.id')
        ->join('users', 'professionals.user_id', '=', 'users.id') 
        ->select(
            'professionals.*',
            'profiles.latitude',
            'profiles.longitude',
            // DB::raw('ROUND(ST_Distance_Sphere(point(profiles.longitude, profiles.latitude), point(?, ?)) / '. Constants::$mileConversion .', 2)
            DB::raw("ROUND((ST_Distance_Sphere(point(profiles.longitude, profiles.latitude), point($longitude, $latitude)) / ". Constants::$mileConversion ."),2) AS distance_between")
        )
        ->whereRaw($rawQuery)
        ->where('users.user_type_id', Constants::$professional) 
        ->where('professionals.status', Constants::$availableProfessional) 
        ->with('user', 'profile');
    
        // Check if the request has a rating parameter
        if ($request->has('rating')) {
            $rating = $request->input('rating');
            $query->where('ratings', '>=', $rating);
        }
    
        // Check if the request has an experience parameter
        if ($request->has('experience')) {
            $experience = $request->input('experience');
            $query->where('years_of_experience', '>=', $experience);
        }

        // Check if the request has an availability parameter
        if ($request->has('availability')) {
            $availability = $request->input('availability');
            $query->where('status', '=', $availability);
        }
    
        $filteredProfessionals = $query->get(); 
    
        return $this->success([
            'professionalsAround' => $filteredProfessionals->isEmpty() ? [] : $filteredProfessionals,
        ], 200);
    }
    

    // public function getHealthCareProvidersWithinRange(Request $request){
    //     $latitude = $request->latitude ?? null;
    //     $longitude = $request->longitude ?? null;

    //     $loggedInUserId = Auth::id();
    //     $professional = Professional::where('user_id', $loggedInUserId)->first();

    //     if (!$professional) {
    //         return $this->error('Error', 'Unable to find professional', 400);
    //     }

    //     $maxDistance = $professional->max_distance ? $professional->max_distance : 2;

    //     if (!$latitude || !$longitude) {
    //         $latitude = $professional->latitude;
    //         $longitude = $professional->longitude;
    //     }

    //     $businessesAround = Business::withinDistanceOf(
    //         $latitude, 
    //         $longitude,
    //         $maxDistance
    //     )->with('user', 'profile')->get();

    //     return $this->success([
    //         'businessesAround' => $businessesAround->isEmpty() ? [] : $businessesAround,
    //     ], 200);
    // }
    public function getHealthCareProvidersWithinRange(Request $request){
        $latitude = $request->latitude ?? null;
        $longitude = $request->longitude ?? null;

        $loggedInUserId = Auth::id();
        $professional = Professional::where('user_id', $loggedInUserId)->first();

        if (!$professional) {
            return $this->error('Error', 'Unable to find professional', 400);
        }
        //there are 1609.34metere in one mile
        $maxDistance = $professional->max_distance ? $professional->max_distance : Constants::$defaultDistance ;

        if (!$latitude || !$longitude) {
            $latitude = (float)$professional['profile']->latitude;
            $longitude = (float)$professional['profile']->longitude; 
        }

        if ($request->has('distance')) {
            $distance = $request->input('distance');
            $rawQuery = "ST_Distance_Sphere(point(profiles.longitude, profiles.latitude),  point($longitude, $latitude))/". Constants::$mileConversion ."<= $distance";
        }else{
            $rawQuery = "ST_Distance_Sphere(point(profiles.longitude, profiles.latitude),  point($longitude, $latitude))/". Constants::$mileConversion ."<= $maxDistance";
        }
    
        $query = Business::join('profiles', 'businesses.profile_id', 'profiles.id')
        ->join('users', 'businesses.user_id', '=', 'users.id') 
        ->select(
            'businesses.*',
            'profiles.latitude',
            'profiles.longitude',
            DB::raw("ROUND((ST_Distance_Sphere(point(profiles.longitude, profiles.latitude), point($longitude, $latitude)) / ". Constants::$mileConversion ."),2) AS distance_between")
        )
        ->whereRaw($rawQuery)
        ->where('users.user_type_id', Constants::$business) 
        ->with('user', 'profile');
    
        // Check if the request has a rating parameter
        if ($request->has('rating')) {
            $rating = $request->input('rating');
            $query->where('ratings', '>=', $rating);
        }

        // Check if the request has an availability parameter
        if ($request->has('availability')) {
            $availability = $request->input('availability');
            $query->where('status', '=', $availability);
        }
    
        $businessesAround = $query->get();
        // $businessesAround = $this->removeUnwantedKeys($businessesAround);
        
        return $this->success([
            'businessesAround' => $businessesAround->isEmpty() ? [] : $businessesAround,
        ], 200);
    }
    
    private function removeUnwantedKeys($businessesAround) {
        $responseData['data']['businessesAround'] = collect($businessesAround)->map(function ($business) {
            unset($business['profile']['total_earnings']);
            unset($business['profile']['pending_payment']);
            
            return $business;
        })->toArray();
    }

    public function getUserProfile($userId = null){
        $loggedInUserId = $userId ?? Auth::id();
        
        $profile = Profile::where("user_id",$loggedInUserId)
        ->with("business","professional","user")
        ->first();

        // return  $profile;


        if ($profile["user"]["user_type_id"] == Constants::$business) {
            unset($profile['professional']);
        }
        if ($profile["user"]["user_type_id"] == Constants::$professional) {
            unset($profile['business']);
        }

        if (!$profile) {
            return $this->error('Error', 'Unable to find profile', 400);
        }
        if (Auth::user()->user_type_id == Constants::$business){
            try {
                $unreadNotification = Notification::where('business_id', $profile['business']['id'])
                    ->where('read', false)
                    ->count();
            } catch (\Exception $e) {
                $unreadNotification = 0;
            }
        }
        if (Auth::user()->user_type_id == Constants::$professional) {
            
             try {
                $unreadNotification = Notification::where('professional_id',$profile['professional']['id'])
                ->where('read',false)
                ->whereNull('business_id')
                ->count();
            } catch (\Exception $e) {
                $unreadNotification = 0;
            }
        }

        return $this->success([
            'profile' => $profile ==null ? [] : $profile,
            'unreadMessages'=>$unreadNotification,
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
                        'longitude' => $request->longitude,
                        'latitude' => $request->latitude,
                    ]);
                }

                $userType = Auth::user()->user_type_id;

                if ($userType == Constants::$professional) {
                    if ($professional = Professional::where('user_id', $user_id)->first()) {
                        $professional->update([
                            'profession_title' => $request->profession_title,
                            'years_of_experience' => $request->years_of_experience,
                            'wage' => $request->wage,
                            'status' => $request->status,
                            'specialities' => json_encode($request->specialities)
                        ]);
                    }
                } elseif ($userType == Constants::$business) {
                    if ($business = Business::where('user_id', $user_id)->first()) {
                        $business->update([
                            'company_name' => $request->company_name,
                            'max_distance' => $request->max_distance, 
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
            return $this->error('Unable to update profile', $e, 400);
        }
    }

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
                $imageUrl = url('/images/' . basename($path));
                $profile->profile_pic = $imageUrl;
                $profile->save();  


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
                case Constants::$professional:
                    $user->deleteProfessionalAccount();
                    return $this->success(['message' => "User account deleted successfully"], 200);
                    break;
    
                case Constants::$business:
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
